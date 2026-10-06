(function ($) {
    'use strict';
    var glozin = glozin || {};

    glozin.found_data = parseInt(glozinVariationImages.variation_id_rendered || 0, 10) !== 0;
    glozin.variation_id = parseInt(glozinVariationImages.variation_id_rendered || 0, 10);
    glozin.requesting_variation_id = null;

    glozin.foundVariationImages = function( ) {
        $( 'div.product .entry-summary .variations_form:not(.form-cart-pbt)' ).on('found_variation', function(e, $variation){
            if( ! $variation || ! $variation.variation_id ) {
                return;
            }

            if( glozin.variation_id === $variation.variation_id || glozin.requesting_variation_id === $variation.variation_id ) {
                return;
            }

            glozin.changeVariationImagesAjax($variation.variation_id, $(this).data('product_id'));
        });
    }

    glozin.resetVariationImages = function( ) {
        $( 'div.product .entry-summary .variations_form:not(.form-cart-pbt)' ).on('reset_data', function(e){
            if( glozin.found_data || glozin.variation_id ) {
                glozin.changeVariationImagesAjax(0, $(this).data('product_id'));
            }

        });
    }

    glozin.initVariationImages = function() {
        $( 'div.product .entry-summary .variations_form:not(.form-cart-pbt)' ).each(function() {
            var $form = $(this),
                defaultVariationId = parseInt(glozinVariationImages.variation_id_default || 0, 10);

            if( ! defaultVariationId ) {
                return;
            }

            setTimeout(function() {
                var variationData = $form.data('product_variations') || [];

                if( glozin.variation_id === defaultVariationId || glozin.requesting_variation_id ) {
                    return;
                }

                $.each(variationData, function(index, variation) {
                    if( variation.variation_id === defaultVariationId ) {
                        glozin.changeVariationImagesAjax(defaultVariationId, $form.data('product_id'));
                        return false;
                    }
                });
            }, 0);
        });
    }

    glozin.changeVariationImagesAjax = function(variation_id, product_id) {
        var $productGallery = $('.woocommerce-product-gallery'),
            galleryHeight = $productGallery.height();
            $productGallery.addClass('loading').css( {'overflow': 'hidden' });
            if( ! $productGallery.closest('.single-product').hasClass('quick-view-modal') ) {
                $productGallery.css( {'height': galleryHeight });
            }

        var data = {
            'variation_id': variation_id,
            'product_id': product_id,
            nonce: glozinData.nonce,
        },
        ajax_url = glozinData.ajax_url.toString().replace('%%endpoint%%', 'glozin_get_variation_images');

        glozin.requesting_variation_id = variation_id;

        $.post(
            ajax_url,
            data,
            function (response) {
                var $gallery = $(response.data);

                $productGallery.html( $gallery.html() );
                glozin.found_data = variation_id !== 0;
                glozin.variation_id = variation_id;

                $productGallery.imagesLoaded(function () {
                    setTimeout(function() {
                        $productGallery.removeClass('loading').removeAttr( 'style' ).css('opacity', '1');
                    }, 200);

                } );

                $productGallery.trigger('product_thumbnails_slider_vertical');
                $productGallery.trigger('product_thumbnails_slider_horizontal');
                $('body').trigger('glozin_product_gallery_zoom');
                $('body').trigger('glozin_product_gallery_lightbox');

            }
        ).always(function() {
            glozin.requesting_variation_id = null;
        });
    }
    /**
     * Document ready
     */
    $(function () {
        if( typeof glozinVariationImages == 'undefined' ) {
            return;
        }

        if( $('.single-product').find('div.product' ).hasClass('product-has-variation-images') ) {
            glozin.foundVariationImages();
            glozin.resetVariationImages();
            glozin.initVariationImages();
        }
    });

})(jQuery);