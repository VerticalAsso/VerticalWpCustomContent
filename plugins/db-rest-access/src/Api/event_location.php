<?php

namespace DbRestAccess\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * @api {get} /wp-json/dbrest/v1/user Get user data from database
 *
 * @apiDescription
 * Retrieves user data from vertical database, with minimal modifications for compatibility purposes.
 *
 * @apiParam {Number} location_id : The ID of the user (required).
 */
function register_event_location_route()
{
    register_rest_route('dbrest/v1', '/eventlocation', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_location',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
        'args' => [
            'location_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_location_id',
            ]
        ]
    ]);
}

/**
 * Validate that location_id is a positive integer.
 */
function validate_location_id($param): bool {
    return is_numeric($param) && $param > 0;
}

/**
 * Handles the postmeta REST API endpoint.
 */
function get_event_location(WP_REST_Request $request)
{
    $location_id = $request->get_param('location_id');

    $results = internal_get_location($location_id);
    return rest_ensure_response($results);
}

/**
 * Returns a JSON object matching the template structure.
 * For missing keys, sets the value to null.
 * For nested arrays/objects, handles recursion as needed.
 */
function internal_get_location(int $location_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_locations';
    $sql_request = $wpdb->prepare("SELECT * FROM $table WHERE location_id = %d", $location_id);

    $results = $wpdb->get_results($sql_request, ARRAY_A);

    // The JSON template keys and structures you want to enforce
    $output = [];
    foreach ($results as $row)
    {
        $data = [
            "location_id" => $row['location_id'],
            "post_id" => $row['post_id'],
            "location_slug" => $row['location_slug'],
            "location_name" => $row['location_name'],
            "location_address" => $row['location_address'],
            // "location_state" => $row['location_state'],
            "location_postcode" => $row['location_postcode'],
            "location_country" => $row['location_country'],
            // "location_region" => $row['location_region'],
            "location_latitude" => $row['location_latitude'],
            "location_longitude" => $row['location_longitude'],
        ];
        array_push($output, $data);
    }

    return $output;
}
