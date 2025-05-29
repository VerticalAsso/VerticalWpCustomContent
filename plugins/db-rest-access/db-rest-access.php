<?php
/**
 * Plugin Name: Database REST Access
 * Description: A plugin to provide REST API routes for querying custom database tables with an API key. It might also be used to write some data within the database itself
 * Version: 1.0
 * Author: bebenlebricolo (but mainly AI)
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for the plugin
define('DB_REST_ACCESS_VERSION', '1.0');
define('DB_REST_ACCESS_APIKEY_OPT_NAME', 'db_rest_access_apikey');

// Include the settings page class
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

add_action( 'admin_menu', 'add_php_info_page' );

function add_php_info_page() {
    add_submenu_page(
        'tools.php',           // Parent page
        'Xdebug Info',         // Menu title
        'Xdebug Info',         // Page title
        'manage_options',      // user "role"
        'php-info-page',       // page slug
        'php_info_page_body'); // callback function
}

function php_info_page_body() {
    $message = '<h2>No Xdebug enabled</h2>';
    if ( function_exists( 'xdebug_info' ) ) {
        xdebug_info();
    } else {
        echo $message;
    }
}
/**
 * Register custom REST route for event queries with timeframe filtering.
 */
function register_events_rest_route()
{
    register_rest_route('dbrest/v1', '/events', [
        'methods' => 'GET',
        'callback' => 'dbrest_get_events_by_timeframe',
        'permission_callback' => 'db_rest_access_verify_api_key',
        'args' => [
            'timeframe' => [
                'required' => false,
                'validate_callback' => function($param) {
                    return in_array($param, ['week', 'month', 'year', 'future', 'custom']);
                }
            ],
            'start_date' => [
                'required' => false,
                'validate_callback' => function($param) {
                    // Basic format check YYYY-MM-DD
                    return preg_match('/^\d{4}-\d{2}-\d{2}/', $param);
                }
            ],
            'end_date' => [
                'required' => false,
                'validate_callback' => function($param) {
                    // Basic format check YYYY-MM-DD
                    return preg_match('/^\d{4}-\d{2}-\d{2}/', $param);
                }
            ],
            'limit' => [
                'required' => false,
                'default' => 100,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param > 0 && $param <= 500;
                }
            ],
            'offset' => [
                'required' => false,
                'default' => 0,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param >= 0;
                }
            ],
        ]
    ]);

}
add_action('rest_api_init', 'register_events_rest_route');

// Retrieves event-tickets (meaning the ticket template used by an event to accept bookings)
function register_event_tickets_route() {
    register_rest_route('dbrest/v1', '/event-tickets', [
        'methods' => 'GET',
        'callback' => 'dbrest_get_event_tickets',
        'permission_callback' => 'db_rest_access_verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param > 0;
                }
            ]
        ]
    ]);
}
add_action('rest_api_init', 'register_event_tickets_route');

function register_event_bookings_route() {
    register_rest_route('dbrest/v1', '/event-bookings', [
        'methods' => 'GET',
        'callback' => 'dbrest_get_event_bookings',
        'permission_callback' => 'db_rest_access_verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param > 0;
                }
            ],
            'status' => [
                'required' => false,
                'validate_callback' => function($param) {
                    return in_array($param, ['all', 'validated']);
                },
                'default' => 'validated'
            ]
        ]
    ]);
}
add_action('rest_api_init', 'register_event_bookings_route');

/**
 * @api {get} /wp-json/dbrest/v1/events Get paginated events
 * @apiName GetEvents
 * @apiGroup Events
 *
 * @apiDescription
 * Retrieve a paginated list of events, filtered by timeframe and supporting offset/limit.
 *
 * @apiParam {Number} [offset=0] Pagination start offset.
 * @apiParam {Number} [limit=100] Maximum number of events to return.
 * @apiParam {String} [timeframe] Optional filter by event start/end date.
 *
 * @apiSuccess {Object[]} events List of event objects.
 * @apiSuccess {Number} count Number of events returned.
 * @apiSuccess {Number} total_events Total number of matching events.
 * @apiSuccess {Number} total_pages Total number of pages.
 * @apiSuccess {Number} current_page Current page number (1-based).
 *
 * @apiExample {curl} Example usage:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/events?offset=0&limit=100"
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "events": [ ... ],
 *         "count": 100,
 *         "total_events": 245,
 *         "total_pages": 3,
 *         "current_page": 1
 *     }
 */

function dbrest_get_events_by_timeframe(WP_REST_Request $request)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_events'; // Adjust if your table name is different

    $where = "WHERE event_status = '1'";
    $params = [];

    // Timeframe handling
    $timeframe = $request->get_param('timeframe');
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    switch ($timeframe) {
        case 'week':
            // Events starting this week (Monday to Sunday)
            $start = date('Y-m-d', strtotime('monday this week'));
            $end = date('Y-m-d', strtotime('sunday this week 23:59:59'));
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;
        case 'month':
            // Events starting this month
            $start = date('Y-m-01');
            $end = date('Y-m-t');
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;
        case 'year':
            // Events starting this year
            $start = date('Y-01-01');
            $end = date('Y-12-31');
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;
        case 'future':
            // All future events
            $where .= " AND event_start_date >= %s";
            $params[] = $now;
            break;
        case 'custom':
            // Use custom start_date and/or end_date
            $start = $request->get_param('start_date');
            $end = $request->get_param('end_date');
            if ($start) {
                $where .= " AND event_start_date >= %s";
                $params[] = $start . (strlen($start) == 10 ? ' 00:00:00' : '');
            }
            if ($end) {
                $where .= " AND event_start_date <= %s";
                $params[] = $end . (strlen($end) == 10 ? ' 23:59:59' : '');
            }
            break;
        default:
            // If no timeframe provided, show future events
            $where .= " AND event_start_date >= %s";
            $params[] = $now;
            break;
    }

    // Pagination
    $limit = (int) $request->get_param('limit') ?: 100;
    $offset = (int) $request->get_param('offset') ?: 0;

    // Prepare SQL
    $sql = "SELECT * FROM $table $where ORDER BY event_start_date DESC LIMIT %d OFFSET %d";
    $params[] = $limit;
    $params[] = $offset;

    $prepared_sql = $wpdb->prepare($sql, ...$params);
    $results = $wpdb->get_results($prepared_sql);

    // Remove LIMIT/OFFSET for count query
    $count_sql = "SELECT COUNT(*) FROM $table $where";
    $total_events = (int) $wpdb->get_var($wpdb->prepare($count_sql, ...array_slice($params, 0, count($params) - 2)));

    $count = count($results);
    $total_pages = ($limit > 0) ? ceil($total_events / $limit) : 1;
    $current_page = ($limit > 0) ? floor($offset / $limit) + 1 : 1;

    $response = [
        "events" => $results,
        "count" => $count,
        "total_events" => $total_events,
        "total_pages" => $total_pages,
        "current_page" => $current_page
    ];

    return rest_ensure_response($response);
}

// Retrieves event-tickets (meaning the ticket template used by an event to accept bookings)
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

function dbrest_get_event_tickets(WP_REST_Request $request) {
    global $wpdb;
    $event_id = $request->get_param('event_id');
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

    return rest_ensure_response([
        "tickets" => $results,
        "count" => count($results),
        "event_id" => $event_id
    ]);
}

// Retrieves all bookings for a given event
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
function dbrest_get_event_bookings(WP_REST_Request $request) {
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
            $event_id, '1'
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


// Verify the API key
function db_rest_access_verify_api_key(WP_REST_Request $request) {
    // Get the API key from the headers
    $api_key = $request->get_header('X-Api-Key');

    // Retrieve the stored API key from the options table
    $options = get_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
    $stored_api_key = isset($options['api_key']) ? $options['api_key'] : '';

    // Reject queries when ApiKey is not there yet
    if(empty($stored_api_key))
    {
        return new WP_Error(
            'Internal Server Error',
            'Plugin is not yet configured',
            ['status' => 500]
        );
    }

    if ($api_key === $stored_api_key) {
        return true; // Access granted
    }

    // Reject unauthenticated calls
    return new WP_Error(
        'forbidden',
        'Invalid or missing API Key.',
        ['status' => 403]
    );
}

// Activation hook: Set default settings
register_activation_hook(__FILE__, function () {
    if (!get_option(DB_REST_ACCESS_APIKEY_OPT_NAME)) {
        // Generate default ApiKey
        // Borrowed from https://stackoverflow.com/a/37193149/8716917
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
        update_option(DB_REST_ACCESS_APIKEY_OPT_NAME, ['api_key' => $key]);
    }
});

// Deactivation hook: Cleanup settings
register_deactivation_hook(__FILE__, function () {
    delete_option(DB_REST_ACCESS_APIKEY_OPT_NAME);
});