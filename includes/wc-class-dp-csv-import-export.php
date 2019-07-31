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


if ( ! class_exists( 'WC_Class_DP_Import_Export' ) ) {
	/**
	 * WC DP Products Import / Export class.
	 */
	class WC_Class_DP_Import_Export {

		/**
		 * Inititiate the DP_Import_Export.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_columns_to_importer' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen' ) );
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'process_import' ), 10, 2 );
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
		 * @param WC_Product $object - Product being imported or updated.
		 * @param array      $data - CSV data read for the product.
		 * @return WC_Product $object
		 */
		public function process_import( $object, $data ) {

			if ( ! empty( $data['is_discontinued'] ) ) {
				$object->update_meta_data( '_is_discontinued', $data['is_discontinued'] );
			}
			if ( ! empty( $data['discontinued_product_text'] ) ) {
				$object->update_meta_data( '_discontinued_product_text', $data['discontinued_product_text'] );
			}
			if ( ! empty( $data['alt_products'] ) ) {
				$object->update_meta_data( '_alt_products', explode( ', ', $data['alt_products'] ) );
			}
			if ( ! empty( $data['alt_product_text'] ) ) {
				$object->update_meta_data( '_alt_product_text', $data['alt_product_text'] );
			}
			if ( ! empty( $data['hide_from_shop'] ) ) {
				$object->update_meta_data( '_hide_from_shop', $data['hide_from_shop'] );
			}
			if ( ! empty( $data['hide_from_search'] ) ) {
				$object->update_meta_data( '_hide_from_search', $data['hide_from_search'] );
			}

			return $object;
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
			$value = $product->get_meta( '_is_discontinued', true, 'edit' );
			return $value;
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
			return implode( ', ', $value );
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
			$value = $product->get_meta( '_hide_from_shop', true, 'edit' );
			return $value;
		}

		/**
		 * Provide the data to be exported for one item in the column.
		 *
		 * @param mixed      $value   (default: '').
		 * @param WC_Product $product Product object.
		 * @return mixed     $value   Should be in a format that can be output into a text file (string, numeric, etc).
		 */
		public function add_export_data_hide_from_search( $value, $product ) {
			$value = $product->get_meta( '_hide_from_search', true, 'edit' );
			return $value;
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

	$wc_class_dp_import_export = new WC_Class_DP_Import_Export();
}
