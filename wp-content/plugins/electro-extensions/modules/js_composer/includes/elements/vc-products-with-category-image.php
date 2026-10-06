<?php
if ( ! function_exists( 'electro_vc_products_with_category_image_block' ) ) :

function electro_vc_products_with_category_image_block( $atts, $content = null ) {

    extract( shortcode_atts( array(
        'title'                 => '',
        'shortcode_tag'         => 'recent_products',
        'orderby'               => 'date',
        'order'                 => 'DESC',
        'per_page'              => 6,
        'columns'               => 3,
        'products_choice'       => 'ids',
        'product_id'            => '',
        'category'              => '',
        'cat_operator'          => 'IN',
        'attribute'             => '',
        'terms'                 => '',
        'terms_operator'        => 'IN',
        'categories_title'      => '',
        'enable_categories'     => '',
        'cat_limit'             => '',
        'cat_has_no_products'   => '',
        'cat_orderby'           => '',
        'cat_order'             => '',
        'cat_slugs'             => '',
        'columns_wide'          => '',
        'vcat_limit'            => '',
        'vcat_has_no_products'  => '',
        'vcat_orderby'          => '',
        'vcat_order'            => '',
        'vcat_slugs'            => '',
        'image'                 => '',
        'img_action_link'       => '#'
    ), $atts ) );

    $shortcode_atts = function_exists( 'electro_get_atts_for_shortcode' )
        ? electro_get_atts_for_shortcode( array(
            'shortcode'              => $shortcode_tag,
            'product_category_slug' => $category,
            'cat_operator'          => $cat_operator,
            'products_choice'       => $products_choice,
            'products_ids_skus'     => $product_id,
            'attribute'             => $attribute,
            'terms'                 => $terms,
            'terms_operator'        => $terms_operator,
        ) )
        : array();

    $shortcode_atts = wp_parse_args( $shortcode_atts, array(
        'order'    => $order,
        'orderby'  => $orderby,
        'per_page' => $per_page,
        'columns'  => $columns,
    ) );

    $category_args = array(
        'number'     => $cat_limit,
        'hide_empty' => empty( $cat_has_no_products ),
        'orderby'    => $cat_orderby,
        'order'      => $cat_order,
    );

    if ( ! empty( $cat_slugs ) ) {
        $category_args['slug'] = array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', $cat_slugs ) )
        );
    }

    $vcategory_args = array(
        'number'     => $vcat_limit,
        'hide_empty' => empty( $vcat_has_no_products ),
        'orderby'    => $vcat_orderby,
        'order'      => $vcat_order,
    );

    if ( ! empty( $vcat_slugs ) ) {
        $vcategory_args['slug'] = array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', $vcat_slugs ) )
        );
    }

    $args = array(
        'section_class'     => '',
        'section_title'     => $title,
        'enable_categories' => filter_var( $enable_categories, FILTER_VALIDATE_BOOLEAN ),
        'categories_title'  => $categories_title,
        'category_args'     => $category_args,
        'vcategory_args'    => $vcategory_args,
        'shortcode_tag'     => $shortcode_tag,
        'shortcode_atts'    => $shortcode_atts,
        'image'             => ( intval( $image ) )
            ? wp_get_attachment_image_src( $image, 'full' )
            : '',
        'img_action_link'   => $img_action_link,
        'columns_wide'      => $columns_wide,
    );

    $html = '';
    if ( function_exists( 'electro_products_with_category_image' ) ) {
        ob_start();
        electro_products_with_category_image( $args );
        $html = ob_get_clean();
    }

    return $html;
}

add_shortcode(
    'electro_vc_products_with_category_image',
    'electro_vc_products_with_category_image_block'
);

endif;