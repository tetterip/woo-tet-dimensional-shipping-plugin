# Changelog

## [1.1.0] - 2026-04-17

### Fixed
- Declared `$multiplier` and `$min_cost` as explicit class properties to resolve PHP 8.2 dynamic-property deprecation notices.
- Replaced `_e()` with `esc_html_e()` in admin notice and `__()` with `esc_html__()` in `wp_die()` call for proper output escaping.

### Added
- Declared HPOS (High-Performance Order Storage) compatibility via `FeaturesUtil::declare_compatibility()`.

### Changed
- Bumped `WC tested up to` from 8.5.0 to 9.5.0.

## [1.0.2] - initial release
