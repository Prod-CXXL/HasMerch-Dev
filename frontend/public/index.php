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
require_once ROOT . '/backend/services/ProductService.php';
require_once ROOT . '/backend/core/App.php';

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

$branding = $store['branding'];
$theme = $store['theme'];
$storeName = $store['storeName'];
$isRootSite = $store['isRootSite'];
$subdomain = $store['subdomain'];
$storeFolder = $store['storeFolder'];
$storeExists = $store['exists'];

/*
|--------------------------------------------------------------------------
| LOAD SERVICES
|--------------------------------------------------------------------------
*/

$productService = new ProductService();

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
| LOAD PRODUCTS
|--------------------------------------------------------------------------
*/

$products = $productService->getByUser($branding['user_id']);

/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/

$route = trim($_GET['route'] ?? '', '/');
$page = $route ?: 'home';

$viewMap = [
    'home' => 'store/home.php',
    'shop' => 'store/shop.php',
    'creators' => 'store/creators.php',
    'search' => 'store/search.php',
    'about' => 'store/about.php',
];

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