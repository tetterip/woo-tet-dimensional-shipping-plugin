<p align="center">
  <img src=".github/icon.svg" width="112" height="112" alt="">
</p>

<h1 align="center">Dimensional Shipping for WooCommerce</h1>

<p align="center"><strong>Shipping rates from volumetric weight, (L × W × H) / DIM factor, configurable per shipping zone.</strong></p>

<p align="center">
  <a href="https://github.com/tetterip/woo-tet-dimensional-shipping-plugin/releases/latest"><img alt="Latest release" src="https://img.shields.io/github/v/release/tetterip/woo-tet-dimensional-shipping-plugin?label=release&color=7c3aed"></a>
  <a href="LICENSE"><img alt="License" src="https://img.shields.io/github/license/tetterip/woo-tet-dimensional-shipping-plugin?color=2563eb"></a>
  <img alt="WordPress 5.0+" src="https://img.shields.io/badge/WordPress-5.0%2B-21759B?logo=wordpress&logoColor=white">
  <img alt="WooCommerce 3.0+" src="https://img.shields.io/badge/WooCommerce-3.0%2B-96588A?logo=woocommerce&logoColor=white">
  <img alt="PHP 7.4+" src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white">
</p>

<p align="center">
  <a href="https://github.com/tetterip/woo-tet-dimensional-shipping-plugin/releases/latest"><strong>Download</strong></a> ·
  <a href="https://plugins.ttrp.gr/#woo-tet-dimensional-shipping-plugin">plugins.ttrp.gr</a> ·
  <a href="CHANGELOG.md">Changelog</a> ·
  <a href="https://github.com/tetterip/woo-tet-dimensional-shipping-plugin/issues/new/choose">Report a bug</a> ·
  <a href=".github/CONTRIBUTING.md">Contributing</a>
</p>

<!--
  Screenshots: save PNGs (about 1600 px wide) in .github/screenshots/ and list them here, e.g.
  <p align="center"><img src=".github/screenshots/checkout.png" width="720" alt="…"></p>
-->

A WooCommerce shipping method that calculates shipping cost from each product's volumetric weight using the formula **(L × W × H) / DIM factor × multiplier**. The DIM factor, calculation unit, cost multiplier, minimum cost and an additional fee are all configurable per shipping zone.

## Requirements

- WordPress 5.0+ (7.1 compatible)
- WooCommerce 3.0+
- PHP 7.4+

## Installation

1. Upload the `woo-tet-dimensional-shipping-plugin` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Go to **WooCommerce → Settings → Shipping**, add or edit a shipping zone, and click **Add shipping method → Dimensional Shipping**.

> **Shortcut:** After activation the shipping settings are also accessible via **ttrp.gr Plugins → Dimensional Shipping** in the WordPress admin sidebar.

## Configuration

Each shipping zone instance exposes the following settings:

| Setting | Description | Default |
|---|---|---|
| **Title** | Label shown to customers at checkout | `Dimensional Shipping` |
| **Tax Status** | Whether the shipping cost is taxable | `Taxable` |
| **DIM Calculation Unit** | The dimension unit your carrier uses for the DIM factor (mm, cm, m, in, yd) | `cm` |
| **DIM Factor** | Divisor in the volumetric formula — match this to your carrier's published value | `5000` |
| **Cost Multiplier** | Scales the dimensional weight to a currency amount | `1` |
| **Minimum Cost** | Floor price applied when the calculated cost is lower | `0` |
| **Additional Fee** | Fixed amount added to every order after the minimum cost (e.g. packaging or handling) | `0` |

### How cost is calculated

```
dim_weight  = (length × width × height) / dim_factor   # dimensions in the selected unit
item_cost   = dim_weight × quantity
order_cost  = sum(item_cost for all shippable items) × multiplier
final_cost  = max(order_cost, minimum_cost) + additional_fee
```

The settings screen also shows these steps with your own values and a worked example (one 40 × 30 × 20 cm box), updated each time you save.

Product dimensions are automatically converted from the store's configured unit to the **DIM Calculation Unit** via `wc_get_dimension()` before the formula runs. Products with any dimension set to zero are excluded.

### Choosing the right DIM factor and unit

| Carrier unit | Typical DIM factor |
|---|---|
| Centimeters (cm) | 5000 |
| Inches (in) | 139 |

Set **DIM Calculation Unit** to match your carrier, then enter the DIM factor they provide. The plugin handles the conversion from whatever unit your store is configured to use.

## Languages

The plugin is fully translatable. A POT template is included at `languages/dimensional-shipping.pot`. A Greek (`el`) translation is bundled. To add a new language:

1. Copy `dimensional-shipping.pot` to `dimensional-shipping-{locale}.po` (e.g. `dimensional-shipping-de_DE.po`).
2. Fill in the `msgstr` entries using [Poedit](https://poedit.net/) or any PO editor.
3. Compile to `.mo` and place both files in the `languages/` folder.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Updates and privacy

This plugin gets its updates from the ttrp.gr update server (`https://plugins.ttrp.gr/`), not from WordPress.org.
When WordPress checks for updates, and when you open **ttrp.gr Plugins → All Plugins**, your site sends that server:

- the plugin's slug and installed version,
- the WordPress version and the site's address (in the request's `User-Agent` header),
- the admin language (All Plugins page only).

The server logs these requests, with the IP address, for 180 days and keeps a list of which sites run which plugin
version. No order, customer or other personal data is sent. Questions: info@ttrp.gr. Details: [privacy policy](https://plugins.ttrp.gr/?page=privacy&lang=en) and [terms of use](https://plugins.ttrp.gr/?page=terms&lang=en); administrators see a one-time notice about this in wp-admin.

## Contributing

Bug reports, ideas and pull requests are welcome. See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for how to set up, test and translate the plugin.

## Security

Please report vulnerabilities privately to **info@ttrp.gr**, not in a public issue. See [SECURITY.md](SECURITY.md).

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).
