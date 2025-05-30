<?php

namespace DbRestAccess\Api;

use WP_REST_Request;

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @api {get} /wp-json/dbrest/v1/event-tickets Get event ticket templates
 * @apiName GetEventTickets
 * @apiGroup Events
 *
 * @apiDescription
 * Retrieve all ticket templates for a specific event.
 *
 * @apiParam {Number} event_id The ID of the event (required).
 *
 * @apiSuccess {Object[]} tickets List of tickets for the event (may be empty).
 * @apiSuccess {Number} count Number of tickets returned.
 * @apiSuccess {Number} event_id The event ID requested.
 *
 * @apiExample {curl} Example usage:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/event-tickets?event_id=123"
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "tickets": [
 *             {
 *                 "ticket_id": 1,
 *                 "event_id": 123,
 *                 "ticket_members_roles": ["role1", "role2"]
 *             }
 *         ],
 *         "count": 1,
 *         "event_id": 123
 *     }
 */
function register_event_tickets_route()
{
    register_rest_route('dbrest/v1', '/event-tickets', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\get_event_tickets',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_event_id',
            ]
        ]
    ]);
}
add_action('rest_api_init', __NAMESPACE__ . '\\register_event_tickets_route');

/**
 * Validate that event_id is a positive integer.
 */
function validate_event_id($param): bool {
    return is_numeric($param) && $param > 0;
}

/**
 * Retrieves event-tickets (meaning the ticket template used by an event to accept bookings)
 */
function get_event_tickets(WP_REST_Request $request)
{
    $event_id = $request->get_param('event_id');
    $results = internal_get_tickets_for_event($event_id);

    return rest_ensure_response([
        "tickets" => $results,
        "count" => count($results),
        "event_id" => $event_id
    ]);
}


function internal_get_tickets_for_event(int $event_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_tickets';

    // Prepare and execute the query safely
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE event_id = %d", $event_id)
    );

    // Unwrap the PHP serialized array so that it'll be easier for consummer code later on to handle it.
    foreach ($results as &$row) {
        if (isset($row->ticket_members_roles)) {
            $roles = @unserialize($row->ticket_members_roles);
            if ($roles !== false && is_array($roles)) {
                $row->ticket_members_roles = $roles;
            }
        }
    }

    return $results;
}

