# Dimensional Shipping for WooCommerce — Claude Code Context

## Project overview

Single-file WooCommerce shipping plugin. Calculates shipping cost from product volumetric weight: `(L × W × H) / 5000 × multiplier`, with a configurable minimum cost floor. No build step, no dependencies beyond WooCommerce.

## File structure

```
dimensional-shipping-plugin.php   # Entire plugin — one file
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

## WooCommerce specifics

- The shipping method class lives inside `dimensional_shipping_init()` to ensure `WC_Shipping_Method` is loaded first.
- HPOS compatibility is declared via `FeaturesUtil::declare_compatibility('custom_order_tables')` — keep this whenever touching the plugin header.
- Use `wc_get_dimension($value, 'cm')` for unit conversion; the store unit is the implicit source.

## Commit style

Short imperative subject line, no period. Body optional. Example:

```
Fix minimum cost not applied when multiplier is zero
```
