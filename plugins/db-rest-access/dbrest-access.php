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

// Register event route (and any others)
\DbRestAccess\Api\register_event_tickets_route();
\DbRestAccess\Api\register_events_route();
\DbRestAccess\Api\register_event_card_route();
\DbRestAccess\Api\register_post_content_route();
\DbRestAccess\Api\register_postmeta_route();
\DbRestAccess\Api\register_event_bookings_route();


// Activation hook: Set default settings
register_activation_hook(__FILE__, function () {
    if (!get_option(DB_REST_ACCESS_APIKEY_OPT_NAME)) {
        // Generate default ApiKey
        // Borrowed from https://stackoverflow.com/a/37193149/8716917
        $key = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
        update_option(DB_REST_ACCESS_APIKEY_OPT_NAME, ['api_key' => $key]);
    }
});

// Deactivation hook: Cleanup settings
register_deactivation_hook(__FILE__, function () {
    delete_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
});
