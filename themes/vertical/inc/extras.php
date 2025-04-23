<?php

/* CONTENT */

/* - Extra body classes
/* - Filter for next/previous image links
/* - Custom metaboxes for Product Categories
/* - Extra editor styles
/* - Shorter Exerpt
/* - Shortcode fixer
/* - Hex 2 Rgb
/* - Set default WooCommerce images
/* - WooCommerce extra tabs
/* - Enable SVG upload

/**
 * Adds custom classes to the array of body classes.
 */

function flatsome_body_classes( $classes ) {
	global $flatsome_opt;
	// add antialias to all texts
	$classes[] = 'antialiased';

	// Adds a class of group-blog to blogs with more than 1 published author
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// adds dark header class
	if($flatsome_opt['header_color'] == 'dark'){
		$classes[] = 'dark-header';
    $classes[] = 'org-dark-header';
  }

	// add stikcy header class
	if($flatsome_opt['header_sticky'] && !isset($_GET["shortcode"])){
		$classes[] = 'sticky_header';
	}

	if($flatsome_opt['breadcrumb_size']){
		$classes[] = $flatsome_opt['breadcrumb_size'];
	}

	// add logo-center class
	if($flatsome_opt['logo_position'] == 'center'){
		$classes[] = 'logo-center';
	}

	if($flatsome_opt['catalog_mode_prices']){
		$classes[] = 'no-prices';
	}

  if(function_exists('is_product')){
     if(is_product() && $flatsome_opt['product_zoom']){
        $classes[] = 'product-zoom';
      }
  }

  /* WooCommerce 2.3 fix */
  if(function_exists('wc_print_notices')){
      if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '>' ) ) {
         $classes[] = 'wc-new';
      }
  }

	// add boxed layout class if selected
	if($flatsome_opt['body_layout']){
		$classes[] = $flatsome_opt['body_layout'];
	}

	if($flatsome_opt['body_layout'] == "framed-layout"){
		$classes[] = "boxed";
	}

	// add background settings
	if($flatsome_opt['body_bg_image']){
		$classes[] = $flatsome_opt['body_bg_type'];
	}

  if ( is_page_template( 'page-transparent-header-light.php' ) || is_page_template( 'page-transparent-header.php' ) || is_page_template( 'page-boxed-header.php' )) {
    $classes[] = 'transparent-header';
  }

  if ( is_page_template( 'page-transparent-header-light.php' )) {
    $classes[] = 'has-dark-header';
    $classes[] = 'dark-header';
  }

   if ( is_page_template( 'page-blank-header.php' )) {
    $classes[] = 'hide-header';
  }

    if ( is_page_template( 'page-boxed-header.php' )) {
    $classes[] = 'boxed-header';
  }


	return $classes;
}
add_filter( 'body_class', 'flatsome_body_classes' );


/**
 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
 */
function flatsome_enhanced_image_navigation( $url, $id ) {
	if ( ! is_attachment() && ! wp_attachment_is_image( $id ) )
		return $url;

	$image = get_post( $id );
	if ( ! empty( $image->post_parent ) && $image->post_parent != $id )
		$url .= '#main';

	return $url;
}
add_filter( 'attachment_link', 'flatsome_enhanced_image_navigation', 10, 2 );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 */
function flatsome_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', 'flatsome' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'flatsome_wp_title', 10, 2 );




/* ADD CUSTOM META BOX TO CATEGORY PAGES */
if(function_exists('get_term_meta')){
function top_text_taxonomy_edit_meta_field($term) {
	// put the term ID into a variable
	$t_id = $term->term_id;
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_term_meta($t_id,'cat_meta');
	if(!$term_meta){$term_meta = add_term_meta($t_id, 'cat_meta', '');}
	 ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[cat_header]"><?php _e( 'Top Content', 'flatsome' ); ?></label></th>
		<td>
				<?php

				$content = esc_attr( $term_meta[0]['cat_header'] ) ? esc_attr( $term_meta[0]['cat_header'] ) : '';
				echo '<textarea id="term_meta[cat_header]" name="term_meta[cat_header]">'.$content.'</textarea>'; ?>
			<p class="description"><?php _e( 'Enter a value for this field. Shortcodes are allowed. This will be displayed at top of the category.','flatsome' ); ?></p>
		</td>
	</tr>
<?php
}
add_action( 'product_cat_edit_form_fields', 'top_text_taxonomy_edit_meta_field', 10, 2 );

/* ADD CUSTOM META BOX TO CATEGORY PAGES */
function bottom_text_taxonomy_edit_meta_field($term) {
  // put the term ID into a variable
  $t_id = $term->term_id;
  // retrieve the existing value(s) for this meta field. This returns an array
  $term_meta = get_term_meta($t_id,'cat_meta');
  if(!$term_meta){$term_meta = add_term_meta($t_id, 'cat_meta', '');}
   ?>
  <tr class="form-field">
  <th scope="row" valign="top"><label for="term_meta[cat_footer]"><?php _e( 'Bottom Content', 'flatsome' ); ?></label></th>
    <td>
        <?php

        $content = esc_attr( $term_meta[0]['cat_footer'] ) ? esc_attr( $term_meta[0]['cat_footer'] ) : '';
        echo '<textarea id="term_meta[cat_footer]" name="term_meta[cat_footer]">'.$content.'</textarea>'; ?>
      <p class="description"><?php _e( 'Enter a value for this field. Shortcodes are allowed. This will be displayed at bottom of the category.','flatsome' ); ?></p>
    </td>
  </tr>
<?php
}
add_action( 'product_cat_edit_form_fields', 'bottom_text_taxonomy_edit_meta_field', 10, 2 );


/* SAVE CUSTOM META*/
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_term_meta($t_id,'cat_meta');
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_term_meta($term_id, 'cat_meta', $term_meta);

	}
}
add_action( 'edited_product_cat', 'save_taxonomy_custom_meta', 10, 2 );
}





/* EXTRA EDITOR STYLES (add extra styles to the content editor box) */
add_filter( 'mce_buttons_2', 'my_mce_buttons_2' );
function my_mce_buttons_2( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}


add_filter( 'tiny_mce_before_init', 'ux_formats_before_init' );
function ux_formats_before_init( $settings ) {

    $style_formats = array(

        array(
              'title' => 'Link styles',
                  'items' => array(
                  array(
                      'title' => 'Button Primary',
                       'selector' => 'a',
                       'classes' => 'button primary',
                  ),
                    array(
                      'title' => 'Button White',
                       'selector' => 'a',
                       'classes' => 'button white',
                  ),
                  array(
                       'title' => 'Button Secondary',
                       'selector' => 'a',
                       'classes' => 'button secondary',

                  ),
                  array(
                       'title' => 'Button Alert',
                       'selector' => 'a',
                       'classes' => 'button alert',

                  ),
                  array(
                       'title' => 'Button Success',
                       'selector' => 'a',
                       'classes' => 'button success',

                  ),
                  array(
                       'title' => 'Button Alternative Primary',
                       'selector' => 'a',
                       'classes' => 'button alt-button',

                  ),
                   array(
                       'title' => 'Button Alternative White',
                       'selector' => 'a',
                       'classes' => 'button alt-button white',

                  ),
                        array(
                      'title' => 'Large - Button Primary',
                       'selector' => 'a',
                       'classes' => 'button large  primary',
                  ),
                  array(
                       'title' => 'Large Button Secondary',
                       'selector' => 'a',
                       'classes' => 'button large  secondary',

                  ),
                  array(
                       'title' => 'Large Button Alert',
                       'selector' => 'a',
                       'classes' => 'button large  alert',

                  ),
                  array(
                       'title' => 'Large Button Success',
                       'selector' => 'a',
                       'classes' => 'button large  success',

                  ),
                  array(
                       'title' => 'Large Button Alternative Primary',
                       'selector' => 'a',
                       'classes' => 'button large  alt-button success',

                  ),
                  array(
                       'title' => 'Large Button Alternative Secondary',
                       'selector' => 'a',
                       'classes' => 'button large  alt-button secondary',

                  ),
                   array(
                       'title' => 'Large Button Alternative White',
                       'selector' => 'a',
                       'classes' => 'button large alt-button white',

                  )
              )
        ),

       array(
          'title' => 'Pull text inn',
          'selector' => 'p',
          'classes' => 'text-pull-inn',
          'exact' => 'true',

        ),
    	  array(
          'title' => 'Paragraph - Lead',
          'selector' => 'p',
          'classes' => 'lead',
          'exact' => 'true',

        ),

    	  array(
          'title' => 'Paragraph - Lead, Centered',
          'selector' => 'p',
          'classes' => 'lead text-center',
          'exact' => 'true',

        ),

         array(
          'title' => 'Uppercase',
          'selector' => '*',
          'classes' => 'uppercase',

        ),
         array(
          'title' => 'Thin Font',
          'selector' => '*',
          'classes' => 'thin-font',
        ),

         array(
          'title' => 'Hide on Mobile screens',
          'selector' => '*',
          'classes' => 'hide-for-small',
        ),

        array(
          'title' => 'Alternative Font',
          'selector' => '*',
          'classes' => 'alt-font',

        ),

        array(
          'title' => 'Title - Large',
          'selector' => '*',
          'classes' => 'h-large',

        ),

         array(
          'title' => 'Title - X-Large',
          'selector' => '*',
          'classes' => 'h-xlarge',

        ),

        array(
          'title' => 'Backgroud - Black',
          'selector' => '*',
          'classes' => 'text-box-dark',

        ),

        array(
          'title' => 'Background - White',
          'selector' => '*',
          'classes' => 'text-box-light',

        ),

         array(
          'title' => 'Background - Primary Color',
          'selector' => '*',
          'classes' => 'text-box-primary',

        ),

          array(
          'title' => 'Text Border White',
          'selector' => '*',
          'classes' => 'text-bordered-white',

        ),
          array(
          'title' => 'Text Border Primary',
          'selector' => '*',
          'classes' => 'text-bordered-primary',

        ),
          array(
          'title' => 'Text Border Dark',
          'selector' => '*',
          'classes' => 'text-bordered-dark',

        )
          ,
          array(
          'title' => 'Text Border Top and Bottom White',
          'selector' => '*',
          'classes' => 'text-boarder-top-bottom-white',

        )
          ,
          array(
          'title' => 'Text Border Top and Bottom Dark',
          'selector' => '*',
          'classes' => 'text-boarder-top-bottom-dark',

        ),
          array(
          'title' => 'Tilt Left',
          'selector' => '*',
          'classes' => 'tilt-left',

        ),
          array(
          'title' => 'Text Border Top and Bottom Dark',
          'selector' => '*',
          'classes' => 'tilt-right',

        )
         ,
        array(
          'title' => 'Bullets List - Check mark',
          'selector' => 'li',
          'classes' => 'bullet-checkmark',

        ),
        array(
          'title' => 'Bullets List - Arrow',
          'selector' => 'li',
          'classes' => 'bullet-arrow',

        ),
        array(
          'title' => 'Bullets List - Star',
          'selector' => 'li',
          'classes' => 'bullet-star',

        ),

        array(
          'title' => 'Text shadow',
          'selector' => '*',
          'classes' => 'drop-shadow',

        ),


          array(
          'title' => 'Animate -Fade In',
          'selector' => '*',
          'classes' => 'animated fadeIn',

        ),

        array(
          'title' => 'Animate - Fade In Left',
          'selector' => '*',
          'classes' => 'animated fadeInLeft',

        ),
        array(
          'title' => 'Animate - Fade In Right',
          'selector' => '*',
          'classes' => 'animated fadeInRight',

        ),

    );

    $settings['style_formats'] = json_encode( $style_formats );

    return $settings;

}


/* Add HTML after Short description */
if($flatsome_opt['html_before_add_to_cart']){
	function before_add_to_cart_html(){
		global $flatsome_opt;
		echo do_shortcode($flatsome_opt['html_before_add_to_cart']);
	}
	add_action( 'woocommerce_single_product_summary', 'before_add_to_cart_html', 20);

}

if($flatsome_opt['html_after_add_to_cart']){
	function after_add_to_cart_html(){
		global $flatsome_opt;
		echo do_shortcode($flatsome_opt['html_after_add_to_cart']);
	}
	add_action( 'woocommerce_single_product_summary', 'after_add_to_cart_html', 30);
}

/* HTML AFTER CART */
if($flatsome_opt['html_cart_footer']){
	function html_cart_footer(){
		global $flatsome_opt;
		echo do_shortcode($flatsome_opt['html_cart_footer']);
	}
	add_action( 'woocommerce_after_cart', 'html_cart_footer', 0);
}


/* SHORTCODE FIX */
function fixShortcode($content){
    $fix = array (
          					    '_____' => '<div class="tx-div large"></div>',
    		                '____' => '<div class="tx-div medium"></div>',
    		                '___' => '<div class="tx-div small"></div>',
                        ']<br />' => ']',
                        '<br />[' => '[',
                        '<br>' => '',
    );
    $content = strtr($content, $fix);
    $content = wpautop( preg_replace( '/<\/?p\>/', "\n", $content ) . "\n" );


    return do_shortcode( shortcode_unautop( $content) );
}


/* EDITOR COLORS */
function ux_mce4_options( $init ) {
global $flatsome_opt;
$default_colours = '
    "000000", "Black",        "993300", "Burnt orange", "333300", "Dark olive",   "003300", "Dark green",   "003366", "Dark azure",   "000080", "Navy Blue",      "333399", "Indigo",       "333333", "Very dark gray",
    "800000", "Maroon",       "FF6600", "Orange",       "808000", "Olive",        "008000", "Green",        "008080", "Teal",         "0000FF", "Blue",           "666699", "Grayish blue", "808080", "Gray",
    "FF0000", "Red",          "FF9900", "Amber",        "99CC00", "Yellow green", "339966", "Sea green",    "33CCCC", "Turquoise",    "3366FF", "Royal blue",     "800080", "Purple",       "999999", "Medium gray",
    "FF00FF", "Magenta",      "FFCC00", "Gold",         "FFFF00", "Yellow",       "00FF00", "Lime",         "00FFFF", "Aqua",         "00CCFF", "Sky blue",       "993366", "Brown",        "C0C0C0", "Silver",
    "FF99CC", "Pink",         "FFCC99", "Peach",        "FFFF99", "Light yellow", "CCFFCC", "Pale green",   "CCFFFF", "Pale cyan",    "99CCFF", "Light sky blue", "CC99FF", "Plum",         "FFFFFF", "White"
';
$custom_colours = '
    "e14d43", "Primary Color", "d83131", "Color 2 Name", "ed1c24", "Color 3 Name", "f99b1c", "Color 4 Name", "50b848", "Color 5 Name", "00a859", "Color 6 Name",   "00aae7", "Color 7 Name", "282828", "Color 8 Name"
';
$init['textcolor_map'] = '['.$custom_colours.','.$default_colours.']';
return $init;
}
add_filter('tiny_mce_before_init', 'ux_mce4_options');



/* Shorter exerpt */
function short_excerpt($limit) {
      $excerpt = explode(' ', get_the_excerpt(), $limit);
      if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
      } else {
        $excerpt = implode(" ",$excerpt);
      }
      $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
      return $excerpt;
    }

    function content($limit) {
      $content = explode(' ', get_the_content(), $limit);
      if (count($content)>=$limit) {
        array_pop($content);
        $content = implode(" ",$content).'...';
      } else {
        $content = implode(" ",$content);
      }
      $content = preg_replace('/\[.+\]/','', $content);
      $content = apply_filters('the_content', $content);
      $content = str_replace(']]>', ']]&gt;', $content);
      return $content;
}

/* HEX TO RGB */
function ux_hex2rgba($color, $opacity = false) {
	$default = 'rgb(0,0,0)';
	//Return default if no color provided
	if(empty($color))
          return $default;

	//Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
        	$color = substr( (string) $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen((string) $color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( (string) $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }
        //Return rgb(a) color string
        return $output;
}


/* SETUP DEFAULT WOOCOMMERCE IMAGE SIZES */
global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) add_action( 'init', 'flatsome_woocommerce_image_dimensions', 1 );

function flatsome_woocommerce_image_dimensions() {
    $catalog = array(
    'width'   => '247', // px
    'height'  => '300', // px
    'crop'    => 1    // true
  );

  $single = array(
    'width'   => '510', // px
    'height'  => '600', // px
    'crop'    => 1    // true
  );

  $thumbnail = array(
    'width'   => '114', // px
    'height'  => '130', // px
    'crop'    => 1    // false
  );


// Catalog Image sizes
  update_option( 'shop_catalog_image_size', $catalog );     // Product category thumbs
  update_option( 'shop_single_image_size', $single );     // Single product image
  update_option( 'shop_thumbnail_image_size', $thumbnail );   // Image gallery thumbs
}


/* ajax navigation fix */
add_filter('_ajax_layered_nav_containers', 'ux_add_custom_container');
function ux_add_custom_container($containers){
$containers[] = '.woocommerce-pagination';
$containers[] = '.woocommerce-result-count';
return $containers;
}


add_filter('sod_ajax_layered_nav_product_container', 'aln_product_container');
function aln_product_container($product_container){
//Enter either the class or id of the container that holds your products
return '.products';
}


/* WooCommerce extra tabs */
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {
  global $wc_cpdf, $flatsome_opt;
  // Adds the new tab
  if($wc_cpdf->get_value(get_the_ID(), '_custom_tab_title')){
    $tabs['ux_custom_tab'] = array(
      'title'   =>  $wc_cpdf->get_value(get_the_ID(), '_custom_tab_title'),
      'priority'  => 40,
      'callback'  => 'ux_custom_tab_content'
    );
  }

  if($flatsome_opt['tab_title']){
  $tabs['ux_global_tab'] = array(
    'title'   => $flatsome_opt['tab_title'],
    'priority'  => 50,
    'callback'  => 'ux_global_tab_content'
  );
  }

  return $tabs;

}
function ux_custom_tab_content() {
  // The new tab content
  global $wc_cpdf;
  echo do_shortcode($wc_cpdf->get_value(get_the_ID(), '_custom_tab'));
}

function ux_global_tab_content() {
  // The new tab content
  global $flatsome_opt;
  echo do_shortcode($flatsome_opt['tab_content']);
}


/* Insert custom header script */
function flatsome_custom_header_js() {
  global $flatsome_opt;
  if($flatsome_opt['html_scripts_header'] && !is_admin()){
    echo $flatsome_opt['html_scripts_header'];
  }
}
add_action( 'wp_head', 'flatsome_custom_header_js');



/* Enable SVG upload */
function ux_enable_svg( $mimes ){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'ux_enable_svg' );


/* Simple Maintenance mode */

if($flatsome_opt['maintenance_mode']){

function flatsome_maintenance_mode_on_activation()  {
  if ( ! current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "activate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
    }
}

function flatsome_maintenance_mode_on_deactivation() {
  if ( ! current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "deactivate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
  }
}

register_activation_hook(   __FILE__, 'flatsome_maintenance_mode_on_activation' );
register_deactivation_hook( __FILE__, 'flatsome_maintenance_mode_on_deactivation' );

/**
 * Alert message when active
*/
$smm_active_message = __('<strong>Maintenance mode</strong> is <strong>active</strong>!', 'flatsome-maintenance-mode' );
$smm_admin_notice = '<div id="message" class="error fade"><p>' . $smm_active_message . ' <a href="themes.php?page=optionsframework&tab=of-option-globalsettings">' . __( 'Deactivate it, when work is done.', 'flatsome-maintenance-mode' ) . '</a></p></div>';

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
add_action( 'network_admin_notices', function()
{
  echo '$smm_admin_notice';
});

add_action( 'admin_notices', function()
{
  echo '$smm_admin_notice';
});

add_filter( 'login_message', function()
{
  return '<div id=\"login_error\">$smm_active_message</div>';
});

/**
 * Maintenance message when active
*/
function flatsome_maintenance_mode()
{
  global $flatsome_opt;

  nocache_headers();
  if(!current_user_can('edit_themes') || !is_user_logged_in()) {
  wp_die( '<center><img src="'.$flatsome_opt['site_logo'].'"/ style="max-width:200px;"><h2>' . __( 'Maintenance', 'flatsome-maintenance-mode' ) . '</h2><p>' . $flatsome_opt['maintenance_mode_text'] . '</p></center>', __( 'Maintenance', 'flatsome-maintenance-mode' ), array('response' => '503'));
  }
}
add_action('get_header', 'flatsome_maintenance_mode');

}
