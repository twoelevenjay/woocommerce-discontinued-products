<?php
/**
 * WooCommerce Discontinued Products Class
 *
 * @package woocommerce
 * @since 1.0.0
 */

if ( ! class_exists( 'WC_Class_Discontinued_Products' ) ) {
	/**
	 * WC_Class_Discontinued_Products Class
	 *
	 * @since 1.0.0
	 */
	class WC_Class_Discontinued_Products {

		/**
		 * Array of discontinued product IDs.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		public $discontinued_prod;

		/**
		 * Set to true during the discontinued product IDs query.
		 *
		 * @since 1.0.0
		 *
		 * @var bool
		 */
		public $doing_dp_ids;

		/**
		 * Inititiate the WC_Class_Discontinued_Products.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_discontinued_product_tab' ) );
			add_action( 'woocommerce_product_write_panels', array( $this, 'add_discontinued_product_panel' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
			add_action( 'template_redirect', array( $this, 'remove_add_to_cart' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'save_post', array( $this, 'set_discontinued_products' ) );
			add_action( 'pre_get_posts', array( $this, 'exclude_discontinued_products' ) );
			$this->discontinued_prod = get_transient( 'discontinued_products' );
			$this->doing_dp_ids = false;
		}

		/**
		 * Enqueue CSS and JS.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {

			$screen = get_current_screen();
			if ( $screen->post_type === 'product' ) {

				wp_register_style( 'discontinued_product_styles', WC_DP_URI . '/assets/css/discontinued_product.css', false, '' );
				wp_enqueue_style( 'discontinued_product_styles' );
			}

		}

		/**
		 * Add tab to the Product Data meta box.
		 *
		 * @since 1.0.0
		 */
		public function add_discontinued_product_tab() {

			?>
			<li class="discontinued_product_tab"><a href="#discontinued_product_tab_data"><?php esc_html_e( 'Discontinued Products', 'woocommerce-discontinued-products' ); ?></a></li>
			<?php
		}

		/**
		 * Add tab to the Product Data meta box.
		 *
		 * @since 1.0.0
		 */
		public function add_discontinued_product_panel() {

			global $post;
			?>
			<div id="discontinued_product_tab_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">
				<div class="options_group">
					<?php
						woocommerce_wp_checkbox(
							array(
								'id'			=> '_is_discontinued',
								'wrapper_class' => '',
								'label'		 => __( 'Is Discontinued', 'woocommerce' ),
								'description'   => __( 'Check if this product is discontinued', 'woocommerce-discontinued-products' ),
							)
						);
					?>
				</div>

				<div class="options_group">
					<p class="form-field">
						<label for="alt_products"><?php esc_html_e( 'Alternative Products', 'woocommerce' ); ?></label>
						<input type="hidden" class="wc-product-search" style="width: 50%;" id="alt_products" name="alt_products" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-exclude="<?php echo intval( $post->ID ); ?>" data-selected="<?php
						$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_alt_products', true ) ) );
						$json_ids	= array();

						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								$json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
							}
						}

						echo esc_attr( wp_json_encode( $json_ids ) );
						?>" value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ); ?>" /> <?php
						// @codingStandardsIgnoreStart
						echo wc_help_tip( __( 'Any product that is added to this field will generate a button for the add to cart area that will link to the corresponding product.', 'woocommerce-discontinued-products' ) );
						// @codingStandardsIgnoreEnd
						?>
					</p>
				</div>
			</div>
			<?php
		}
		/**
		 * Save dicontinued product settings.
		 *
		 * @since 1.0.0
		 * @param int $post_id Optional. ID of the product to update.
		 */
		public static function save( $post_id ) {

			$is_discontinued = filter_input( INPUT_POST, '_is_discontinued' ) !== null ? 'yes' : 'no';
			update_post_meta( $post_id, '_is_discontinued', $is_discontinued );
			$alt_products = filter_input( INPUT_POST, 'alt_products' ) !== null ? array_filter( array_map( 'intval', explode( ',', filter_input( INPUT_POST, 'alt_products' ) ) ) ) : array();
			update_post_meta( $post_id, '_alt_products', $alt_products );
		}

		/**
		 * Remove add to cart.
		 * Remove add to cart and all related buttons like wishlist, and add the alt products.
		 *
		 * @since 1.0.0
		 */
		public static function remove_add_to_cart() {

			if ( dp_is_discontinued() ) {
				remove_all_actions( 'woocommerce_single_product_summary' );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
				add_action( 'woocommerce_single_product_summary', 'dp_alternative_products', 60 );
			}
		}

		/**
		 * Set discontinued products.
		 * Query discontinued products and save them in a transient.
		 *
		 * @since 1.0.0
		 */
		public function set_discontinued_products() {

			$args = array(
				'post_type' => 'product',
				'meta_query' => array(
					array(
						'key'   => '_is_discontinued',
						'value' => 'yes',
					),
				),
				'fields' => 'ids',
			);
			$this->doing_dp_ids = true;
			$products = new WP_Query( $args );
			$this->doing_dp_ids = false;
			if ( $products->posts !== $this->discontinued_prod ) {
				set_transient( 'discontinued_products', $products->posts );
				$this->discontinued_prod;
			}

		}

		/**
		 * Exclude discontinued products.
		 * Add a the post__not_in argiment to the main query for products.
		 *
		 * @since 1.0.0
		 * @param object $query Main WP Query.
		 */
		public function exclude_discontinued_products( $query ) {

			if ( ! $this->doing_dp_ids && $query->is_main_query() && ! is_single() && ( $query->get( 'post_type' ) === 'product' || is_search() ) ) {
				$query->set( 'post__not_in', $this->discontinued_prod );
			}
		}
	}

	$wc_class_discontinued_products = new WC_Class_Discontinued_Products();
}
