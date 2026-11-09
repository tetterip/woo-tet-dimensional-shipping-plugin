# Changelog


## [1.7.1] - 2026-11-09

### Fixed
- Escape the redirect URL at output in the shared **ttrp.gr Plugins** menu page (reported by the health check).

### Changed
- Tested up to WordPress 7.1 and WooCommerce 11.2.
- Shared ttrp-common library 1.1.0: a one-time admin notice on what update checks send to plugins.ttrp.gr, a "Data and privacy" box on **ttrp.gr Plugins → All Plugins**, and a suggested paragraph for the WordPress privacy policy guide.

---

## [1.7.0] - 2026-11-09

### Added
- **Additional Fee** setting: a fixed amount added to every order after the minimum cost (e.g. packaging or handling). Defaults to 0, so existing zones charge the same as before.
- **How the shipping cost is calculated** section in the method's settings: the steps of the calculation with the zone's own values, and a worked example for a 40 × 30 × 20 cm box. Greek translation included.

---

## [1.6.0] - 2026-09-25

### Added
- **"All Plugins" page** under **ttrp.gr Plugins**: lists the ttrp.gr plugins available to this site, with install / activate / update buttons. Installation goes through WordPress's own installer. This comes from the shared `ttrp-common/` library, which is bundled in each ttrp.gr plugin; only the newest copy on a site is loaded.

### Changed
- Updates now come through `ttrp-common/` instead of `update-checker.php` (removed). The update check also sends the installed plugin version, so the update server can show which site runs which version.

---

## [1.5.4] - 2026-09-25

### Changed
- `update-checker.php`: passes plugin icons and banners from the update server to WordPress, so the plugin gets its icon in **Dashboard → Updates** and the details modal.

---

## [1.5.3] - 2026-05-27

### Fixed
- Guard `$wp_filesystem` global before calling `move()` in the update checker's `fix_directory_name()` — call `WP_Filesystem()` if not already initialised to prevent a fatal error during plugin updates.

### Changed
- Tested up to WordPress 7.0.

---

## [1.5.2] - 2026-05-14

### Fixed
- Load plugin textdomain on `init` instead of `plugins_loaded` to suppress the WordPress 6.7+ `_load_textdomain_just_in_time` notice.

---

## [1.5.1] - 2026-05-13

### Fixed
- Removed UTF-8 BOM from the main plugin file. The BOM was being output before the HTML document, causing a stray character to appear at the start of every page.

---

## [1.5.0] - 2026-05-13

### Added
- **DIM Calculation Unit** select — choose the unit (mm, cm, m, in, yd) that matches your carrier's DIM factor. Product dimensions are converted to this unit before the formula runs, so the DIM factor value is always correct regardless of what unit the store is configured to use.
- **Multilingual support** — `load_plugin_textdomain()` loads translations from `languages/` on `init`. Translation template (`languages/dimensional-shipping.pot`) included for translators.
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
