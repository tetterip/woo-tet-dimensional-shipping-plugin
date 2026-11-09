<?php
/**
 * Plugin Name:       Dimensional Shipping for WooCommerce
 * Plugin URI:        https://ttrp.gr
 * Description:       Custom shipping method that calculates shipping cost based on product dimensions (L×W×H)/DIM factor.
 * Version:           1.7.0
 * Author:            Michalis Tetteris
 * Author URI:        https://ttrp.gr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://plugins.ttrp.gr/
 * Text Domain:       dimensional-shipping
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * Requires at least: 5.0
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * WC requires at least: 3.0
 * WC tested up to:   10.7.0
 */

defined( 'ABSPATH' ) || exit;

add_action('init', function() {
    load_plugin_textdomain('dimensional-shipping', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables', __FILE__, true
        );
    }
});

/**
 * Check if WooCommerce is active
 */
function dimensional_shipping_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'dimensional_shipping_woocommerce_notice');
        return false;
    }
    return true;
}

/**
 * Display WooCommerce requirement notice
 */
function dimensional_shipping_woocommerce_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e('Dimensional Shipping requires WooCommerce to be installed and active.', 'dimensional-shipping'); ?></p>
    </div>
    <?php
}

/**
 * Initialize the shipping method
 */
function dimensional_shipping_init() {
    if (!dimensional_shipping_check_woocommerce()) {
        return;
    }

    if (!class_exists('WC_Dimensional_Shipping_Method')) {
        /**
         * Dimensional Shipping Method Class
         */
        class WC_Dimensional_Shipping_Method extends WC_Shipping_Method {
            public $multiplier;
            public $min_cost;
            public $dim_factor;
            public $dim_unit;
            public $extra_fee;

            /**
             * Constructor
             */
            public function __construct($instance_id = 0) {
                $this->id                 = 'dimensional_shipping';
                $this->instance_id        = absint($instance_id);
                $this->method_title       = __('Dimensional Shipping', 'dimensional-shipping');
                $this->method_description = __('Calculates shipping cost from each product\'s volume: (Length × Width × Height) ÷ DIM factor × multiplier. The DIM factor and cost multiplier are configurable per shipping zone. A minimum cost and an additional fee can also be set.', 'dimensional-shipping');
                
                // Zone based support
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                );
                
                // Initialize settings
                $this->init();
            }
            
            /**
             * Initialize settings
             */
            public function init() {
                $this->init_form_fields();
                $this->init_settings();
                
                // Define user set variables
                $this->title      = $this->get_option('title');
                $this->tax_status = $this->get_option('tax_status');
                $this->dim_factor = $this->get_option('dim_factor');
                $this->dim_unit   = $this->get_option('dim_unit');
                $this->multiplier = $this->get_option('multiplier');
                $this->min_cost   = $this->get_option('min_cost');
                $this->extra_fee  = $this->get_option('extra_fee');
                
                // Save settings
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }
            
            /**
             * Define settings fields
             */
            public function init_form_fields() {
                $dim_unit = get_option('woocommerce_dimension_unit', 'cm');

                $this->instance_form_fields = array(
                    'title' => array(
                        'title'       => __('Title', 'dimensional-shipping'),
                        'type'        => 'text',
                        'description' => __('The shipping method name shown to customers at checkout.', 'dimensional-shipping'),
                        'default'     => __('Dimensional Shipping', 'dimensional-shipping'),
                        'desc_tip'    => false,
                    ),
                    'tax_status' => array(
                        'title'       => __('Tax Status', 'dimensional-shipping'),
                        'type'        => 'select',
                        'description' => __('Choose whether the calculated shipping cost is subject to tax.', 'dimensional-shipping'),
                        'default'     => 'taxable',
                        'desc_tip'    => false,
                        'options'     => array(
                            'taxable' => __('Taxable', 'dimensional-shipping'),
                            'none'    => __('None', 'dimensional-shipping'),
                        ),
                    ),
                    'store_info' => array(
                        'title'       => __('Store Dimension Settings', 'dimensional-shipping'),
                        'type'        => 'title',
                        /* translators: %1$s: dimension unit abbreviation (e.g. cm), %2$s: URL to WooCommerce General settings */
                        'description' => sprintf(
                            __('Your store\'s dimension unit is set to <strong>%1$s</strong>. Product dimensions will be converted to the unit you choose below before the DIM calculation is applied. You can change your store unit under <a href="%2$s">WooCommerce &rarr; Settings &rarr; General</a>.', 'dimensional-shipping'),
                            esc_html($dim_unit),
                            esc_url(admin_url('admin.php?page=wc-settings&tab=general'))
                        ),
                    ),
                    'dim_unit' => array(
                        'title'       => __('DIM Calculation Unit', 'dimensional-shipping'),
                        'type'        => 'select',
                        'description' => __('The unit your shipping carrier uses for the DIM factor. Product dimensions are converted to this unit before dividing by the DIM factor. Choose the unit that matches the DIM factor value provided by your carrier.', 'dimensional-shipping'),
                        'default'     => 'cm',
                        'desc_tip'    => false,
                        'options'     => array(
                            'mm' => __('Millimeters (mm)', 'dimensional-shipping'),
                            'cm' => __('Centimeters (cm)', 'dimensional-shipping'),
                            'm'  => __('Meters (m)', 'dimensional-shipping'),
                            'in' => __('Inches (in)', 'dimensional-shipping'),
                            'yd' => __('Yards (yd)', 'dimensional-shipping'),
                        ),
                    ),
                    'dim_factor' => array(
                        'title'       => __('DIM Factor', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('The divisor in the formula: (L &times; W &times; H) / DIM factor. This value depends on the unit selected above — common carrier values are <strong>5000</strong> for cm and <strong>139</strong> for inches. A higher number produces a lower shipping cost.', 'dimensional-shipping'),
                        'default'     => '5000',
                        'desc_tip'    => false,
                        'custom_attributes' => array(
                            'step' => '1',
                            'min'  => '1',
                        ),
                    ),
                    'multiplier' => array(
                        'title'       => __('Cost Multiplier', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('The dimensional weight (volume / DIM factor) is multiplied by this number to produce the final shipping cost. For example, a value of 2.5 means each unit of dimensional weight costs 2.50 in your store currency.', 'dimensional-shipping'),
                        'default'     => '1',
                        'desc_tip'    => false,
                        'custom_attributes' => array(
                            'step' => '0.01',
                            'min'  => '0',
                        ),
                    ),
                    'min_cost' => array(
                        'title'       => __('Minimum Cost', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('A cost floor applied after the calculation. If the calculated cost is lower than this value, this amount is charged instead. Set to 0 to disable.', 'dimensional-shipping'),
                        'default'     => '0',
                        'desc_tip'    => false,
                        'custom_attributes' => array(
                            'step' => '0.01',
                            'min'  => '0',
                        ),
                    ),
                    'extra_fee' => array(
                        'title'       => __('Additional Fee', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('A fixed amount added to every order after the minimum cost is applied, e.g. for packaging or handling. Set to 0 to disable.', 'dimensional-shipping'),
                        'default'     => '0',
                        'desc_tip'    => false,
                        'custom_attributes' => array(
                            'step' => '0.01',
                            'min'  => '0',
                        ),
                    ),
                    'explanation' => array(
                        'title' => __('How the shipping cost is calculated', 'dimensional-shipping'),
                        'type'  => 'ds_explanation',
                    ),
                );
            }

            /**
             * Final cost from the summed dimensional weight: × multiplier, minimum cost floor, then the additional fee.
             */
            public static function compute_cost($total_dimensional_value, $multiplier, $min_cost, $extra_fee) {
                $cost = $total_dimensional_value * $multiplier;

                if ($cost < $min_cost) {
                    $cost = $min_cost;
                }

                return $cost + max(0, $extra_fee);
            }

            /**
             * Settings field: step-by-step explanation of the calculation, with a worked example from the saved settings.
             */
            public function generate_ds_explanation_html($key, $data) {
                $dim_unit   = $this->get_option('dim_unit') ?: 'cm';
                $dim_factor = max(1, floatval($this->get_option('dim_factor')));
                $multiplier = floatval($this->get_option('multiplier'));
                $min_cost   = floatval($this->get_option('min_cost'));
                $extra_fee  = floatval($this->get_option('extra_fee'));
                $taxable    = 'taxable' === $this->get_option('tax_status');

                // Example parcel: 40 × 30 × 20 cm, converted to the DIM unit like a real product.
                $l = wc_get_dimension(40, $dim_unit, 'cm');
                $w = wc_get_dimension(30, $dim_unit, 'cm');
                $h = wc_get_dimension(20, $dim_unit, 'cm');
                $volume      = $l * $w * $h;
                $dim_weight  = $volume / $dim_factor;
                $calculated  = $dim_weight * $multiplier;
                $after_floor = max($calculated, $min_cost);
                $total       = self::compute_cost($dim_weight, $multiplier, $min_cost, $extra_fee);

                // Whole numbers without decimals, small ones (e.g. volumes in m) with up to 4.
                $num   = function ($n) {
                    $decimals = 0;
                    while ($decimals < 4 && round($n, $decimals) != round($n, 4)) {
                        $decimals = 0 === $decimals ? 2 : $decimals + 1;
                    }
                    return esc_html(number_format_i18n($n, $decimals));
                };
                $price = function ($n) { return wp_kses_post(wc_price($n)); };
                $unit  = esc_html($dim_unit);

                ob_start();
                ?>
                </table>
                <h3 class="wc-settings-sub-title" id="<?php echo esc_attr($this->get_field_key($key)); ?>"><?php echo wp_kses_post($data['title']); ?></h3>
                <ol>
                    <li><?php
                        /* translators: %s: DIM calculation unit, e.g. cm */
                        echo wp_kses_post(sprintf(__('Each product\'s length, width and height are converted to the DIM calculation unit (<strong>%s</strong>).', 'dimensional-shipping'), $unit));
                    ?></li>
                    <li><?php
                        /* translators: %s: DIM factor */
                        echo wp_kses_post(sprintf(__('Its dimensional weight is (Length &times; Width &times; Height) &divide; DIM factor (<strong>%s</strong>), times the quantity in the cart.', 'dimensional-shipping'), $num($dim_factor)));
                    ?></li>
                    <li><?php
                        /* translators: %s: cost multiplier */
                        echo wp_kses_post(sprintf(__('The dimensional weights of all products are added up and multiplied by the cost multiplier (<strong>%s</strong>).', 'dimensional-shipping'), $num($multiplier)));
                    ?></li>
                    <li><?php
                        /* translators: %s: minimum cost, formatted as a price */
                        echo wp_kses_post(sprintf(__('If the result is below the minimum cost (<strong>%s</strong>), the minimum cost is charged instead.', 'dimensional-shipping'), $price($min_cost)));
                    ?></li>
                    <li><?php
                        /* translators: %s: additional fee, formatted as a price */
                        echo wp_kses_post(sprintf(__('The additional fee (<strong>%s</strong>) is added.', 'dimensional-shipping'), $price($extra_fee)));
                    ?></li>
                    <li><?php
                        echo esc_html($taxable
                            ? __('Tax is added on top, because the tax status is Taxable.', 'dimensional-shipping')
                            : __('No tax is added, because the tax status is None.', 'dimensional-shipping'));
                    ?></li>
                </ol>
                <p><?php esc_html_e('Products that are virtual or have no length, width or height count as 0. A cart with only such products is charged the minimum cost plus the additional fee.', 'dimensional-shipping'); ?></p>
                <p><strong><?php esc_html_e('Example with the saved settings: one 40 × 30 × 20 cm box', 'dimensional-shipping'); ?></strong></p>
                <ol>
                    <li><?php
                        /* translators: 1: length, 2: width, 3: height, 4: unit, 5: volume */
                        echo wp_kses_post(sprintf(__('%1$s &times; %2$s &times; %3$s %4$s = %5$s', 'dimensional-shipping'), $num($l), $num($w), $num($h), $unit, $num($volume)));
                    ?></li>
                    <li><?php
                        /* translators: 1: volume, 2: DIM factor, 3: dimensional weight */
                        echo wp_kses_post(sprintf(__('%1$s &divide; %2$s = %3$s (dimensional weight)', 'dimensional-shipping'), $num($volume), $num($dim_factor), $num($dim_weight)));
                    ?></li>
                    <li><?php
                        /* translators: 1: dimensional weight, 2: cost multiplier, 3: calculated cost */
                        echo wp_kses_post(sprintf(__('%1$s &times; %2$s = %3$s', 'dimensional-shipping'), $num($dim_weight), $num($multiplier), $price($calculated)));
                    ?></li>
                    <?php if ($calculated < $min_cost) : ?>
                    <li><?php
                        /* translators: %s: minimum cost, formatted as a price */
                        echo wp_kses_post(sprintf(__('Below the minimum cost, so %s is used', 'dimensional-shipping'), $price($min_cost)));
                    ?></li>
                    <?php endif; ?>
                    <li><?php
                        if ($extra_fee > 0) {
                            /* translators: 1: cost after the minimum, 2: additional fee, 3: shipping cost */
                            echo wp_kses_post(sprintf(__('%1$s + %2$s additional fee = <strong>%3$s</strong> shipping', 'dimensional-shipping'), $price($after_floor), $price($extra_fee), $price($total)));
                        } else {
                            /* translators: %s: shipping cost */
                            echo wp_kses_post(sprintf(__('Shipping: <strong>%s</strong>', 'dimensional-shipping'), $price($total)));
                        }
                    ?></li>
                </ol>
                <p class="description"><?php esc_html_e('The example uses the saved values: save your changes to update it.', 'dimensional-shipping'); ?></p>
                <table class="form-table">
                <?php
                return ob_get_clean();
            }

            /**
             * The explanation is display-only: store nothing for it.
             */
            public function validate_ds_explanation_field($key, $value) {
                return '';
            }
            
            /**
             * Calculate shipping based on dimensions
             */
            public function calculate_shipping($package = array()) {
                $total_dimensional_value = 0;
                $dim_unit   = $this->dim_unit ?: 'cm';
                $dim_factor = max(1, floatval($this->dim_factor));
                $multiplier = floatval($this->multiplier);
                $min_cost   = floatval($this->min_cost);

                // Loop through cart items
                foreach ($package['contents'] as $item_id => $values) {
                    $product = $values['data'];
                    $quantity = $values['quantity'];

                    if (!$product->needs_shipping()) {
                        continue;
                    }

                    // Get dimensions converted to the carrier's DIM unit
                    $length = wc_get_dimension(floatval($product->get_length()), $dim_unit);
                    $width  = wc_get_dimension(floatval($product->get_width()),  $dim_unit);
                    $height = wc_get_dimension(floatval($product->get_height()), $dim_unit);
                    
                    // Check if dimensions are set
                    if ($length > 0 && $width > 0 && $height > 0) {
                        $dimensional_value = ($length * $width * $height) / $dim_factor;
                        $total_dimensional_value += $dimensional_value * $quantity;
                    }
                }
                
                // Multiplier, minimum cost, additional fee
                $cost = self::compute_cost($total_dimensional_value, $multiplier, $min_cost, floatval($this->extra_fee));
                
                // Register rate
                $this->add_rate(array(
                    'id'        => $this->get_rate_id(),
                    'label'     => $this->title,
                    'cost'      => $cost,
                    'tax_status' => $this->tax_status,
                ));
            }
        }
    }
}
add_action('woocommerce_shipping_init', 'dimensional_shipping_init');

/**
 * Add shipping method to WooCommerce
 */
function add_dimensional_shipping_method($methods) {
    $methods['dimensional_shipping'] = 'WC_Dimensional_Shipping_Method';
    return $methods;
}
add_filter('woocommerce_shipping_methods', 'add_dimensional_shipping_method');

/**
 * Plugin activation check
 */
function dimensional_shipping_activate() {
    if (!dimensional_shipping_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('Dimensional Shipping requires WooCommerce to be installed and active.', 'dimensional-shipping'));
    }
}
register_activation_hook(__FILE__, 'dimensional_shipping_activate');


// ── ttrp.gr Plugins menu ─────────────────────────────────────────────────────
function ttrp_ds_maybe_register_brand_menu(): void {
    global $menu;
    if ( is_array( $menu ) ) {
        foreach ( $menu as $item ) {
            if ( isset( $item[2] ) && 'ttrp-plugins' === $item[2] ) return;
        }
    }
    if ( ! function_exists( 'ttrp_brand_dashboard' ) ) {
        function ttrp_brand_dashboard(): void {
            global $submenu;
            $target = null;
            if ( ! empty( $submenu['ttrp-plugins'] ) ) {
                foreach ( $submenu['ttrp-plugins'] as $item ) {
                    if ( $item[2] !== 'ttrp-plugins' ) { $target = $item[2]; break; }
                }
            }
            if ( $target ) {
                $url = esc_url( admin_url( 'admin.php?page=' . $target ) );
                echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
                echo '<script>window.location.replace("' . $url . '");</script>';
            }
        }
    }
    $svg_path = plugin_dir_path( __FILE__ ) . 'assets/ttrp-logo.svg';
    $icon = file_exists( $svg_path )
        ? 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( $svg_path ) )
        : 'dashicons-admin-plugins';
    add_menu_page( 'ttrp.gr Plugins', 'ttrp.gr Plugins', 'manage_options', 'ttrp-plugins', 'ttrp_brand_dashboard', $icon, 56 );
    add_action( 'admin_menu', function () { remove_submenu_page( 'ttrp-plugins', 'ttrp-plugins' ); }, 999 );
}
function ttrp_ds_add_submenu(): void {
    add_submenu_page( 'ttrp-plugins', 'Dimensional Shipping', 'Dimensional Shipping', 'manage_options', 'ttrp-dimensional-shipping', '__return_null' );
}
function ttrp_ds_redirect(): void {
    if ( isset( $_GET['page'] ) && 'ttrp-dimensional-shipping' === $_GET['page'] ) {
        wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) );
        exit;
    }
}
add_action( 'admin_menu', 'ttrp_ds_maybe_register_brand_menu', 5 );
add_action( 'admin_menu', 'ttrp_ds_add_submenu' );
add_action( 'admin_init',  'ttrp_ds_redirect' );

// Updates + "All Plugins" page (shared ttrp-common library).
require_once plugin_dir_path( __FILE__ ) . 'ttrp-common/loader.php';
ttrp_common_register( __FILE__, 'woo-tet-dimensional-shipping-plugin' );