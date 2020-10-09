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

			$discontinued   = term_exists( 'dp-discontinued', 'product_discontinued' );
			$hide_on_shop   = term_exists( 'dp-hide-shop', 'product_discontinued' );
			$show_on_shop   = term_exists( 'dp-show-shop', 'product_discontinued' );
			$hide_on_search = term_exists( 'dp-hide-search', 'product_discontinued' );
			$show_on_search = term_exists( 'dp-show-search', 'product_discontinued' );
			$discontinued   = $discontinued ? $discontinued : wp_insert_term( 'dp-discontinued', 'product_discontinued', array( 'slug' => 'dp-discontinued' ) );
			$hide_on_shop   = $hide_on_shop ? $hide_on_shop : wp_insert_term( 'dp-hide-shop', 'product_discontinued', array( 'slug' => 'dp-hide-shop' ) );
			$show_on_shop   = $show_on_shop ? $show_on_shop : wp_insert_term( 'dp-show-shop', 'product_discontinued', array( 'slug' => 'dp-show-shop' ) );
			$hide_on_search = $hide_on_search ? $hide_on_search : wp_insert_term( 'dp-hide-search', 'product_discontinued', array( 'slug' => 'dp-hide-search' ) );
			$show_on_search = $show_on_search ? $show_on_search : wp_insert_term( 'dp-show-search', 'product_discontinued', array( 'slug' => 'dp-show-search' ) );
			update_option( 'dp_discontinued_term', $discontinued['term_id'] );
			update_option( 'dp_hide_shop_term', $hide_on_shop['term_id'] );
			update_option( 'dp_show_shop_term', $show_on_shop['term_id'] );
			update_option( 'dp_hide_search_term', $hide_on_search['term_id'] );
			update_option( 'dp_show_search_term', $show_on_search['term_id'] );
		}
	}

	$dp_taxonomy = new DP_Taxonomy();
}
