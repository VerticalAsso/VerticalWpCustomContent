<?php
/* ADD custom theme functions here  */

/*  Registrer menus. */
	register_nav_menus( array(
		'secondary' => __( 'Secondary Menu', 'flatsome' ),
	) );

	add_action( 'after_setup_theme', 'flatsome_setup' );

// We might use this approach if a standard PHP date format doesn't
// quite match what we want. Example: "Sept" for September (neither
// "F" or "M"  formats get us there).

// Change the mini cal title
add_filter('tribe_events_the_mini_calendar_title', 'change_minical_title_month_format');

// Look for "Sep" and change it to "Sept"
function change_minical_title_month_format($title) {
	return str_replace('Septembre', 'Sep', $title);
}

// add_filter('wp_nav_menu_items', 'add_edit_post_link', 10, 2);
// function add_edit_post_link($items, $args) {
	// if ( is_user_logged_in() && is_singular() && in_category( 'galerie-photos' )) {
		// if( $args->theme_location == 'top_bar_nav' ) {
			// $editPostLink .= '<li id="edit_post_link" class="nav-top-link"><a href="' . get_edit_post_link() . '">Modifier la galerie</a></li>';
		// }
	// }
    // return $editPostLink . $items;
// }


remove_action("wp_head", "wp_generator");

add_filter('login_errors', function ($a) {
    return null;
});

add_filter("um_email_template_body_attrs", function( $css_atts ){
	return 'style="background: #fff;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;"';
 });