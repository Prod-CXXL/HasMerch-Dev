<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../partials/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../partials/header-copy.php'; ?>
    <div class="overlay"></div>
        <main class="site-main">
            <?php include $viewPath; ?>
        </main>

    <?php include __DIR__ . '/../partials/footer.php'; 
    ?>

    <script src="/frontend/assets/js/scripts.js"></script>

</body>
</html>

