# Dimensional Shipping for WooCommerce

A WooCommerce shipping method that calculates shipping cost from each product's volumetric weight using the formula **(L × W × H) / DIM factor × multiplier**. The DIM factor, calculation unit, cost multiplier, and minimum cost are all configurable per shipping zone.

## Requirements

- WordPress 5.0+ (7.0 compatible)
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

### How cost is calculated

```
dim_weight  = (length × width × height) / dim_factor   # dimensions in the selected unit
item_cost   = dim_weight × quantity
order_cost  = sum(item_cost for all shippable items) × multiplier
final_cost  = max(order_cost, minimum_cost)
```

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

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).
