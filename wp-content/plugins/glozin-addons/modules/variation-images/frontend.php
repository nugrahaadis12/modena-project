<?php

namespace Glozin\Addons\Modules\Variation_Images;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class of plugin for admin
 */
class Frontend {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Has variation images
	 *
	 * @var $has_variation_images
	 */
	protected static $has_variation_images = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_before_single_product', array( $this, 'add_post_class' ) );
		add_action( 'glozin_before_single_product', array( $this, 'add_post_class' ) );
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'remove_post_class' ) );
		add_action( 'glozin_before_woocommerce_product_content', array( $this, 'remove_post_class' ) );

		add_filter( 'woocommerce_product_get_image_id', array( $this, 'product_get_image_id' ), 20, 2 );
		add_filter( 'woocommerce_product_get_gallery_image_ids', array( $this, 'product_get_gallery_image_ids' ), 20, 2 );

		add_action( 'wc_ajax_glozin_get_variation_images', array( $this, 'get_variation_images' ) );
	}

	public function product_get_image_id( $image_id, $product ) {
		$variation = $this->get_initial_variation_product( $product );

		if ( ! $variation ) {
			return $image_id;
		}

		$variation_image_id = $variation->get_image_id();

		return $variation_image_id ? $variation_image_id : $image_id;
	}

	public function product_get_gallery_image_ids( $attachment_ids, $product ) {
		$variation = $this->get_initial_variation_product( $product );

		if ( ! $variation ) {
			return $attachment_ids;
		}

		$variation_images = get_post_meta( $variation->get_id(), 'glozin_variation_images', true );

		if ( empty( $variation_images ) ) {
			return $attachment_ids;
		}

		return array_filter( array_map( 'absint', explode( ',', $variation_images ) ) );
	}

	protected function get_initial_variation_product( $product ) {
		if ( ! is_product() || wp_doing_ajax() || empty( $product ) || ! $product->is_type( 'variable' ) ) {
			return false;
		}

		$variation_id = $this->get_variation_id_default( $product );
		if ( ! $variation_id ) {
			return false;
		}

		$variation = wc_get_product( $variation_id );
		if ( ! $variation ) {
			return false;
		}

		$variation_images = get_post_meta( $variation_id, 'glozin_variation_images', true );
		if ( ! $variation->get_image_id() && empty( $variation_images ) ) {
			return false;
		}

		return $variation;
	}

	protected function get_current_product() {
		global $product;

		if ( $product instanceof \WC_Product ) {
			return $product;
		}

		$product_id = get_queried_object_id();
		if ( ! $product_id ) {
			return false;
		}

		return wc_get_product( $product_id );
	}

	public function has_variation_images() {
		if ( isset( self::$has_variation_images ) ) {
			return self::$has_variation_images;
		}

		global $product;
		self::$has_variation_images = false;

		if ( empty( $product ) || 'variable' !== $product->get_type() ) {
			return self::$has_variation_images;
		}

		$variation_ids = $product->get_children();
		if ( empty( $variation_ids ) ) {
			return self::$has_variation_images;
		}

		foreach ( $variation_ids as $variation_id ) {
			if ( get_post_meta( $variation_id, 'glozin_variation_images', true ) ) {
				self::$has_variation_images = true;
				return self::$has_variation_images;
			}
		}
	}

	public function add_post_class() {
		add_filter( 'post_class', array( $this, 'product_class' ), 10, 3 );
	}

	public function remove_post_class() {
		remove_filter( 'post_class', array( $this, 'product_class' ), 10, 3 );
	}

	/**
	 * Adds classes to products
	 *
	 * @since 1.0.0
	 *
	 * @param string $class Post class.
	 *
	 * @return array
	 */
	public function product_class( $classes ) {
		if ( is_admin() || get_post_type( get_the_ID() ) != 'product' ) {
			return $classes;
		}

		if ( $this->has_variation_images() ) {
			$classes[] = 'product-has-variation-images';
		}

		return $classes;
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'glozin_variation_images', GLOZIN_ADDONS_URL . '/modules/variation-images/assets/variation-images-frontend' . $debug . '.js', array( 'jquery' ), '20220319', array( 'strategy' => 'defer' ) );

		$variation_id = $this->get_variation_id_default( $this->get_current_product() );

		wp_localize_script( 'glozin_variation_images', 'glozinVariationImages', array(
			'variation_id_default'  => $variation_id,
			'variation_id_rendered' => $variation_id,
		) );
	}

	protected function get_matching_variation_id( $product, $attributes ) {
		return (int) \WC_Data_Store::load( 'product' )->find_matching_product_variation( $product, $attributes );
	}

	protected function has_separate_gallery_thumbnails() {
		if ( ! class_exists( '\Glozin\Helper' ) ) {
			return true;
		}

		$gallery_layout = apply_filters( 'glozin_gallery_layout', \Glozin\Helper::get_option( 'product_gallery_layout' ) );

		return ! in_array( $gallery_layout, array( 'grid-1', 'grid-2', 'stacked', 'hidden-thumbnails' ), true );
	}

	public function get_variation_images() {
		check_ajax_referer( '_glozin_nonce', 'nonce' );

		$product_id = '';
		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$product_id = $_POST['variation_id'];
		} elseif ( isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ) {
			$product_id = $_POST['product_id'];
		}

		if ( $product_id ) {
			$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
			setup_postdata( $GLOBALS['post'] );
			ob_start();
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			add_action( 'woocommerce_product_thumbnails', array( $this, 'show_product_images' ), 20 );

			if ( $this->has_separate_gallery_thumbnails() ) {
				add_action( 'woocommerce_product_thumbnails', array( $this, 'show_product_thumbnails' ), 30 );
			}

			woocommerce_show_product_images();
			wp_reset_postdata();
			wp_send_json_success( ob_get_clean() );
			die();
		}

		wp_send_json_error();
		die();
	}

	public function show_product_images() {
		$thumbnail_ids  = $this->get_attachment_image_ids();
		$image_id       = $thumbnail_ids['image_id'];
		$attachment_ids = $thumbnail_ids['attachment_ids'];

		if ( $attachment_ids && $image_id ) {
			foreach ( $attachment_ids as $attachment_id ) {
				if ( empty( $attachment_id ) ) {
					continue;
				}

				echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
		}
	}

	public function show_product_thumbnails() {
		$thumbnail_ids = $this->get_attachment_image_ids();
		$this->get_product_gallery_thumbnails( $thumbnail_ids['image_id'], $thumbnail_ids['attachment_ids'] );
	}

	public function get_attachment_image_ids() {
		$image_id = $attachment_ids = '';
		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id     = absint( $_POST['variation_id'] );
			$variation_images = get_post_meta( $variation_id, 'glozin_variation_images', true );
			$attachment_ids   = $variation_images ? explode( ',', $variation_images ) : '';
			$variation        = wc_get_product( $variation_id );
			$image_id         = $variation ? $variation->get_image_id() : '';
		}

		if ( empty( $attachment_ids ) && isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ) {
			$product_id     = absint( $_POST['product_id'] );
			$product        = wc_get_product( $product_id );
			$attachment_ids = $product ? $product->get_gallery_image_ids() : '';
			$image_id       = $product && empty( $image_id ) ? $product->get_image_id() : $image_id;
		}

		return array(
			'image_id'       => $image_id,
			'attachment_ids' => $attachment_ids,
		);
	}

	/**
	 * Product gallery thumbnails
	 *
	 * @return void
	 */
	public function get_product_gallery_thumbnails( $image_id, $attachment_ids ) {
		if ( $attachment_ids && $image_id ) {
			add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false' );

			echo '<div class="glozin-product-gallery-thumbnails">';
			echo apply_filters( 'glozin_product_get_gallery_image', wc_get_gallery_image_html( $image_id ), 1 );
			$index = 2;
			foreach ( $attachment_ids as $attachment_id ) {
				if ( empty( $attachment_id ) ) {
					continue;
				}

				echo apply_filters( 'glozin_product_get_gallery_thumbnail', wc_get_gallery_image_html( $attachment_id ), $index );
				$index++;
			}
			echo '</div>';

			remove_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false' );
		}
	}

	/**
	 * Get variation id default
	 *
	 * @return int
	 */
	public function get_variation_id_default( $product = null ) {
		$variation_id = 0;
		$product      = $product ? $product : $this->get_current_product();

		if ( empty( $product ) || 'variable' !== $product->get_type() ) {
			return $variation_id;
		}

		$default_variation = $product->get_default_attributes();
		if ( empty( $default_variation ) ) {
			return $variation_id;
		}

		$match_attributes = array();
		foreach ( $default_variation as $key => $value ) {
			if ( '' === $value ) {
				return $variation_id;
			}

			$match_attributes[ 'attribute_' . sanitize_title( $key ) ] = $value;
		}

		return $this->get_matching_variation_id( $product, $match_attributes );
	}
}
