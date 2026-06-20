<?php require_once __DIR__ . '/../../../backend/core/App.php'; ?>
<?php $theme = $theme ?? []; ?>
  
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Page Title -->
  <title>
    <?= htmlspecialchars($storeName) ?>
  </title>

  <!-- Styles -->
  <link rel="stylesheet" href="/assets/css/style.css">

  <style>
  :root {

  /* Default Theme Fallbacks */
  --color-bg: #a1a5a9;
  --color-font: #111111;
  --color-nav: #000000;
  --color-nav-hover: #ffffff;
  --color-button: #000000;
  --color-button-text: #ffffff;
  --color-card: #ffffff;
  --shadow-card: 0 18px 30px rgba(0,0,0,.20);

  <?php foreach ($theme as $key => $value): ?>
  --<?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>;
  <?php endforeach; ?>

  }
  </style>


  <!-- Canonical URL -->
  <link rel="canonical" href="http://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">

  <!-- Favicon -->
  <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/70f6532a43.js" crossorigin="anonymous"></script>

  <!-- Snipcart CSS -->
  <link rel="stylesheet" href="https://cdn.snipcart.com/themes/v3.6.0/default/snipcart.css"/>

  <!-- Snipcart Config -->
  <script>
    window.SnipcartSettings = {
      publicApiKey: "<?= $branding['snipcart_key'] ?? '' ?>",
      addProductBehavior: "none",
      modalStyle: "side"
    };
  </script>

  <!-- Snipcart JS -->
  <script async src="https://cdn.snipcart.com/themes/v3.6.0/default/snipcart.js"></script>

  <!-- Site JS -->
  <script src="/assets/js/scripts.js"></script>
</head>
