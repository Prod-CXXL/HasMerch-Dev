<?php

/*
|--------------------------------------------------------------------------
| BASE PATHS
|--------------------------------------------------------------------------
*/

define('ROOT', dirname(__DIR__, 2));

$host = $_SERVER['HTTP_HOST'];

if (str_contains($host, 'localhost') || $host === '127.0.0.1') {
    define('BASE_URL', '/frontend/public');
} else {
    define('BASE_URL', '');
}

/*
|--------------------------------------------------------------------------
| LOAD CORE SYSTEM
|--------------------------------------------------------------------------
*/

require_once ROOT . '/backend/core/StoreResolver.php';
require_once ROOT . '/backend/core/App.php';
require_once ROOT . '/backend/services/ContentService.php';

/*
|--------------------------------------------------------------------------
| LOAD STORE CONTEXT
|--------------------------------------------------------------------------
*/

$store = StoreResolver::loadStore();

/*
|--------------------------------------------------------------------------
| EXTRACT STORE DATA
|--------------------------------------------------------------------------
*/

$branding    = $store['branding'];
$theme       = $store['theme'];
$storeName   = $store['storeName'];
$isRootSite  = $store['isRootSite'];
$subdomain   = $store['subdomain'];
$storeFolder = $store['storeFolder'];
$storeExists = $store['exists'];

/*
|--------------------------------------------------------------------------
| LOAD SOCIAL LINKS
|--------------------------------------------------------------------------
*/

$socials = [];

if ($storeExists && !empty($branding['user_id'])) {
    require_once ROOT . '/backend/core/BrandLoader.php';
    $brandLoader = new BrandLoader();
    $socials     = $brandLoader->getSocialsByUser($branding['user_id']);
}

/*
|--------------------------------------------------------------------------
| STORE EXISTS?
|--------------------------------------------------------------------------
*/

if (!$storeExists) {
    http_response_code(404);
    $viewPath = ROOT . '/frontend/views/errors/claim-store.php';
    require ROOT . '/frontend/views/layouts/default.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/

$route = trim($_GET['route'] ?? '', '/');

// Split route into segments for product detail matching
// e.g. "shop/card-business" → ['shop', 'card-business']
$segments = explode('/', $route);
$page     = $segments[0] ?: 'home';

// Static page routes
$viewMap = [
    'home'     => 'store/home.php',
    'shop'     => 'store/shop.php',
    'creators' => 'store/creators.php',
    'search'   => 'store/search.php',
    'about'    => 'store/about.php',
    'register' => 'store/register.php',
];

/*
|--------------------------------------------------------------------------
| PRODUCT DETAIL ROUTE  — /shop/{identifier}
|--------------------------------------------------------------------------
*/

$product = null;

if ($page === 'shop' && isset($segments[1]) && $segments[1] !== '') {
    $identifier = preg_replace('/[^a-zA-Z0-9\-_]/', '', $segments[1]);

    $product    = ContentService::loadProduct($storeFolder, $identifier);

    $viewPath   = ROOT . '/frontend/views/store/product-detail.php';

    require ROOT . '/frontend/views/layouts/default.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| LOAD PRODUCTS FOR SHOP VIEW
|--------------------------------------------------------------------------
*/

$products = [];

if ($page === 'shop') {
    $products = ContentService::loadProducts($storeFolder);
}

/*
|--------------------------------------------------------------------------
| STATIC PAGE ROUTING
|--------------------------------------------------------------------------
*/

if (!isset($viewMap[$page])) {
    http_response_code(404);
    $viewPath = ROOT . '/frontend/views/errors/404.php';
    require ROOT . '/frontend/views/layouts/default.php';
    exit;
}

$viewPath = ROOT . '/frontend/views/' . $viewMap[$page];

/*
|--------------------------------------------------------------------------
| LOAD LAYOUT
|--------------------------------------------------------------------------
*/

require ROOT . '/frontend/views/layouts/default.php';
