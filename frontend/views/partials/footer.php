<?php $socials = $socials ?? []; ?>

<footer>
  <div class="footer-content">

    <div class="social-media">

      <?php if (!empty($socials['spotify'])): ?>
        <a href="<?= $socials['spotify'] ?>" target="_blank">
          <i class="fab fa-spotify"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($socials['instagram'])): ?>
        <a href="<?= $socials['instagram'] ?>" target="_blank">
          <i class="fab fa-instagram"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($socials['soundcloud'])): ?>
        <a href="<?= $socials['soundcloud'] ?>" target="_blank">
          <i class="fab fa-soundcloud"></i>
        </a>
      <?php endif; ?>

      <!-- LOGO (dynamic) -->
      <a href="<?= App::home() ?>">
      <img 
        src="/assets/images/HM.png" class="logo" alt="HasMerch Logo" 
        class="logo" 
        alt="Logo"
      >
      </a>

      <?php if (!empty($socials['twitter'])): ?>
        <a href="<?= $socials['twitter'] ?>" target="_blank">
          <i class="fab fa-twitter"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($socials['tiktok'])): ?>
        <a href="<?= $socials['tiktok'] ?>" target="_blank">
          <i class="fab fa-tiktok"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($socials['snapchat'])): ?>
        <a href="<?= $socials['snapchat'] ?>" target="_blank">
          <i class="fab fa-snapchat"></i>
        </a>
      <?php endif; ?>

    </div>

    <p>
      &copy; <?= date('Y') ?>
      <?= htmlspecialchars($storeName ?? 'HasMerch') ?>
      - Made with
      <a href="https://hasmerch.com" target="_blank">
        HasMerch
      </a>
      All rights reserved.
    </p>

  </div>
</footer>