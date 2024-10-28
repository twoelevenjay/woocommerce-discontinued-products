<?php
/**
 * WooCommerce Discontinued Products Taxonomy Class
 *
 * @package woocommerce
 * @since 1.1.0
 */

if ( ! class_exists( 'DP_Taxonomy' ) ) {
	/**
	 * DP_Taxonomy Class
	 *
	 * @since 1.1.0
	 */
	class DP_Taxonomy {

		/**
		 * Inititiate the DP_Taxonomy.
		 *
		 * @since 2.0.0
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'register_taxonomy' ), 5 );
			add_action( 'init', array( $this, 'maybe_create_terms' ), 100 );
		}

		/**
		 * Reguster the discontinued taxonomy.
		 *
		 * @since 2.0.0
		 */
		public function register_taxonomy() {

			register_taxonomy(
				'product_discontinued',
				array( 'product' ),
				array(
					'hierarchical'      => false,
					'show_ui'           => false,
					'show_in_nav_menus' => false,
					'query_var'         => is_admin(),
					'rewrite'           => false,
					'public'            => false,
					'label'             => _x( 'Product discontinued', 'Taxonomy name', 'discontinued-products' ),
				)
			);
		}

		/**
		 * Create the product_discontinued terms if they don't exist.
		 *
		 * @since 2.0.0
		 */
		public function maybe_create_terms() {
			// Define terms and their slugs in an array for easy iteration.
			$terms = array(
				'dp-discontinued' => 'dp-discontinued',
				'dp-hide-shop'    => 'dp-hide-shop',
				'dp-show-shop'    => 'dp-show-shop',
				'dp-hide-search'  => 'dp-hide-search',
				'dp-show-search'  => 'dp-show-search',
			);

			// Loop through and create any missing terms.
			foreach ( $terms as $name => $slug ) {
				if ( ! term_exists( $slug, 'product_discontinued' ) ) {
					wp_insert_term( $name, 'product_discontinued', array( 'slug' => $slug ) );
				}
			}
		}
	}

	$dp_taxonomy = new DP_Taxonomy();
}
