<?php
/**
 * Plugin Name: WisePlus Shipping
 * Plugin URI: https://wiseplus.com
 * Description: Flexible shipping rates based on shipping class, city/region, and weight
 * Version: 1.0.0
 * Author: WisePlus
 * Author URI: https://wiseplus.com
 * Text Domain: wiseplus-shipping
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'WISEPLUS_SHIPPING_VERSION', '1.0.0' );
define( 'WISEPLUS_SHIPPING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WISEPLUS_SHIPPING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WISEPLUS_SHIPPING_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Display notice if WooCommerce is not active
 */
function wiseplus_shipping_wc_missing_notice() {
    echo '<div class="error"><p><strong>WisePlus Shipping</strong> requires WooCommerce to be installed and active.</p></div>';
}

/**
 * Declare compatibility with WooCommerce features
 */
function wiseplus_shipping_declare_compatibility() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true
        );
    }
}

// Declare WooCommerce feature compatibility before WooCommerce initializes
add_action( 'before_woocommerce_init', 'wiseplus_shipping_declare_compatibility' );

/**
 * Main WisePlus Shipping Class
 */
class WisePlus_Shipping {

    /**
     * Single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
        $this->init_admin();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'includes/class-wiseplus-database.php';
        require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'admin/class-wiseplus-admin.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'woocommerce_shipping_init', array( $this, 'init_shipping_method' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
    }

    /**
     * Initialize admin
     */
    private function init_admin() {
        if ( is_admin() ) {
            new WisePlus_Admin();
        }
    }

    /**
     * Initialize shipping method
     */
    public function init_shipping_method() {
        // Load shipping method class when WooCommerce shipping is initialized
        require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'includes/class-wiseplus-shipping-method.php';
    }

    /**
     * Add shipping method to WooCommerce
     */
    public function add_shipping_method( $methods ) {
        $methods['wiseplus_shipping'] = 'WisePlus_Shipping_Method';
        return $methods;
    }
}

/**
 * Returns the main instance of WisePlus_Shipping
 */
function WisePlus_Shipping() {
    return WisePlus_Shipping::instance();
}

/**
 * Initialize plugin after WooCommerce is loaded
 */
function wiseplus_shipping_init() {
    // Check if WooCommerce classes are loaded
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wiseplus_shipping_wc_missing_notice' );
        return;
    }

    // Initialize the plugin
    WisePlus_Shipping();
}

// Hook into plugins_loaded with priority 20 (after WooCommerce at priority 10)
add_action( 'plugins_loaded', 'wiseplus_shipping_init', 20 );

/**
 * Plugin activation
 */
function wiseplus_shipping_activate() {
    require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'includes/class-wiseplus-database.php';
    WisePlus_Database::create_tables();
    flush_rewrite_rules();
}

/**
 * Plugin deactivation
 */
function wiseplus_shipping_deactivate() {
    flush_rewrite_rules();
}

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'wiseplus_shipping_activate' );
register_deactivation_hook( __FILE__, 'wiseplus_shipping_deactivate' );
