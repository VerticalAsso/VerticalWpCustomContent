<?php

namespace VerticalAppDriver\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';
require_once __DIR__ . '/event_bookings.php';
require_once __DIR__ . '/event_location.php';
require_once __DIR__ . '/post_content.php';
require_once __DIR__ . '/post_meta.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/user_meta.php';
require_once __DIR__ . '/comments.php';

use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * Registers the /full-event REST API endpoint for retrieving full event details.
 *
 * @api {get} /wp-json/vdriver/v1/full-event Get full event details
 * @apiName GetFullEvent
 * @apiGroup Events
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve extensive event details, including the event's raw data, metadata, card, bookings, comments, and location.
 *
 * @apiParam {Number} event_id The ID of the event (required).
 *
 * @return void
 */
function register_full_event_route()
{
    register_rest_route('vdriver/v1', '/full-event', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\get_full_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => function ($param)
                {
                    return is_numeric($param) && $param > 0;
                }
            ]
        ]
    ]);
}

/**
 * Callback for the /full-event endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_full_event(WP_REST_Request $request)
{
    $event_id = $request->get_param('event_id');

    $event_base_data = internal_get_single_event_record($event_id);
    $post_id = $event_base_data['post_id'];
    $post_metadata = internal_get_postmeta($post_id);

    // Event card already contains information about thumbnail, title, excerpt, etc.
    // Reusing it will allow to retrieve valuable data more easily
    $event_card = internal_get_event_card($event_id);

    // Extracting raw bookings data
    $raw_bookings = internal_get_bookings_for_event($event_id);
    $bookings = [];

    // Unwrap the PHP serialized array so that it'll be easier for consumer code later on to handle it.
    foreach ($raw_bookings as &$rb)
    {
        $user = internal_get_user_data($rb->person_id);
        $filtered_booking = [
            "booking_id" => $rb->booking_id,
            "user" => $user,
            "spaces" => $rb->booking_spaces,
            "booking_date" => $rb->booking_date,
            "booking_status" => $rb->booking_status
        ];

        array_push($bookings, $filtered_booking);
    }

    $comments = internal_get_comments($post_id);

    // categories
    $location_id = $event_base_data['location_id'];
    $location = internal_get_location($location_id);

    $result = [
        "event_raw"      => $event_base_data,
        "event_metadata" => $post_metadata,
        "event_card"     => $event_card,
        "bookings"       => $bookings,
        "comments"       => $comments,
        "location"       => $location,
        "categories"     => "not implemented"
    ];

    return rest_ensure_response($result);
}