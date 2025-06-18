<?php

namespace VerticalAppDriver\Api\Database;

require_once __DIR__ . '/../../Auth/apikey_checking.php';

use WP_REST_Request;
use WP_Error;

/**
 * Registers the /event-card REST API endpoint for retrieving event card data.
 *
 * @api {get} /wp-json/vdriver/v1/event-card Get event card data
 * @apiName GetEventCard
 * @apiGroup Events
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve the card data for an event, including excerpt, thumbnail, event ID, post ID, dates, times, seat info, etc.
 *
 * @apiParam {Number} event_id The ID of the event (required).
 *
 * @apiError (Error 400) InvalidEventId The event_id parameter is required and must be a positive integer.
 * @apiError (Error 404) NotFound No event found for the given event_id.
 *
 * @return void
 */
function register_event_card_route()
{
    register_rest_route('vdriver/v1', '/event-card', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_card',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_event_id',
            ]
        ]
    ]);
}

/**
 * Validate that event_id is a positive integer.
 *
 * @param mixed $param
 * @return bool
 */
function validate_event_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

/**
 * Callback for the /event-card endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error Event card information or error.
 */
function get_event_card(WP_REST_Request $request)
{
    $event_id = (int) $request->get_param('event_id');
    if ($event_id <= 0) {
        return new WP_Error('invalid_event_id', 'The event_id parameter is required and must be a positive integer.', ['status' => 400]);
    }

    $result = internal_get_event_card($event_id);
    return rest_ensure_response($result);
}

/**
 * Retrieves the event card data for a given event.
 *
 * @param int $event_id The ID of the event.
 * @return array|WP_Error Event card details or error.
 *
 */
function internal_get_event_card(int $event_id)
{
    $event_record = internal_get_single_event_record($event_id);

    if (!$event_record || !isset($event_record['post_id'])) {
        return new WP_Error('not_found', 'Event not found', ['status' => 404]);
    }

    $post_id = (int) $event_record['post_id'];
    $post_metadata = internal_get_postmeta($post_id);
    $thumbnail_id = isset($post_metadata['_thumbnail_id']) ? (int) $post_metadata['_thumbnail_id'] : null;

    $thumbnail_source = internal_get_event_thumbnail($thumbnail_id);

    $post_content = internal_get_post_content($post_id);
    $excerpt = $post_content && isset($post_content['post_excerpt']) ? $post_content['post_excerpt'] : '';

    $start_date = $event_record['event_start_date'] ?? null;
    $end_date   = $event_record['event_end_date'] ?? null;
    $start_time = $event_record['event_start_time'] ?? null;
    $end_time   = $event_record['event_end_time'] ?? null;

    $start_time_fmt = $start_time ? substr($start_time, 0, 5) : null;
    $end_time_fmt   = $end_time   ? substr($end_time, 0, 5)   : null;

    $tickets_template_for_event = internal_get_tickets_for_event($event_id);
    $total_seats = 0;
    if (!empty($tickets_template_for_event) && isset($tickets_template_for_event[0]->ticket_spaces)) {
        $total_seats = (int) $tickets_template_for_event[0]->ticket_spaces;
    }

    $available_seats = null;
    if ($total_seats !== null) {
        $available_seats = $total_seats - (int)internal_get_event_booking_count($event_id);
        if ($available_seats < 0) $available_seats = 0;
    }

    $reservations_opened = !empty($event_record['event_rsvp']) && $event_record['event_rsvp'] == 1;
    $title = $event_record['event_name'];

    // Spans weekend logic
    $spans_weekend = false;
    if ($start_date && $end_date) {
        $period = new \DatePeriod(
            new \DateTime($start_date),
            new \DateInterval('P1D'),
            (new \DateTime($end_date))->modify('+1 day')
        );
        foreach ($period as $dt) {
            $weekday = (int)$dt->format('N'); // 6=Saturday, 7=Sunday
            if ($weekday === 6 || $weekday === 7) {
                $spans_weekend = true;
                break;
            }
        }
    }

    // Whole day event
    $whole_day = (
        ($start_time === '00:00:00' || $start_time === null || $start_time === '') &&
        ($end_time === '23:59:59' || $end_time === null || $end_time === '')
    );

    $results = [
        "title"              => $title,
        "thumbnail_source"   => $thumbnail_source,
        "start_date"         => $start_date,
        "end_date"           => $end_date,
        "start_time"         => $start_time_fmt,
        "end_time"           => $end_time_fmt,
        "available_seats"    => $available_seats,
        "total_seats"        => $total_seats,
        "reservations_opened"=> $reservations_opened,
        "excerpt"            => $excerpt,
        "event_id"           => $event_id,
        "post_id"            => $post_id,
        "spans_weekend"      => $spans_weekend,
        "whole_day"          => $whole_day
    ];

    return $results;
}

/**
 * Helper: Count current bookings for an event.
 *
 * @param int $event_id
 * @return int
 */
function internal_get_event_booking_count(int $event_id)
{
    global $wpdb;
    $bookings_table = $wpdb->prefix . 'em_bookings';
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $bookings_table WHERE event_id = %d AND booking_status=1", $event_id);
    return (int) $wpdb->get_var($sql);
}

/**
 * Returns one event row as associative array, or null if not found.
 *
 * @param int $event_id
 * @return array|null
 */
function internal_get_single_event_record(int $event_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_events';
    $event = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE event_id = %d", $event_id),
        ARRAY_A
    );
    return $event ?: null;
}

/**
 * Retrieves event's thumbnail from database.
 *
 * @param int $thumbnail_id
 * @return string|null The URL of the thumbnail or null if not found.
 */
function internal_get_event_thumbnail(int $thumbnail_id)
{
    $thumbnail_source = null;
    if ($thumbnail_id) {
        $thumbnail_post = internal_get_post_content($thumbnail_id);
        if ($thumbnail_post && isset($thumbnail_post['guid'])) {
            $thumbnail_source = $thumbnail_post['guid'];
        }
    }

    return $thumbnail_source;
}