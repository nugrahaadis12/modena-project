<?php
/**
 * Posts hooks.
 *
 * @package Glozin
 */

namespace Glozin\Addons\Modules\Advanced_Search\Ajax_Search;

use Glozin\Addons\Modules\Advanced_Search\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Posts
 */
class Posts {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * posts
	 *
	 * @var $instance
	 */
	protected static $posts = null;


	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
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

	}

	public function get_products_base() {
		if( isset(self::$posts) ) {
			return self::$posts;
		}
		global $wpdb;

		$products_ids = array();
		$output = array();
		$keyword   = trim( $_POST['term'] );

		$query_var = $this->get_query_var();
		$query = $wpdb->get_results($query_var);
		self::$posts = array();

		$use_wpml = defined( 'ICL_SITEPRESS_VERSION' );
		$current_lang = null;
		if ( $use_wpml ) {
			$current_lang = apply_filters( 'wpml_current_language', null );
			if ( empty( $current_lang ) || $current_lang === 'all' ) {
				$current_lang = apply_filters( 'wpml_default_language', null );
			}
		}

		foreach ( $query as $post ) {
			if( in_array($post->post_type, array( 'product' ) ) ) {
				if ( $use_wpml ) {
					$post_lang_details = apply_filters( 'wpml_post_language_details', null, $post->ID );
					if ( ! is_array( $post_lang_details ) || empty( $post_lang_details['language_code'] ) ) {
						continue;
					}
					$post_lang = $post_lang_details['language_code'];

					if ( $post_lang !== $current_lang ) {
						continue;
					}
				}

				$products_ids[] = $post->ID;
			}
		}

		ob_start();
			wc_setup_loop(
				array(
					'columns' => 5,
					'name' => 'ajax_search_popular_products',
				)
			);
			Helper::get_template_loop( $products_ids );
		$output['products'] = ob_get_clean();

		$output['view_all'] = sprintf(
			'<div class="results-content-view-all-results w-100 text-center mt-30"><a href="%s" class="gz-button gz-button-hover-effect">%s</a></div>',
			esc_url( home_url( '/' ) . '?s=' . $keyword . '&post_type=product' ),
			esc_html__('View all Results', 'glozin-addons'),
		);

		return $output;
	}

	public function get_products() {
		$post_result = $this->get_search();
		if( empty($post_result) ) {
			return;
		}

		if( ! isset( $post_result['product'] ) || empty( $post_result['product'] ) ) {
			return;
		}
		$keyword   = trim( $_POST['term'] );
		$result = array();
		$result['classes'] = 'results-products';
		$result['name'] = esc_html__('Products', 'glozin-addons');
		$result['view_all'] = sprintf(
			'<a href="%s" class="gz-button">%s</a>',
			esc_url( home_url( '/' ) . '?s=' . $keyword . '&post_type=product' ),
			esc_html__('View all Results', 'glozin-addons'),
		);

		$result['response'] = implode('', $post_result['product']);

		return Helper::get_result_list($result);
	}

	public function get_posts() {
		$post_result = $this->get_search();
		if( empty($post_result) ) {
			return;
		}

		if( ! isset( $post_result['post'] ) || empty( $post_result['post'] ) ) {
			return;
		}

		$result = array();
		$result['classes'] = 'results-posts';
		$result['name'] = esc_html__('Articles', 'glozin-addons');
		$result['view_all'] = '';
		$result['response'] = implode('', $post_result['post']);

		return Helper::get_result_list($result);
	}

	public function get_pages() {
		$post_result = $this->get_search();
		if( empty($post_result) ) {
			return;
		}

		if( ! isset( $post_result['page'] ) || empty( $post_result['page'] ) ) {
			return;
		}

		$result = array();
		$result['classes'] = 'results-pages';
		$result['name'] = esc_html__('Pages', 'glozin-addons');
		$result['view_all'] = '';
		$result['response'] = implode('', $post_result['page']);

		return Helper::get_result_list($result);
	}

	private function get_query_var() {
		global $wpdb;

		$result_number = isset( $_POST['ajax_search_number'] ) ? intval( $_POST['ajax_search_number'] ) : 0;
		$keyword   = trim( $_POST['term'] );

		$query_var = '';
		$fields = 'posts.ID, posts.post_type';
		$search_string = '%' . $keyword . '%';

		// Multi-keyword search: split the term into individual words and require
		// every word to match (in any order). Falls back to the whole-phrase
		// match when the user typed a single word.
		$terms         = preg_split( '/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY );
		$multi_keyword = is_array( $terms ) && count( $terms ) > 1;

		// Title-only matcher reused by the post/page UNION queries.
		if ( $multi_keyword ) {
			$title_conditions = array();
			foreach ( $terms as $term ) {
				$title_conditions[] = $wpdb->prepare( "posts.post_title LIKE %s", '%' . $term . '%' );
			}
			$post_title_match = '(' . implode( ' AND ', $title_conditions ) . ')';
		} else {
			$post_title_match = $wpdb->prepare( "(posts.post_title LIKE %s)", $search_string );
		}

		if( get_option('glozin_ajax_search_products', 'yes') == 'yes' ) {
			$sku_join = $sku_where = $product_by = $post_status = $visibility_query = '';
			$sku_join = " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON posts.ID = wc_product_meta_lookup.product_id ";

			// Get visibility term IDs to exclude hidden products
			$product_visibility_terms = wc_get_product_visibility_term_ids();
			$excluded_terms = array();

			if ( isset( $product_visibility_terms['exclude-from-search'] ) ) {
				$excluded_terms[] = $product_visibility_terms['exclude-from-search'];
			}

			if ( ! empty( $excluded_terms ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $excluded_terms ), '%d' ) );
				$visibility_join = "
					LEFT JOIN {$wpdb->term_relationships} visibility_tr
					ON posts.ID = visibility_tr.object_id
					AND visibility_tr.term_taxonomy_id IN ($placeholders)
				";
				$visibility_query = " AND visibility_tr.object_id IS NULL ";
			}

			$title_enabled   = get_option('glozin_ajax_search_products_by_title', 'yes') === 'yes';
			$content_enabled = get_option('glozin_ajax_search_products_by_content', 'yes') === 'yes';
			$sku_enabled     = get_option('glozin_ajax_search_products_by_sku', 'yes') === 'yes';

			if ( $multi_keyword ) {
				// One OR-group per word across the enabled fields (including SKU),
				// all groups combined with AND so every word must match somewhere.
				$keyword_groups = array();

				foreach ( $terms as $term ) {
					$like       = '%' . $term . '%';
					$fields_or  = array();

					if ( $title_enabled ) {
						$fields_or[] = $wpdb->prepare( "posts.post_title LIKE %s", $like );
					}
					if ( $content_enabled ) {
						$fields_or[] = $wpdb->prepare( "posts.post_content LIKE %s", $like );
					}
					if ( $sku_enabled ) {
						$fields_or[] = $wpdb->prepare( "wc_product_meta_lookup.sku LIKE %s", $like );
					}

					if ( ! empty( $fields_or ) ) {
						$keyword_groups[] = '(' . implode( ' OR ', $fields_or ) . ')';
					}
				}

				$product_by = ! empty( $keyword_groups ) ? ' AND (' . implode( ' AND ', $keyword_groups ) . ')' : '';
				// SKU is already folded into each keyword group above.
				$sku_where  = '';
			} else {
				if( $sku_enabled ) {
					$sku_where =  ' OR ' . $wpdb->prepare("(wc_product_meta_lookup.sku LIKE %s) ", $search_string);
				}

				if( $title_enabled ) {
					$product_by =  $wpdb->prepare("(posts.post_title LIKE %s) ", $search_string);
				}

				if( $content_enabled ) {
					$product_by .= !empty( $product_by ) ? ' OR' : '';
					$product_by .=  $wpdb->prepare("(posts.post_content LIKE %s) ", $search_string);
				}

				$product_by = !empty( $product_by ) ? ' AND (' . $product_by . ')' : '';
			}

			$post_status = is_user_logged_in() && current_user_can('read_private_posts') ? " OR posts.post_status = 'private'" : "";

			$query_var = "(SELECT {$fields} FROM {$wpdb->posts} as posts"
				. $sku_join 
				. $visibility_join .
				"WHERE 1 = 1
				{$product_by}
				AND(
					posts.post_type = 'product'
					AND	( posts.post_status = 'publish' {$post_status} )
				)"
				. $visibility_query
				. $sku_where .
				"ORDER BY posts.post_date DESC
				LIMIT %d
				)";

			if ( ! empty( $excluded_terms ) ) {
				$prepare_args = array_merge( $excluded_terms, array( $result_number ) );
				$query_var = $wpdb->prepare(
					$query_var,
					...$prepare_args
				);
			} else {
				$query_var = $wpdb->prepare(
					$query_var,
					$result_number
				);
			}
		}

		if( get_option('glozin_ajax_search_posts', 'yes') == 'yes' ) {
			$query_var .= $wpdb->prepare("
				UNION ALL (SELECT {$fields} FROM {$wpdb->posts} as posts
				WHERE 1 = 1
					AND {$post_title_match}
					AND(
						posts.post_type = 'post'
						AND ( posts.post_status = 'publish' {$post_status} )
					)
				ORDER BY posts.post_date DESC
				LIMIT %d
				)",
				$result_number
			);
		}

		if( get_option('glozin_ajax_search_pages', 'yes') == 'yes' ) {
			$query_var .= $wpdb->prepare("
				UNION ALL (SELECT {$fields} FROM {$wpdb->posts} as posts
				WHERE 1 = 1
					AND {$post_title_match}
					AND(
						posts.post_type = 'page'
						AND ( posts.post_status = 'publish' {$post_status} )
					)
				ORDER BY posts.post_date DESC
				LIMIT %d
				)",
				$result_number
			);
		}

		return $query_var;
	}

	private function get_search() {
		if( ! empty(self::$posts) ) {
			return self::$posts;
		}
		global $wpdb;

		$query_var = $this->get_query_var();
		$query = $wpdb->get_results($query_var);
		self::$posts = array();

		$use_wpml = defined( 'ICL_SITEPRESS_VERSION' );
		$current_lang = null;
		if ( $use_wpml ) {
			$current_lang = apply_filters( 'wpml_current_language', null );
			if ( empty( $current_lang ) || $current_lang === 'all' ) {
				$current_lang = apply_filters( 'wpml_default_language', null );
			}
		}

		foreach ( $query as $post ) {
			$result = array();
			$post_type = $post->post_type;
			$post_id = $post->ID;

			if ( $use_wpml ) {
				$post_lang_details = apply_filters( 'wpml_post_language_details', null, $post_id );
				if ( ! is_array( $post_lang_details ) || empty( $post_lang_details['language_code'] ) ) {
					continue;
				}
				$post_lang = $post_lang_details['language_code'];

				if ( $post_lang !== $current_lang ) {
					continue;
				}
			}

			if( in_array($post_type, array( 'product' ) ) ) {
				$product   = wc_get_product( $post_id );
				$result['permalink'] = $product->get_permalink();
				$result['image'] = $product->get_image( 'woocommerce_thumbnail' );
				$result['name'] = $product->get_title();
				$result['desc'] = '<span class="price d-flex">' . $product->get_price_html() . '</span>';
				self::$posts['product'][]= Helper::get_result_item($result);
			} elseif( $post_type == 'post' ) {
				$post = get_post( $post_id );
				$result['permalink'] = get_permalink($post_id);
				$result['image'] = wp_get_attachment_image( get_post_thumbnail_id( $post_id ), 'large');
				$result['name'] = $post->post_title;
				$result['desc'] = '<span class="post-date">' . get_the_date( '', $post_id ) . '</span>';
				self::$posts['post'][]= Helper::get_result_item($result);
			} elseif( $post_type == 'page' ) {
				$post = get_post( $post_id );
				$result['permalink'] = get_permalink($post_id);
				$result['image'] = '';
				$result['name'] = $post->post_title;
				$result['desc'] = '';
				self::$posts['page'][]= Helper::get_result_item($result);
			}
		}

		return self::$posts;
	}

}
