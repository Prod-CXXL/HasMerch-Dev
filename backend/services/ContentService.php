<?php

/**
 * ContentService
 *
 * Loads and parses product Markdown files from a store's products/ folder.
 * Each file uses YAML front matter followed by a Markdown body.
 *
 * Returns an array of product arrays, each containing:
 *   - All YAML front matter fields (title, price, identifier, category,
 *     image, images[], description, permalink, available, etc.)
 *   - 'body' => rendered HTML from the Markdown body section
 *   - 'slug' => filename without extension (used as fallback identifier)
 */
class ContentService
{
    /**
     * Load all products from a store's products directory.
     *
     * @param  string $storeFolder  Absolute path to the store root
     *                              (e.g. /var/www/content/stores/CXXL)
     * @return array  Flat array of product arrays, available first then coming-soon,
     *                each group sorted alphabetically by title.
     */
    public static function loadProducts(string $storeFolder): array
    {

        $productsDir = self::normalisePath($storeFolder) . DIRECTORY_SEPARATOR . 'products';

        if (!is_dir($productsDir)) {
            return [];
        }

        // Use forward slashes for glob() — it accepts them on all platforms
        // including Windows, and avoids glob() returning false on mixed paths.
        $globPattern = str_replace('\\', '/', $productsDir) . '/*.md';
        $files       = glob($globPattern);

        if (empty($files)) {
            return [];
        }

        $available   = [];
        $comingSoon  = [];

        foreach ($files as $file) {
            $product = self::parseFile($file);

            if ($product === null) {
                continue;
            }

            if (!empty($product['available']) && $product['available'] !== false) {
                $available[] = $product;
            } else {
                $comingSoon[] = $product;
            }
        }

        // Sort each group alphabetically by title
        usort($available,  fn($a, $b) => strcmp($a['title'] ?? '', $b['title'] ?? ''));
        usort($comingSoon, fn($a, $b) => strcmp($a['title'] ?? '', $b['title'] ?? ''));

        return array_merge($available, $comingSoon);
    }

    /**
     * Load a single product by its identifier or filename slug.
     *
     * @param  string $storeFolder
     * @param  string $identifier   Value of 'identifier' field or filename slug
     * @return array|null
     */
    public static function loadProduct(string $storeFolder, string $identifier): ?array
    {
        $productsDir = self::normalisePath($storeFolder) . DIRECTORY_SEPARATOR . 'products';

        if (!is_dir($productsDir)) {
            return null;
        }

        $globPattern = str_replace('\\', '/', $productsDir) . '/*.md';
        $files       = glob($globPattern);

        if (empty($files)) {
            return null;
        }

        foreach ($files as $file) {
            $product = self::parseFile($file);

            if ($product === null) {
                continue;
            }

            // Match on 'identifier' field first, then filename slug
            if (
                (isset($product['identifier']) && $product['identifier'] === $identifier)
                || $product['slug'] === $identifier
            ) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Parse a single .md file into a product array.
     *
     * @param  string $filePath  Absolute path to the .md file
     * @return array|null        Returns null if the file cannot be parsed
     */
    private static function parseFile(string $filePath): ?array
    {
        $raw = file_get_contents($filePath);

        if ($raw === false) {
            return null;
        }

        // Normalise Windows line endings (CRLF → LF) before any parsing.
        // Files created or edited on Windows contain \r\n. Without this, the
        // YAML regex fails to match and every parsed value has a trailing \r,
        // causing 'available' === 'true\r' to never equal 'true'.
        $raw = str_replace("\r\n", "\n", $raw);
        $raw = str_replace("\r", "\n", $raw); // legacy Mac CR-only, belt-and-suspenders

        // Require opening --- delimiter
        if (strpos(ltrim($raw), '---') !== 0) {
            return null;
        }

        // Split on the closing --- delimiter
        // Pattern: opening ---, YAML block, closing ---, optional body
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n?(.*)/s', ltrim($raw), $matches)) {
            return null;
        }

        $yamlRaw    = trim($matches[1]);
        $bodyRaw    = trim($matches[2]);

        $data       = self::parseYaml($yamlRaw);
        $bodyHtml   = self::renderMarkdown($bodyRaw);

        // Derive slug from filename
        $data['slug'] = pathinfo($filePath, PATHINFO_FILENAME);

        // Ensure 'body' key exists
        $data['body'] = $bodyHtml;

        // Normalize 'available' to a true boolean
        if (isset($data['available'])) {
            $val = $data['available'];
            if (is_string($val)) {
                $data['available'] = ($val === 'true');
            }
        } else {
            $data['available'] = false;
        }

        // Normalize 'images' — if defined as a YAML list, it comes through
        // as a multi-line string from simple parsing; convert to array.
        if (isset($data['images']) && is_string($data['images'])) {
            $lines = explode("\n", $data['images']);
            $images = [];
            foreach ($lines as $line) {
                $line = trim($line, " -\t");
                if ($line !== '') {
                    $images[] = $line;
                }
            }
            $data['images'] = $images;
        }

        // Ensure 'images' is always an array (fallback to single image)
        if (empty($data['images']) && !empty($data['image'])) {
            $data['images'] = [$data['image']];
        }

        return $data;
    }

    /**
     * Minimal YAML parser — handles simple key: value pairs and list items.
     * Sufficient for product front matter; not a full YAML implementation.
     *
     * Supports:
     *   key: value
     *   key: "quoted value"
     *   key:            (empty value)
     *   images:
     *     - /path/one
     *     - /path/two
     *
     * @param  string $yaml
     * @return array
     */
    private static function parseYaml(string $yaml): array
    {
        $lines  = explode("\n", $yaml);
        $result = [];

        $currentKey  = null;
        $isList      = false;

        foreach ($lines as $line) {
            // List item under current key
            if ($isList && preg_match('/^\s+-\s+(.+)$/', $line, $m)) {
                if (!isset($result[$currentKey]) || !is_array($result[$currentKey])) {
                    $result[$currentKey] = [];
                }
                $result[$currentKey][] = trim($m[1]);
                continue;
            }

            // Key: value pair
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*):\s*(.*)$/', $line, $m)) {
                $key   = trim($m[1]);
                $value = trim($m[2], " \t\"'");

                $currentKey = $key;
                $isList     = ($value === '');

                if (!$isList) {
                    // Inline list — unlikely in these files but handle gracefully
                    $result[$key] = $value;
                }

                continue;
            }

            // Blank line or unexpected format — reset list detection
            if (trim($line) === '') {
                $isList = false;
            }
        }

        return $result;
    }

    /**
     * Render Markdown to HTML.
     *
     * Uses Parsedown if available (Parsedown.php in /backend/lib/).
     * Falls back to basic nl2br + paragraph wrapping if not.
     *
     * @param  string $markdown
     * @return string  Safe HTML
     */
    private static function renderMarkdown(string $markdown): string
    {
        if ($markdown === '') {
            return '';
        }

        // Try Parsedown
        $parsePath = defined('ROOT')
            ? ROOT . '/backend/lib/Parsedown.php'
            : __DIR__ . '/../lib/Parsedown.php';

        if (file_exists($parsePath)) {
            require_once $parsePath;
            return Parsedown::instance()->text($markdown);
        }

        // Basic fallback: wrap paragraphs and convert list items
        $html  = '';
        $lines = explode("\n", $markdown);
        $para  = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($para !== '') {
                    $html .= '<p>' . htmlspecialchars($para, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
                    $para  = '';
                }
                continue;
            }

            if (strpos($trimmed, '- ') === 0) {
                if ($para !== '') {
                    $html .= '<p>' . htmlspecialchars($para, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
                    $para  = '';
                }
                $html .= '<li>' . htmlspecialchars(substr($trimmed, 2), ENT_QUOTES, 'UTF-8') . '</li>' . "\n";
                continue;
            }

            $para .= ($para !== '' ? ' ' : '') . $trimmed;
        }

        if ($para !== '') {
            $html .= '<p>' . htmlspecialchars($para, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
        }

        // Wrap orphan <li> elements in <ul>
        $html = preg_replace('/(<li>.*<\/li>\n)+/s', "<ul>\n$0</ul>\n", $html);

        return $html;
    }

    /**
     * Normalise a filesystem path for the current OS.
     *
     * Converts all forward and backward slashes to DIRECTORY_SEPARATOR,
     * then removes any trailing separator. This ensures is_dir() and
     * path concatenation work correctly on both Windows (XAMPP) and Unix.
     *
     * @param  string $path
     * @return string
     */
    private static function normalisePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return rtrim($path, DIRECTORY_SEPARATOR);
    }
}
