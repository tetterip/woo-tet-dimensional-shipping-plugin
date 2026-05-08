<?php
/**
 * Plugin Name: Dimensional Shipping for WooCommerce
 * Description: Custom shipping method that calculates shipping cost based on product dimensions (L×W×H)/5000 in cm
 * Version: 1.2.0
 * Author: Michalis Tetteris
 * Author URI: https://ttrp.gr
 * Text Domain: dimensional-shipping
 * WC requires at least: 3.0.0
 * WC tested up to: 9.5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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

            /**
             * Constructor
             */
            public function __construct($instance_id = 0) {
                $this->id                 = 'dimensional_shipping';
                $this->instance_id        = absint($instance_id);
                $this->method_title       = __('Dimensional Shipping', 'dimensional-shipping');
                $this->method_description = __('Shipping method based on product dimensions using (L×W×H)/5000 in cm', 'dimensional-shipping');
                
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
                $this->multiplier = $this->get_option('multiplier');
                $this->min_cost   = $this->get_option('min_cost');
                
                // Save settings
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }
            
            /**
             * Define settings fields
             */
            public function init_form_fields() {
                $this->instance_form_fields = array(
                    'title' => array(
                        'title'       => __('Title', 'dimensional-shipping'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'dimensional-shipping'),
                        'default'     => __('Dimensional Shipping', 'dimensional-shipping'),
                        'desc_tip'    => true,
                    ),
                    'tax_status' => array(
                        'title'       => __('Tax Status', 'dimensional-shipping'),
                        'type'        => 'select',
                        'description' => __('Set whether or not the shipping cost is taxable.', 'dimensional-shipping'),
                        'default'     => 'taxable',
                        'options'     => array(
                            'taxable' => __('Taxable', 'dimensional-shipping'),
                            'none'    => __('None', 'dimensional-shipping'),
                        ),
                    ),
                    'multiplier' => array(
                        'title'       => __('Cost Multiplier', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('Multiply the calculated dimensional value by this amount to get the shipping cost.', 'dimensional-shipping'),
                        'default'     => '1',
                        'desc_tip'    => true,
                        'custom_attributes' => array(
                            'step' => '0.01',
                            'min'  => '0'
                        )
                    ),
                    'min_cost' => array(
                        'title'       => __('Minimum Cost', 'dimensional-shipping'),
                        'type'        => 'number',
                        'description' => __('Minimum shipping cost to apply, even if the calculated cost is lower.', 'dimensional-shipping'),
                        'default'     => '0',
                        'desc_tip'    => true,
                        'custom_attributes' => array(
                            'step' => '0.01',
                            'min'  => '0'
                        )
                    ),
                );
            }
            
            /**
             * Calculate shipping based on dimensions
             */
            public function calculate_shipping($package = array()) {
                $total_dimensional_value = 0;
                $multiplier = floatval($this->multiplier);
                $min_cost = floatval($this->min_cost);
                
                // Loop through cart items
                foreach ($package['contents'] as $item_id => $values) {
                    $product = $values['data'];
                    $quantity = $values['quantity'];
                    
                    if (!$product->needs_shipping()) {
                        continue;
                    }
                    
                    // Get dimensions
                    $length = wc_get_dimension(floatval($product->get_length()), 'cm');
                    $width = wc_get_dimension(floatval($product->get_width()), 'cm');
                    $height = wc_get_dimension(floatval($product->get_height()), 'cm');
                    
                    // Check if dimensions are set
                    if ($length > 0 && $width > 0 && $height > 0) {
                        $dimensional_value = ($length * $width * $height) / 5000;
                        $total_dimensional_value += $dimensional_value * $quantity;
                    }
                }
                
                // Calculate cost
                $cost = $total_dimensional_value * $multiplier;
                
                // Apply minimum cost
                if ($cost < $min_cost) {
                    $cost = $min_cost;
                }
                
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


require_once plugin_dir_path( __FILE__ ) . 'update-checker.php';
new TTRP_Update_Checker( __FILE__, 'woo-tet-dimensional-shipping-plugin' );