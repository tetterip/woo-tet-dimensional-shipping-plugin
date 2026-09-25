# Dimensional Shipping for WooCommerce — Claude Code Context

## Project overview

Single-file WooCommerce shipping plugin. Calculates shipping cost from product volumetric weight: `(L × W × H) / dim_factor × multiplier`, with a configurable minimum cost floor. The DIM factor, calculation unit (mm/cm/m/in/yd), multiplier, and minimum cost are all per-zone-instance settings. No build step, no dependencies beyond WooCommerce.

## File structure

```
dimensional-shipping-plugin.php   # Entire plugin — one file
ttrp-common/                      # Shared ttrp.gr library: updates + "All Plugins" page (synced from the update server repo; never edit)
assets/
  ttrp-logo.svg                   # Admin menu icon
languages/
  dimensional-shipping.pot        # Translation template (update after string changes)
  dimensional-shipping-el.po      # Greek translation source
  dimensional-shipping-el.mo      # Greek translation binary (compiled from .po)
CHANGELOG.md
README.md
.gitignore
.gitattributes
CLAUDE.md                         # This file
```

## Key conventions

- **One file** — keep all logic in `dimensional-shipping-plugin.php`. Do not split into includes unless the file grows substantially.
- **No build tooling** — plain PHP, no Composer, no npm. Don't introduce them unless explicitly asked.
- **WordPress coding standards** — use `esc_html_e()` / `esc_html__()` for output, never raw `_e()` / `__()` in HTML context.
- **Line endings** — LF only (enforced by `.gitattributes`).

## Versioning

Follows [Semantic Versioning](https://semver.org/):
- **Patch** (x.y.Z) — bug fixes, no new behaviour
- **Minor** (x.Y.0) — backwards-compatible additions (new settings, new compatibility declarations)
- **Major** (X.0.0) — breaking changes to the shipping calculation or settings schema

Always update `Version:` in the plugin header **and** `CHANGELOG.md` together.

## Compatibility targets

| Requirement | Value |
|---|---|
| PHP | 7.4+ (no deprecations on 8.2/8.3) |
| WordPress | 5.0+ |
| WooCommerce | 3.0+ |
| WC tested up to | keep in sync with latest stable WC |

## Admin menu

The plugin registers a **ttrp.gr Plugins → Dimensional Shipping** entry via `ttrp_ds_maybe_register_brand_menu()` / `ttrp_ds_add_submenu()` / `ttrp_ds_redirect()` at the bottom of the main file. Clicking the submenu redirects (via `admin_init`) to **WooCommerce → Settings → Shipping**. The top-level menu is shared across all ttrp.gr plugins — guarded by a `global $menu` check so only the first active plugin registers it.

## WooCommerce specifics

- The shipping method class lives inside `dimensional_shipping_init()` to ensure `WC_Shipping_Method` is loaded first.
- HPOS compatibility is declared via `FeaturesUtil::declare_compatibility('custom_order_tables')` — keep this whenever touching the plugin header.
- Dimensions are converted via `wc_get_dimension($value, $dim_unit)` where `$dim_unit` is the admin-selected DIM calculation unit, not always `'cm'`. The store unit is the implicit source; `wc_get_dimension` reads `woocommerce_dimension_unit` internally.

## Multilingual

- Translations live in `languages/`. The text domain is `dimensional-shipping`.
- `load_plugin_textdomain()` is called on `init`.
- After adding or changing any translatable string: update the `.pot` file and recompile any bundled `.po` files to `.mo`. Use `wp i18n make-pot` (WP-CLI) or the Node compile script pattern used when the Greek translation was created.
- The `.mo` is a compiled binary — always commit both the `.po` source and the `.mo` together.

## Commit style

Short imperative subject line, no period. Body optional. Example:

```
Fix minimum cost not applied when multiplier is zero
```
