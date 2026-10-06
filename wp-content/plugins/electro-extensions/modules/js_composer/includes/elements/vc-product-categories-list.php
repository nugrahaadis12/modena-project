<?php

if ( ! function_exists( 'electro_product_categories_list_element' ) ) :

	function electro_product_categories_list_element( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'columns'         => 4,
			'limit'           => 8,
			'has_no_products' => false,
			'orderby'         => 'name',
			'order'           => 'ASC',
			'include'         => '',
			'slugs'           => '',
			'el_class'        => ''
		), $atts, 'electro_product_categories_list' );

		
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
			'columns'       => intval( $atts['columns'] ),
			'category_args' => $cat_args,
			'section_class' => sanitize_html_class( $atts['el_class'] ),
		];

		$html = '';
		if ( function_exists( 'electro_product_categories_list' ) ) {
			ob_start();
			electro_product_categories_list( $args );
			$html = ob_get_clean();
		}

		return $html;
	}

	add_shortcode( 'electro_product_categories_list', 'electro_product_categories_list_element' );

endif;
