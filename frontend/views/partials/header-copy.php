<header>
  <!-- Bar A: Menu + Logo + Cart (always visible) -->
  <div class="top-bar-always">
    <div class="left-section">
      <div class="menu-button" id="menu-toggle" type="button">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <a href=<?= App::url() ?>>
        <img src="/assets/images/HM.png" class="logo" alt="Logo">
      </a>
      <!-- LOGO (dynamic) 
      <a class="site-title" href=<?= App::url() ?>>
        HasMerch
      </a>
      -->
      <a href="/" class="site-title">
        <?= htmlspecialchars($storeName) ?>
      </a>
    </div>

    <div class="right-section">
      <div class="btnLoginEtc">
        <a href="/login" class="btn btn-primary">
          LOGIN
        </a>

        <a href="/register" class="btn btn-primary">
          REGISTER
        </a>
      </div>

      <a href="#" class="snipcart-checkout cart-link">
        <span class="material-symbols-outlined cart-icon">shopping_cart</span>
        <span class="snipcart-total-price cart-total"></span>
      </a>
      
    </div>
  </div>

  <!-- Bar B: Nav links -->
  <div class="top-bar-extra" id="topBarExtra">
    <ul class="nav-links">
      <li>
        <a href="<?= App::url('home') ?>"
          class="<?= App::isActive('home') ?>">
          HOME
        </a>
      </li>

      <li>
        <a href="<?= App::url('creators') ?>"
          class="<?= App::isActive('creators') ?>">
          CREATORS
        </a>
      </li>

      <li>
        <a href="<?= App::url('shop') ?>"
          class="<?= App::isActive('shop') ?>">
          SHOP
        </a>
      </li>

      <li>
        <a href="<?= App::url('about') ?>"
          class="<?= App::isActive('about') ?>">
          ABOUT
        </a>
      </li>

    </ul>
  </div>

  <!-- Sidebar -->
  <div class="side-menu" id="sideMenu">
    <ul class="nav-links">
      <li>
        <a href="<?= App::url() ?>"
          class="<?= App::isActive('home') ?>">
          HOME
        </a>
      </li>

      <li>
        <a href="<?= App::url('creators') ?>"
          class="<?= App::isActive('creators') ?>">
          CREATORS
        </a>
      </li>

      <li>
        <a href="<?= App::url('shop') ?>"
          class="<?= App::isActive('shop') ?>">
          SHOP
        </a>
      </li>

      <li>
        <a href="<?= App::url('about') ?>">
          ABOUT
        </a>
      </li>

      <li>
        <a href="<?= App::url('shop') ?>" 
            class="snipcart-checkout cart-link">
          <span class="material-symbols-outlined cart-icon">shopping_cart</span>
          <span class="snipcart-total-price cart-total"></span>
        </a>
      </li>
    </ul>

    <div class="SideLoginEtc">
        <li>
        <a href="<?= App::url('login') ?>">
          LOGIN
        </a>
      </li>

      <li>
        <a href="<?= App::url('register') ?>">
          REGISTER
        </a>
      </li>
    </div>

  </div>
</header>
