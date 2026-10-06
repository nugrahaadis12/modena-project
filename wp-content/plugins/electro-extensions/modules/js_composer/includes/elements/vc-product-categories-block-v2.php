<?php

if ( ! function_exists( 'electro_vc_product_categories_block_v2_element' ) ) :

	function electro_vc_product_categories_block_v2_element( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'columns'			=> '5',
			'limit'				=> '5',
			'has_no_products'	=> false,
			'orderby' 			=> 'menu_order',
			'order' 			=> 'ASC',
			'include'			=> '',
			'slugs'				=> '',
			'el_class'			=> ''
		), $atts ) );

		$cat_args = array(
			'columns'    => intval( $columns ),
			'number'     => intval( $limit ),
			'hide_empty' => empty( $has_no_products ) ? true : false,
			'orderby'    => sanitize_text_field( $orderby ),
			'order'      => sanitize_text_field( $order ),
		);

		if ( ! empty( $slugs ) ) {
			$slugs = array_map( 'sanitize_title', array_map( 'trim', explode( ',', $slugs ) ) );
			if ( ! empty( $slugs ) ) {
				$cat_args['slug'] = $slugs;
			}

		} elseif ( ! empty( $include ) ) {
			$include_ids = array_map( 'intval', array_map( 'trim', explode( ',', $include ) ) );
			if ( ! empty( $include_ids ) ) {
				$cat_args['include'] = $include_ids;
				$cat_args['orderby'] = 'include';
			}
		}

		$args = apply_filters( 'electro_vc_product_categories_block_v2_element_args', array(
			'is_enabled' => true,
			'animation'  => false,
			'el_class'   => sanitize_html_class( $el_class ),
			'categories' => $cat_args
		));

		$html = '';
		if ( function_exists( 'electro_home_v12_categories_block' ) ) {
			ob_start();
			electro_home_v12_categories_block( $args );
			$html = ob_get_clean();
		}

		return $html;
	}

	add_shortcode( 'electro_product_categories_block_v2', 'electro_vc_product_categories_block_v2_element' );

endif;
