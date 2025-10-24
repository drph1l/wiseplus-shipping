<?php
/**
 * Admin Settings Class
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WisePlus_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'WisePlus Shipping', 'wiseplus-shipping' ),
            __( 'WisePlus Shipping', 'wiseplus-shipping' ),
            'manage_woocommerce',
            'wiseplus-shipping',
            array( $this, 'render_cities_page' ),
            'dashicons-location-alt',
            56
        );

        add_submenu_page(
            'wiseplus-shipping',
            __( 'Cities', 'wiseplus-shipping' ),
            __( 'Cities', 'wiseplus-shipping' ),
            'manage_woocommerce',
            'wiseplus-shipping',
            array( $this, 'render_cities_page' )
        );

        add_submenu_page(
            'wiseplus-shipping',
            __( 'Weight Rates', 'wiseplus-shipping' ),
            __( 'Weight Rates', 'wiseplus-shipping' ),
            'manage_woocommerce',
            'wiseplus-weight-rates',
            array( $this, 'render_weight_rates_page' )
        );

        add_submenu_page(
            'wiseplus-shipping',
            __( 'Class Rates', 'wiseplus-shipping' ),
            __( 'Class Rates', 'wiseplus-shipping' ),
            'manage_woocommerce',
            'wiseplus-class-rates',
            array( $this, 'render_class_rates_page' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'wiseplus' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'wiseplus-admin',
            WISEPLUS_SHIPPING_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WISEPLUS_SHIPPING_VERSION
        );

        wp_enqueue_script(
            'wiseplus-admin',
            WISEPLUS_SHIPPING_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            WISEPLUS_SHIPPING_VERSION,
            true
        );

        wp_localize_script(
            'wiseplus-admin',
            'wiseplusAdmin',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wiseplus_admin' ),
            )
        );
    }

    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if ( ! isset( $_POST['wiseplus_action'] ) || ! isset( $_POST['wiseplus_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['wiseplus_nonce'], 'wiseplus_admin_action' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $action = sanitize_text_field( $_POST['wiseplus_action'] );

        switch ( $action ) {
            case 'save_city':
                $this->save_city();
                break;
            case 'delete_city':
                $this->delete_city();
                break;
            case 'save_weight_rate':
                $this->save_weight_rate();
                break;
            case 'delete_weight_rate':
                $this->delete_weight_rate();
                break;
            case 'save_class_rate':
                $this->save_class_rate();
                break;
            case 'delete_class_rate':
                $this->delete_class_rate();
                break;
        }
    }

    /**
     * Save city
     */
    private function save_city() {
        $data = array(
            'id'        => isset( $_POST['city_id'] ) ? (int) $_POST['city_id'] : 0,
            'city_name' => sanitize_text_field( $_POST['city_name'] ),
            'region'    => sanitize_text_field( $_POST['region'] ),
            'enabled'   => isset( $_POST['enabled'] ) ? 1 : 0,
        );

        WisePlus_Database::save_city( $data );
        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-shipping', 'message' => 'city_saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Delete city
     */
    private function delete_city() {
        if ( isset( $_POST['city_id'] ) ) {
            WisePlus_Database::delete_city( (int) $_POST['city_id'] );
        }

        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-shipping', 'message' => 'city_deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Save weight rate
     */
    private function save_weight_rate() {
        $data = array(
            'id'            => isset( $_POST['rate_id'] ) ? (int) $_POST['rate_id'] : 0,
            'city_id'       => (int) $_POST['city_id'],
            'min_weight'    => (float) $_POST['min_weight'],
            'max_weight'    => (float) $_POST['max_weight'],
            'shipping_cost' => (float) $_POST['shipping_cost'],
            'enabled'       => isset( $_POST['enabled'] ) ? 1 : 0,
        );

        WisePlus_Database::save_weight_rate( $data );
        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-weight-rates', 'message' => 'rate_saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Delete weight rate
     */
    private function delete_weight_rate() {
        if ( isset( $_POST['rate_id'] ) ) {
            WisePlus_Database::delete_weight_rate( (int) $_POST['rate_id'] );
        }

        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-weight-rates', 'message' => 'rate_deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Save class rate
     */
    private function save_class_rate() {
        $data = array(
            'id'                => isset( $_POST['rate_id'] ) ? (int) $_POST['rate_id'] : 0,
            'city_id'           => (int) $_POST['city_id'],
            'shipping_class_id' => (int) $_POST['shipping_class_id'],
            'shipping_cost'     => (float) $_POST['shipping_cost'],
            'enabled'           => isset( $_POST['enabled'] ) ? 1 : 0,
        );

        WisePlus_Database::save_class_rate( $data );
        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-class-rates', 'message' => 'rate_saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Delete class rate
     */
    private function delete_class_rate() {
        if ( isset( $_POST['rate_id'] ) ) {
            WisePlus_Database::delete_class_rate( (int) $_POST['rate_id'] );
        }

        wp_redirect( add_query_arg( array( 'page' => 'wiseplus-class-rates', 'message' => 'rate_deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Render cities page
     */
    public function render_cities_page() {
        $cities = WisePlus_Database::get_cities();
        $edit_city = null;

        if ( isset( $_GET['edit'] ) ) {
            $edit_city = WisePlus_Database::get_city( (int) $_GET['edit'] );
        }

        include WISEPLUS_SHIPPING_PLUGIN_DIR . 'admin/views/cities.php';
    }

    /**
     * Render weight rates page
     */
    public function render_weight_rates_page() {
        $cities = WisePlus_Database::get_cities( true );
        $rates = WisePlus_Database::get_weight_rates();
        $edit_rate = null;

        if ( isset( $_GET['edit'] ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'wiseplus_weight_rates';
            $edit_rate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $_GET['edit'] ) );
        }

        include WISEPLUS_SHIPPING_PLUGIN_DIR . 'admin/views/weight-rates.php';
    }

    /**
     * Render class rates page
     */
    public function render_class_rates_page() {
        $cities = WisePlus_Database::get_cities( true );
        $rates = WisePlus_Database::get_class_rates();
        $shipping_classes = WC()->shipping()->get_shipping_classes();
        $edit_rate = null;

        if ( isset( $_GET['edit'] ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'wiseplus_class_rates';
            $edit_rate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $_GET['edit'] ) );
        }

        include WISEPLUS_SHIPPING_PLUGIN_DIR . 'admin/views/class-rates.php';
    }
}

// Initialize admin
new WisePlus_Admin();
