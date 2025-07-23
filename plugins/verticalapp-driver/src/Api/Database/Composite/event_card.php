<?php

namespace VerticalAppDriver\Api\Database\Composite;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

// Required to use internal events retrieval functions
require_once __DIR__ . '/../Core/events.php';
require_once __DIR__ . '/../Core/arg_validation.php';

use VerticalAppDriver\Api\Database\Core as Core;

use WP_REST_Request;
use WP_Error;

/**
 * Registers the /event-card REST API endpoints for retrieving event card data.
 * Includes :
 *   - event-card/by-id => queries a single event card based on an event id.
 *   - event-card/query => custom timeframe query (similar to events/ endpoint)
 * @return void
 */
function register_event_card_route()
{
    register_event_card_by_id_route();
    register_event_card_query_route();
}


/**
 * Registers the /event-card REST API endpoint for retrieving event card data.
 *
 * @api {get} /wp-json/vdriver/v1/event-card/by-id Get event card data
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
function register_event_card_by_id_route()
{
    register_rest_route('vdriver/v1', '/event-card/by-id', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_card',
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
 * Registers the /events REST API endpoint for fetching events.
 *
 * @api {get} /wp-json/vdriver/v1/event-card/query Get event cards using a search query
 * @apiName GetEventCards
 * @apiGroup Events
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve events by timeframe or custom date range, with pagination support.
 *
 * @apiParam {String="week","month","year","future","custom"} [timeframe] Filter by predefined timeframes.
 * @apiParam {String} [start_date] The start date (yyyy-mm-dd, required for custom timeframe).
 * @apiParam {String} [end_date] The end date (yyyy-mm-dd, required for custom timeframe).
 * @apiParam {Number{1-500}} [limit=100] The maximum number of events to return.
 * @apiParam {Number} [offset=0] Offset for pagination.
 *
 * @apiError (Error 400) InvalidParams Parameters are invalid or missing required values.
 *
 * @return void
 */
function register_event_card_query_route()
{
    register_rest_route('vdriver/v1', '/event-card/query', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_event_cards_by_timeframe',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'timeframe' => [
                'required' => false,
                'validate_callback' => function ($param)
                {
                    return in_array($param, ['week', 'month', 'year', 'future', 'custom']);
                }
            ],

            'start_date' => [
                'required' => false,
                'validate_callback' => __NAMESPACE__ . '\\validate_event_date_param'
            ],

            'end_date' => [
                'required' => false,
                'validate_callback' => __NAMESPACE__ . '\\validate_event_date_param'
            ],

            'limit' => [
                'required' => false,
                'default'  => 100,
                'validate_callback' => function ($param)
                {
                    return is_numeric($param) && $param > 0 && $param <= 500;
                }
            ],

            'offset' => [
                'required' => false,
                'default'  => 0,
                'validate_callback' => function ($param)
                {
                    return is_numeric($param) && $param >= 0;
                }
            ],
        ]
    ]);
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

class WpEventCard
{
    public $title;
    public $thumbnail_source;
    public $start_date;
    public $end_date;
    public $start_time;
    public $end_time;
    public $available_seats;
    public $total_seats;
    public $reservations_opened;
    public $excerpt;
    public $event_id;
    public $post_id;
    public $spans_weekend;
    public $whole_day;
}

/**
 * Retrieves the event card data for a given event.
 *
 * @param int $event_id The ID of the event.
 * @return ?WpEventCard Event card details or error.
 *
 */
function internal_get_event_card(int $event_id) : ?WpEventCard
{
    $event_record = internal_get_single_event_record($event_id);

    if (!$event_record || !isset($event_record['post_id'])) {
        return null;
    }

    $post_id = (int) $event_record['post_id'];
    $post_metadata = Core\internal_get_postmeta($post_id);
    $thumbnail_id = isset($post_metadata['_thumbnail_id']) ? (int) $post_metadata['_thumbnail_id'] : null;

    // Happens with half-cleared database
    if($thumbnail_id == null)
    {
        $thumbnail_source = null;
    }
    else
    {
        $thumbnail_source = internal_get_event_thumbnail($thumbnail_id);
    }

    $post_content = Core\internal_get_post_content($post_id);
    $excerpt = $post_content && isset($post_content['post_excerpt']) ? $post_content['post_excerpt'] : '';

    $start_date = $event_record['event_start_date'] ?? null;
    $end_date   = $event_record['event_end_date'] ?? null;
    $start_time = $event_record['event_start_time'] ?? null;
    $end_time   = $event_record['event_end_time'] ?? null;

    $start_time_fmt = $start_time ? substr($start_time, 0, 5) : null;
    $end_time_fmt   = $end_time   ? substr($end_time, 0, 5)   : null;

    $tickets_template_for_event = Core\internal_get_tickets_for_event($event_id);
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

    $event_card = new WpEventCard();
    $event_card->title = $title;
    $event_card->thumbnail_source = $thumbnail_source;
    $event_card->start_date = $start_date;
    $event_card->end_date = $end_date;
    $event_card->start_time = $start_time_fmt;
    $event_card->end_time = $end_time_fmt;
    $event_card->available_seats = $available_seats;
    $event_card->total_seats = $total_seats;
    $event_card->reservations_opened = $reservations_opened;
    $event_card->excerpt = $excerpt;
    $event_card->event_id = $event_id;
    $event_card->post_id = $post_id;
    $event_card->spans_weekend = $spans_weekend;
    $event_card->whole_day = $whole_day;

    return $event_card;
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
        $thumbnail_post = Core\internal_get_post_content($thumbnail_id);
        if ($thumbnail_post && isset($thumbnail_post['guid'])) {
            $thumbnail_source = $thumbnail_post['guid'];
        }
    }

    return $thumbnail_source;
}





// #########################################################################################################################
// ########################################## Get event card using timeframe query #########################################
// #########################################################################################################################

const DATETIME_REGEX = '/^\d{4}-\d{2}-\d{2}/';

/**
 * Used to sanitize input date (end and start) for an event.
 *
 * @param string $param
 * @return bool
 */
function validate_event_date_param($param)
{
    return is_string($param) && preg_match(DATETIME_REGEX, $param);
}


// Event card query maps an endpoint that accepts the same input query as the /events endpoint but returns a list of higher level constructs (event cards)

/**
 * Callback for the /event-card/query endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_event_cards_by_timeframe(WP_REST_Request $request)
{
    // Timeframe logic
    $query = new Core\EventQuery($request->get_param('timeframe'),
                            $request->get_param('start_date'),
                            $request->get_param('end_date'),
                            $request->get_param('offset'),
                            $request->get_param('limit'));


    $events_list = Core\internal_get_events_by_timeframe($query);
    $event_cards = array();
    foreach($events_list['events'] as $event)
    {
        $id = $event->event_id;
        if($id == null)
        {
            continue;
        }

        $card = internal_get_event_card($id);
        array_push($event_cards, $card);
    }

    $response = [
        'events'        => $event_cards,
        'count'         => $events_list["count"],
        'total_events'  => $events_list["total_events"],
        'total_pages'   => $events_list["total_pages"],
        'current_page'  => $events_list["current_page"],
    ];

    return rest_ensure_response($response);
}
