<?php
/**
 * Product card partial — shop grid
 *
 * Expects $product array in scope (set by the shop.php foreach loop).
 * Fields used: title, description, image, images[], permalink, identifier,
 *              slug, price, available
 */

// Resolve the product detail URL
$productUrl = !empty($product['permalink'])
    ? $product['permalink']
    : '/shop/' . htmlspecialchars($product['identifier'] ?? $product['slug'] ?? '', ENT_QUOTES, 'UTF-8') . '/';

// Primary image
$primaryImage = !empty($product['images'][0])
    ? $product['images'][0]
    : (!empty($product['image']) ? $product['image'] : '');

$isAvailable = !empty($product['available']) && $product['available'] === true;
?>

<div class="main">
    <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>" class="product__link">
        <div class="product">

            <?php if ($primaryImage): ?>
                <img
                    src="<?= htmlspecialchars($primaryImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?> product image"
                    class="product__image"
                    loading="lazy"
                >
            <?php endif; ?>

            <div class="product__information">
                <h2 class="product__title">
                    <?= htmlspecialchars($product['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <?php if (!empty($product['description'])): ?>
                    <p class="product__description">
                        <?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <?php if ($isAvailable && !empty($product['price'])): ?>
                    <p class="product__price">
                        $<?= htmlspecialchars(number_format((float)$product['price'], 2), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <?php if ($isAvailable): ?>
                    <button class="buy-button">
                        Buy — $<?= htmlspecialchars(number_format((float)($product['price'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php else: ?>
                    <button class="buy-button coming-soon" disabled>
                        COMING SOON
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </a>
</div>
