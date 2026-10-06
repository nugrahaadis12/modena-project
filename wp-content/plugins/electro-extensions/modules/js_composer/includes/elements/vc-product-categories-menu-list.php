<?php

if ( ! function_exists( 'electro_product_categories_menu_list_elements' ) ) :

function electro_product_categories_menu_list_elements( $atts, $content = null ) {

    $atts = shortcode_atts( array(
        'section_title'   => '',
        'list_categories' => '',
        'action_text'     => '',
        'action_link'     => '',
        'el_class'        => '',
    ), $atts );

    $list_categories_args = array();

    $list_categories = is_array( $atts['list_categories'] )
        ? $atts['list_categories']
        : json_decode( urldecode( $atts['list_categories'] ), true );

    if ( is_array( $list_categories ) ) {

        foreach ( $list_categories as $list_category ) {

            $list_category = shortcode_atts( array(
                'title'           => '',
                'limit'           => '',
                'has_no_products' => '',
                'orderby'         => 'name',
                'order'           => 'ASC',
                'slugs'           => '',
            ), $list_category );

            $cat_args = array(
                'number'     => $list_category['limit'],
                'hide_empty' => empty( $list_category['has_no_products'] ),
                'orderby'    => $list_category['orderby'],
                'order'      => $list_category['order'],
            );

            if ( ! empty( $list_category['slugs'] ) ) {
                $cat_args['slug'] = array_map(
                    'sanitize_title',
                    array_map( 'trim', explode( ',', $list_category['slugs'] ) )
                );
            }

            $list_categories_args[] = array(
                'title'         => $list_category['title'],
                'category_args' => $cat_args,
            );
        }
    }

    $args = array(
        'section_class' => $atts['el_class'],
        'section_title' => $atts['section_title'],
        'category_list' => $list_categories_args,
        'action_text'   => $atts['action_text'],
        'action_link'   => $atts['action_link'],
    );

    if ( function_exists( 'electro_product_categories_menu_list' ) ) {
        ob_start();
        electro_product_categories_menu_list( $args );
        return ob_get_clean();
    }

    return '';
}

add_shortcode(
    'electro_product_categories_menu_list',
    'electro_product_categories_menu_list_elements'
);

endif;
