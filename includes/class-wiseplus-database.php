<?php
/**
 * Database Management Class
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WisePlus_Database {

    /**
     * Create all required database tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Table for cities/regions
        $cities_table = $wpdb->prefix . 'wiseplus_cities';
        $cities_sql = "CREATE TABLE $cities_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            city_name varchar(255) NOT NULL,
            region varchar(255) DEFAULT NULL,
            enabled tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY city_name (city_name),
            KEY region (region)
        ) $charset_collate;";

        // Table for weight-based rates (weight + city)
        $weight_rates_table = $wpdb->prefix . 'wiseplus_weight_rates';
        $weight_rates_sql = "CREATE TABLE $weight_rates_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            city_id bigint(20) UNSIGNED NOT NULL,
            min_weight decimal(10,2) NOT NULL DEFAULT 0.00,
            max_weight decimal(10,2) NOT NULL,
            shipping_cost decimal(10,2) NOT NULL,
            enabled tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY city_id (city_id),
            KEY weight_range (min_weight, max_weight)
        ) $charset_collate;";

        // Table for shipping class-based rates (class + city)
        $class_rates_table = $wpdb->prefix . 'wiseplus_class_rates';
        $class_rates_sql = "CREATE TABLE $class_rates_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            city_id bigint(20) UNSIGNED NOT NULL,
            shipping_class_id bigint(20) UNSIGNED NOT NULL,
            shipping_cost decimal(10,2) NOT NULL,
            enabled tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY city_id (city_id),
            KEY shipping_class_id (shipping_class_id),
            UNIQUE KEY unique_class_city (city_id, shipping_class_id)
        ) $charset_collate;";

        dbDelta( $cities_sql );
        dbDelta( $weight_rates_sql );
        dbDelta( $class_rates_sql );

        // Store database version
        update_option( 'wiseplus_shipping_db_version', WISEPLUS_SHIPPING_VERSION );
    }

    /**
     * Get all cities
     */
    public static function get_cities( $enabled_only = false ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_cities';

        $where = $enabled_only ? 'WHERE enabled = 1' : '';

        return $wpdb->get_results( "SELECT * FROM $table $where ORDER BY city_name ASC" );
    }

    /**
     * Get city by ID
     */
    public static function get_city( $city_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_cities';

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $city_id ) );
    }

    /**
     * Get city by name
     */
    public static function get_city_by_name( $city_name ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_cities';

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE city_name = %s AND enabled = 1", $city_name ) );
    }

    /**
     * Add or update city
     */
    public static function save_city( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_cities';

        if ( isset( $data['id'] ) && $data['id'] > 0 ) {
            // Update
            $wpdb->update(
                $table,
                array(
                    'city_name' => sanitize_text_field( $data['city_name'] ),
                    'region'    => isset( $data['region'] ) ? sanitize_text_field( $data['region'] ) : null,
                    'enabled'   => isset( $data['enabled'] ) ? (int) $data['enabled'] : 1,
                ),
                array( 'id' => (int) $data['id'] )
            );
            return (int) $data['id'];
        } else {
            // Insert
            $wpdb->insert(
                $table,
                array(
                    'city_name' => sanitize_text_field( $data['city_name'] ),
                    'region'    => isset( $data['region'] ) ? sanitize_text_field( $data['region'] ) : null,
                    'enabled'   => isset( $data['enabled'] ) ? (int) $data['enabled'] : 1,
                )
            );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete city
     */
    public static function delete_city( $city_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_cities';

        return $wpdb->delete( $table, array( 'id' => (int) $city_id ) );
    }

    /**
     * Get weight-based rates
     */
    public static function get_weight_rates( $city_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_weight_rates';

        if ( $city_id ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE city_id = %d AND enabled = 1 ORDER BY min_weight ASC", $city_id ) );
        }

        return $wpdb->get_results( "SELECT * FROM $table ORDER BY city_id, min_weight ASC" );
    }

    /**
     * Get weight rate for specific weight and city
     */
    public static function get_weight_rate( $city_id, $weight ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_weight_rates';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE city_id = %d AND min_weight <= %f AND max_weight >= %f AND enabled = 1 LIMIT 1",
            $city_id,
            $weight,
            $weight
        ) );
    }

    /**
     * Save weight-based rate
     */
    public static function save_weight_rate( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_weight_rates';

        $rate_data = array(
            'city_id'       => (int) $data['city_id'],
            'min_weight'    => (float) $data['min_weight'],
            'max_weight'    => (float) $data['max_weight'],
            'shipping_cost' => (float) $data['shipping_cost'],
            'enabled'       => isset( $data['enabled'] ) ? (int) $data['enabled'] : 1,
        );

        if ( isset( $data['id'] ) && $data['id'] > 0 ) {
            $wpdb->update( $table, $rate_data, array( 'id' => (int) $data['id'] ) );
            return (int) $data['id'];
        } else {
            $wpdb->insert( $table, $rate_data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete weight rate
     */
    public static function delete_weight_rate( $rate_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_weight_rates';

        return $wpdb->delete( $table, array( 'id' => (int) $rate_id ) );
    }

    /**
     * Get shipping class-based rates
     */
    public static function get_class_rates( $city_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_class_rates';

        if ( $city_id ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE city_id = %d AND enabled = 1", $city_id ) );
        }

        return $wpdb->get_results( "SELECT * FROM $table ORDER BY city_id, shipping_class_id" );
    }

    /**
     * Get class rate for specific class and city
     */
    public static function get_class_rate( $city_id, $shipping_class_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_class_rates';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE city_id = %d AND shipping_class_id = %d AND enabled = 1 LIMIT 1",
            $city_id,
            $shipping_class_id
        ) );
    }

    /**
     * Save shipping class-based rate
     */
    public static function save_class_rate( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_class_rates';

        $rate_data = array(
            'city_id'           => (int) $data['city_id'],
            'shipping_class_id' => (int) $data['shipping_class_id'],
            'shipping_cost'     => (float) $data['shipping_cost'],
            'enabled'           => isset( $data['enabled'] ) ? (int) $data['enabled'] : 1,
        );

        if ( isset( $data['id'] ) && $data['id'] > 0 ) {
            $wpdb->update( $table, $rate_data, array( 'id' => (int) $data['id'] ) );
            return (int) $data['id'];
        } else {
            $wpdb->insert( $table, $rate_data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete class rate
     */
    public static function delete_class_rate( $rate_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wiseplus_class_rates';

        return $wpdb->delete( $table, array( 'id' => (int) $rate_id ) );
    }
}
