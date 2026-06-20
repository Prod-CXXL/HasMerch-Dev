<?php
/**
 * <head> partial
 *
 * Injects:
 *   - Theme CSS custom properties (from ThemeLoader merge)
 *   - Google Fonts, Material Symbols
 *   - Font Awesome
 *   - Snipcart CSS + JS (with public API key from branding)
 *
 * NOTE: scripts.js is intentionally NOT loaded here.
 * It is loaded once at the bottom of default.php with defer
 * to avoid blocking rendering and prevent double-execution.
 */

require_once __DIR__ . '/../../../backend/core/App.php';

$theme = $theme ?? [];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page Title -->
    <title><?= htmlspecialchars($storeName ?? 'HasMerch', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Canonical URL -->
    <link rel="canonical" href="http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>">

    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/70f6532a43.js" crossorigin="anonymous" defer></script>

    <!-- Global Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Dynamic Theme — CSS custom properties from ThemeLoader -->
    <style>
        :root {
            /* Defaults — overridden by per-store theme below */
            --color-bg:           #a1a5a9;
            --color-font:         #111111;
            --color-nav:          #000000;
            --color-nav-hover:    #ffffff;
            --color-button:       #000000;
            --color-button-text:  #ffffff;
            --color-card:         #ffffff;
            --shadow-card:        0 18px 30px rgba(0,0,0,.20);

            <?php foreach ($theme as $key => $value): ?>
            --<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>;
            <?php endforeach; ?>
        }
    </style>

    <!-- Snipcart CSS -->
    <link rel="stylesheet" href="https://cdn.snipcart.com/themes/v3.6.0/default/snipcart.css">

    <!-- Snipcart Config -->
    <script>
        window.SnipcartSettings = {
            publicApiKey: "<?= htmlspecialchars($branding['snipcart_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>",
            addProductBehavior: "none",
            modalStyle: "side"
        };
    </script>

    <!-- Snipcart JS -->
    <script async src="https://cdn.snipcart.com/themes/v3.6.0/default/snipcart.js"></script>

</head>
