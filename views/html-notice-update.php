<?php
/**
 * Admin View: Notice - Update.
 *
 * @package WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';

$update_url = wp_nonce_url(
	add_query_arg(
		array(
			'do_update_dp' => 'true',
		),
		$current_url
	),
	'dp_update_nonce'
);
?>

<div id="message" class="updated woocommerce-message wc-connect">
	<p>
		<strong><?php esc_html_e( 'Discontinued Products data update required', 'discontinued-products' ); ?></strong>
	</p>
	<p>
		<?php
			esc_html_e( 'Discontinued Products has been updated. We have detected some products with outdated discontinued data. For your discontinued products to keep functioning as they were, you will need to update the data. The update runs in the background, we will let you know when it has completed.', 'discontinued-products' );
		?>
	</p>
	<p class="submit">
		<a href="<?php echo esc_url( $update_url ); ?>" class="wc-update-now button-primary">
			<?php esc_html_e( 'Update Discontinued Data', 'discontinued-products' ); ?>
		</a>
	</p>
</div>
