<?php
/**
 * Update Discontinued Product data.
 *
 * @package WooCommerce\Classes
 * @version 3.3.0
 * @since   3.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Background process to update data.
 */
class DP_Data_Background_Process extends WP_Background_Process {

	/**
	 * Starts the background process for updating legacy product data.
	 * This function adds one product with legacy metadata to the queue.
	 */
	public function start() {
		$this->queue_next_product();
	}

	/**
	 * Add one product with legacy meta to the queue if it exists.
	 */
	protected function queue_next_product() {
		global $wpdb;

		// Query the database for one product with a matching legacy meta key.
		$product_id = $wpdb->get_var(
			"
			SELECT p.ID 
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'product'
			AND pm.meta_key IN ('_is_discontinued', '_hide_from_shop', '_hide_from_search')
			LIMIT 1
			"
		);

		// If a product with legacy meta data is found, queue it.
		if ( $product_id ) {
			$this->push_to_queue( $product_id );
			$this->save()->dispatch();
		}
	}

	/**
	 * Processes a single product item.
	 *
	 * @param int $product_id Product ID to process.
	 * @return bool False to indicate that this item is complete.
	 */
	protected function task( $product_id ) {
		$this->process_legacy_data( $product_id );
		return false;
	}

	/**
	 * Process legacy data for the given product.
	 *
	 * @param int $product_id Product ID to process.
	 */
	protected function process_legacy_data( $product_id ) {
		$is_discontinued  = get_post_meta( $product_id, '_is_discontinued', true );
		$hide_from_shop   = get_post_meta( $product_id, '_hide_from_shop', true );
		$hide_from_search = get_post_meta( $product_id, '_hide_from_search', true );

		$terms = array();
		if ( 'yes' === $is_discontinued ) {
			$terms[] = 'dp-discontinued';
		}
		if ( 'hide' === $hide_from_search ) {
			$terms[] = 'dp-hide-search';
		} elseif ( 'show' === $hide_from_search ) {
			$terms[] = 'dp-show-search';
		}
		if ( 'hide' === $hide_from_shop ) {
			$terms[] = 'dp-hide-shop';
		} elseif ( 'show' === $hide_from_shop ) {
			$terms[] = 'dp-show-shop';
		}

		wp_set_object_terms( $product_id, $terms, 'product_discontinued' );
		delete_post_meta( $product_id, '_is_discontinued' );
		delete_post_meta( $product_id, '_hide_from_shop' );
		delete_post_meta( $product_id, '_hide_from_search' );
	}

	/**
	 * Runs when the queue is complete.
	 * Initiates start() to check for additional items.
	 */
	protected function complete() {
		parent::complete();
		$this->start(); // Check and queue the next item if available.
	}
}
