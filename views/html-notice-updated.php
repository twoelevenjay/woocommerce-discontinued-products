<?php
/**
 * Admin View: Notice - Updated.
 *
 * @package WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated woocommerce-message wc-connect woocommerce-message--success is-dismissible">
	<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'dp_updated', remove_query_arg( 'do_update_dp' ) ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'discontinued-products' ); ?></a>

	<p><?php esc_html_e( 'Discontinued Products data update complete. Thank you.', 'discontinued-products' ); ?></p>
</div>
