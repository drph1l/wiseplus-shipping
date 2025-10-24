<?php
/**
 * Cities Management Page
 *
 * @package WisePlus_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap wiseplus-admin">
    <h1><?php _e( 'Manage Cities / Regions', 'wiseplus-shipping' ); ?></h1>

    <?php if ( isset( $_GET['message'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ( $_GET['message'] === 'city_saved' ) {
                    _e( 'City saved successfully!', 'wiseplus-shipping' );
                } elseif ( $_GET['message'] === 'city_deleted' ) {
                    _e( 'City deleted successfully!', 'wiseplus-shipping' );
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="wiseplus-grid">
        <!-- Add/Edit City Form -->
        <div class="wiseplus-card">
            <h2><?php echo $edit_city ? __( 'Edit City', 'wiseplus-shipping' ) : __( 'Add New City', 'wiseplus-shipping' ); ?></h2>

            <form method="post" action="">
                <?php wp_nonce_field( 'wiseplus_admin_action', 'wiseplus_nonce' ); ?>
                <input type="hidden" name="wiseplus_action" value="save_city">

                <?php if ( $edit_city ) : ?>
                    <input type="hidden" name="city_id" value="<?php echo esc_attr( $edit_city->id ); ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="city_name"><?php _e( 'City Name', 'wiseplus-shipping' ); ?> *</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="city_name"
                                   name="city_name"
                                   class="regular-text"
                                   value="<?php echo $edit_city ? esc_attr( $edit_city->city_name ) : ''; ?>"
                                   required>
                            <p class="description"><?php _e( 'Enter the exact city name as it will appear in checkout.', 'wiseplus-shipping' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="region"><?php _e( 'Region / State', 'wiseplus-shipping' ); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   id="region"
                                   name="region"
                                   class="regular-text"
                                   value="<?php echo $edit_city ? esc_attr( $edit_city->region ) : ''; ?>">
                            <p class="description"><?php _e( 'Optional: Specify a region or state for better organization.', 'wiseplus-shipping' ); ?></p>
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
                                       <?php checked( $edit_city ? $edit_city->enabled : true ); ?>>
                                <?php _e( 'Enable shipping for this city', 'wiseplus-shipping' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php echo $edit_city ? __( 'Update City', 'wiseplus-shipping' ) : __( 'Add City', 'wiseplus-shipping' ); ?>
                    </button>

                    <?php if ( $edit_city ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=wiseplus-shipping' ); ?>" class="button">
                            <?php _e( 'Cancel', 'wiseplus-shipping' ); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </form>
        </div>

        <!-- Cities List -->
        <div class="wiseplus-card">
            <h2><?php _e( 'Existing Cities', 'wiseplus-shipping' ); ?></h2>

            <?php if ( empty( $cities ) ) : ?>
                <p><?php _e( 'No cities added yet. Add your first city to start configuring shipping rates.', 'wiseplus-shipping' ); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'City Name', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Region', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Status', 'wiseplus-shipping' ); ?></th>
                            <th><?php _e( 'Actions', 'wiseplus-shipping' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $cities as $city ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $city->city_name ); ?></strong></td>
                                <td><?php echo esc_html( $city->region ); ?></td>
                                <td>
                                    <?php if ( $city->enabled ) : ?>
                                        <span class="wiseplus-status wiseplus-status-enabled"><?php _e( 'Enabled', 'wiseplus-shipping' ); ?></span>
                                    <?php else : ?>
                                        <span class="wiseplus-status wiseplus-status-disabled"><?php _e( 'Disabled', 'wiseplus-shipping' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=wiseplus-shipping&edit=' . $city->id ); ?>" class="button button-small">
                                        <?php _e( 'Edit', 'wiseplus-shipping' ); ?>
                                    </a>

                                    <form method="post" style="display: inline;" onsubmit="return confirm('<?php _e( 'Are you sure you want to delete this city?', 'wiseplus-shipping' ); ?>');">
                                        <?php wp_nonce_field( 'wiseplus_admin_action', 'wiseplus_nonce' ); ?>
                                        <input type="hidden" name="wiseplus_action" value="delete_city">
                                        <input type="hidden" name="city_id" value="<?php echo esc_attr( $city->id ); ?>">
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
