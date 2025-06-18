<?php

namespace VerticalAppDriver\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';

use WP_REST_Request;

/**
 * Registers the /event-bookings REST API endpoint for retrieving bookings for a specific event.
 *
 * @api {get} /wp-json/vdriver/v1/event-bookings Get event bookings
 * @apiName GetEventBookings
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription
 * Retrieve all bookings for a specific event, either just validated bookings or all (including pending).
 *
 * @apiParam {Number} event_id The ID of the event (required).
 * @apiParam {String="validated","all"} [status="validated"] Booking status filter ("validated" = only validated bookings, "all" = all bookings).
 *
 * @apiError (Error 400) MissingEventId The event_id parameter is required and must be a positive integer.
 * @apiError (Error 400) InvalidStatus The status parameter must be "validated" or "all".
 *
 * @return void
 */
function register_event_bookings_route()
{
    register_rest_route('vdriver/v1', '/event-bookings', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_bookings',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args'                => [
            'event_id' => [
                'required'          => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param) && $param > 0;
                }
            ],
            'status' => [
                'required'          => false,
                'validate_callback' => function ($param) {
                    return in_array($param, ['all', 'validated']);
                },
                'default'           => 'validated'
            ]
        ]
    ]);
}

/**
 * Callback for the /event-bookings endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error List of bookings with metadata or error.
 */
function get_event_bookings(WP_REST_Request $request)
{
    $event_id = $request->get_param('event_id');
    $status   = $request->get_param('status', 'validated');

    if (empty($event_id) || !is_numeric($event_id) || $event_id <= 0) {
        return new \WP_Error('missing_event_id', 'The event_id parameter is required and must be a positive integer.', ['status' => 400]);
    }
    if (!in_array($status, ['validated', 'all'])) {
        return new \WP_Error('invalid_status', 'The status parameter must be "validated" or "all".', ['status' => 400]);
    }

    $results = internal_get_bookings_for_event((int)$event_id, $status);

    return rest_ensure_response([
        "bookings"      => $results,
        "count"         => count($results),
        "event_id"      => (int)$event_id,
        "filter_status" => $status
    ]);
}

/**
 * Retrieves the list of bookings for a single event.
 *
 * @param int    $event_id The ID of the event (positive integer).
 * @param string $status   Filter: "all" or "validated" (default: "validated").
 * @return array[stdClass] List of bookings (each as an object or associative array).
 *
 * Each booking contains at least:
 *   - booking_id (int)
 *   - event_id (int)
 *   - booking_status (string)
 *   - booking_meta (array|mixed) (unserialized if available)
 *   - ...other fields from the bookings table...
 */
function internal_get_bookings_for_event(int $event_id, string $status = "validated")
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_bookings'; // Adjust if your table prefix is different

    if ($status === 'all') {
        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d",
            $event_id
        );
    } else { // validated only
        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d AND booking_status = %s",
            $event_id,
            '1'
        );
    }

    $results = $wpdb->get_results($query);

    // Unwrap the PHP serialized array so that it'll be easier for consumer code later on to handle it.
    foreach ($results as &$row) {
        if (isset($row->booking_meta)) {
            $meta = @unserialize($row->booking_meta);
            if ($meta !== false && is_array($meta)) {
                $row->booking_meta = $meta;
            }
        }
    }

    return $results;
}