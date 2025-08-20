<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;

/**
 * Registers the /eventlocation REST API endpoint for retrieving event location data.
 *
 * @api {get} /wp-json/vdriver/v1/eventlocation Get event location data
 * @apiName GetEventLocation
 * @apiGroup Locations
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieves location data for a given location ID.
 *
 * @apiParam {Number} location_id The ID of the location (required).
 *
 * @apiError (Error 400) InvalidLocationId The location_id parameter is required and must be a positive integer.
 *
 * @return void
 */
function register_event_location_route()
{
    register_rest_route('vdriver/v1', '/eventlocation', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_location',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
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
 *
 * @param mixed $param
 * @return bool
 */
function validate_location_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

/**
 * Callback for the /eventlocation endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error Event location data or error.
 */
function get_event_location(WP_REST_Request $request)
{
    $location_id = $request->get_param('location_id');
    if (empty($location_id) || !is_numeric($location_id) || $location_id <= 0) {
        return new WP_Error('invalid_location_id', 'The location_id parameter is required and must be a positive integer.', ['status' => 400]);
    }

    $results = internal_get_location((int)$location_id);
    return rest_ensure_response($results);
}

/**
 * Retrieves the location data for a given location ID.
 *
 * @param int $location_id The ID of the location.
 * @return array|WP_Error Location data or error.
 */
function internal_get_location(int $location_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_locations';
    $sql_request = $wpdb->prepare("SELECT * FROM $table WHERE location_id = %d", $location_id);

    $result = $wpdb->get_row($sql_request, ARRAY_A);

    // Return null if no location found
    if (!$result) {
        return null;
    }

    // Return single location data structure
    return [
        "location_id"       => $result['location_id'],
        "post_id"           => $result['post_id'],
        "location_slug"     => $result['location_slug'],
        "location_name"     => $result['location_name'],
        "location_town"     => $result['location_town'],
        "location_state"    => $result['location_state'],
        "location_region"   => $result['location_region'],
        "location_address"  => $result['location_address'],
        "location_postcode" => $result['location_postcode'],
        "location_country"  => $result['location_country'],
        "location_latitude" => $result['location_latitude'],
        "location_longitude"=> $result['location_longitude'],
    ];
}