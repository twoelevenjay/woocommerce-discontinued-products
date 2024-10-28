<?php
/**
 * Admin View: Notice - Updating
 *
 * @package WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated woocommerce-message wc-connect">
	<p>
		<strong><?php esc_html_e( 'Discontinued Product data update', 'discontinued-products' ); ?></strong><br>
		<?php esc_html_e( 'Discontinued Product is updating the data in the background. The data update process may take a little while, so please be patient.', 'discontinued-products' ); ?>
	</p>
</div>
