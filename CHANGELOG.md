# Changelog

## [1.5.0] - 2026-05-13

### Added
- **DIM Calculation Unit** select — choose the unit (mm, cm, m, in, yd) that matches your carrier's DIM factor. Product dimensions are converted to this unit before the formula runs, so the DIM factor value is always correct regardless of what unit the store is configured to use.
- **Multilingual support** — `load_plugin_textdomain()` loads translations from `languages/` on `plugins_loaded`. Translation template (`languages/dimensional-shipping.pot`) included for translators.
- **Greek translation** (`el`) — full admin UI translation included; the shipping method title stays in English.

### Changed
- Store dimension info notice updated to explain that dimensions are converted to the selected DIM unit (not always cm).
- DIM Factor description updated to reference the unit selector and clarify the cm/in examples.
- `sprintf` placeholders in the store info notice changed from `%s` to `%1$s`/`%2$s` so translators can reorder them.

---

## [1.4.0] - 2026-05-13

### Added
- **DIM Factor** setting — the divisor in the `(L×W×H) / DIM` formula is now configurable per zone instance (default 5000). Common values: 5000 for cm/kg, 139 for in/lb.
- **Store dimension unit notice** — the settings panel now displays the WooCommerce store dimension unit with a link to WooCommerce → Settings → General.
- Descriptive help text displayed below every settings field (previously hidden behind tooltip icons).

### Changed
- Shipping method admin description updated to explain the full formula and all configurable fields.

---

## [1.3.0] - 2026-05-12

### Added
- **ttrp.gr Plugins** top-level admin menu with a **Dimensional Shipping** submenu entry that links directly to **WooCommerce → Settings → Shipping**. The menu is shared across all ttrp.gr plugins — whichever plugin loads first registers it.

---

## [1.2.1] - 2026-05-08

### Changed
- Housekeeping: verified plugin header and documentation are consistent with WooCommerce title guidelines.

## [1.2.0] - 2026-05-08

### Added
- Self-hosted update checker (`update-checker.php`) — the plugin now checks `plugins.ttrp.gr` for new versions and surfaces them in the WordPress admin Updates screen.
- GitHub Actions release workflow — pushing a `v*` tag builds a clean plugin zip and attaches it to a GitHub release automatically.

## [1.1.0] - 2026-04-17

### Fixed
- Declared `$multiplier` and `$min_cost` as explicit class properties to resolve PHP 8.2 dynamic-property deprecation notices.
- Replaced `_e()` with `esc_html_e()` in admin notice and `__()` with `esc_html__()` in `wp_die()` call for proper output escaping.

### Added
- Declared HPOS (High-Performance Order Storage) compatibility via `FeaturesUtil::declare_compatibility()`.

### Changed
- Bumped `WC tested up to` from 8.5.0 to 9.5.0.

## [1.0.2] - initial release
