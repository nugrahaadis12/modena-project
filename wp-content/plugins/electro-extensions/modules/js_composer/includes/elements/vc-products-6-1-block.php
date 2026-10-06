<?php
if ( ! function_exists( 'electro_vc_products_6_1_block' ) ) :

function electro_vc_products_6_1_block( $atts, $content = null ) {

    extract( shortcode_atts( array(
        'title'                 => '',
        'shortcode_tag'         => 'recent_products',
        'orderby'               => 'date',
        'order'                 => 'DESC',
        'products_choice'       => 'ids',
        'product_id'            => '',
        'category'              => '',
        'cat_operator'          => 'IN',
        'attribute'             => '',
        'terms'                 => '',
        'terms_operator'        => 'IN',
        'cat_limit'             => '',
        'cat_has_no_products'   => '',
        'cat_orderby'           => '',
        'cat_order'             => '',
        'cat_include'           => '',
        'cat_slugs'             => '',
    ), $atts ) );

    $category_args = array(
        'number'     => $cat_limit,
        'hide_empty' => empty( $cat_has_no_products ) ? true : false,
        'orderby'    => $cat_orderby,
        'order'      => $cat_order,
    );

    if ( ! empty( $cat_include ) ) {
        $cat_include = array_map( 'intval', array_map( 'trim', explode( ',', $cat_include ) ) );
        if ( ! empty( $cat_include ) ) {
            $category_args['include'] = $cat_include;
            $category_args['orderby'] = 'include';
        }
    }

    if ( ! empty( $cat_slugs ) ) {
        $category_args['slug'] = array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', $cat_slugs ) )
        );
    }

    $per_page = electro_is_wide_enabled() ? 9 : 7;

    $shortcode_atts = function_exists( 'electro_get_atts_for_shortcode' ) ? electro_get_atts_for_shortcode( array(
        'shortcode'             => $shortcode_tag,
        'product_category_slug' => $category,
        'cat_operator'          => $cat_operator,
        'products_choice'       => $products_choice,
        'products_ids_skus'     => $product_id,
        'attribute'             => $attribute,
        'terms'                 => $terms,
        'terms_operator'        => $terms_operator,
    ) ) : array();

    $shortcode_atts = wp_parse_args( $shortcode_atts, array(
        'order'    => $order,
        'orderby'  => $orderby,
        'per_page' => $per_page,
    ) );

    if ( class_exists( 'Electro_Products' ) && method_exists( 'Electro_Products', $shortcode_tag ) ) {
        $products_html = Electro_Products::$shortcode_tag( $shortcode_atts );
    } else {
        $products_html = '';
    }

    $args = array(
        'section_title' => $title,
        'category_args' => $category_args,
        'products'      => $products_html,
    );

    $html = '';
    if ( function_exists( 'electro_products_6_1_block' ) ) {
        ob_start();
        electro_products_6_1_block( $args );
        $html = ob_get_clean();
    }

    return $html;
}

add_shortcode( 'electro_vc_products_6_1', 'electro_vc_products_6_1_block' );

endif;
