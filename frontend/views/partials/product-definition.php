<?php
/**
 * Product definition partial — buy button or coming-soon
 *
 * Expects $product array in scope.
 * Fields used: available, identifier, slug, title, price, image, description
 */

$isAvailable = !empty($product['available']) && $product['available'] === true;

$identifier  = htmlspecialchars($product['identifier'] ?? $product['slug'] ?? '', ENT_QUOTES, 'UTF-8');
$title       = htmlspecialchars($product['title']      ?? '',                      ENT_QUOTES, 'UTF-8');
$price       = isset($product['price']) ? (float)$product['price'] : 0.00;
$priceCents  = (int)round($price * 100);
$priceFormatted = number_format($price, 2);
$image       = htmlspecialchars($product['image'] ?? ($product['images'][0] ?? ''), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<?php if (!$isAvailable): ?>

    <button class="buy-button coming-soon" disabled>
        COMING SOON
    </button>

<?php else: ?>

    <button
        class="buy-button stripe-buy"
        data-name="<?= $title ?>"
        data-price="<?= $priceCents ?>"
        data-identifier="<?= $identifier ?>"
        data-image="<?= $image ?>"
        data-description="<?= $description ?>">
        Buy with Stripe ($<?= $priceFormatted ?>)
    </button>

<?php endif; ?>
