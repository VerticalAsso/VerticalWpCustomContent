<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) $content_width = 1000; /* pixels */

/* add woocommerce support */
add_theme_support( 'woocommerce' );

/* Add theme option panel */
require_once('admin/index.php'); // load theme option panel

global $flatsome_opt;
$flatsome_opt = $smof_data;


/************ Plugin recommendations **********/
require_once ('inc/class-tgm-plugin-activation.php');
add_action( 'tgmpa_register', 'flatsome_register_required_plugins' );

function flatsome_register_required_plugins() {

	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		array(
			'name'     				=> 'WooCommerce', // The plugin name
			'slug'     				=> 'woocommerce', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/woocommerce.zip', // The plugin source
			'required' 				=> true, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '2.2.8', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'http://wordpress.org/plugins/woocommerce/', // If set, overrides default API URL and points to an external URL
		),

		array(
			'name'     				=> 'Ninja Forms', // The plugin name
			'slug'     				=> 'ninja-forms', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/ninja-forms.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '2.8.10', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/ninja-forms/', // If set, overrides default API URL and points to an external URL
		),

		array(
			'name'     				=> 'Regenerate Thumbnails', // The plugin name
			'slug'     				=> 'regenerate-thumbnails', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/regenerate-thumbnails.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '2.2.4', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/regenerate-thumbnails/', // If set, overrides default API URL and points to an external URL
		),


		array(
			'name'     				=> 'Taxonomy Metadata', // The plugin name
			'slug'     				=> 'taxonomy-metadata', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/taxonomy-metadata.zip', // The plugin source
			'required' 				=> true, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '0.4', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/taxonomy-metadata/', // If set, overrides default API URL and points to an external URL
		),

		array(
			'name'     				=> 'Unlimited Sidebars Woosidebars', // The plugin name
			'slug'     				=> 'woosidebars', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/woosidebars.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '1.3.1', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/woosidebars/', // If set, overrides default API URL and points to an external URL
		),

		array(
			'name'     				=> 'YITH WooCommerce Ajax Search', // The plugin name
			'slug'     				=> 'yith-woocommerce-ajax-search', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/yith-woocommerce-ajax-search.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '1.2.2', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/yith-woocommerce-ajax-search/', // If set, overrides default API URL and points to an external URL
		),
			array(
			'name'     				=> 'YITH WooCommerce Wishlist', // The plugin name
			'slug'     				=> 'yith-woocommerce-wishlist', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/yith-woocommerce-wishlist.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '2.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/yith-woocommerce-wishlist/', // If set, overrides default API URL and points to an external URL
		),
			array(
			'name'     				=> 'Nextend Facebook Connect', // The plugin name
			'slug'     				=> 'nextend-facebook-connect', // The plugin slug (typically the folder name)
			//'source'   				=> get_template_directory() . '/inc/plugins/nextend-facebook-connect.zip', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '1.5.3', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> 'https://wordpress.org/plugins/nextend-facebook-connect/', // If set, overrides default API URL and points to an external URL
		)

	);

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
    $config = array(
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );

}


if ( ! function_exists( 'flatsome_setup' ) ) :
function flatsome_setup() {

	/* Custom CSS */
	require( get_template_directory() . '/inc/custom-css.php' );

	/* Custom template tags */
	require( get_template_directory() . '/inc/template-tags.php' );

	/*  Custom functions that act independently of the theme templates */
	require( get_template_directory() . '/inc/extras.php' );

	/* load theme languages */
	load_theme_textdomain( 'flatsome', get_template_directory() . '/languages' );

	/* Add default posts and comments RSS feed links to head */
	add_theme_support( 'automatic-feed-links' );

	/* Add support for post thumbnails */
	add_theme_support( 'post-thumbnails' );

	/*  Registrer menus. */
	register_nav_menus( array(
		'primary' => __( 'Main Menu', 'flatsome' ),
		'footer' => __( 'Footer Menu', 'flatsome' ),
		'top_bar_nav' => __( 'Top bar Menu', 'flatsome' ),
		'my_account' => __( 'My Account Menu', 'flatsome' ),
	) );

	/*  Enable support for Post Formats */
	//add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );
}
endif; // flatsome_setup
add_action( 'after_setup_theme', 'flatsome_setup' );


/**
 * Register widgetized area and update sidebar with default widgets
 */
function flatsome_widgets_init() {
	global $flatsome_opt;



	register_sidebar( array(
		'name'          => __( 'Sidebar', 'flatsome' ),
		'id'            => 'sidebar-main',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3><div class="tx-div small"></div>',
	) );


	register_sidebar( array(
		'name'          => __( 'Shop Sidebar', 'flatsome' ),
		'id'            => 'shop-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title shop-sidebar">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Product Sidebar', 'flatsome' ),
		'id'            => 'product-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title shop-sidebar">',
		'after_title'   => '</h3>',
	) );


	$footer_1 = 'large-3';
	$footer_2 = 'large-3';

	if(isset($flatsome_opt['footer_1_columns'])){
		$footer_1 = $flatsome_opt['footer_1_columns'];
		$footer_2 = $flatsome_opt['footer_2_columns'];
	}


	 register_sidebar( array(
		'name'          => __( 'Footer 1', 'flatsome' ),
		'id'            => 'sidebar-footer-1',
		'before_widget' => '<div id="%1$s" class="'.$footer_1.' columns widget left %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3><div class="tx-div small"></div>',
	) );


	 register_sidebar( array(
		'name'          => __( 'Footer 2', 'flatsome' ),
		'id'            => 'sidebar-footer-2',
		'before_widget' => '<div id="%1$s" class="'.$footer_2.' columns widget left %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3><div class="tx-div small"></div>',
	) );


}
add_action( 'widgets_init', 'flatsome_widgets_init' );

include_once('inc/widgets/recent-posts.php'); // Load Widget Recent Posts
include_once('inc/widgets/upsell-widget.php'); // Load Upsell widget

/**
 * Enqueue scripts and styles
 */

function flatsome_scripts() {

	global $flatsome_opt;

	/* Styles */
	if(!isset($flatsome_opt['minified_flatsome']) || !$flatsome_opt['minified_flatsome']){
	   wp_enqueue_style( 'flatsome-icons', get_template_directory_uri() .'/css/fonts.css', array(), '2.2.3', 'all' );
	   wp_enqueue_style( 'flatsome-animations', get_template_directory_uri() .'/css/animations.css', array(), '2.2.3', 'all' );
	   wp_enqueue_style( 'flatsome-style', get_stylesheet_uri(), array(), '2.2.3', 'all');
	   wp_register_style('flatsome-effect', get_template_directory_uri() .'/css/effects.css', array(), '2.2.3', 'all' );
	} else {
	   wp_enqueue_style( 'flatsome-css-minified', get_template_directory_uri() .'/css/flatsome.min.css', array(), '2.2.3', 'all' );
	}

	/* JS libaries */
	if(!isset($flatsome_opt['minified_flatsome']) || !$flatsome_opt['minified_flatsome']){
		wp_enqueue_script( 'flatsome-modernizer', get_template_directory_uri() .'/js/modernizr.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
 		wp_enqueue_script( 'flatsome-plugins', get_template_directory_uri() .'/js/plugins.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
		wp_enqueue_script( 'flatsome-iosslider', get_template_directory_uri() .'/js/jquery.iosslider.min.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
		wp_enqueue_script( 'flatsome-magnific-popup', get_template_directory_uri() .'/js/jquery.magnific-popup.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
		wp_enqueue_script( 'flatsome-theme-js', get_template_directory_uri() .'/js/theme.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
	} else {
		wp_enqueue_script( 'flatsome-theme-js-minified', get_template_directory_uri() .'/js/flatsome.min.js?v=2.2.3', array( 'jquery' ), '2.1.1', true );
	}

	/* add JS variables to scripts */
    wp_localize_script( 'flatsome-theme-js', 'ajaxURL',  array( 'ajaxurl'    => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'flatsome-theme-js-minified', 'ajaxURL',  array( 'ajaxurl'    => admin_url( 'admin-ajax.php' ) ) );

	/* Remove plugin styles */
	wp_deregister_style('yith-wcwl-font-awesome');
	wp_deregister_style('yith-wcwl-font-awesome-ie7');
	wp_deregister_style('yith-wcwl-main');
	wp_deregister_style('yith_wcas_frontend');
	wp_deregister_style('nextend_fb_connect_stylesheet');

	// Remove WooCommerce pretty photo
    wp_deregister_style( 'woocommerce_prettyPhoto_css' );
    wp_dequeue_script( 'prettyPhoto' );
    wp_dequeue_script( 'prettyPhoto-init' );

	if ( ! is_admin() ) {
		wp_deregister_style('woocommerce-layout');
		wp_deregister_style('woocommerce-smallscreen');
		wp_deregister_style('woocommerce-general');
	}


	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}
add_action( 'wp_enqueue_scripts', 'flatsome_scripts' );



/* WOOCOMMERCE SETTINGS */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_show_messages', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 10 );

add_action( 'ux_woocommerce_navigate_products', 'woocommerce_result_count', 20 );
add_action( 'ux_woocommerce_navigate_products', 'woocommerce_catalog_ordering', 30 );

// include custom product page settings
include_once('inc/class-wc-product-data-fields.php');
include_once('inc/custom-wc-fields.php');

// remoce checkout from checkout page if not set
if(!$flatsome_opt['coupon_checkout'] || !isset($flatsome_opt['coupon_checkout'])){
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}




/* add shortcode to widgets */
add_filter('widget_text', 'do_shortcode');

/* add shortcode to excerpt */
add_filter('the_excerpt', 'do_shortcode');


/* add exerpt option to pages */
add_action('init', 'flatsome_post_type_support');
function flatsome_post_type_support() {
	add_post_type_support( 'page', 'excerpt' );
}


/** LOAD GOOGLE FONTS */
include_once( get_template_directory() . '/inc/google-fonts.php' );


/* SHORTCODES */

include_once('inc/shortcodes/banners.php');
include_once('inc/shortcodes/slider.php');
include_once('inc/shortcodes/banner_grid.php');
include_once('inc/shortcodes/grid.php');
include_once('inc/shortcodes/accordion.php');
include_once('inc/shortcodes/tabs.php');
include_once('inc/shortcodes/featured_box.php');
include_once('inc/shortcodes/buttons.php');
include_once('inc/shortcodes/share_follow.php');
include_once('inc/shortcodes/elements.php');
include_once('inc/shortcodes/titles_dividers.php');
include_once('inc/shortcodes/lightbox.php');
include_once('inc/shortcodes/blog_posts.php');
include_once('inc/shortcodes/google_maps.php');
include_once('inc/shortcodes/testimonials.php');
include_once('inc/shortcodes/team_members.php');
include_once('inc/shortcodes/messages.php');
include_once('inc/shortcodes/search.php');
include_once('inc/shortcodes/product_flip.php');
include_once('inc/shortcodes/product_sliders.php');
include_once('inc/shortcodes/product_categories.php');
include_once('inc/shortcodes/product_lookbook.php');
include_once('inc/shortcodes/product_pinterest_style.php');
include_once('inc/shortcodes/featured_items.php');

/* SHORTCODE INSERTER */
if(is_admin()){
	include_once('inc/shortcodes/inserter/tinymce.php');
}

/* CUSTOM POST TYPES */
include_once('inc/post-types/blocks.php');
include_once('inc/post-types/featured-items.php');


/* PAGE BUILDER BETA */
if(is_admin() && $flatsome_opt['flatsome_builder']){
	include_once('inc/builder/flatsome-builder.php');
}


/* ADD CUSTOM WP_EDITOR CSS */
add_filter('mce_css', 'my_editor_style');
function my_editor_style($url) {
  if ( !empty($url) )
    $url .= ',';
  // Change the path here if using sub-directory
  $url .= trailingslashit( get_template_directory_uri() ) . 'css/editor.css';
  return $url;
}


/* ADD IE 8/9 FIX TO HEADER */
function add_ieFix () {
	$ie_css = get_template_directory_uri() .'/css/ie8.css';
    echo '<!--[if lt IE 9]>';
    echo '<link rel="stylesheet" type="text/css" href="'.$ie_css.'">';
    echo '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>';
    echo "<script>var head = document.getElementsByTagName('head')[0],style = document.createElement('style');style.type = 'text/css';style.styleSheet.cssText = ':before,:after{content:none !important';head.appendChild(style);setTimeout(function(){head.removeChild(style);}, 0);</script>";
    echo '<![endif]-->';
}
add_action('wp_head', 'add_ieFix');


// Change products per page if set.
if (isset($flatsome_opt['products_pr_page'])) {
    $products = $flatsome_opt['products_pr_page'];
    add_filter('loop_shop_per_page', function ($cols) use ($products) {
        return $products;
    }, 20);
}

/* Redirect to Homepage when customer log out */
add_filter('logout_url', 'new_logout_url', 10, 2);
function new_logout_url($logouturl, $redir)
{
	$redir = get_option('siteurl');
	return $logouturl . '&amp;redirect_to=' . urlencode($redir);
}



/* Include custom theme functions */
get_template_part( 'functions_custom' );


?>