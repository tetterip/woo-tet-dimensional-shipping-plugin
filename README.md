# Dimensional Shipping for WooCommerce

A WooCommerce shipping method that calculates shipping cost based on product volumetric weight using the formula **(L × W × H) / 5000** (dimensions in cm).

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Installation

1. Upload the `woo-tet-dimensional-shipping-plugin` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Go to **WooCommerce → Settings → Shipping**, add or edit a shipping zone, and click **Add shipping method → Dimensional Shipping**.

## Configuration

Each shipping zone instance exposes four settings:

| Setting | Description | Default |
|---|---|---|
| **Title** | Label shown to customers at checkout | `Dimensional Shipping` |
| **Tax Status** | Whether the shipping cost is taxable | `Taxable` |
| **Cost Multiplier** | Scales the dimensional value to a currency amount | `1` |
| **Minimum Cost** | Floor price applied when the calculated cost is lower | `0` |

### How cost is calculated

```
dimensional_value = (length_cm × width_cm × height_cm) / 5000
item_cost         = dimensional_value × quantity
order_cost        = sum(item_cost for all shippable items) × multiplier
final_cost        = max(order_cost, minimum_cost)
```

Products with any dimension set to zero are excluded from the calculation. Dimensions are automatically converted from the store's unit to centimetres via `wc_get_dimension()`.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPLv2 or later.
