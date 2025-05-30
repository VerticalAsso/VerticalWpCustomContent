<?php
namespace DbRestAccess\Api;
require_once __DIR__ . '/../Auth/apikey_checking.php';


use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}


/**
 * @api {get} /wp-json/dbrest/v1/event-bookings Get event bookings
 * @apiName GetEventBookings
 * @apiGroup Bookings
 *
 * @apiDescription
 * Retrieve all bookings for a specific event, either just validated bookings or all (including pending).
 *
 * @apiParam {Number} event_id The ID of the event (required).
 * @apiParam {String="validated","all"} [status="validated"] Booking status filter:
 *   - "validated": only validated bookings (status = '1')
 *   - "all": all bookings (validated, pending, etc.)
 *
 * @apiSuccess {Object[]} bookings List of bookings matching criteria (may be empty).
 * @apiSuccess {Number} count Number of bookings returned.
 * @apiSuccess {Number} event_id The event ID requested.
 * @apiSuccess {String} filter_status The status filter applied.
 *
 * @apiExample {curl} Get only validated bookings:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/event-bookings?event_id=123"
 *
 * @apiExample {curl} Get all bookings:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/event-bookings?event_id=123&status=all"
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "bookings": [
 *             {
 *                 "booking_id": 5,
 *                 "event_id": 123,
 *                 "booking_status": "1"
 *             }
 *         ],
 *         "count": 1,
 *         "event_id": 123,
 *         "filter_status": "validated"
 *     }
 */
function register_event_bookings_route()
{
    register_rest_route('dbrest/v1', '/event-bookings', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\get_event_bookings',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param) && $param > 0;
                }
            ],
            'status' => [
                'required' => false,
                'validate_callback' => function ($param) {
                    return in_array($param, ['all', 'validated']);
                },
                'default' => 'validated'
            ]
        ]
    ]);
}

// Retrieves all bookings for a given event
function get_event_bookings(WP_REST_Request $request)
{
    global $wpdb;
    $event_id = $request->get_param('event_id');
    $status = $request->get_param('status');
    $table = $wpdb->prefix . 'em_bookings'; // Adjust if your table prefix is different

    // Build the query depending on status
    if ($status === 'all') {
        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d",
            $event_id
        );
    } else { // validated only
        $query = $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = '%d' AND booking_status = '1'",
            $event_id,
            '1'
        );
    }

    $results = $wpdb->get_results($query);


    // Unwrap the PHP serialized array so that it'll be easier for consummer code later on to handle it.
    foreach ($results as &$row) {
        if (isset($row->booking_meta)) {
            $meta = @unserialize($row->booking_meta);
            if ($meta !== false && is_array($meta)) {
                $row->booking_meta = $meta;
            }
        }
    }


    return rest_ensure_response([
        "bookings" => $results,
        "count" => count($results),
        "event_id" => $event_id,
        "filter_status" => $status
    ]);
}