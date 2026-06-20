<?php
/**
 * Store shop view
 *
 * Receives from index.php:
 *   $products    — array of product arrays from ContentService::loadProducts()
 *   $storeName   — string
 *   $storeFolder — absolute path to store content folder
 */

// Group products into categories and coming-soon
$grouped    = [];
$comingSoon = [];

foreach ($products as $product) {
    if (!empty($product['available']) && $product['available'] === true) {
        $cat = !empty($product['category'])
            ? ucfirst(strtolower(trim($product['category'])))
            : 'Other';

        $grouped[$cat][] = $product;
    } else {
        $comingSoon[] = $product;
    }
}

// Sort categories alphabetically
ksort($grouped);
?>

<div class="shop-container">

    <?php if (empty($grouped) && empty($comingSoon)): ?>
        <p class="shop-empty">No products available yet. Check back soon.</p>

    <?php else: ?>

        <?php foreach ($grouped as $category => $items): ?>
            <h2 class="shop-category"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="product-grid">
                <?php foreach ($items as $product): ?>
                    <?php include __DIR__ . '/../partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($comingSoon)): ?>
            <h2 class="shop-category">Coming Soon</h2>
            <div class="product-grid">
                <?php foreach ($comingSoon as $product): ?>
                    <?php include __DIR__ . '/../partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>
