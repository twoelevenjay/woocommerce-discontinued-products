<?php
/**
 * WooCommerce Discontinued Products Template Functions
 *
 * @package woocommerce
 * @since 1.0.0
 */

if ( ! function_exists( 'dp_is_discontinued' ) ) {

	/**
	 * Is discontiued.
	 * Check if product is discontinued.
	 *
	 * @since 1.0.0
	 * @param int|null $product_id Optional. ID of the product to check.
	 */
	function dp_is_discontinued( $product_id = null ) {

		global $post;
		if ( $post || null !== $product_id ) {
			$product_id      = null !== $product_id ? $product_id : $post->ID;
			$is_discontinued = get_post_meta( $product_id, '_is_discontinued', true );
			return 'yes' === $is_discontinued;
		}
		return false;
	}
}

if ( ! function_exists( 'dp_alt_products' ) ) {

	/**
	 * Alternative Products.
	 * Output buttons to alternative products.
	 *
	 * @since 1.0.0
	 */
	function dp_alt_products() {

		global $post;
		$alt_products = get_post_meta( $post->ID, '_alt_products', true );
		$alt_products = is_array( $alt_products ) ? $alt_products : array();
		$notice       = dp_alt_products_notice( $post->ID, empty( $alt_products ) );
		?>
		<?php echo wp_kses( $notice, 'post' ); ?></h4>
		<?php
		foreach ( $alt_products as $alt_product ) {
			?>
			<a href="<?php echo esc_url( get_permalink( $alt_product ) ); ?>" class="button"><?php echo esc_html( get_the_title( $alt_product ) ); ?></a>
			<?php
		}
	}
}

if ( ! function_exists( 'dp_alt_products_notice' ) ) {

	/**
	 * Alternative Products Notice.
	 * Determin notice output for discontinued products based on settings.
	 *
	 * @since 1.1.0
	 * @param int     $product_id ID of the product to check.
	 * @param boolean $no_alt true or false if there are no alternative products.
	 */
	function dp_alt_products_notice( $product_id, $no_alt ) {

		$prod_text_option = get_post_meta( $product_id, '_discontinued_product_text', true );
		$prod_alt_option  = get_post_meta( $product_id, '_alt_product_text', true );
		$text_option      = get_option( 'dc_discontinued_text' );
		$alt_option       = get_option( 'dc_alt_text' );
		$text             = dp_alt_products_text( $prod_text_option, $text_option, __( 'This product has been discontinued.', 'discontinued-products' ) );
		$alt              = dp_alt_products_text( $prod_alt_option, $alt_option, __( 'You may be interested in:', 'discontinued-products' ) );
		$notice           = $no_alt ? '<h4 class="discontinued-notice">' . esc_html( $text ) . '</H4>' : '<h4 class="discontinued-notice">' . esc_html( $text ) . '</H4><h4 class="discontinued-notice-alt">' . esc_html( $alt ) . '</H4>';
		return $notice;
	}
}

if ( ! function_exists( 'dp_alt_products_text' ) ) {

	/**
	 * Alternative Products Text.
	 * Determin text for discontinued products based on settings.
	 *
	 * @since 1.1.0
	 * @param string $product_text product meta text.
	 * @param string $option_text options settings text.
	 * @param string $default_text default text.
	 */
	function dp_alt_products_text( $product_text, $option_text, $default_text ) {

		$text = $product_text ? $product_text : ( $option_text ? $option_text : $default_text );
		return $text;
	}
}

if ( ! function_exists( 'is_dp_shop' ) ) {

	/**
	 * Is_shop - Returns true when viewing the product type archive (shop).
	 *
	 * @return bool
	 */
	function is_dp_shop() {
		return ( is_page( (int) get_option( 'dc_shop_page_id' ) ) );
	}
}

add_filter( 'woocommerce_variable_sale_price_html', 'discontinued_template_loop_price', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'discontinued_template_loop_price', 10, 2 );
add_filter( 'woocommerce_get_price_html', 'discontinued_template_loop_price', 10, 2 );

if ( ! function_exists( 'discontinued_template_loop_price' ) ) {

	/**
	 * Discontinued_template_loop_price - replaces price with discontinued text.
	 * Replaces price in admin with "discontinued".
	 *
	 * @param int    $price Product price.
	 * @param object $product Product object.
	 * @return null
	 */
	function discontinued_template_loop_price( $price, $product ) {
		$product_id = $product->get_id();
		if ( dp_is_discontinued( $product_id ) ) {
			if ( is_admin() ) {
				return 'Discontinued';
			}
			$prod_text_option = get_post_meta( $product_id, '_discontinued_product_text', true );
			$text_option      = get_option( 'dc_discontinued_text' );
			$text             = dp_alt_products_text( $prod_text_option, $text_option, __( 'This product has been discontinued.', 'discontinued-products' ) );
			$price            = $text;
		}
		return $price;
	}
}

add_filter( 'woocommerce_product_price_class', 'discontinued_template_price_class', 10, 1 );

if ( ! function_exists( 'discontinued_template_price_class' ) ) {

	/**
	 * Discontinued_template_price_class - Add "discontinued" to the price class.
	 *
	 * @param int $class Product price css class.
	 * @return null
	 */
	function discontinued_template_price_class( $class ) {
		$class = $class . ' discontinued';

		return $class;
	}
}
