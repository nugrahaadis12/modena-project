<?php
if ( ! function_exists( 'electro_vc_products_6_1_with_categories_block' ) ) :

function electro_vc_products_6_1_with_categories_block( $atts, $content = null ) {

    $atts = shortcode_atts( array(
        'title'                     => '',
        'shortcode_tag'             => 'recent_products',
        'orderby'                   => 'date',
        'order'                     => 'DESC',
        'products_choice'           => 'ids',
        'product_id'                => '',
        'category'                  => '',
        'cat_operator'              => 'IN',
        'attribute'                 => '',
        'terms'                     => '',
        'terms_operator'            => 'IN',
        'featured_shortcode_tag'    => 'recent_products',
        'featured_products_choice'  => 'ids',
        'featured_product_id'       => '',
        'categories_title'          => '',
        'enable_categories'         => '',
        'cat_limit'                 => '',
        'cat_has_no_products'       => '',
        'cat_orderby'               => 'name',
        'cat_order'                 => 'ASC',
        'cat_slugs'                 => '',
        'vcat_limit'                => '',
        'vcat_has_no_products'      => '',
        'vcat_orderby'              => 'name',
        'vcat_order'                => 'ASC',
        'vcat_slugs'                => '',
    ), $atts );

    if ( electro_is_wide_enabled() ) {
        $per_page = 8;
        $columns  = 4;
    } else {
        $per_page = 6;
        $columns  = 3;
    }

    $shortcode_atts = function_exists( 'electro_get_atts_for_shortcode' )
        ? electro_get_atts_for_shortcode( array(
            'shortcode'              => $atts['shortcode_tag'],
            'product_category_slug' => $atts['category'],
            'cat_operator'          => $atts['cat_operator'],
            'products_choice'       => $atts['products_choice'],
            'products_ids_skus'     => $atts['product_id'],
            'attribute'             => $atts['attribute'],
            'terms'                 => $atts['terms'],
            'terms_operator'        => $atts['terms_operator'],
        ) )
        : array();

    $shortcode_atts = wp_parse_args( $shortcode_atts, array(
        'order'    => $atts['order'],
        'orderby'  => $atts['orderby'],
        'per_page' => $per_page,
        'columns'  => $columns,
    ) );

    $featured_shortcode_atts = function_exists( 'electro_get_atts_for_shortcode' )
        ? electro_get_atts_for_shortcode( array(
            'shortcode'          => $atts['featured_shortcode_tag'],
            'products_choice'   => $atts['featured_products_choice'],
            'products_ids_skus' => $atts['featured_product_id'],
        ) )
        : array();

    $featured_shortcode_atts = wp_parse_args( $featured_shortcode_atts, array(
        'per_page' => 1,
        'columns'  => 1,
    ) );

    $category_args = array(
        'number'     => $atts['cat_limit'],
        'hide_empty' => empty( $atts['cat_has_no_products'] ),
        'orderby'    => $atts['cat_orderby'],
        'order'      => $atts['cat_order'],
    );

    if ( ! empty( $atts['cat_slugs'] ) ) {
        $category_args['slug'] = array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', $atts['cat_slugs'] ) )
        );
    }

    $vcategory_args = array(
        'number'     => $atts['vcat_limit'],
        'hide_empty' => empty( $atts['vcat_has_no_products'] ),
        'orderby'    => $atts['vcat_orderby'],
        'order'      => $atts['vcat_order'],
    );

    if ( ! empty( $atts['vcat_slugs'] ) ) {
        $vcategory_args['slug'] = array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', $atts['vcat_slugs'] ) )
        );
    }

    $args = array(
        'section_class'           => '',
        'section_title'           => $atts['title'],
        'enable_categories'       => filter_var( $atts['enable_categories'], FILTER_VALIDATE_BOOLEAN ),
        'categories_title'        => $atts['categories_title'],
        'category_args'           => $category_args,
        'vcategory_args'          => $vcategory_args,
        'shortcode_tag'           => $atts['shortcode_tag'],
        'shortcode_atts'          => $shortcode_atts,
        'shortcode_tag_featured'  => $atts['featured_shortcode_tag'],
        'shortcode_atts_featured' => $featured_shortcode_atts,
    );

    if ( function_exists( 'electro_products_6_1_with_categories' ) ) {
        ob_start();
        electro_products_6_1_with_categories( $args );
        return ob_get_clean();
    }

    return '';
}

add_shortcode(
    'electro_vc_products_6_1_with_categories',
    'electro_vc_products_6_1_with_categories_block'
);

endif;
