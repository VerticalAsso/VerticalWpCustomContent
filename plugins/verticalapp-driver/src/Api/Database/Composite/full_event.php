<?php

namespace VerticalAppDriver\Api\Database\Composite;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';
require_once __DIR__ . '/../Core/event_bookings.php';
require_once __DIR__ . '/../Core/event_location.php';
require_once __DIR__ . '/../Core/post_content.php';
require_once __DIR__ . '/../Core/post_meta.php';
require_once __DIR__ . '/../Core/user.php';
require_once __DIR__ . '/../Core/user_meta.php';
require_once __DIR__ . '/../Core/comments.php';

use WP_REST_Request;
use VerticalAppDriver\Api\Database\Core as Core;
use VerticalAppDriver\Api\Database\Core\EventMetadata;
use WP_Error;
use WP_REST_Response;

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

    $result = internal_get_full_event(intval($event_id));

    return rest_ensure_response($result);
}

class FullEventResult
{
    public $event_raw;
    public EventMetadata $event_metadata;
    public array $em_roles; // Mandatory roles to register to this event (derived from events manager)
    public $event_card;
    public $bookings;
    public $comments;
    public $location;
    public $categories; // Not implemented yet
}

/**
 * Retrieves comprehensive details about an event, including its raw data, metadata, card, bookings, comments, and location.
 *
 * @param int $event_id The ID of the event to retrieve.
 * @return FullEventResult|WP_REST_Response The comprehensive event details or a WP_REST_Response with a 404 status if the event does not exist.
 */
function internal_get_full_event(int $event_id) : FullEventResult | WP_REST_Response
{
    // Can happen as event ids are not contiguous (there are big id jumps between event records )
    $event_base_data = internal_get_single_event_record($event_id);
    if($event_base_data == null)
    {
        return new WP_REST_Response("Requested event Id does not exist", 404);
    }

    $post_id = $event_base_data['post_id'];
    $post_metadata = Core\internal_get_postmeta($post_id);
    if( $post_metadata == null || $post_metadata instanceof WP_REST_Response)
    {
        return new WP_REST_Response("Requested event's post metadata could not be found", 404);
    }

    // Event card already contains information about thumbnail, title, excerpt, etc.
    // Reusing it will allow to retrieve valuable data more easily
    $event_card = internal_get_event_card($event_id);

    // Extracting raw bookings data
    $raw_bookings = Core\internal_get_bookings_for_event($event_id);
    $bookings = [];

    // Retrieving mandatory roles to register to this event (derived from events manager)
    // This is a bit hacky, but events manager does not provide a direct way to get this information
    // So we have to load the event and its tickets to get the roles
    // Note: this requires events manager to be active and the EM classes to be available
    // This information can be found in the em_tickets table, column ticket_members_roles
    $em_event = em_get_event($event_id);
    $em_tickets = $em_event->get_tickets();
    $em_roles = $em_tickets->get_first()->members_roles;

    // Unwrap the PHP serialized array so that it'll be easier for consumer code later on to handle it.
    foreach ($raw_bookings as &$rb)
    {
        $user = Core\internal_get_user_data($rb->person_id);
        $filtered_booking = [
            "booking_id" => $rb->booking_id,
            "user" => $user,
            "spaces" => $rb->booking_spaces,
            "booking_date" => $rb->booking_date,
            "booking_status" => $rb->booking_status
        ];

        array_push($bookings, $filtered_booking);
    }

    $comments = Core\internal_get_comments($post_id);

    // categories
    $location_id = $event_base_data['location_id'];
    $location = Core\internal_get_location($location_id);

    $result = new FullEventResult();
    $result->event_raw      = $event_base_data;
    $result->event_metadata = $post_metadata;
    $result->event_card     = $event_card;
    $result->em_roles       = $em_roles;
    $result->bookings       = $bookings;
    $result->comments       = $comments;
    $result->location       = $location;
    $result->categories     = "not implemented";

    return $result;
}