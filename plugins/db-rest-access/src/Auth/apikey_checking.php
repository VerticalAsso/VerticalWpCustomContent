<?php

namespace DbRestAccess\Auth;
use WP_REST_Request, WP_Error;

const DB_REST_ACCESS_APIKEY_OPT_NAME = 'dbrest_access_apikey';

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}


// Verify the API key
function verify_api_key(WP_REST_Request $request)
{
    // Get the API key from the headers
    $api_key = $request->get_header('X-Api-Key');

    // Retrieve the stored API key from the options table
    $options = get_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
    $stored_api_key = isset($options['api_key']) ? $options['api_key'] : '';

    // Reject queries when ApiKey is not there yet
    if (empty($stored_api_key)) {
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
