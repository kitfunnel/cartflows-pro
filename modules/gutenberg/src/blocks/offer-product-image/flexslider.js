export const Flexslider = ( props )=>{

    var product_gallery = jQuery(document).find('.woocommerce-product-gallery');

    if ( product_gallery.length > 0 ) {
        var a = jQuery(document).find('.woocommerce-product-gallery__image');;

        if ( product_gallery.find('.woocommerce-product-gallery__image').length > 1 ) {

            var slider_obj = jQuery('.woocommerce-product-gallery').flexslider({
                animation: "slide",
                animationLoop: false,
                controlNav: "thumbnails",
                selector: ".slides .woocommerce-product-gallery__image",
                directionNav: false
            });
    
            jQuery( window ).on("load", function() {
                slider_obj.resize();
            });
    
            /* Variation change gallery */
            var variations_form = jQuery('.variations_form');
    
            if ( variations_form.length > 0 ) {
                
                /**
                 * Reset the slide position if the variation has a different image than the current one
                 */
                var maybe_trigger_slide_position_reset = function( variation ) {
                    var $form                = variations_form,
                        $product_gallery     = jQuery( '.woocommerce-product-gallery' ),
                        reset_slide_position = false,
                        new_image_id         = ( variation && variation.image_id ) ? variation.image_id : '';
    
                    if ( $form.attr( 'current-image' ) !== new_image_id ) {
                        reset_slide_position = true;
                    }
    
                    $form.attr( 'current-image', new_image_id );
    
                    if ( reset_slide_position ) {
                        $product_gallery.flexslider( 0 );
                    }
                };
    
                /**
                 * Sets product images for the chosen variation
                 */
                var variations_image_update = function( variation ) {
    
                    var $form             = variations_form,
                        $product_gallery  = jQuery( '.woocommerce-product-gallery' ),
                        $gallery_nav      = $product_gallery.find( '.flex-control-nav' ),
                        $gallery_img      = $gallery_nav.find( 'li:eq(0) img' ),
                        $product_img_wrap = $product_gallery.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' ).eq( 0 ),
                        $product_img      = $product_img_wrap.find( '.wp-post-image' ),
                        $product_link     = $product_img_wrap.find( 'a' ).eq( 0 );
    
                    if ( variation && variation.image && variation.image.src && variation.image.src.length > 1 ) {
                        // See if the gallery has an image with the same original src as the image we want to switch to.
                        var galleryHasImage = $gallery_nav.find( 'li img[data-o_src="' + variation.image.gallery_thumbnail_src + '"]' ).length > 0;
    
                        // If the gallery has the image, reset the images. We'll scroll to the correct one.
                        if ( galleryHasImage ) {
                            variations_image_reset();
                        }
    
                        // See if gallery has a matching image we can slide to.
                        var slideToImage = $gallery_nav.find( 'li img[src="' + variation.image.gallery_thumbnail_src + '"]' );
    
                        if ( slideToImage.length > 0 ) {
                            slideToImage.trigger( 'click' );
                            $form.attr( 'current-image', variation.image_id );
                            window.setTimeout( function() {
                                jQuery( window ).trigger( 'resize' );
                                $product_gallery.trigger( 'woocommerce_gallery_init_zoom' );
                            }, 20 );
                            return;
                        }
    
                        $product_img.wc_set_variation_attr( 'src', variation.image.src );
                        $product_img.wc_set_variation_attr( 'height', variation.image.src_h );
                        $product_img.wc_set_variation_attr( 'width', variation.image.src_w );
                        $product_img.wc_set_variation_attr( 'srcset', variation.image.srcset );
                        $product_img.wc_set_variation_attr( 'sizes', variation.image.sizes );
                        $product_img.wc_set_variation_attr( 'title', variation.image.title );
                        $product_img.wc_set_variation_attr( 'data-caption', variation.image.caption );
                        $product_img.wc_set_variation_attr( 'alt', variation.image.alt );
                        $product_img.wc_set_variation_attr( 'data-src', variation.image.full_src );
                        $product_img.wc_set_variation_attr( 'data-large_image', variation.image.full_src );
                        $product_img.wc_set_variation_attr( 'data-large_image_width', variation.image.full_src_w );
                        $product_img.wc_set_variation_attr( 'data-large_image_height', variation.image.full_src_h );
                        $product_img_wrap.wc_set_variation_attr( 'data-thumb', variation.image.src );
                        $gallery_img.wc_set_variation_attr( 'src', variation.image.gallery_thumbnail_src );
                        $product_link.wc_set_variation_attr( 'href', variation.image.full_src );
                    } else {
                        variations_image_reset();
                    }
    
                    window.setTimeout( function() {
                        jQuery( window ).trigger( 'resize' );
                        maybe_trigger_slide_position_reset( variation );
                    }, 20 );
                };
    
                /**
                    * Reset main image to defaults.
                    */
                var variations_image_reset = function() {
                    var $form             = variations_form,
                        $product_gallery  = jQuery( '.woocommerce-product-gallery' ),
                        $gallery_nav      = $product_gallery.find( '.flex-control-nav' ),
                        $gallery_img      = $gallery_nav.find( 'li:eq(0) img' ),
                        $product_img_wrap = $product_gallery.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' ).eq( 0 ),
                        $product_img      = $product_img_wrap.find( '.wp-post-image' ),
                        $product_link     = $product_img_wrap.find( 'a' ).eq( 0 );
    
                    $product_img.wc_reset_variation_attr( 'src' );
                    $product_img.wc_reset_variation_attr( 'width' );
                    $product_img.wc_reset_variation_attr( 'height' );
                    $product_img.wc_reset_variation_attr( 'srcset' );
                    $product_img.wc_reset_variation_attr( 'sizes' );
                    $product_img.wc_reset_variation_attr( 'title' );
                    $product_img.wc_reset_variation_attr( 'data-caption' );
                    $product_img.wc_reset_variation_attr( 'alt' );
                    $product_img.wc_reset_variation_attr( 'data-src' );
                    $product_img.wc_reset_variation_attr( 'data-large_image' );
                    $product_img.wc_reset_variation_attr( 'data-large_image_width' );
                    $product_img.wc_reset_variation_attr( 'data-large_image_height' );
                    $product_img_wrap.wc_reset_variation_attr( 'data-thumb' );
                    $gallery_img.wc_reset_variation_attr( 'src' );
                    $product_link.wc_reset_variation_attr( 'href' );
                };
    
                jQuery('.variations_form').on( 'found_variation', function( event, variation ){
                    
                    variations_image_update( variation );
                });
            }
        }
    }

}