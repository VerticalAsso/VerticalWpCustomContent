<?php

/**
 * Plugin Name: Vertical Application Wordpress Driver
 * Description: A plugin to provide REST API routes for querying custom database tables with an API key.
 * It might also be used to write some data within the database itself.
 * This plugin allows to subscribe/unsubscribe from events without having to use WordPress' Ajax (nor cookies) requests.
 * Version: 1.3
 * Author: bebenlebricolo (Benoît Tarrade)
 */

if (!defined('ABSPATH')) exit;

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/constants.php'; // adjust path if needed
require_once __DIR__ . '/src/Api/Database/routes.php';
require_once __DIR__ . '/src/Helpers/debug_tools.php';
require_once __DIR__ . '/src/Admin/Settings.php';

// Register event route (and any others)
add_action('rest_api_init', 'VerticalAppDriver\Api\Database\register_all_routes');

// Activation hook: Set default settings
register_activation_hook(__FILE__, function ()
{
    if (!get_option(VERTICALAPP_DRIVER_APIKEY_OPT_NAME))
    {
        // Generate default ApiKey
        // Borrowed from https://stackoverflow.com/a/37193149/8716917
        $key = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
        update_option(VERTICALAPP_DRIVER_APIKEY_OPT_NAME, ['api_key' => $key]);
    }
});

add_action('admin_menu', 'VerticalAppDriver\Helpers\add_php_info_page');


// Used to add hooks to events manager new event creation
// (published for the first time or state changed from draft to published)
add_filter('em_event_save', function($result, $EM_Event) {
    if (!$result) return $result; // Save failed, ignore

    // Get previous and current status
    $previous_status = $EM_Event->get_previous_status(); // e.g. 'draft', 'pending', etc.
    $current_status = $EM_Event->get_status(true); // true: get current status

    // Only trigger when an event goes from not published to published
    if ($previous_status !== '1' && $current_status === 1)
    {
        echo("New event detected !");
        // This is a new publication or a status change to published
    }
    return $result;
}, 10, 2);


// Deactivation hook: Cleanup settings
register_deactivation_hook(__FILE__, function ()
{
    delete_option(VERTICALAPP_DRIVER_APIKEY_OPT_NAME);
});
