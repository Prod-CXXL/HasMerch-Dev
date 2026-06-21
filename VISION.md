# HasMerch — Vision

> **If you have fans, you should have merch. It should take ten minutes to set up.**

---

## The One-Line Version

HasMerch is the platform that turns creators into brands — combining a personal storefront, a marketplace, a digital identity hub, and a physical NFC product line into a single, unified ecosystem.

---

## The Problem

The creator economy is fragmented. A musician selling merch today needs Shopify for their store, Linktree for their bio link, a third-party NFC card vendor for networking, and Amazon or Etsy if they want discoverability beyond their own following. None of these tools talk to each other. None of them are designed specifically for creators. And none of them can launch in under ten minutes.

Existing platforms solve one piece:

| Platform | What they do well | What they miss |
|---|---|---|
| Shopify | Full-featured storefront | Expensive, complex, no marketplace, no NFC |
| Amazon | Discovery at scale | No creator identity, no storefronts, race to the bottom on price |
| Linktree | Link aggregation | No commerce, no physical products |
| Dot / Popl | NFC smart cards | No storefront, no marketplace, just the card |

**No single platform does all four. HasMerch does.**

---

## What HasMerch Is

HasMerch is a **multi-tenant creator commerce platform** at the intersection of four things:

```
Shopify  ×  Amazon  ×  Linktree  ×  NFC
```

Every creator gets:

1. **A subdomain storefront** — `yourname.hasmerch.com` — fully themed, branded, and live in minutes
2. **Marketplace presence** — products listed on `hasmerch.com/shop` alongside every other creator
3. **A digital identity hub** — social links, product links, and creator profile in one place
4. **Physical NFC products** — cards, stickers, and kits that tap directly to their store

---

## Who It's For

### Creators
Musicians, streamers, artists, podcasters, influencers — anyone with an audience who wants to sell branded merchandise without the overhead of running a full e-commerce operation.

### Small Businesses and Organizations
Recruiters who want smart business cards. Sports teams who want branded gear. Event organizers who want QR and NFC products for attendees. Local businesses who want a quick online presence.

### Builders and Early Adopters
People who want a storefront in minutes, not days. People who would rather spend time on their craft than on web development.

---

## The Core Loop

Every HasMerch product is both a revenue event and a marketing event:

```
Creator signs up
    ↓
Sets up store in under 10 minutes
    ↓
Orders NFC cards through the shop
    ↓
Hands cards out in real life — at shows, events, meetings
    ↓
Fans tap the card → land on the creator's storefront
    ↓
Fans discover HasMerch → some become creators themselves
    ↓
Creator earns, HasMerch earns, loop repeats
```

Every NFC card in the wild is a physical HasMerch advertisement. The product line and the growth engine are the same thing.

---

## Revenue Model

HasMerch is not a one-trick monetization platform. Revenue comes from multiple directions:

### 1. Transaction Fees (Stripe Connect)
Every creator sale carries a platform fee, automatically routed by Stripe Connect before the creator receives their payout. The fee decreases as creators upgrade their plan.

| Tier | Monthly Price | Transaction Fee |
|---|---|---|
| Free | $0 | 10% |
| Creator | ~$15 | 5% |
| Brand | ~$35 | 3% |
| Enterprise | $99+ | 1–2% |

### 2. Subscription Revenue
Paid tiers unlock serious creator tooling: custom themes, custom domains, affiliate programs, inventory management, team members, and priority support. The free tier exists purely to maximize creator onboarding volume.

### 3. Direct Product Sales
HasMerch sells its own products — NFC cards, creator kits, branded sticker packs — through the same storefront infrastructure that powers every creator store. The platform is its own first-party creator.

### 4. Marketplace Promotion (Phase 5)
Featured placement, homepage spotlights, and category promotion sold to creators who want more visibility on the central marketplace.

### 5. Premium Themes (Phase 5)
Paid layout presets beyond the default library, available for purchase directly through the dashboard.

---

## The NFC Advantage

NFC cards are HasMerch's signature physical product and its most powerful growth mechanic.

A HasMerch NFC card is programmed with a URL that routes through our redirect system:

```
tap.hasmerch.com/{card-uid}
      ↓
Lookup: card UID → destination URL
      ↓
301 redirect → creator.hasmerch.com
```

The redirect layer means:
- A card's destination can be changed at any time without touching the card
- Cards can point to a store, a product, a social hub, or any URL
- Analytics can be attached to tap events over time

Cards are cheap to produce, carry real perceived value, and convert every in-person interaction into a potential digital customer. No other merch platform has this. It's the thing that makes HasMerch feel inevitable once someone tries it.

---

## Platform Architecture Vision

HasMerch is built as a **multi-tenant PHP application** hosted on Cloudflare Pages. Every creator store is a subdomain of `hasmerch.com`. The root domain is HasMerch's own first-party store — using identical infrastructure to every creator, which keeps the codebase uniform and forces the platform to eat its own cooking.

### How a request flows today:

```
cxxl.hasmerch.com/shop/card-business
          ↓
.htaccess → index.php
          ↓
StoreResolver — reads HTTP_HOST, finds subdomain "cxxl"
          ↓
BrandLoader — queries DB: users.subdomain = "cxxl" → branding row
          ↓
ThemeLoader — merges preset JSON + per-store overrides → CSS variables
          ↓
ContentService — scans /content/stores/cxxl/products/*.md
          ↓
Router — /shop/card-business → product-detail view
          ↓
Layout → rendered HTML with store branding
```

### How it scales:

**Phase 1 (now):** Single server, file-based products, Stripe direct checkout.

**Phase 2 (next):** Auth system, creator dashboard, theme configurator, product manager UI.

**Phase 3:** Stripe Connect for creator payouts, subscription enforcement, order tracking.

**Phase 4:** Marketplace discovery, search, creator directory, NFC card redirect system.

**Phase 5:** Analytics, affiliate system, digital products, API access, white-label for enterprise.

---

## Technology Commitments

These are decisions made deliberately and held firmly:

**Plain PHP, HTML, CSS, JavaScript.** No React, no Next.js, no TypeScript, no Tailwind. Minimal dependencies. The simpler the stack, the longer it lasts and the easier it is for a small team to maintain.

**Markdown-first products.** Product definitions live as `.md` files with YAML front matter. Human-readable, version-controlled, editable without a UI. `ContentService` bridges these files to the rendering layer.

**Security by default.** No credentials in source control. No user input trusted without validation. No output rendered without escaping. Stripe prices always looked up server-side. These are non-negotiables, not suggestions.

**Mobile-first.** Every view designed for small screens first. The majority of storefront visitors come from phones.

**`hasmerch` is a first-party store.** The platform sells its own products through the same system every creator uses. This ensures the infrastructure is always tested under real commerce conditions.

---

## The Ten-Year Picture

HasMerch is not trying to be the platform for every online seller. It is trying to be the platform for creators who want a physical + digital brand presence without the complexity of running a business.

In ten years, the vision is:

- Tens of thousands of creator storefronts on `*.hasmerch.com`
- Millions of NFC cards and physical products in the wild
- A marketplace that surfaces great creator merch the way Bandcamp surfaces independent music
- A dashboard so simple that a touring musician can manage their whole merch operation from a phone between sets
- An affiliate system that lets fans earn a cut of sales they drive — turning every fan into a sales channel
- A digital product system for selling presets, sample packs, courses, and downloads
- An analytics layer that tells creators which products their audience actually wants

The platform that wins in this space will be the one that is **fastest to set up, most honest about fees, and most useful to creators as actual human beings** — not as accounts on a dashboard.

That is what HasMerch is built to be.

---

## What This Document Is Not

This is not a business plan. It is not a pitch deck. It is a north star — a document that answers "why does this exist and where is it going" so that every engineering decision, every design choice, and every product addition can be evaluated against a clear picture of what we are building.

When in doubt: does this make it faster to set up, more honest to use, or more useful to creators? If yes, build it. If no, don't.

---

*Last updated: 2026. Maintained by the HasMerch engineering team.*
*For technical architecture details, see `ARCHITECTURE.md` and `PROJECT_BRIEF.md`.*
*For development workflow, see `DEVELOPMENT.md`.*
