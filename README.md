# The360Hub theme

Mobile-first WordPress + WooCommerce theme for The360Hub (UAE electronics).
Architecture, design tokens and roadmap: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

The deployable theme is the [`the360hub/`](the360hub) folder. Built CSS/JS/fonts are
committed, so deploying doesn't need Node.

Install: `npm run package` creates `dist/the360hub.zip`; upload it in
**Appearance → Themes → Add New → Upload Theme**, then activate. Or copy the
`the360hub/` folder to `wp-content/themes/`.

## Develop

```bash
npm install
npm run build        # fonts → CSS (Tailwind v4) → JS (esbuild)
npm run watch:css    # while editing templates/CSS
npm run check        # contrast, asset size budgets, PHP lint
```

Source lives in `src/css` and `src/js`. Never edit `the360hub/assets` by hand.

Coding standards: `phpcs` with WPCS 3 using `phpcs.xml.dist`.

## Local test store

`tools/local-wp/` builds a WordPress + WooCommerce site on SQLite (no MySQL) with a
sample catalogue, then serves it with PHP's built-in server:

```bash
tools/local-wp/setup.sh   # expects WordPress, SQLite plugin and WooCommerce sources; see the script header
tools/local-wp/serve.sh   # http://localhost:8080  (admin / admin)
node tools/browser-test.mjs http://localhost:8080 screenshots
```

## Configuration

Everything visual is configured under **Appearance → Customize → The360Hub theme**:
brand colors, fonts, header, mobile navigation, search, product cards, homepage
module order, banners, trust items, footer and performance. Menus: *Mobile menu*,
*Desktop category bar*, *Footer column 1/2*.
