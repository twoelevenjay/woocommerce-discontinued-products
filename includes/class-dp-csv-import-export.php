<?php
/**
 * WooCommerce Discontinued Products Import / Export Class
 *
 * @package woocommerce
 * @since 1.1.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'DP_CSV_Import_Export' ) ) {
	/**
	 * WC DP Products Import / Export class.
	 */
	class DP_CSV_Import_Export {

		/**
		 * Inititiate the DP_CSV_Import_Export.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_columns_to_importer' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen' ) );
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'process_import' ), 10, 2 );
			add_action( 'woocommerce_product_import_inserted_product_object', array( $this, 'process_import_terms' ), 10, 2 );
			add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_export_column' ) );
			add_filter( 'woocommerce_product_export_product_column_is_discontinued', array( $this, 'add_export_data_is_discontinued' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_discontinued_product_text', array( $this, 'add_export_data_discontinued_product_text' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_alt_products', array( $this, 'add_export_data_alt_products' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_alt_product_text', array( $this, 'add_export_data_alt_product_text' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_hide_from_shop', array( $this, 'add_export_data_hide_from_shop' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_hide_from_search', array( $this, 'add_export_data_hide_from_search' ), 10, 2 );
			add_filter( 'woocommerce_product_export_skip_meta_keys', array( $this, 'skip_meta_keys' ) );
		}
		/**
		 * Register the 'Custom Column' column in the importer.
		 *
		 * @param  array $options Array of options.
		 * @return array $options
		 */
		public function add_columns_to_importer( $options ) {

			$options['is_discontinued']           = 'Is discontinued?';
			$options['discontinued_product_text'] = 'Discontinued Product Text';
			$options['alt_products']              = 'Alternative Products';
			$options['alt_product_text']          = 'Alternative Products Text';
			$options['hide_from_shop']            = 'Hide from shop?';
			$options['hide_from_search']          = 'Hide from search?';
			return $options;
		}
		/**
		 * Add automatic mapping support for 'Custom Column'.
		 * This will automatically select the correct mapping for columns named 'Custom Column' or 'custom column'.
		 *
		 * @param  array $columns Array of columns.
		 * @return array $columns
		 */
		public function add_column_to_mapping_screen( $columns ) {

			$columns['Is discontinued?']          = 'is_discontinued';
			$columns['Discontinued Product Text'] = 'discontinued_product_text';
			$columns['Alternative Products']      = 'alt_products';
			$columns['Alternative Products Text'] = 'alt_product_text';
			$columns['Hide from shop?']           = 'hide_from_shop';
			$columns['Hide from search?']         = 'hide_from_search';

			return $columns;
		}
		/**
		 * Process the data read from the CSV file.
		 * This just saves the value in meta data, but you can do anything you want here with the data.
		 *
		 * @param WC_Product $product - Product being imported or updated.
		 * @param array      $data - CSV data read for the product.
		 * @return WC_Product $product
		 */
		public function process_import( $product, $data ) {

			if ( ! empty( $data['discontinued_product_text'] ) ) {
				$product->update_meta_data( '_discontinued_product_text', $data['discontinued_product_text'] );
			}
			if ( ! empty( $data['alt_products'] ) ) {
				$product->update_meta_data( '_alt_products', array_values( array_filter( array_map( 'absint', explode( ',', $data['alt_products'] ) ) ) ) );
			}
			if ( ! empty( $data['alt_product_text'] ) ) {
				$product->update_meta_data( '_alt_product_text', $data['alt_product_text'] );
			}
			return $product;
		}

		/**
		 * Save the discontinued, shop and search flags as product_discontinued terms
		 * once the imported product has an ID. Only columns present in the file change.
		 *
		 * @since 2.0.9
		 * @param WC_Product $product Imported product.
		 * @param array      $data    CSV data read for the product.
		 */
		public function process_import_terms( $product, $data ) {

			if ( ! is_a( $product, 'WC_Product' ) || ! $product->get_id() ) {
				return;
			}
			$product_id = $product->get_id();
			$groups     = array(
				'is_discontinued'  => array( 'yes' => 'dp-discontinued' ),
				'hide_from_shop'   => array(
					'hide' => 'dp-hide-shop',
					'show' => 'dp-show-shop',
				),
				'hide_from_search' => array(
					'hide' => 'dp-hide-search',
					'show' => 'dp-show-search',
				),
			);

			foreach ( $groups as $column => $map ) {
				if ( ! array_key_exists( $column, $data ) ) {
					continue;
				}
				$value = strtolower( trim( (string) $data[ $column ] ) );
				if ( 'is_discontinued' === $column && in_array( $value, array( '1', 'true' ), true ) ) {
					$value = 'yes';
				}
				wp_remove_object_terms( $product_id, array_values( $map ), 'product_discontinued' );
				if ( isset( $map[ $value ] ) ) {
					wp_add_object_terms( $product_id, $map[ $value ], 'product_discontinued' );
				}
			}
		}
		/**
		 * Add the custom column to the exporter and the exporter column menu.
		 *
		 * @param  array $columns Array of columns.
		 * @return array $columns
		 */
		public function add_export_column( $columns ) {

			$columns['is_discontinued']           = 'Is discontinued?';
			$columns['discontinued_product_text'] = 'Discontinued Product Text';
			$columns['alt_products']              = 'Alternative Products';
			$columns['alt_product_text']          = 'Alternative Products Text';
			$columns['hide_from_shop']            = 'Hide from shop?';
			$columns['hide_from_search']          = 'Hide from search?';

			return $columns;
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_is_discontinued( $value, $product ) {
			return has_term( 'dp-discontinued', 'product_discontinued', $product->get_id() ) ? 'yes' : '';
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_discontinued_product_text( $value, $product ) {
			$value = $product->get_meta( '_discontinued_product_text', true, 'edit' );
			return $value;
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_alt_products( $value, $product ) {
			$value = $product->get_meta( '_alt_products', true, 'edit' );
			return is_array( $value ) ? implode( ', ', $value ) : '';
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_alt_product_text( $value, $product ) {
			$value = $product->get_meta( '_alt_product_text', true, 'edit' );
			return $value;
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_hide_from_shop( $value, $product ) {
			return $this->get_visibility_value( $product->get_id(), 'shop' );
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_hide_from_search( $value, $product ) {
			return $this->get_visibility_value( $product->get_id(), 'search' );
		}

		/**
		 * Read the per product shop or search override from its terms.
		 *
		 * @since 2.0.9
		 * @param int    $product_id Product ID.
		 * @param string $context    Either shop or search.
		 * @return string hide, show or an empty string for the default.
		 */
		protected function get_visibility_value( $product_id, $context ) {
			if ( has_term( 'dp-hide-' . $context, 'product_discontinued', $product_id ) ) {
				return 'hide';
			}
			if ( has_term( 'dp-show-' . $context, 'product_discontinued', $product_id ) ) {
				return 'show';
			}
			return '';
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param array $meta_keys Array of meta keys to skip.
		 * @return mixed $meta_keys Array of meta keys to skip.
		 */
		public function skip_meta_keys( $meta_keys ) {
			$dp_meta_keys = array(
				'_is_discontinued',
				'_discontinued_product_text',
				'_alt_products',
				'_alt_product_text',
				'_hide_from_shop',
				'_hide_from_search',
			);
			$meta_keys    = array_unique( array_merge( $meta_keys, $dp_meta_keys ) );
			return $meta_keys;
		}
	}

	$dp_import_export = new DP_CSV_Import_Export();
}
