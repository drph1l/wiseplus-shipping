<?php
/**
 * Weight Rates Management Page
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap wiseplus-admin">
    <h1><?php _e( 'Weight-Based Shipping Rates', 'wiseplus-shipping' ); ?></h1>

    <?php if ( isset( $_GET['message'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ( $_GET['message'] === 'rate_saved' ) {
                    _e( 'Rate saved successfully!', 'wiseplus-shipping' );
                } elseif ( $_GET['message'] === 'rate_deleted' ) {
                    _e( 'Rate deleted successfully!', 'wiseplus-shipping' );
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ( empty( $cities ) ) : ?>
        <div class="notice notice-warning">
            <p>
                <?php _e( 'Please add at least one city before creating weight-based rates.', 'wiseplus-shipping' ); ?>
                <a href="<?php echo admin_url( 'admin.php?page=wiseplus-shipping' ); ?>"><?php _e( 'Add Cities', 'wiseplus-shipping' ); ?></a>
            </p>
        </div>
    <?php endif; ?>

    <div class="wiseplus-grid">
        <!-- Add/Edit Weight Rate Form -->
        <div class="wiseplus-card">
            <h2><?php echo $edit_rate ? __( 'Edit Weight Rate', 'wiseplus-shipping' ) : __( 'Add New Weight Rate', 'wiseplus-shipping' ); ?></h2>

            <form method="post" action="">
                <?php wp_nonce_field( 'wiseplus_admin_action', 'wiseplus_nonce' ); ?>
                <input type="hidden" name="wiseplus_action" value="save_weight_rate">

                <?php if ( $edit_rate ) : ?>
                    <input type="hidden" name="rate_id" value="<?php echo esc_attr( $edit_rate->id ); ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="city_id"><?php _e( 'City', 'wiseplus-shipping' ); ?> *</label>
                        </th>
                        <td>
                            <select id="city_id" name="city_id" class="regular-text" required>
                                <option value=""><?php _e( 'Select a city', 'wiseplus-shipping' ); ?></option>
                                <?php foreach ( $cities as $city ) : ?>
                                    <option value="<?php echo esc_attr( $city->id ); ?>"
                                            <?php selected( $edit_rate ? $edit_rate->city_id : '', $city->id ); ?>>
                                        <?php echo esc_html( $city->city_name ); ?>
                                        <?php echo $city->region ? ' (' . esc_html( $city->region ) . ')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="min_weight"><?php _e( 'Min Weight', 'wiseplus-shipping' ); ?> *</label>
                        </th>
                        <td>
                            <input type="number"
                                   id="min_weight"
                                   name="min_weight"
                                   step="0.01"
                                   min="0"
                                   class="small-text"
                                   value="<?php echo $edit_rate ? esc_attr( $edit_rate->min_weight ) : '0'; ?>"
                                   required>
                            <span><?php echo get_option( 'woocommerce_weight_unit' ); ?></span>
                            <p class="description"><?php _e( 'Minimum weight for this rate (inclusive).', 'wiseplus-shipping' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="max_weight"><?php _e( 'Max Weight', 'wiseplus-shipping' ); ?> *</label>
                        </th>
                        <td>
                            <input type="number"
                                   id="max_weight"
                                   name="max_weight"
                                   step="0.01"
                                   min="0.01"
                                   class="small-text"
                                   value="<?php echo $edit_rate ? esc_attr( $edit_rate->max_weight ) : ''; ?>"
                                   required>
                            <span><?php echo get_option( 'woocommerce_weight_unit' ); ?></span>
                            <p class="description"><?php _e( 'Maximum weight for this rate (inclusive).', 'wiseplus-shipping' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="shipping_cost"><?php _e( 'Shipping Cost', 'wiseplus-shipping' ); ?> *</label>
                        </th>
                        <td>
                            <input type="number"
                                   id="shipping_cost"
                                   name="shipping_cost"
                                   step="0.01"
                                   min="0"
                                   class="small-text"
                                   value="<?php echo $edit_rate ? esc_attr( $edit_rate->shipping_cost ) : ''; ?>"
                                   required>
                            <span><?php echo get_woocommerce_currency_symbol(); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php _e( 'Enabled', 'wiseplus-shipping' ); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       id="enabled"
                                       name="enabled"
                                       value="1"
                                       <?php checked( $edit_rate ? $edit_rate->enabled : true ); ?>>
                                <?php _e( 'Enable this rate', 'wiseplus-shipping' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php echo $edit_rate ? __( 'Update Rate', 'wiseplus-shipping' ) : __( 'Add Rate', 'wiseplus-shipping' ); ?>
                    </button>

                    <?php if ( $edit_rate ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=wiseplus-weight-rates' ); ?>" class="button">
                            <?php _e( 'Cancel', 'wiseplus-shipping' ); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </form>
        </div>

        <!-- Weight Rates List -->
        <div class="wiseplus-card">
            <h2><?php _e( 'Existing Weight Rates', 'wiseplus-shipping' ); ?></h2>

            <?php if ( empty( $rates ) ) : ?>
                <p><?php _e( 'No weight-based rates added yet.', 'wiseplus-shipping' ); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'City', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Weight Range', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Shipping Cost', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Status', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Actions', 'wiseplus-shipping' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $rates as $rate ) : ?>
                            <?php $city = WisePlus_Database::get_city( $rate->city_id ); ?>
                            <tr>
                                <td><strong><?php echo $city ? esc_html( $city->city_name ) : __( 'Unknown', 'wiseplus-shipping' ); ?></strong></td>
                                <td>
                                    <?php
                                    echo esc_html( $rate->min_weight ) . ' - ' . esc_html( $rate->max_weight );
                                    echo ' ' . get_option( 'woocommerce_weight_unit' );
                                    ?>
                                </td>
                                <td><?php echo get_woocommerce_currency_symbol() . esc_html( number_format( $rate->shipping_cost, 2 ) ); ?></td>
                                <td>
                                    <?php if ( $rate->enabled ) : ?>
                                        <span class="wiseplus-status wiseplus-status-enabled"><?php _e( 'Enabled', 'wiseplus-shipping' ); ?></span>
                                    <?php else : ?>
                                        <span class="wiseplus-status wiseplus-status-disabled"><?php _e( 'Disabled', 'wiseplus-shipping' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=wiseplus-weight-rates&edit=' . $rate->id ); ?>" class="button button-small">
                                        <?php _e( 'Edit', 'wiseplus-shipping' ); ?>
                                    </a>

                                    <form method="post" style="display: inline;" onsubmit="return confirm('<?php _e( 'Are you sure you want to delete this rate?', 'wiseplus-shipping' ); ?>');">
                                        <?php wp_nonce_field( 'wiseplus_admin_action', 'wiseplus_nonce' ); ?>
                                        <input type="hidden" name="wiseplus_action" value="delete_weight_rate">
                                        <input type="hidden" name="rate_id" value="<?php echo esc_attr( $rate->id ); ?>">
                                        <button type="submit" class="button button-small button-link-delete">
                                            <?php _e( 'Delete', 'wiseplus-shipping' ); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
