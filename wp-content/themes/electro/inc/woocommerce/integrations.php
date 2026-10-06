<?php
/**
 * WooCommerce Extensions Integrations
 *
 * @package electro
 */

if ( is_yith_wcwl_activated() ) {

	global $yith_wcwl;

	function electro_add_to_wishlist_button() {
		echo do_shortcode( "[yith_wcwl_add_to_wishlist]" );
	}

	add_action( 'electro_single_product_action_buttons', 	'electro_add_to_wishlist_button' );
	add_action( 'electro_loop_action_buttons',				'electro_add_to_wishlist_button' );

	if( property_exists( $yith_wcwl, 'wcwl_init' ) ) {
		remove_action( 'wp_enqueue_scripts', array( $yith_wcwl->wcwl_init, 'enqueue_styles_and_stuffs' ) );
	}

	if ( !function_exists( 'electro_wishlist_dequeue_main_style' ) ) {
		function electro_wishlist_dequeue_main_style() {
			wp_dequeue_style( 'yith-wcwl-main' );
		}
	}

	add_action( 'wp_enqueue_scripts', 'electro_wishlist_dequeue_main_style', 10 );

	if( ! function_exists( 'electro_get_wishlist_page_id' ) ){
		/**
		 * Gets the page ID of wishlist page
		 * 
		 * @return int
		 */
		function electro_get_wishlist_page_id() {
			$wishlist_page_id = yith_wcwl_object_id( get_option( 'yith_wcwl_wishlist_page_id' ) );
			return $wishlist_page_id;
		}
	}

	if( ! function_exists( 'electro_is_wishlist_page' ) ) {
		/**
		 * Conditional tag to determine if a page is a wishlist page or not
		 *
		 * @return boolean
		 */
		function electro_is_wishlist_page() {
			$wishlist_page_id = electro_get_wishlist_page_id();
			return is_page( $wishlist_page_id );
		}
	}

	if( ! function_exists( 'electro_get_wishlist_url') ) {
		/**
		 * Returns URL of wishlist page
		 *
		 * @return string
		 */
		function electro_get_wishlist_url(){
			$wishlist_page_id = electro_get_wishlist_page_id();
			return get_permalink( $wishlist_page_id );
		}
	}

	if ( ! function_exists( 'electro_handheld_footer_bar_wishlist_link') ) {
		function electro_handheld_footer_bar_wishlist_link() { ?>
			<a href="<?php echo esc_attr( electro_get_wishlist_url() ); ?>" class="has-icon"><i class="<?php echo esc_attr( apply_filters( 'electro_wishlist_icon', 'ec ec-favorites' ) ); ?>"></i><span class="count"><?php echo yith_wcwl_count_products(); ?></span></a><?php
		}
	}
}

if( is_yith_woocompare_activated() ) {

	global $yith_woocompare;

	remove_action( 'woocommerce_single_product_summary', array( $yith_woocompare->obj , 'add_compare_link' ), 35 );
	remove_action( 'init', array( YITH_WooCompare_Frontend::instance(), 'add_compare_button' ) );

	function electro_add_compare_url_to_localize_data( $data ) {
		$data[ 'compare_page_url' ] = electro_get_compare_page_url();
		return $data;
	}

	add_filter( 'electro_localize_script_data', 'electro_add_compare_url_to_localize_data' );

	function electro_add_to_compare_link() {
		global $product;
		$product_id = $product->get_id();

		YITH_WooCompare_Frontend::instance()->output_button( $product_id );
	}

	add_action( 'electro_single_product_action_buttons', 'electro_add_to_compare_link', 20 );
	add_action( 'electro_loop_action_buttons', 'electro_add_to_compare_link', 20 );

	add_filter( 'yith_woocompare_button_classes', 'electro_add_compare_button_classes', 10, 2 );

	function electro_add_compare_button_classes( $classes, $product_id ) {
		$classes[] = 'add-to-compare-link';
		return $classes;
	}
	
	function electro_update_yith_compare_options( $options ) {
		
		foreach ( $options['general'] as $key => $option ) {
			
			if ( $option['id'] == 'yith_woocompare_auto_open' ) {
				$options['general'][$key]['std']     = 'no';
				$options['general'][$key]['default'] = 'no';
			}
		
		}
		
		return $options;
	}
	
	add_filter( 'yith_woocompare_tab_options', 'electro_update_yith_compare_options' );

	if ( ! function_exists( 'electro_get_compare_page_url' ) ) {
		/**
		 * Returns URL of Product Comparision Page
		 *
		 * @return string
		 */
		function electro_get_compare_page_url() {
			return YITH_WooCompare_Frontend::instance()->get_table_url();
		}
	}

	function electro_compare_product_list_count() {
		return YITH_WooCompare_Products_List::instance()->count();
	}

	add_filter( 'yith_woocompare_general_settings', 'electro_update_compare_link_default' );

	if ( ! function_exists( 'electro_update_compare_link_default' ) ) {
		/**
	     * Forces the "Compare style" default to "link" in the YITH Compare settings.
	     * 
	     *
	     * @param array $options The YITH Compare general settings array.
	     * @return array Modified settings with default set to 'link'.
	     */
	    function electro_update_compare_link_default( $options ) {
	        if ( isset( $options['general']['yith_woocompare_is_button'] ) ) {
	            $options['general']['yith_woocompare_is_button']['default'] = 'link';
	        }

	        return $options;
	    }
	}
}

if ( is_yith_wcan_activated() ) {
	add_action( 'woocommerce_before_shop_loop', 'electro_wcan_wrap_start', 10 );
	add_action( 'woocommerce_after_shop_loop', 'electro_wcan_wrap_end', 91 );

	function electro_wcan_wrap_start() {
		?><div class="wcan-products-container"><?php
	}

	function electro_wcan_wrap_end() {
		?></div><!-- /.wcan-products-container --><?php
	}

	function electro_wcan_custom_scripts() {
		if ( yith_wcan_can_be_displayed() ) {
			$custom_script = "
				(function($) {
					$( document ).on( 'yith-wcan-ajax-loading', function( e, response ) {
						if ( $( '.site-content' ).hasClass( 'active-hh-sidebar' ) ) {
							$( '.site-content' ).toggleClass( 'active-hh-sidebar' );
						}
					} );
				})(jQuery);
			";
			wp_add_inline_script( 'electro-js', $custom_script );
		}
	}

	add_action( 'wp_enqueue_scripts', 'electro_wcan_custom_scripts', 20 );
}

if ( is_wc_product_reviews_activated() ) {
	add_filter( 'electro_use_advanced_reviews', '__return_false', 100 );
}

if ( is_wc_simple_auction_activated() ) {

	function electro_wc_simple_auction_before_shop_loop() {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_auctions_ordering', 30, 0 );
	}
	add_action( 'woocommerce_before_shop_loop', 'electro_wc_simple_auction_before_shop_loop', 0 );

	function electro_wc_simple_auction_wp_loaded() {
		global $woocommerce_auctions;
		remove_action( 'woocommerce_before_shop_loop_item_title', array( $woocommerce_auctions, 'add_winning_bage' ), 60 );
	}
	add_action( 'wp_loaded', 'electro_wc_simple_auction_wp_loaded' );
	
	function electro_template_loop_product_thumbnail_wc_simple_auction( $html ) {
		global $woocommerce_auctions, $product;
		
		if( electro_wc_get_product_type( $product ) == 'auction' ) {
			ob_start();
			echo '<div class="product-thumbnail">';
			$woocommerce_auctions->add_winning_bage();
			echo woocommerce_get_product_thumbnail();
			echo '</div>';
			$html = ob_get_clean();
		}
		return $html;
	}
	add_filter( 'electro_template_loop_product_thumbnail', 'electro_template_loop_product_thumbnail_wc_simple_auction' );
}

require_once get_template_directory() . '/inc/woocommerce/integrations/yith-quick-view/functions.php';

if ( electro_is_yith_multistep_checkout_activated() ) {
	require_once get_template_directory() . '/inc/woocommerce/integrations/yith-woocommerce-multi-step-checkout/functions.php';
}

if ( electro_is_prdctfltr_activated() ) {
	require_once get_template_directory() . '/inc/woocommerce/integrations/prdctfltr/functions.php';
}

if ( ! function_exists( 'electro_is_advanced_woo_search_activated' ) ) {
	function electro_is_advanced_woo_search_activated() {
		 return function_exists( 'aws_get_search_form' );
	}
}

if ( electro_is_advanced_woo_search_activated() ) {
	require_once get_template_directory() . '/inc/woocommerce/integrations/advanced-woo-search/functions.php';
}