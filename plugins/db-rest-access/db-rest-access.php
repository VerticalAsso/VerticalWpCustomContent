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
add_action('rest_api_init', function () {
    register_rest_route('dbrest/v1', '/table=(?P<table>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'db_rest_access_get_data',
        'permission_callback' => 'db_rest_access_verify_api_key',
    ]);
});

// REST API callback: Fetch data from the database
function db_rest_access_get_data(WP_REST_Request $request) {
    global $wpdb;

    // Example: Query a custom table named "wp_custom_table"
    $table_name = $wpdb->prefix . 'custom_table';
    $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 10");

    return rest_ensure_response($results);
}

// Verify the API key
function db_rest_access_verify_api_key(WP_REST_Request $request) {
    // Get the API key from the headers
    $api_key = $request->get_header('X-Api-Key');

    // Retrieve the stored API key from the options table
    $options = get_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
    $stored_api_key = isset($options['option_value']) ? $options['option_value'] : '';

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