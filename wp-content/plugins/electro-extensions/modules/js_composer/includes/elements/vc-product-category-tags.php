<?php

if ( ! function_exists( 'electro_home_product_category_tags_element' ) ) :

	function electro_home_product_category_tags_element( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'title'          => '',
			'limit'          => 8,
			'has_no_products'=> false,
			'orderby'        => 'name',
			'order'          => 'ASC',
			'include'        => '',
			'slugs'          => '',
			'el_class'       => '',
		), $atts, 'electro_product_category_tags' );

		
		$cat_args = [
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => empty( $atts['has_no_products'] ) ? true : false,
			'orderby'    => sanitize_text_field( $atts['orderby'] ),
			'order'      => sanitize_text_field( $atts['order'] ),
		];

		
		if ( ! empty( $atts['include'] ) ) {
			$include_ids = array_map( 'intval', array_map( 'trim', explode( ',', $atts['include'] ) ) );
			if ( ! empty( $include_ids ) ) {
				$cat_args['include'] = $include_ids;
				$cat_args['orderby'] = 'include';
			}

		
		} elseif ( ! empty( $atts['slugs'] ) ) {
			$slug_array = array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['slugs'] ) ) );
			if ( ! empty( $slug_array ) ) {
				$cat_args['slug'] = $slug_array;
				
			}
		}

		$args = [
			'section_title' => sanitize_text_field( $atts['title'] ),
			'category_args' => $cat_args,
			'section_class' => sanitize_html_class( $atts['el_class'] ),
		];

		$html = '';
		if ( function_exists( 'electro_home_product_category_tags' ) ) {
			ob_start();
			electro_home_product_category_tags( $args );
			$html = ob_get_clean();
		}

		return $html;
	}

	add_shortcode( 'electro_product_category_tags', 'electro_home_product_category_tags_element' );

endif;
