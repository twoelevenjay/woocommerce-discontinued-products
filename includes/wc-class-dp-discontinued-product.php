<?php
/**
 * WooCommerce Discontinued Product Class
 *
 * @package woocommerce
 * @since 1.0.0
 */

if ( ! class_exists( 'WC_Class_DP_Discontinued_Product' ) ) {
	/**
	 * WC_Class_DP_Discontinued_Product Class
	 *
	 * @since 1.0.0
	 */
	class WC_Class_DP_Discontinued_Product {

		/**
		 * Array of discontinued product IDs to hide from shop.
		 *
		 * @since 1.1.0
		 *
		 * @var array
		 */
		public $hide_from_shop;

		/**
		 * Array of discontinued product IDs to hide from search.
		 *
		 * @since 1.1.0
		 *
		 * @var array
		 */
		public $hide_from_search;

		/**
		 * Set to true during the discontinued product IDs query.
		 *
		 * @since 1.0.0
		 *
		 * @var bool
		 */
		public $doing_dp_ids;

		/**
		 * Inititiate the WC_Class_DP_Discontinued_Product.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_discontinued_product_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_discontinued_product_panel' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
			add_action( 'template_redirect', array( $this, 'remove_add_to_cart' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'save_post', array( $this, 'set_discontinued_products_to_hide' ) );
			add_action( 'update_option_dc_hide_from_shop', array( $this, 'set_discontinued_products_to_hide' ) );
			add_action( 'update_option_dc_hide_from_search', array( $this, 'set_discontinued_products_to_hide' ) );
			add_action( 'pre_get_posts', array( $this, 'exclude_discontinued_products' ), 1000 );
			$this->hide_from_shop   = get_transient( 'dp_hide_from_shop' );
			$this->hide_from_search = get_transient( 'dp_hide_from_search' );
			$this->doing_dp_ids     = false;
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
			<li class="discontinued_product_tab"><a href="#discontinued_product_tab_data"><span><?php esc_html_e( 'Discontinued Products', 'woocommerce-discontinued-products' ); ?></span></a></li>
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
								'id'            => '_is_discontinued',
								'wrapper_class' => '',
								'label'         => __( 'Is Discontinued', 'woocommerce-discontinued-products' ),
								'description'   => __( 'Check if this product is discontinued', 'woocommerce-discontinued-products' ),
							)
						);
						$placeholder = get_option( 'dc_discontinued_text' );
						woocommerce_wp_text_input(
							array(
								'id'          => '_discontinued_product_text',
								'label'       => __( 'Display text', 'woocommerce-discontinued-products' ),
								'placeholder' => $placeholder,
								'desc_tip'    => 'true',
								'description' => __( 'Enter text to be shown when this product is discontinued', 'woocommerce-discontinued-products' ),
							)
						);
					?>
				</div>

				<div class="options_group">

					<p class="form-field">
						<label for="alt_products"><?php esc_html_e( 'Alternative Products', 'woocommerce-discontinued-products' ); ?></label>
						<select name="alt_products[]" class="wc-product-search" multiple="multiple" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_alt_products', true ) ) );

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
								}
							}
							?>
						</select>
						<?php
						// @codingStandardsIgnoreStart
						echo wc_help_tip( __( 'Any product that is added to this field will generate a button for the add to cart area that will link to the corresponding product.', 'woocommerce-discontinued-products' ) );
						// @codingStandardsIgnoreEnd
						?>
					</p>


					<?php
						$placeholder = get_option( 'dc_alt_text' );
						woocommerce_wp_text_input(
							array(
								'id'          => '_alt_product_text',
								'label'       => __( 'Alternative product text', 'woocommerce-discontinued-products' ),
								'placeholder' => $placeholder,
								'desc_tip'    => 'true',
								'description' => __( 'Enter text to be shown when alternative product are suggested.', 'woocommerce-discontinued-products' ),
							)
						);
					?>
				</div>

				<div class="options_group">
					<?php
						woocommerce_wp_select(
							array(
								'id'          => '_hide_from_shop',
								'label'       => __( 'Hide on shop / archive.', 'woocommerce-discontinued-products' ),
								'desc_tip'    => 'true',
								'description' => __( 'Hide from shop / archive pages.', 'woocommerce-discontinued-products' ),
								'options'     => array(
									''     => __( 'Default', 'woocommerce-discontinued-products' ),
									'hide' => __( 'Hide', 'woocommerce-discontinued-products' ),
									'show' => __( 'Show', 'woocommerce-discontinued-products' ),
								),
							)
						);
						woocommerce_wp_select(
							array(
								'id'          => '_hide_from_search',
								'label'       => __( 'Hide on search.', 'woocommerce-discontinued-products' ),
								'desc_tip'    => 'true',
								'description' => __( 'Hide from search results.', 'woocommerce-discontinued-products' ),
								'options'     => array(
									''     => __( 'Default', 'woocommerce-discontinued-products' ),
									'hide' => __( 'Hide', 'woocommerce-discontinued-products' ),
									'show' => __( 'Show', 'woocommerce-discontinued-products' ),
								),
							)
						);
					?>
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
			update_post_meta( $post_id, '_discontinued_product_text', filter_input( INPUT_POST, '_discontinued_product_text' ) );
			$alt_products = filter_input( INPUT_POST, 'alt_products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ? filter_input( INPUT_POST, 'alt_products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) : array();
			update_post_meta( $post_id, '_alt_products', $alt_products );
			update_post_meta( $post_id, '_alt_product_text', filter_input( INPUT_POST, '_alt_product_text' ) );
			update_post_meta( $post_id, '_hide_from_shop', filter_input( INPUT_POST, '_hide_from_shop' ) );
			update_post_meta( $post_id, '_hide_from_search', filter_input( INPUT_POST, '_hide_from_search' ) );
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
				add_action( 'woocommerce_single_product_summary', 'dp_alt_products', 60 );
			}
		}

		/**
		 * Set discontinued products.
		 * Query discontinued products and save them in a transient.
		 *
		 * @since 1.0.0
		 */
		public function set_discontinued_products_to_hide() {

			$hide_from_shop   = get_option( 'dc_hide_from_shop' );
			$hide_from_search = get_option( 'dc_hide_from_search' );
			$ids_hide_shop    = $this->get_product_ids_to_hide( '_hide_from_shop', $hide_from_shop );
			$ids_hide_search  = $this->get_product_ids_to_hide( '_hide_from_search', $hide_from_search );
			if ( $ids_hide_shop !== $this->hide_from_shop ) {
				set_transient( 'dp_hide_from_shop', $ids_hide_shop );
				$this->hide_from_shop = $ids_hide_shop;
			}
			if ( $ids_hide_search !== $this->hide_from_search ) {
				set_transient( 'dp_hide_from_search', $ids_hide_search );
				$this->hide_from_search = $ids_hide_search;
			}
		}

		/**
		 * Get product IDs t hide.
		 * Query discontinued products based settings and return IDs.
		 *
		 * @since 1.0.1
		 * @param string $where_to_hide meta_query key based on where to hide product.
		 * @param string $option the global setting of whether or not to hide.
		 */
		public function get_product_ids_to_hide( $where_to_hide, $option ) {

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				// @codingStandardsIgnoreStart
				'meta_query'     => array(
					array(
						'key'   => '_is_discontinued',
						'value' => 'yes',
					),
					array(
						'key'     => $where_to_hide,
						'value'   => 'show',
						'compare' => '!=',
					),
				),
				// @codingStandardsIgnoreStart
				'fields'         => 'ids',
			);
			if ( $option === 'no' ) {
				$args['meta_query'][1]['value']   = 'hide';
				$args['meta_query'][1]['compare'] = '==';
			}
			$this->doing_dp_ids = true;
			$products           = new WP_Query( $args );
			$this->doing_dp_ids = false;
			return $products->posts;
		}

		/**
		 * Exclude discontinued products.
		 * Add a the post__not_in argiment to the main query for products.
		 *
		 * @since 1.0.0
		 * @param object $query Main WP Query.
		 */
		public function exclude_discontinued_products( $query ) {
			$ids_to_hide = false;
			if ( $query->is_post_type_archive( 'product' ) || isset( $query->query_vars['product_cat'] ) ) {
				$ids_to_hide = $this->hide_from_shop;
			}
			if ( is_search() ) {
				$ids_to_hide = $this->hide_from_search;
			}
			if ( ! is_admin() && ! $this->doing_dp_ids && $query->is_main_query() && ! is_single() && $ids_to_hide ) {

				$query->set( 'post__not_in', $ids_to_hide );
			}
			return $query;
		}

		/**
		 * Borrow the WooCommerce shop template for discontinued products.
		 *
		 * @since 1.4.0
		 * @param int $shop_page_id Shop page ID.
		 */
		public function override_shop_page_id( $shop_page_id ) {

			$dc_shop_page_id = get_option( 'dc_shop_page_id' );
			if ( get_the_ID() === (int) $dc_shop_page_id ) {

				$shop_page_id = $dc_shop_page_id;
			}
			return $shop_page_id;
		}
	}

	$wc_class_dp_discontinued_product = new WC_Class_DP_Discontinued_Product();
}
