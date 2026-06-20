<?php

class StoreResolver
{
    public static function resolve(): array
    {
        $host = $_SERVER['HTTP_HOST'];

        $parts = explode('.', $host);

        $subdomain = null;
        $isRootSite = false;

        $isLocalhost = str_contains($host, 'localhost')
            || str_contains($host, '127.0.0.1');

        /*
        |--------------------------------------------------------------------------
        | LOCALHOST
        |--------------------------------------------------------------------------
        */

        if ($isLocalhost) {

            // hasmerch.localhost
            if (count($parts) === 2) {

                $isRootSite = true;
                $subdomain = 'hasmerch';
            }

            // cxxl.hasmerch.localhost
            elseif (count($parts) >= 3) {

                $subdomain = $parts[0];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION
        |--------------------------------------------------------------------------
        */

        else {

            // hasmerch.com
            if (count($parts) === 2) {

                $isRootSite = true;
                $subdomain = 'hasmerch';
            }

            // cxxl.hasmerch.com
            elseif (count($parts) >= 3) {

                $subdomain = $parts[0];
            }
        }

        return [
            'host' => $host,
            'subdomain' => $subdomain,
            'isRootSite' => $isRootSite,
            'isLocalhost' => $isLocalhost
        ];
    }

    public static function loadStore(): array
{
    $resolved = self::resolve();

    require_once ROOT . '/backend/core/BrandLoader.php';
    require_once ROOT . '/backend/core/ThemeLoader.php';

    $brandLoader = new BrandLoader();
    $themeLoader = new ThemeLoader();

    /*
    |--------------------------------------------------------------------------
    | LOAD BRANDING
    |--------------------------------------------------------------------------
    */

    if ($resolved['isRootSite']) {

        $branding = $brandLoader->getBrandingBySlug('hasmerch');

    } else {

        $branding = $brandLoader->getBrandingBySlug(
            $resolved['subdomain']
        );
        
    }

    /*
    |--------------------------------------------------------------------------
    | STORE EXISTS?
    |--------------------------------------------------------------------------
    */

    $exists = !empty($branding);

    /*
    |--------------------------------------------------------------------------
    | LOAD THEME
    |--------------------------------------------------------------------------
    */

    $theme = [];

    if ($exists) {
        $theme = $themeLoader->load($branding);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE NAME
    |--------------------------------------------------------------------------
    */

    $storeName = 'HasMerch';

    if (!$resolved['isRootSite']) {

    if (!empty($branding)) {

        $storeName = $branding['store_name']
            ?? ($branding['slug'] . "'s Store");

    } else {

        $storeName = 'Claim This Store';
    }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE FOLDER
    |--------------------------------------------------------------------------
    */

    $storeFolder = ROOT . '/content/stores/';

    if ($resolved['isRootSite']) {

        $storeFolder .= 'hasmerch';

    } else {

        if (!empty($branding['slug'])) {

        $storeFolder .= strtolower($branding['slug']);

    } else {

        $storeFolder .= 'missing';
    }
    }

    return [
        ...$resolved,
        'branding' => $branding ?? [],
        'theme' => $theme,
        'storeName' => $storeName,
        'storeFolder' => $storeFolder,
        'exists' => $exists
    ];
}
}