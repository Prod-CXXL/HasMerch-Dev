<!DOCTYPE html>
<html lang="en">

<?php include __DIR__ . '/../partials/head.php'; ?>

<body>

    <?php include __DIR__ . '/../partials/header-copy.php'; ?>

    <div class="overlay"></div>

    <main class="site-main">
        <?php include $viewPath; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <!--
        Scripts loaded once at bottom of body.
        defer ensures DOM is ready before execution.
        Stripe checkout script only runs on pages with .stripe-buy buttons.
    -->
    <script src="/assets/js/scripts.js" defer></script>
    <script src="/assets/js/stripe-checkout.js" defer></script>

</body>
</html>


