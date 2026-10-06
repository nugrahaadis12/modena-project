<?php

if ( ! function_exists( 'electro_product_categories_list_with_header_element' ) ) :

	function electro_product_categories_list_with_header_element( $atts, $content = null ){

		extract(shortcode_atts(array(
			'title'				=> '',
			'sub_title'			=> '',
			'bg_image'			=> '',
			'enable_header'		=> true,
			'columns'			=> '',
			'limit'				=> '',
			'has_no_products'	=> false,
			'orderby' 			=> 'name',
			'order' 			=> 'ASC',
			'include'			=> '',
			'slugs'				=> '',
			'type'				=> '',
			'el_class'			=> ''
		), $atts));

		$cat_args = array(
			'number'			=> $limit,
			'hide_empty'		=> $has_no_products,
			'orderby' 			=> $orderby,
			'order' 			=> $order,
		);

		 if ( ! empty( $atts['slugs'] ) ) {
			$cat_args['slug'] = array_map(
				'sanitize_title',
				array_map( 'trim', explode( ',', $atts['slugs'] ) )
			);
		}

		$args = array(
			'section_title'			=> $title,
			'sub_title'				=> $sub_title,
			'bg_image'				=> $bg_image,
			'enable_header'			=> $enable_header,
			'type'					=> $type,
			'columns'				=> $columns,
			'category_args'			=> $cat_args,
			'section_class'			=> $el_class,
		);

		$html = '';
		if( function_exists( 'electro_product_categories_list_with_header' ) ) {
			ob_start();
			electro_product_categories_list_with_header( $args );
			$html = ob_get_clean();
		}

	    return $html;
	}

	add_shortcode( 'electro_product_categories_list_with_header' , 'electro_product_categories_list_with_header_element' );

endif;