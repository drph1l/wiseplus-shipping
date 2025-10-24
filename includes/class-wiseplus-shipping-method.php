<?php
/**
 * WisePlus Shipping Method
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WisePlus_Shipping_Method' ) ) {

    class WisePlus_Shipping_Method extends WC_Shipping_Method {

        /**
         * Constructor
         */
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'wiseplus_shipping';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __( 'WisePlus Shipping', 'wiseplus-shipping' );
            $this->method_description = __( 'Flexible shipping rates based on shipping class, city/region, and weight', 'wiseplus-shipping' );
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();
        }

        /**
         * Initialize settings
         */
        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->enabled = $this->get_option( 'enabled' );
            $this->title   = $this->get_option( 'title' );

            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * Initialize form fields
         */
        public function init_form_fields() {
            $this->instance_form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'wiseplus-shipping' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this shipping method', 'wiseplus-shipping' ),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'       => __( 'Method Title', 'wiseplus-shipping' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'wiseplus-shipping' ),
                    'default'     => __( 'WisePlus Shipping', 'wiseplus-shipping' ),
                    'desc_tip'    => true,
                ),
            );
        }

        /**
         * Calculate shipping
         */
        public function calculate_shipping( $package = array() ) {
            // Get destination city
            $destination_city = isset( $package['destination']['city'] ) ? $package['destination']['city'] : '';

            if ( empty( $destination_city ) ) {
                return; // No shipping available if city is not provided
            }

            // Get city from database
            $city = WisePlus_Database::get_city_by_name( $destination_city );

            if ( ! $city ) {
                // City not found in our database - no shipping available
                return;
            }

            $shipping_cost = null;

            // PRIORITY 1: Check for shipping class-based rates (class + city)
            $shipping_cost = $this->calculate_class_based_rate( $package, $city->id );

            // PRIORITY 2: Check for weight-based rates (weight + city) if class rate not found
            if ( is_null( $shipping_cost ) ) {
                $shipping_cost = $this->calculate_weight_based_rate( $package, $city->id );
            }

            // If we found a valid shipping cost, add the rate
            if ( ! is_null( $shipping_cost ) && $shipping_cost >= 0 ) {
                $rate = array(
                    'id'    => $this->get_rate_id(),
                    'label' => $this->title,
                    'cost'  => $shipping_cost,
                );

                $this->add_rate( $rate );
            }
            // If no rate found, don't add any rate (shipping not available)
        }

        /**
         * Calculate shipping based on shipping class + city
         * Returns the cost or null if not found
         */
        private function calculate_class_based_rate( $package, $city_id ) {
            $cart_items = $package['contents'];

            if ( empty( $cart_items ) ) {
                return null;
            }

            // Get shipping classes from cart items
            $shipping_classes = array();
            foreach ( $cart_items as $item ) {
                $product = $item['data'];
                $shipping_class_id = $product->get_shipping_class_id();

                if ( $shipping_class_id ) {
                    $shipping_classes[] = $shipping_class_id;
                }
            }

            // If no shipping classes found, return null
            if ( empty( $shipping_classes ) ) {
                return null;
            }

            // Get unique shipping classes
            $shipping_classes = array_unique( $shipping_classes );

            // Check if we have rates for any of these shipping classes
            $total_cost = 0;
            $found_rate = false;

            foreach ( $shipping_classes as $class_id ) {
                $rate = WisePlus_Database::get_class_rate( $city_id, $class_id );

                if ( $rate ) {
                    $total_cost += (float) $rate->shipping_cost;
                    $found_rate = true;
                }
            }

            return $found_rate ? $total_cost : null;
        }

        /**
         * Calculate shipping based on weight + city
         * Returns the cost or null if not found
         */
        private function calculate_weight_based_rate( $package, $city_id ) {
            $cart_items = $package['contents'];

            if ( empty( $cart_items ) ) {
                return null;
            }

            // Calculate total weight
            $total_weight = 0;
            foreach ( $cart_items as $item ) {
                $product = $item['data'];
                $quantity = $item['quantity'];
                $weight = $product->get_weight();

                if ( $weight ) {
                    $total_weight += (float) $weight * $quantity;
                }
            }

            // If no weight, return null
            if ( $total_weight <= 0 ) {
                return null;
            }

            // Get rate for this weight and city
            $rate = WisePlus_Database::get_weight_rate( $city_id, $total_weight );

            if ( $rate ) {
                return (float) $rate->shipping_cost;
            }

            return null;
        }

        /**
         * Check if this method is available
         */
        public function is_available( $package ) {
            $is_available = true;

            if ( 'yes' !== $this->enabled ) {
                $is_available = false;
            }

            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
        }
    }
}
