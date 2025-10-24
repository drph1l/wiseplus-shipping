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
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', 'wiseplus_shipping_wc_missing_notice' );
    return;
}

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
        require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'includes/class-wiseplus-shipping-method.php';
        require_once WISEPLUS_SHIPPING_PLUGIN_DIR . 'admin/class-wiseplus-admin.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'woocommerce_shipping_init', array( $this, 'init_shipping_method' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
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
        // Shipping method class is already included
    }

    /**
     * Add shipping method to WooCommerce
     */
    public function add_shipping_method( $methods ) {
        $methods['wiseplus_shipping'] = 'WisePlus_Shipping_Method';
        return $methods;
    }

    /**
     * Plugin activation
     */
    public function activate() {
        WisePlus_Database::create_tables();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Returns the main instance of WisePlus_Shipping
 */
function WisePlus_Shipping() {
    return WisePlus_Shipping::instance();
}

// Initialize the plugin
WisePlus_Shipping();
