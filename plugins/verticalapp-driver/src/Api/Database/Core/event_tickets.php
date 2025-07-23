<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';
require_once __DIR__ . '/arg_validation.php';

use WP_REST_Request;

/**
 * Registers the /event-tickets REST API endpoint for retrieving ticket templates for a specific event.
 *
 * @api {get} /wp-json/vdriver/v1/event-tickets Get event ticket templates
 * @apiName GetEventTickets
 * @apiGroup Events
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve all ticket templates for a specific event.
 *
 * @apiParam {Number} event_id The ID of the event (required).
 *
 * @apiError (Error 400) InvalidEventId The event_id parameter is required and must be a positive integer.
 *
 * @return void
 */
function register_event_tickets_route()
{
    register_rest_route('vdriver/v1', '/event-tickets', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\get_event_tickets',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => 'VerticalAppDriver\\Api\\Database\\Core\\validate_event_id',
            ]
        ]
    ]);
}

/**
 * Retrieves event tickets (the ticket template used by an event to accept bookings).
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response
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

/**
 * Retrieves the ticket templates for a given event.
 *
 * @param int $event_id The ID of the event.
 * @return array Event ticket templates.
 */
function internal_get_tickets_for_event(int $event_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_tickets';

    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE event_id = %d", $event_id)
    );

    // Unwrap the PHP serialized array for ticket_members_roles if present
    foreach ($results as &$row)
    {
        if (isset($row->ticket_members_roles))
        {
            $roles = @unserialize($row->ticket_members_roles);
            if ($roles !== false && is_array($roles))
            {
                $row->ticket_members_roles = $roles;
            }
        }
    }

    return $results;
}