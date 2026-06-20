<?php
/**
 * Product detail view
 *
 * Receives from index.php:
 *   $product     — array from ContentService::loadProduct()
 *   $storeName   — string
 *
 * If $product is null (not found), renders a 404-style message.
 */

if (empty($product)): ?>

    <div class="container">
        <h1>Product Not Found</h1>
        <p>That product doesn't exist in this store.</p>
        <div class="hero-404">
            <a href="<?= App::url('shop') ?>" class="btn btn-primary">Back to Shop</a>
        </div>
    </div>

<?php else: ?>

<main class="product-page">
    <div class="product-container">

        <!-- Gallery -->
        <div class="product-gallery">
            <?php if (!empty($product['images']) && count($product['images']) > 1): ?>

                <div class="gallery-main">
                    <img
                        src="<?= htmlspecialchars($product['images'][0], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?>"
                        id="gallery-main-img"
                    >
                </div>

                <div class="gallery-thumbs">
                    <?php foreach ($product['images'] as $i => $imgSrc): ?>
                        <img
                            src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?> view <?= (int)($i + 1) ?>"
                            class="gallery-thumb<?= $i === 0 ? ' gallery-thumb-active' : '' ?>"
                            loading="lazy"
                        >
                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <img
                    src="<?= htmlspecialchars($product['image'] ?? ($product['images'][0] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?>"
                >

            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-info">
            <h1><?= htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if (!empty($product['description'])): ?>
                <p class="product-description">
                    <?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($product['price'])): ?>
                <p class="product-price">
                    $<?= htmlspecialchars(number_format((float)$product['price'], 2), ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($product['body'])): ?>
                <div class="product-details">
                    <?php
                    /*
                     * $product['body'] is HTML rendered server-side from a
                     * trusted .md file in the store's content folder.
                     * Per SECURITY.md: only output as raw HTML when the source
                     * is controlled (not user-submitted form input).
                     */
                    echo $product['body'];
                    ?>
                </div>
            <?php endif; ?>

            <?php include __DIR__ . '/../partials/product-definition.php'; ?>
        </div>

    </div>
</main>

<!-- Gallery thumbnail switcher — plain JS, no framework -->
<script>
(function () {
    var thumbs   = document.querySelectorAll('.gallery-thumb');
    var mainImg  = document.getElementById('gallery-main-img');

    if (!thumbs.length || !mainImg) return;

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            mainImg.src = this.src;
            mainImg.alt = this.alt;

            thumbs.forEach(function (t) {
                t.classList.remove('gallery-thumb-active');
            });
            this.classList.add('gallery-thumb-active');
        });
    });
}());
</script>

<?php endif; ?>
