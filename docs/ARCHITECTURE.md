# The360Hub Theme — Architecture

Custom WordPress + WooCommerce theme for The360Hub, a UAE electronics store.
Roughly 99% of traffic is mobile, so every decision below starts from a
320–430px viewport and enhances upward. Sharaf DG UAE was used only as a
reference for *what* a UAE electronics store needs (IA, discovery, delivery,
merchandising). No layout, copy, iconography or visual identity is taken from it.

Contents:

1. [Information architecture](#1-information-architecture)
2. [Page architecture](#2-page-architecture)
3. [Mobile UX architecture](#3-mobile-ux-architecture)
4. [Component architecture](#4-component-architecture)
5. [WooCommerce integration architecture](#5-woocommerce-integration-architecture)
6. [Design token system](#6-design-token-system)
7. [Folder structure](#7-folder-structure)
8. [Development phases](#8-development-phases)

---

## 1. Information architecture

### Catalogue model (all native WooCommerce)

| Concept | Source of truth | Notes |
|---|---|---|
| Departments / categories | `product_cat` (hierarchical) | Top level = department (Mobiles, Laptops, TV & Audio, Gaming, Smart Home, Accessories…). Max 3 levels deep. |
| Brands | `product_brand` (native since WooCommerce 9.6) | Filterable via `the360hub_brand_taxonomy` so a legacy brand taxonomy (e.g. `pa_brand`) can be used instead. |
| Specs & filter facets | Global attributes `pa_*` | e.g. `pa_storage`, `pa_ram`, `pa_color`, `pa_screen-size`. Used for variations, filters and the spec table. |
| Merchandising flags | Native: *featured*, *on sale*, `product_tag` | Home sections query these; no custom tables. |
| Prices, stock, orders, reviews | WooCommerce core | Never duplicated. |

### Navigation hierarchy

```
Home
├── Departments (product_cat level 1)
│   └── Sub-categories (level 2) → leaf categories (level 3)
├── Brands (product_brand)
├── Deals (on-sale products)          ─ merchandised views of the
├── New arrivals (date desc)          ─ same catalogue, not
├── Trending (total_sales desc)       ─ separate data
├── Search (products · categories · brands)
├── Wishlist
├── Account (WooCommerce My Account)
└── Cart → Checkout → Order received
```

### Global utilities
Deliver-to emirate (location), delivery promise, WhatsApp support, returns and
warranty info, and payment trust marks (Tabby / Tamara / COD). These appear in
the header (location chip), the trust section, the product page and the footer.

---

## 2. Page architecture

Each page is a vertical stack of modules. Order below is the mobile order.

| Page | Template | Modules (mobile order) |
|---|---|---|
| **Home** | `front-page.php` | Hero banner → Quick categories (scroll row) → Hot deals → Featured → Promo banners → New arrivals → Shop by category (grid) → Trending → Brand showcase → Trust/services → Recently viewed → Footer. Each module can be toggled, re-ordered and re-titled in theme settings. |
| **Category / shop** | `woocommerce/archive-product.php` | Breadcrumb → H1 + product count → Sub-category chips → Toolbar (Sort · Filter, sticky) → Product grid → Pagination / load more → Category description (SEO, below the grid). |
| **Product** | `woocommerce/single-product.php` + parts | Gallery (swipe) → Title, brand, rating → Price block (current, was, save) → Stock + delivery ETA → Variations → Qty → Add to cart / Buy now → Highlights → Payment options (Tabby/Tamara widgets) → Specs → Description → Delivery & warranty → Reviews → Related → Recently viewed. Sticky purchase bar replaces the bottom nav. |
| **Search results** | `search.php` / product archive | Same as category, scoped to the query. |
| **Cart** | `woocommerce/cart/*` | Items (qty stepper, remove) → Coupon → Delivery info → Totals → Checkout CTA (sticky) → Cross-sells. |
| **Checkout** | `woocommerce/checkout/*` (classic) | Contact → Delivery address → Exact location → Payment → Collapsible order summary → Terms → Place order (sticky). |
| **Wishlist** | Page template `page-templates/wishlist.php` | Product grid, empty state. |
| **Account** | WooCommerce My Account | Restyled navigation as a list of large rows. |
| **Content pages / 404** | `page.php`, `404.php` | Readable prose column, search + popular categories on 404. |

---

## 3. Mobile UX architecture

Baseline viewport: **360×740**. Everything is checked at 320, 375, 390, 414, 430.

### Chrome (persistent UI)

```
┌──────────────────────────────┐  ← safe-area-inset-top
│ ☰  The360Hub       ♡  🛒 3   │  56px  header row (sticky)
│ [🔍 Search products, brands] │  48px  search row (hides on scroll down,
├──────────────────────────────┤        returns on scroll up)
│                              │
│           content            │
│                              │
├──────────────────────────────┤
│ Home  Categories  ♡  Acct  🛒│  56px  bottom nav (fixed)
└──────────────────────────────┘  ← + env(safe-area-inset-bottom)
```

* **Header** is `position: sticky`. On scroll down the whole header is
  translated up by the search-row height (transform only, no layout shift).
  Without JS, and with `prefers-reduced-motion`, it simply stays in place.
* **Bottom nav** is fixed, five items, icon + label, ≥48×56px targets. Active
  item uses `aria-current="page"`. The body gets bottom padding equal to the
  nav height plus the safe area, so content is never hidden behind it. From
  `lg` (≥1024px) up it is removed and the header grows a full category bar.
* **Viewport**: `width=device-width, initial-scale=1, viewport-fit=cover`.
  Zoom is never disabled.

### Overlays
One pattern, built on native `<dialog>` + `showModal()` (free focus trap, Esc,
inert background, no library):

| Overlay | Mobile presentation | ≥lg |
|---|---|---|
| Menu drawer | Left sheet, 88vw max 380px | Not used (mega bar) |
| Search | Full screen | Dropdown panel under the header field |
| Filters | Full-screen sheet, sticky *Clear all* / *Apply (n)* footer | Left sidebar |
| Sort | Bottom sheet, radio list | Inline select |
| Emirate picker | Bottom sheet | Popover |

The back button closes an open overlay (history state pushed on open).

### Interaction rules
* Tap targets ≥44×44px, 8px minimum gap.
* Inputs are 16px so iOS does not zoom on focus.
* Feedback within 100ms (button busy state, skeletons for async lists).
* Motion: 150–250ms opacity/transform only; none under `prefers-reduced-motion`.
* Horizontal scroll rows use CSS scroll-snap and show a partial next card so
  scrollability is visible. No auto-rotating carousels.

---

## 4. Component architecture

Components are PHP template parts with a single `$args` array (passed through
`get_template_part()`), plus a matching CSS block in `src/css/components/`.
JS is attached by `data-` attributes, never by component-specific inline script.

| Component | Template part | JS module | Notes |
|---|---|---|---|
| Header | `template-parts/header/site-header.php` | `core.js` (scroll hide) | Logo from Customizer; `<h1>` only on the home page, visually hidden. |
| Menu drawer | `template-parts/navigation/drawer.php` | `core.js` (dialog) | Category tree cached in a transient, flushed on category edits. |
| Bottom nav | `template-parts/navigation/bottom-nav.php` | `core.js` (counts) | Items and labels from settings. |
| Search overlay | `template-parts/search/overlay.php` | `search.js` | Works as a plain GET form without JS. |
| Product card | `template-parts/product/card.php` | `wishlist.js`, `cart.js` | One card for every grid and rail. Data prepared by `WooCommerce\Product_Card`. |
| Product grid / rail | `template-parts/product/grid.php`, `rail.php` | — | Grid for archives, horizontal rail for home modules. |
| Section header | `template-parts/components/section-header.php` | — | H2 + "View all" link. |
| Home modules | `template-parts/home/{module}.php` | — | Registered in `Home\Sections`; order and titles from settings. |
| Icons | `Icons::render( 'cart' )` | — | Inline SVG sprite printed once in the footer (`<use href="#i-cart">`). Paths based on Lucide (ISC). |
| Price block | inside card / product summary | — | Uses `wc_price()`; savings computed from WooCommerce prices. |

### JS modules (vanilla, no jQuery dependency, no framework)

| Module | Loaded on | Responsibility |
|---|---|---|
| `core.js` (2.2KB gz) | every page, `defer` | sheets, header scroll, cart badge sync, card add-to-cart, wishlist toggle, recently-viewed tracking, toast |
| `search.js` (2.0KB gz) | injected by `core.js` the first time search opens | instant search, recent/popular searches |
| `lists.js` (0.8KB gz) | homepage and wishlist page only | renders wishlist / recently viewed cards |
| `product.js` | single product only (phase 3) | gallery, variations, sticky bar |
| `filters.js` | archives only (phase 2) | filter sheet |
| `checkout.js` | checkout only (phase 5) | inline validation, summary toggle |

---

## 5. WooCommerce integration architecture

### Principles
* No WooCommerce core edits. Everything goes through hooks, filters and
  template overrides in `the360hub/woocommerce/`.
* Overrides are kept to the minimum set. Each one records the WooCommerce
  template version it was copied from (`@version`) so *System Status* flags
  outdated overrides after upgrades.
* WooCommerce's own actions are still fired inside overridden templates
  (`woocommerce_before_shop_loop_item`, `woocommerce_after_shop_loop_item`,
  `woocommerce_before_main_content`, etc.). The theme removes only the
  *default* callbacks it replaces, so plugins that add to those hooks
  (Tabby/Tamara promo snippets, Rank Math, analytics) keep working.

### Loop
`woocommerce/content-product.php` renders `template-parts/product/card.php`.
`loop/loop-start.php` outputs the responsive grid. Column counts are CSS, not
`loop_shop_columns`. Mobile 2, ≥640px 3, ≥1024px 4, ≥1280px 5.

### AJAX / REST surface

| Endpoint | Method | Auth | Purpose |
|---|---|---|---|
| `?wc-ajax=t360_search` | GET | public, read-only | Instant search: products, categories, brands. 2–64 char query, max 6 products, object-cached 10 min, `Cache-Control: public, max-age=300` so a CDN absorbs bursts (rate limiting belongs at the CDN/WAF). |
| `?wc-ajax=t360_cards` | GET | public, read-only | Rendered cards for ≤24 IDs (wishlist, recently viewed). Only published, visible products. |
| `?wc-ajax=t360_cart_count` | GET | session, read-only | Cart item count + hash for the badge. No customer data. |
| `?wc-ajax=add_to_cart` | POST | WooCommerce core | Card add-to-cart (core endpoint, not re-implemented). |
| Later phases | POST | nonce + capability | Cart qty/remove, coupon, wishlist sync (logged-in). |

Read-only public endpoints deliberately take no nonce so they work behind full
page caches. Anything that changes state uses a nonce, and a capability or
ownership check where it touches account data. No endpoint returns order,
address or customer data.

### Cart badge without `wc-cart-fragments`
WooCommerce's fragments script runs an AJAX request on page load and pulls in
jQuery. The theme dequeues it. Instead `core.js` reads the
`woocommerce_cart_hash` cookie, compares it with the hash cached in
`sessionStorage`, and only calls `t360_cart_count` when they differ.

### Checkout (phase 5)
The classic shortcode checkout (`[woocommerce_checkout]`) is used because it
supports template overrides and all current gateways (Paymob, Tabby, Tamara,
COD, WhatsApp/Pay on Delivery) support it. The theme changes presentation only:
field markup/order via `woocommerce_checkout_fields`, layout via
`checkout/form-checkout.php`, and inline errors by mapping WooCommerce's
`checkout_error` notices back to their fields. The payment method list,
`woocommerce_checkout_order_review` and `#place_order` keep their core markup
and events, so gateway scripts continue to bind to them. Checkout JS loads only
on `is_checkout()`.

### SEO compatibility
Semantic HTML5 landmarks, one H1 per page, WooCommerce's product JSON-LD left
untouched, breadcrumbs via `woocommerce_breadcrumb()` (Rank Math can replace
them). No meta tags are output by the theme, so SEO plugins stay in control.

---

## 6. Design token system

Tokens are CSS custom properties on `:root`. Tailwind v4 maps utilities to them
(`@theme inline`), so `bg-cta` means `var(--t360-cta)`. The Customizer
overrides the *brand* tokens by printing a small `:root{}` block. Nothing in
templates uses raw hex values.

### Color
The palette is original. The ui-ux-pro-max colour search returned no
electronics-retail match, so it was built by hand and checked with the WCAG
formula (ratios against white unless noted).

| Token | Default | Use | Contrast |
|---|---|---|---|
| `--t360-primary` | `#0B1220` Ink | Brand, headings, dark surfaces, *Buy now* | 18.7:1 |
| `--t360-secondary` | `#1D4ED8` Cobalt | Links, active nav, selected states | 6.7:1 |
| `--t360-cta` | `#1238C4` Deep cobalt | *Add to cart*, primary buttons | 8.8:1 (white text) |
| `--t360-focus` | follows secondary | 2px focus ring + 2px offset | 6.7:1 |
| `--t360-text` | `#0B1220` | Body text | 18.7:1 |
| `--t360-text-2` | `#475467` | Secondary text | 7.7:1 |
| `--t360-text-3` | `#667085` | Meta, placeholders | 5.0:1 |
| `--t360-bg` | `#FFFFFF` | Page | — |
| `--t360-surface` | `#F4F6F8` | Section bands, image wells | text-2 7.1:1 |
| `--t360-border` | `#E4E7EC` | Dividers (decorative) | — |
| `--t360-border-strong` | `#7D8797` | Input borders (must be ≥3:1) | checked in build |
| `--t360-deal` | `#C8102E` | Discount badge, deal price | 5.9:1 |
| `--t360-save` | `#067647` | "Save AED…", in stock | 5.7:1 |
| `--t360-warn` | `#B54708` | Low stock | 5.4:1 |
| `--t360-star` | `#F79009` | Rating stars (decorative; rating also given as text) | — |

Brand tokens exposed in the Customizer: primary, secondary, CTA.

### Typography
* **Pairing:** Rubik (headings) + Nunito Sans (body), the ui-ux-pro-max
  "E-commerce Clean" match. Self-hosted variable WOFF2 (Latin subset),
  `font-display: swap`, body font preloaded. A **System** option in the
  Customizer serves zero font bytes.
* **Scale (mobile → ≥lg):**

| Token | Mobile | Desktop | Use |
|---|---|---|---|
| `--t360-fs-xs` | 12px | 12px | Badges, legal |
| `--t360-fs-sm` | 14px | 14px | Card titles, meta |
| `--t360-fs-base` | 16px | 16px | Body, inputs |
| `--t360-fs-lg` | 18px | 20px | Prices on PDP, sub-heads |
| `--t360-fs-xl` | 20px | 24px | Section titles (H2) |
| `--t360-fs-2xl` | 24px | 32px | Page titles (H1) |

Line height 1.5 body, 1.25 headings. Prices use `font-variant-numeric: tabular-nums`.

### Space, shape, elevation, motion
* **Spacing:** 4px base (Tailwind's default scale). Page gutter 16px mobile,
  24px tablet, 32px desktop. Max content width 1320px.
* **Radius:** `--t360-radius-sm` 6px (inputs, badges), `--t360-radius` 8px
  (cards, buttons), `--t360-radius-lg` 12px (sheets, banners). No pill cards.
* **Elevation:** flat by default, borders not shadows. One shadow token for
  things that float over content (sticky bars, sheets).
* **Motion:** `--t360-dur-fast` 150ms, `--t360-dur` 220ms, `--t360-ease`
  `cubic-bezier(.2,0,0,1)`. All zeroed under `prefers-reduced-motion`.
* **Layout tokens:** `--t360-header-h` 56px, `--t360-search-h` 48px,
  `--t360-bottom-nav-h` 56px, `--t360-safe-bottom` `env(safe-area-inset-bottom)`.

### Breakpoints
Tailwind defaults, mobile-first: `sm` 640 · `md` 768 · `lg` 1024 · `xl` 1280.

---

## 7. Folder structure

The repository root holds tooling. The deployable theme is the self-contained
`the360hub/` directory (zip it, or symlink it into `wp-content/themes/`).

```
/
├── docs/ARCHITECTURE.md
├── package.json              Tailwind v4 CLI + esbuild (only dev dependencies)
├── src/
│   ├── css/
│   │   ├── app.css           entry: tokens, base, components
│   │   ├── tokens.css
│   │   ├── base.css
│   │   └── components/*.css
│   └── js/*.js               ES modules, bundled/minified by esbuild
├── phpcs.xml.dist            WordPress coding standards (WPCS 3)
├── tools/                    local WP test stack, browser test, contrast + size checks
└── the360hub/                ← the theme
    ├── style.css             theme header only
    ├── functions.php         bootstrap + autoloader
    ├── header.php  footer.php  index.php  front-page.php  page.php  search.php  404.php
    ├── page-templates/wishlist.php
    ├── inc/
    │   ├── helpers.php                       t360_setting(), t360_icon() …
    │   ├── core/                             class-theme, class-assets (incl. token output),
    │   │                                     class-settings, class-customizer, class-icons,
    │   │                                     class-performance
    │   ├── home/class-sections.php
    │   └── woocommerce/                      class-setup, class-catalog (categories,
    │                                         brands, cached ID lists), class-product-card,
    │                                         class-ajax
    ├── template-parts/
    │   ├── header/  navigation/  search/  footer/
    │   ├── home/                             one file per module
    │   ├── product/                          card, grid, rail
    │   └── components/
    ├── woocommerce/                          template overrides only
    │   ├── archive-product.php  content-product.php
    │   └── loop/ loop-start.php loop-end.php
    ├── assets/                               build output (committed)
    │   ├── css/app.css
    │   ├── js/*.js
    │   └── fonts/*.woff2
    └── languages/
```

Classes live in the `The360Hub\` namespace and are loaded by a small
autoloader that maps `The360Hub\Core\Theme` to `inc/core/class-theme.php`
(WordPress file naming). Built assets are committed so the theme can be
deployed without Node.

---

## 8. Development phases

Each phase ends with: PHP lint, a render test against a local WordPress +
WooCommerce install with sample products, a check at 360px and 1280px, and an
asset-size check.

| Phase | Scope | Exit criteria |
|---|---|---|
| **1. Foundation** *(done)* | Theme bootstrap, settings + Customizer, design tokens, typography, header, menu drawer, search UI + endpoint, bottom nav, homepage modules, product card, WooCommerce loop/archive (sort + pagination), wishlist storage, recently-viewed storage | Home and shop render with real products; no PHP notices; global CSS ≤ 30KB gz, global JS ≤ 10KB gz; keyboard-operable header, drawer, search. |
| **2. Discovery** | Filter sheet (category, brand, price, availability, rating, attributes) with counts, sort sheet, load more, categories page, emirate picker + delivery ETA | Filters are shareable URLs; results match WooCommerce's own queries. |
| **3. Product page** | Gallery, variation swatches over WooCommerce's variation form, sticky purchase bar, buy now, highlights/specs/description, delivery & warranty, reviews, related | Variations, stock and prices identical to core behaviour; JSON-LD unchanged. |
| **4. Cart** | AJAX cart (qty, remove, subtotal), coupon, delivery info, cross-sells, mini-cart | All mutations nonce-checked; works with JS off (plain form posts). |
| **5. Checkout** | Custom presentation of the classic checkout, inline field errors, exact location, collapsible summary, sticky place-order | Test orders pass with Paymob, Tabby, Tamara, COD, WhatsApp/POD in staging. |
| **6. Account & content** | My Account, logged-in wishlist sync, search results page, 404, content pages, RTL/Arabic readiness | — |
| **7. Hardening** | Lighthouse mobile budgets, accessibility audit, Rank Math/cache plugin compatibility, release packaging | LCP < 2.5s on mid-range Android over 4G for home, category, product. |

### Phase 1 status

Built and verified against WordPress 6.8 + WooCommerce 9.9 (SQLite, sample catalogue):

* `npm run check`: all WCAG contrast pairs pass; CSS 8.1KB gz, core.js 2.2KB gz.
* `phpcs`: 0 WordPress-Extra / WordPress-Docs violations.
* `node tools/browser-test.mjs`: 28 checks at 360 / 768 / 1100 / 1440px, covering the
  drawer, instant search, recent searches, AJAX add to cart, cart badge sync,
  wishlist, recently viewed, grid columns, header condense, tap-target sizes,
  skip link and JS errors.
* Home and catalogue pages load no jQuery. WooCommerce's scripts and CSS still load
  on product, cart, checkout and account pages until phases 3–6 replace them.

Deviations from the plan above:

* WebP conversion applies to JPEG uploads only. GD cannot convert palette PNGs.
* The single product page uses WooCommerce's default template inside the theme
  shell until phase 3.

---

## Design v2: gadget-store redesign

Design direction came from the ui-ux-pro-max skill. The **Bento Grid** style (verified
match) drives the hero and promo tiles. The **E-commerce** product profile
("Vibrant & Block-based", brand primary + success green) sets bolder colour blocks
on a grey page with white cards. The skill's rule `auto-rotation-controls` applies to
the slider: it pauses on hover and focus, never autoplays under reduced motion, and
always shows a pause button. Its palette and font suggestions for this query were
off-target (pharmacy green, editorial serif), so colours and fonts are selectable
presets instead. Every preset is contrast-checked in `tools/check-presets.php`.

### Homepage modules (reorderable in the Customizer)

| Module | Notes |
|---|---|
| Hero | Slider, plus a "Deal of the day" and a "Just launched" product tile. Slides build themselves from featured products (brand, name, price, photo) unless a banner image is uploaded. |
| Category icons | Circles or tiles. |
| Flash deals | Dark band with a countdown (daily reset or fixed end date; hides after the end date). |
| Promo tiles | Bento grid, defaults to top categories. |
| Product tabs | Featured / Best sellers / New / Top rated / On sale. Tabs after the first load on demand. |
| Category spotlight ×2 | Category banner plus that category's best sellers. |
| Brands, New arrivals, Hot deals, Featured, Best sellers, Top rated rows, CTA banner, Trust, Recently viewed | — |

### Customizer panels

* **Design:** colour preset (6) plus 8 colour overrides with live preview; corner style;
  button shape; shadows; content width; animations; font pairing (5 + system);
  text size; heading weight; product card style, image shape/fit, hover image,
  add-to-cart style, badges, free-delivery label, instalments; shop columns.
* **Header, footer & navigation:** top bar and links; header style (dark, light or
  accent); sticky and condense behaviour; logo sizes; mega menu; highlighted nav
  link; bottom nav; floating WhatsApp; popular searches; footer style, social
  links, newsletter shortcode, payments.
* **Homepage:** section order and visibility, hero slides and side tiles, promo
  tiles, flash deals, tabs, spotlights, CTA banner, trust items, titles.

### Weight

The theme zip is still well under 1MB. A visitor downloads about 12KB of CSS and
2.6KB of JS (plus 1.5KB on the homepage), all gzipped, plus one font pairing.
