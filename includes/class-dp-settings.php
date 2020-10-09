<?php
/**
 * WooCommerce Discontinued Products Settings Class
 *
 * @package woocommerce
 * @since 1.1.0
 */

if ( ! class_exists( 'DP_Settings' ) ) {
	/**
	 * DP_Settings Class
	 *
	 * @since 1.1.0
	 */
	class DP_Settings {

		/**
		 * Inititiate the DP_Settings.
		 *
		 * @since 1.1.0
		 */
		public function __construct() {

			add_filter( 'woocommerce_get_sections_products', array( $this, 'add_settings_section' ) );
			add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings_section_fields' ), 10, 2 );
		}

		/**
		 * Add Discontinued Products section.
		 *
		 * @since 1.1.0
		 * @param array $sections Array of "Product" tab sections.
		 */
		public function add_settings_section( $sections ) {

			$sections['discontinued_products'] = __( 'Discontinued Products', 'discontinued-products' );
			return $sections;
		}

		/**
		 * Add Discontinued Products settings.
		 *
		 * @since 1.1.0
		 * @param array  $settings Array of "Product" tab settings.
		 * @param string $current_section current section ID.
		 */
		public function add_settings_section_fields( $settings, $current_section ) {

			if ( 'discontinued_products' === $current_section ) {
				$settings   = array();
				$settings[] = array(
					'name' => __( 'Discontinued Products', 'discontinued-products' ),
					'type' => 'title',
					'desc' => __( 'The following options are global settings for Discontinued Products', 'discontinued-products' ),
					'id'   => 'discontinued_products',
				);
				$settings[] = array(
					'name'    => __( 'Hide from shop', 'discontinued-products' ),
					'id'      => 'dp_hide_from_shop',
					'type'    => 'checkbox',
					'css'     => 'min-width:300px;',
					'desc'    => __( 'Hide on product archive pages including the shop page by default.', 'discontinued-products' ),
					'default' => '',
				);
				$settings[] = array(
					'name'    => __( 'Hide from search', 'discontinued-products' ),
					'id'      => 'dp_hide_from_search',
					'type'    => 'checkbox',
					'css'     => 'min-width:300px;',
					'desc'    => __( 'Hide from the product search results page by default.', 'discontinued-products' ),
					'default' => '',
				);
				$settings[] = array(
					'name'     => __( 'Discontinued product text', 'discontinued-products' ),
					'desc_tip' => __( 'This can be overridden on a per product basis the default is: "This product has been discontinued.".', 'discontinued-products' ),
					'id'       => 'dp_discontinued_text',
					'type'     => 'text',
					'desc'     => __( 'Enter text to be shown when product is discontinued.', 'discontinued-products' ),
				);
				$settings[] = array(
					'name'     => __( 'Alternative product text', 'discontinued-products' ),
					'desc_tip' => __( 'This can be overridden on a per product basis the default is: "You may be interested in:".', 'discontinued-products' ),
					'id'       => 'dp_alt_text',
					'type'     => 'text',
					'desc'     => __( 'Enter text to be shown when alternative product are suggested.', 'discontinued-products' ),
				);

				$settings[] = array(
					'title'    => __( 'Discontinued page', 'discontinued-products' ),
					'id'       => 'dp_shop_page_id',
					'type'     => 'single_select_page',
					'default'  => '',
					'class'    => 'wc-enhanced-select-nostd',
					'css'      => 'min-width:300px;',
					'desc_tip' => __( 'This sets the page of your discontinued products - this is where your discontinued product archive will be.', 'discontinued-products' ),
				);

				$settings[] = array(
					'type' => 'sectionend',
					'id'   => 'discontinued_products',
				);
				return $settings;
			}

			return $settings;
		}
	}

	$dp_settings = new DP_Settings();
}
