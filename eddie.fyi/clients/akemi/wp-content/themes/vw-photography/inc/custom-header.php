<?php
/**
 * @package VW Photography
 * Setup the WordPress core custom header feature.
 *
 * @uses vw_photography_header_style()
*/
function vw_photography_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'vw_photography_custom_header_args', array(
		'default-text-color'     => 'fff',
		'header-text' 			 =>	false,
		'width'                  => 1600,
		'height'                 => 400,
		'wp-head-callback'       => 'vw_photography_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'vw_photography_custom_header_setup' );

if ( ! function_exists( 'vw_photography_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see vw_photography_custom_header_setup().
 */
add_action( 'wp_enqueue_scripts', 'vw_photography_header_style' );
function vw_photography_header_style() {
	//Check if user has defined any header image.
	if ( get_header_image() ) :
	$custom_css = "
        #header{
			background-image:url('".esc_url(get_header_image())."');
			background-position: center top;
		}";
	   	wp_add_inline_style( 'vw-photography-basic-style', $custom_css );
	endif;
}
endif; // vw_photography_header_style