<main class="product-page">
  <div class="product-container">

    <!-- Gallery -->
    <div class="product-gallery">

      <?php if (!empty($product['images'])): ?>
        <div class="gallery-main">
            <img src="<?= $product['images'][0] ?>" alt="<?= $product['title'] ?>">
        </div>

        <div class="gallery-thumbs">
          <?php foreach ($product['images'] as $image): ?>
            <img src="<?= $image ?>" alt="">
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <img src="<?= $product['image'] ?>" alt="<?= $product['title'] ?>">
      <?php endif; ?>

    </div>

    <!-- Info -->
    <div class="product-info">
        <h1><?= $product['title'] ?></h1>

        <p class="product-description">
            <?= $product['description'] ?>
        </p>

        <p class="product-price">
            $<?= $product['price'] ?>
        </p>

        <div class="product-details">
            <?= $body ?>
        </div>

        <?php include __DIR__ . '/../partials/product-definition.php'; ?>
    </div>

  </div>
</main>