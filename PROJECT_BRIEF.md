# HasMerch — Project Brief

## Vision

HasMerch is a **multi-tenant creator commerce platform** that merges physical merchandise, digital storefronts, NFC smart products, and marketplace discovery into a single ecosystem. The core premise is simple: if you have fans, you should have merch — and it should take less than ten minutes to set up.

The platform sits at the intersection of four existing ideas:

- **Shopify** — creator-owned storefronts
- **Amazon** — marketplace-style discovery
- **Linktree** — digital identity and link hub
- **Dot** — NFC smart cards that bridge physical and digital presence

No single platform does all four. HasMerch does.

---

## What the Platform Does

### For Creators
A creator signs up, claims a subdomain, configures their store, uploads products, and immediately has a live storefront. Their store lives at `creator.hasmerch.com` and is fully themed, branded, and connected to Stripe for payments. Creators can sell physical merchandise, digital products, and services. An optional link hub page (`hasmerch.com/creator`) aggregates their social links, store, and content — functioning like Linktree.

### For Customers
Customers browse the central HasMerch marketplace (`hasmerch.com/shop`) to discover products from all creators. They can visit individual creator storefronts, search products, and check out via Stripe. NFC cards allow a physical tap to open a creator's store or profile directly on a phone, no app required.

### For HasMerch (the Platform)
HasMerch itself operates as a seller alongside its creator network. The platform sells its own products — NFC cards, branded merchandise, creator kits — through the same infrastructure that powers creator stores. Revenue comes from transaction fees on creator sales (via Stripe Connect), subscription tiers, and direct product sales.

---

## Core Components

### 1. Multi-Tenant Subdomain Storefronts

Each creator gets a subdomain: `cxxl.hasmerch.com`, `brand.hasmerch.com`, etc. The routing system resolves the subdomain on every request, looks up the corresponding store in the database, loads their branding and theme, and renders their storefront. `hasmerch.com` itself is the root store — HasMerch's own marketplace and home page.

The store resolution flow:

```
Incoming request
      ↓
StoreResolver — reads HTTP_HOST, extracts subdomain
      ↓
BrandLoader — queries DB: users.subdomain → branding table
      ↓
ThemeLoader — merges layout preset + scheme overrides → CSS vars
      ↓
Router — maps /route to a view file
      ↓
Layout — renders header + view + footer with store context
```

If no store is found for a subdomain, the visitor sees a "Claim This Store" page with a signup prompt.

---

### 2. Product System

Products are defined in Markdown files with YAML front matter. This is intentional — it makes products human-readable, easily version-controlled, and configurable without a database UI. Each product file lives in the owning store's folder.

**Product front matter fields:**

```yaml
---
layout: product
title: "NFC Cards"
identifier: business-card-premium
price: 29.99
stripe_link:
category: "cards"
image: /assets/images/card-front.png
images:
  - /assets/images/card-front.png
  - /assets/images/card-back.png
description: "A premium NFC business card."
permalink: /shop/card-business/
available: true
---

Long-form description in Markdown goes here.
```

`available: true` shows the product with a buy button. `available: false` renders it as "Coming Soon" and disables purchase. This makes it trivial to pre-announce products.

Products are loaded by `ContentService`, which parses the front matter and renders the Markdown body into HTML. The PHP product view then receives a `$product` array and `$body` HTML string.

---

### 3. Theme System

The theme system has two layers:

**Layout Theme (Preset)** — A JSON file in `storage/themes/presets/` that defines the structural look and feel of a store. Examples: `minimal.json`, `bold.json`, `creator.json`. This is what a creator selects first — it sets the overall visual personality of their storefront.

**Scheme Theme (Overrides)** — A set of color, font, and style overrides that the creator configures through a dashboard form. These override the preset values without replacing the entire layout. This is stored in the `branding` database row as `theme_json`.

The two layers merge at render time in `ThemeLoader`:

```
preset JSON
    +
scheme overrides (theme_json from DB)
    ↓
merged theme array
    ↓
injected as CSS custom properties in <head>
```

**Available CSS variables (theme tokens):**

```
--color-bg          Page background
--color-font        Default text color
--color-nav         Navigation link color
--color-nav-hover   Navigation hover / active color
--color-button      Buy/CTA button background
--color-button-text Button label color
--color-card        Product card background
--shadow-card       Product card drop shadow
--font-primary      Primary typeface
```

Creators never write CSS. They fill out a form. The form writes `theme_json`. The system injects the result.

---

### 4. Store Configuration (Branding)

Each store has a branding record in the database that controls:

- Store name
- Subdomain / slug
- Logo and favicon assets
- Social media handles (Spotify, Instagram, TikTok, Twitter, SoundCloud, Snapchat)
- Snipcart API key (to be migrated to env config)
- Layout theme preset name
- Scheme theme overrides (`theme_json`)
- Stripe account ID (for Connect payouts)

The `branding.json` file in `storage/branding/` is the legacy flat-file version of this config, carried over from a Jekyll era. It represents the **template and reference structure** for what a branding record contains — not a live data source. New stores will populate this data through a registration form and dashboard. The JSON structure remains useful as a default template when seeding a new store's database record.

---

### 5. Payments

**HasMerch direct sales** (platform products):

```
Customer → Stripe Checkout → HasMerch Stripe account
```

**Creator sales** (marketplace sellers):

```
Customer → Stripe Checkout → Creator Stripe account
                                    ↓
                             HasMerch platform fee (via Stripe Connect)
```

The Stripe checkout session is created server-side. A `stripe-checkout.js` script intercepts clicks on `.stripe-buy` buttons, posts to a `/create-checkout-session` endpoint, and redirects to Stripe-hosted checkout. The endpoint URL must be environment-configured (not hardcoded).

---

### 6. NFC Card System

NFC cards are HasMerch's signature physical product. Each card is programmed with a URL that routes through HasMerch's redirect system:

```
tap.hasmerch.com/{card-uid}
      ↓
Server looks up card UID → destination URL
      ↓
301 redirect to creator.hasmerch.com (or any configured URL)
```

The redirect layer means a card's destination can be changed at any time without physically reprogramming the chip. Cards can point to a creator's storefront, a specific product, a link hub page, or any external URL.

Cards are sold as products on the HasMerch platform. Creators order them through the shop, customize design and destination, and hand them out physically.

---

## Folder Structure

```
/
├── backend/
│   ├── config/
│   │   └── db.php                  # DB credentials (must move to env)
│   ├── core/
│   │   ├── App.php                 # URL helpers, active nav detection
│   │   ├── BrandLoader.php         # DB: load branding + socials by slug
│   │   ├── Database.php            # PDO connection wrapper
│   │   ├── StoreResolver.php       # Subdomain → store context
│   │   └── ThemeLoader.php         # Merge preset + overrides → CSS vars
│   ├── services/
│   │   ├── ContentService.php      # Parse product .md files (YAML + Markdown)
│   │   └── ProductService.php      # DB product queries (future)
│   └── global/
│       ├── terms.md                # Terms of service content
│       └── privacy.md              # Privacy policy content
│
├── content/
│   └── stores/
│       ├── hasmerch/               # HasMerch's own products and pages
│       │   ├── pages/              # Home, about, shop overrides
│       │   └── products/           # Platform product .md files
│       └── CXXL/                   # Example creator store (test account)
│           ├── pages/              # Creator-specific page overrides
│           └── products/           # Creator product .md files
│               ├── card.md
│               ├── hoodie-classic.md
│               ├── stickers.md
│               ├── stickers-bulk.md
│               └── tumblers-basic.md
│
├── frontend/
│   ├── public/
│   │   ├── index.php               # Entry point — loads store, routes request
│   │   ├── .htaccess               # Route all requests to index.php
│   │   └── assets/
│   │       ├── css/
│   │       │   └── style.css       # Global styles with CSS custom properties
│   │       └── js/
│   │           ├── scripts.js      # Menu, scroll, search, Snipcart integration
│   │           └── stripe-checkout.js  # Stripe buy button handler
│   └── views/
│       ├── layouts/
│       │   └── default.php         # Main HTML shell (head + header + main + footer)
│       ├── partials/
│       │   ├── head.php            # <head> tag — theme injection, scripts, fonts
│       │   ├── header.php          # Navigation header
│       │   ├── footer.php          # Footer with social links and copyright
│       │   ├── product.php         # Product card partial (shop grid)
│       │   └── product-definition.php  # Buy button / coming soon logic
│       ├── store/
│       │   ├── home.php            # Store homepage view
│       │   ├── shop.php            # Product grid view
│       │   ├── about.php           # About page view
│       │   ├── creators.php        # Creators directory view
│       │   ├── search.php          # Search results view
│       │   └── register.php        # Store registration view
│       └── errors/
│           ├── 404.php             # Not found page
│           └── claim-store.php     # Unclaimed subdomain prompt
│
└── storage/
    ├── branding/
    │   └── branding.json           # Reference template for store branding config
    └── themes/
        └── presets/
            └── minimal.json        # Layout theme preset (expandable)
```

---

## Creator Store Structure

Each folder under `content/stores/` represents one store. The slug must match the `subdomain` field in the `users` database table.

```
content/stores/{slug}/
├── pages/          # Optional page-level content overrides
│   ├── home.php
│   ├── about.php
│   ├── shop.php
│   └── dashboard.php
└── products/       # One .md file per product
    └── product-name.md
```

**HasMerch itself** (`content/stores/hasmerch/`) follows the same structure. This means the platform is a first-party creator on its own marketplace — using identical infrastructure to every other store.

---

## Database Tables (Target Schema)

### users
```
id, subdomain, email, password_hash, stripe_account_id,
subscription_plan, created_at
```

### branding
```
id, user_id, store_name, logo, favicon, hero_image,
theme_preset, theme_json, snipcart_key, created_at
```

### social_links
```
id, user_id, platform, url
```

### products (future — supplement .md files)
```
id, store_id, identifier, title, price, category,
image, description, available, stripe_price_id, created_at
```

### orders
```
id, user_id, product_identifier, price, status,
stripe_session_id, created_at
```

### nfc_cards
```
id, card_uid, destination_url, owner_id, created_at
```

---

## Subscription Tiers

| Tier | Price | Transaction Fee | Key Features |
|---|---|---|---|
| Free | $0/mo | 10% | 1 store, up to 10 products, HasMerch branding, marketplace listing |
| Creator | ~$15/mo | 5% | Unlimited products, custom themes, remove HasMerch branding, social hub, NFC integration |
| Brand | ~$35/mo | 3% | Custom domain, affiliate program, team members, inventory management, priority support |
| Enterprise | $99+/mo | 1–2% | API access, white-label, bulk NFC management, wholesale tools |

The free plan exists solely to maximize creator onboarding. Transaction fees and product sales carry early revenue. Paid tiers unlock serious creator tooling.

---

## Business Model & Revenue Streams

1. **Transaction fees** via Stripe Connect — automatic platform cut on every creator sale
2. **Subscription revenue** — monthly Creator / Brand / Enterprise plans
3. **Direct product sales** — NFC cards, creator kits, branded merch sold through the HasMerch store
4. **Marketplace promotion** (future) — featured creator slots, homepage spotlights
5. **Premium themes** (future) — paid layout presets beyond the default library

**Growth flywheel:**

```
Creator signs up → sells merch → buys NFC cards
→ hands out cards in real life → fans tap cards
→ fans discover HasMerch → more creators sign up
```

Every NFC card in the wild is a physical HasMerch advertisement.

---

## Current Development Status

The project is transitioning from a **Jekyll static site** to a **PHP multi-tenant application**. Core infrastructure is in place:

- Subdomain resolution and store loading — working
- Theme system (preset + override merge) — working
- PHP routing via `.htaccess` — working
- Product `.md` files defined and structured — done, need to be wired to `ContentService`
- Product views and layouts — partially migrated from Jekyll, Liquid syntax must be replaced
- Stripe checkout — partially wired, hardcoded to localhost
- Database integration — schema partially defined, `BrandLoader` and `ProductService` exist

**What still needs to happen:**

- Complete the Jekyll → PHP migration (remove all Liquid syntax from PHP views)
- Wire `ContentService` to load product `.md` files and pass data to product views
- Connect the shop view to dynamically render products from the store's content folder
- Move credentials and API keys to environment variables (`.env`)
- Build the store registration and login system
- Build the creator dashboard (theme configurator form, product manager)
- Configure Stripe Checkout endpoint for production
- Implement NFC card redirect system
- Build the `hasmerch` store content (platform's own homepage and products)

---

## Technical Constraints & Decisions

**Allowed languages:** HTML, CSS, JavaScript, PHP, JSON, Markdown. Frameworks like React, Vue, Next.js, TypeScript, and Tailwind are not approved without explicit sign-off. Keep the stack minimal and maintainable.

**No credentials in source control.** Database passwords, API keys, and Stripe secrets must live in environment variables or a `.env` file excluded from the repository.

**Product files stay as Markdown.** The `.md` format for products is a deliberate choice — easy to read, version-controlled, portable, and editable without a database interface. `ContentService` is the bridge between these files and the PHP rendering layer.

**Mobile-first.** All views are designed for small screens first, scaled up for desktop.

**`hasmerch` is a first-party store.** The root domain resolves to `content/stores/hasmerch/` and uses the same product and page infrastructure as any creator. This keeps the codebase uniform.

---

## Key Files Reference

| File | Purpose |
|---|---|
| `frontend/public/index.php` | Application entry point and router |
| `backend/core/StoreResolver.php` | Subdomain detection and store context loading |
| `backend/core/BrandLoader.php` | Fetch branding and socials from DB by slug |
| `backend/core/ThemeLoader.php` | Merge theme preset + overrides into CSS var map |
| `backend/services/ContentService.php` | Parse product `.md` YAML front matter + Markdown body |
| `frontend/views/layouts/default.php` | Master HTML layout shell |
| `frontend/views/partials/head.php` | `<head>` block with dynamic theme CSS injection |
| `frontend/views/partials/product-definition.php` | Buy button / coming soon button logic |
| `storage/branding/branding.json` | Reference template for store branding configuration |
| `storage/themes/presets/` | Layout theme JSON presets |
| `content/stores/{slug}/products/` | Per-store product `.md` files |
