<?php
/**
 * Products shortcode
 *
 * @package  WooCommerce/Shortcodes
 * @version  3.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class 211J\Discontined_Products\DP_Shortcode_Discontinued.
 */
class DP_Shortcode_Discontinued extends WC_Shortcode_Products {

	/**
	 * Set sale products query args.
	 *
	 * @since 3.2.0
	 * @param array $query_args Query args.
	 */
	protected function set_discontinued_products_query_args( &$query_args ) {
		$dp_discontinued_term      = (int) get_option( 'dp_discontinued_term' );
		$query_args['tax_query'][] = array(
			'taxonomy' => 'product_discontinued',
			'field'    => 'id',
			'terms'    => $dp_discontinued_term,
			'operator' => 'IN',
		);
	}
}
