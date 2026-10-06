<?php

if ( ! function_exists( 'electro_product_list_categories_element' ) ) :

	function electro_product_list_categories_element( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'title'				=> '',
			'limit'				=> 6,
			'has_no_products'	=> false,
			'orderby' 			=> 'name',
			'order' 			=> 'ASC',
			'include'			=> '',
			'slugs'				=> '',
		), $atts, 'electro_product_list_categories' );

		$cat_args = array(
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => empty( $atts['has_no_products'] ) ? true : false,
			'orderby'    => sanitize_text_field( $atts['orderby'] ),
			'order'      => sanitize_text_field( $atts['order'] ),
		);

		
		if ( ! empty( $atts['slugs'] ) ) {
			$slugs = array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['slugs'] ) ) );
			if ( ! empty( $slugs ) ) {
				$cat_args['slug'] = $slugs; 
			}

		} elseif ( ! empty( $atts['include'] ) ) {
			$include_ids = array_map( 'intval', array_map( 'trim', explode( ',', $atts['include'] ) ) );
			if ( ! empty( $include_ids ) ) {
				$cat_args['include'] = $include_ids; 
				$cat_args['orderby'] = 'include';
			}
		}

		$args = array(
			'section_title' => sanitize_text_field( $atts['title'] ),
			'category_args' => $cat_args,
		);

		$html = '';
		if ( function_exists( 'electro_home_list_categories' ) ) {
			ob_start();
			electro_home_list_categories( $args );
			$html = ob_get_clean();
		}

		return $html;
	}

	add_shortcode( 'electro_product_list_categories', 'electro_product_list_categories_element' );

endif;
