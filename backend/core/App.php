<?php 

class App {
    /*
    |--------------------------------------------------------------------------
    | Generate URL (works for local + subdomains)
    |--------------------------------------------------------------------------
    */
    public static function url(string $path = ''): string {
        return '/' . ltrim($path, '/');
    }
    /*
    |--------------------------------------------------------------------------
    | Homepage URL
    |--------------------------------------------------------------------------
    */
    public static function home(): string
    {
        return '/';
    }

    /*
    |--------------------------------------------------------------------------
    | Active nav class
    |--------------------------------------------------------------------------
    */
    public static function isActive(string $page): string {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Homepage
    if ($uri === '') {
        $current = 'home';
    } else {
        $segments = explode('/', $uri);
        $current = end($segments);
    }

    return $current === $page ? 'active' : '';
    }
}