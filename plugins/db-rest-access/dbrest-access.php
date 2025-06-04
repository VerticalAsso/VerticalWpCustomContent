<?php

/**
 * Plugin Name: Database REST Access
 * Description: A plugin to provide REST API routes for querying custom database tables with an API key. It might also be used to write some data within the database itself
 * Version: 1.0
 * Author: bebenlebricolo (Benoît Tarrade)
 */

if (!defined('ABSPATH')) exit;

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/constants.php'; // adjust path if needed
require_once __DIR__ . '/src/Api/routes.php';
require_once __DIR__ . '/src/Helpers/debug_tools.php';
require_once __DIR__ . '/src/Admin/Settings.php';

// Register event route (and any others)
add_action('rest_api_init', 'DbRestAccess\Api\register_all_routes');

// Activation hook: Set default settings
register_activation_hook(__FILE__, function ()
{
    if (!get_option(DB_REST_ACCESS_APIKEY_OPT_NAME))
    {
        // Generate default ApiKey
        // Borrowed from https://stackoverflow.com/a/37193149/8716917
        $key = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
        update_option(DB_REST_ACCESS_APIKEY_OPT_NAME, ['api_key' => $key]);
    }
});

add_action('admin_menu', 'DbRestAccess\Helpers\add_php_info_page');


// Deactivation hook: Cleanup settings
register_deactivation_hook(__FILE__, function ()
{
    delete_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
});
