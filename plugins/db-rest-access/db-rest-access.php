<?php
/**
 * Plugin Name: Database REST Access
 * Description: A plugin to provide REST API routes for querying custom database tables with an API key. It might also be used to write some data within the database itself
 * Version: 1.0
 * Author: bebenlebricolo (but mainly AI)
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for the plugin
define('DB_REST_ACCESS_VERSION', '1.0');
define('DB_REST_ACCESS_APIKEY_OPT_NAME', 'db_rest_access_apikey');

// Include the settings page class
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Register the REST API routes
add_action('rest_api_init', 'register_rest_routes');

add_action( 'admin_menu', 'add_php_info_page' );

function add_php_info_page() {
    add_submenu_page(
        'tools.php',           // Parent page
        'Xdebug Info',         // Menu title
        'Xdebug Info',         // Page title
        'manage_options',      // user "role"
        'php-info-page',       // page slug
        'php_info_page_body'); // callback function
}

function php_info_page_body() {
    $message = '<h2>No Xdebug enabled</h2>';
    if ( function_exists( 'xdebug_info' ) ) {
        xdebug_info();
    } else {
        echo $message;
    }
}

/**
 * @brief registers REST routes
 */
function register_rest_routes()
{
    register_rest_route('dbrest/v1', '/table', [
        'methods' => 'GET',
        'callback' => 'db_rest_access_get_table',
        'permission_callback' => 'db_rest_access_verify_api_key',
        'args' => [
            'name' => [
                'required' => true,
                'validate_callback' => 'check_table_name_arg_from_request'
            ]
        ]
    ]);
}

function check_table_name_arg_from_request(string $param)
{
    // Validate table name to prevent SQL injection
    global $wpdb;
    $table_name = $wpdb->prefix . sanitize_text_field($param);
    return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
}

// REST API callback: Fetch data from the database
function db_rest_access_get_table(WP_REST_Request $request)
{
    global $wpdb;

    // Get the table name from the query parameter
    $table_name = $wpdb->prefix . sanitize_text_field($request->get_param('name'));

    // Get the optional custom SQL query
    $custom_query = $request->get_param('custom_query');

    // If a custom query is provided, validate and use it
    if (!empty($custom_query)) {
        // Ensure the custom query doesn't contain harmful SQL (basic validation)
        if (strpos(strtolower($custom_query), 'delete') !== false || strpos(strtolower($custom_query), 'update') !== false) {
            return new WP_Error('forbidden', 'Custom SQL query contains forbidden operations.', ['status' => 403]);
        }

        // Execute the custom query
        $results = $wpdb->get_results($custom_query);
    }
    else
    {
        // Default query: Select all rows from the table
        //$results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 10");
        $results = $wpdb->get_results("SELECT * FROM $table_name");
    }

    return rest_ensure_response($results);
}

// Verify the API key
function db_rest_access_verify_api_key(WP_REST_Request $request) {
    // Get the API key from the headers
    $api_key = $request->get_header('X-Api-Key');

    // Retrieve the stored API key from the options table
    $options = get_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
    $stored_api_key = isset($options['api_key']) ? $options['api_key'] : '';

    // Reject queries when ApiKey is not there yet
    if(empty($stored_api_key))
    {
        return new WP_Error(
            'Internal Server Error',
            'Plugin is not yet configured',
            ['status' => 500]
        );
    }

    if ($api_key === $stored_api_key) {
        return true; // Access granted
    }

    // Reject unauthenticated calls
    return new WP_Error(
        'forbidden',
        'Invalid or missing API Key.',
        ['status' => 403]
    );
}

// Activation hook: Set default settings
register_activation_hook(__FILE__, function () {
    if (!get_option(DB_REST_ACCESS_APIKEY_OPT_NAME)) {
        // Generate default ApiKey
        // Borrowed from https://stackoverflow.com/a/37193149/8716917
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
        update_option(DB_REST_ACCESS_APIKEY_OPT_NAME, ['api_key' => $key]);
    }
});

// Deactivation hook: Cleanup settings
register_deactivation_hook(__FILE__, function () {
    delete_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
});