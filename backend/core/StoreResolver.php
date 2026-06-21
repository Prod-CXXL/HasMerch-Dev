<?php

class StoreResolver {

    /**
     * Extract subdomain and root-site flag from HTTP_HOST.
     *
     * Production:  cxxl.hasmerch.com  → subdomain=cxxl,  isRootSite=false
     *              hasmerch.com        → subdomain=hasmerch, isRootSite=true
     *
     * Local XAMPP: cxxl.localhost      → subdomain=cxxl,  isRootSite=false
     *              localhost           → subdomain=hasmerch, isRootSite=true
     *              hasmerch.localhost  → subdomain=hasmerch, isRootSite=true
     *
     * The previous code split on '.' and used count($parts) to decide.
     * cxxl.localhost splits into ['cxxl','localhost'] — 2 parts — which
     * matched the count===2 root-site branch, hardcoding subdomain='hasmerch'
     * and ignoring 'cxxl' entirely. That was Bug 1.
     */
    public static function resolve(): array
    {
        $host        = strtolower(trim($_SERVER['HTTP_HOST']));
        $isLocalhost = str_contains($host, 'localhost') || str_contains($host, '127.0.0.1');
        $parts       = explode('.', $host);

        $subdomain  = null;
        $isRootSite = false;

        if ($isLocalhost) {
            /*
             * Local patterns:
             *   'localhost'            → 1 part  → root site
             *   '127.0.0.1'           → 1 part  → root site
             *   'hasmerch.localhost'  → 2 parts, first part = 'hasmerch' → root site
             *   'cxxl.localhost'      → 2 parts, first part ≠ 'hasmerch' → subdomain
             *   'cxxl.hasmerch.localhost' → 3 parts → subdomain (future)
             */
            if (count($parts) === 1) {
                // bare 'localhost' or '127.0.0.1'
                $isRootSite = true;
                $subdomain  = 'hasmerch';
            } elseif (count($parts) === 2) {
                // 'something.localhost'
                if ($parts[0] === 'hasmerch') {
                    $isRootSite = true;
                    $subdomain  = 'hasmerch';
                } else {
                    // e.g. cxxl.localhost — this IS a creator subdomain
                    $isRootSite = false;
                    $subdomain  = $parts[0];
                }
            } else {
                // 3+ parts: cxxl.hasmerch.localhost — take first segment
                $isRootSite = false;
                $subdomain  = $parts[0];
            }
        } else {
            /*
             * Production patterns:
             *   'hasmerch.com'         → 2 parts → root site
             *   'cxxl.hasmerch.com'    → 3 parts → subdomain
             */
            if (count($parts) <= 2) {
                $isRootSite = true;
                $subdomain  = 'hasmerch';
            } else {
                $isRootSite = false;
                $subdomain  = $parts[0];
            }
        }

        return [
            'host'        => $host,
            'subdomain'   => $subdomain,
            'isRootSite'  => $isRootSite,
            'isLocalhost' => $isLocalhost,
        ];
    }

    /**
     * Load full store context: branding, theme, store name, and store folder.
     */
    public static function loadStore(): array
    {
        $resolved = self::resolve();

        require_once ROOT . '/backend/core/BrandLoader.php';
        require_once ROOT . '/backend/core/ThemeLoader.php';

        $brandLoader = new BrandLoader();
        $themeLoader = new ThemeLoader();

        /*
         * Attempt DB lookup. On local dev without a database, this will
         * throw or return false — both are handled below.
         */
        $branding = false;
        try {
            $branding = $resolved['isRootSite']
                ? $brandLoader->getBrandingBySlug('hasmerch')
                : $brandLoader->getBrandingBySlug($resolved['subdomain']);
        } catch (\Throwable $e) {
            // Database unavailable (common in early local dev). Continue with
            // filesystem-only resolution below.
            $branding = false;
        }

        /*
         * Resolve the store folder.
         *
         * Priority order:
         *   1. Root site → always 'hasmerch' folder
         *   2. DB branding has a slug → find the matching folder case-insensitively
         *   3. No DB row → find a folder matching the subdomain case-insensitively
         *   4. Nothing found → 'missing' (triggers claim-store page)
         *
         * Case-insensitive matching is required because:
         *   - The subdomain from HTTP_HOST is lowercased ('cxxl')
         *   - The folder on disk may be uppercase ('CXXL')
         *   - Linux (production) is case-sensitive; strtolower() would break it
         *
         * self::findStoreFolder() scans content/stores/ and returns the real
         * directory name as it exists on disk, preserving its original casing.
         */
        $storesRoot  = ROOT . '/content/stores';
        $storeFolder = null;

        if ($resolved['isRootSite']) {
            $storeFolder = self::findStoreFolder($storesRoot, 'hasmerch');
        } else {
            // Try slug from DB first, fall back to subdomain from hostname
            $slugToFind  = !empty($branding['slug'])
                ? $branding['slug']
                : $resolved['subdomain'];

            $storeFolder = self::findStoreFolder($storesRoot, $slugToFind);
        }

        // If no matching folder found, mark as non-existent
        $exists = ($storeFolder !== null);

        $theme = [];
        if ($exists && !empty($branding)) {
            $theme = $themeLoader->load($branding);
        }

        /*
         * Store name resolution:
         *   - Root site: always 'HasMerch'
         *   - Subdomain with DB record: use store_name field
         *   - Subdomain without DB record but folder exists: use folder name + "'s Store"
         *   - Nothing: 'Claim This Store'
         */
        $storeName = 'HasMerch';
        if (!$resolved['isRootSite']) {
            if (!empty($branding['store_name'])) {
                $storeName = $branding['store_name'];
            } elseif ($exists) {
                // Derive a readable name from the actual folder name on disk
                $folderName = basename($storeFolder);
                $storeName  = $folderName . "'s Store";
            } else {
                $storeName = 'Claim This Store';
            }
        }

        return [
            ...$resolved,
            'branding'    => is_array($branding) ? $branding : [],
            'theme'       => $theme,
            'storeName'   => $storeName,
            'storeFolder' => $storeFolder ?? ($storesRoot . DIRECTORY_SEPARATOR . 'missing'),
            'exists'      => $exists,
        ];
    }

    /**
     * Scan content/stores/ for a directory matching $slug case-insensitively.
     * Returns the full absolute path using the real directory name on disk,
     * or null if no match is found.
     *
     * Examples:
     *   findStoreFolder('/path/stores', 'cxxl')     → '/path/stores/CXXL'
     *   findStoreFolder('/path/stores', 'CXXL')     → '/path/stores/CXXL'
     *   findStoreFolder('/path/stores', 'hasmerch') → '/path/stores/hasmerch'
     *   findStoreFolder('/path/stores', 'unknown')  → null
     *
     * @param  string $storesRoot  Absolute path to content/stores/
     * @param  string $slug        Slug to search for (any casing)
     * @return string|null
     */
    private static function findStoreFolder(string $storesRoot, string $slug): ?string
    {
        if (!is_dir($storesRoot) || $slug === '') {
            return null;
        }

        $slugLower = strtolower($slug);

        $entries = scandir($storesRoot);
        if ($entries === false) {
            return null;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $storesRoot . DIRECTORY_SEPARATOR . $entry;

            if (!is_dir($fullPath)) {
                continue;
            }

            if (strtolower($entry) === $slugLower) {
                return $fullPath;
            }
        }

        return null;
    }
}
