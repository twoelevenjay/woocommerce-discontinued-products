<?php
/**
 * WooCommerce Discontinued Products Template Functions
 *
 * @package woocommerce
 * @since 1.0.0
 */

if ( ! function_exists( 'is_discontinued' ) ) {

	/**
	 * Is discontiued.
	 * Check if product is discontinued.
	 *
	 * @since 1.0.0
	 * @param int $product_id Optional. ID of the product to check.
	 */
	function is_discontinued( $product_id = null ) {

		global $post;
		$product_id = $product_id !== null ? $product_id : $post->ID;
		$is_discontinued = get_post_meta( $product_id, '_is_discontinued', true );
		return $is_discontinued === 'yes';
	}
}

if ( ! function_exists( 'woocommerce_alternative_products' ) ) {

	/**
	 * Alternative Products.
	 * Output buttons to alternative products.
	 *
	 * @since 1.0.0
	 */
	function woocommerce_alternative_products() {

		global $post;
		$alt_products = get_post_meta( $post->ID, '_alt_products', true );
		$alt_products = is_array( $alt_products ) ? $alt_products : array();
		$text = _( 'This product has been discontinued.' );
		$notice = empty( $alt_products ) ? $text : $text . ' ' . _( 'You may be interested in:' );
		?>
		<h4><?php echo esc_html( $notice ); ?></h4>
		<?php
		foreach ( $alt_products as $alt_product ) {
			?>
			<a href="<?php echo esc_url( get_permalink( $alt_product ) ); ?>" class="button"><?php echo get_the_title( $alt_product ); ?></a>
			<?php
		}
	}
}
