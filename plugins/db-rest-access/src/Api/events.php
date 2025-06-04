<?php

namespace DbRestAccess\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';

use WP_REST_Request;

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

/**
 * Registers the /events REST API endpoint for fetching events.
 *
 * @api {get} /wp-json/dbrest/v1/events Get events
 * @apiName GetEvents
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
function register_events_route()
{
    register_rest_route('dbrest/v1', '/events', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_events_by_timeframe',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
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
 * Callback for the /events endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_events_by_timeframe(WP_REST_Request $request)
{
    global $wpdb;
    $table = $wpdb->prefix . 'em_events';

    $where = "WHERE event_status = '1'";
    $params = [];

    // Timeframe logic
    $timeframe = $request->get_param('timeframe');
    $now = date('Y-m-d H:i:s');

    switch ($timeframe)
    {
        case 'week':
            $start = date('Y-m-d', strtotime('monday this week'));
            $end   = date('Y-m-d', strtotime('sunday this week 23:59:59'));
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;

        case 'month':
            $start = date('Y-m-01');
            $end   = date('Y-m-t');
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;

        case 'year':
            $start = date('Y-01-01');
            $end   = date('Y-12-31');
            $where .= " AND event_start_date >= %s AND event_start_date <= %s";
            $params[] = $start . ' 00:00:00';
            $params[] = $end . ' 23:59:59';
            break;

        case 'future':
            $where .= " AND event_start_date >= %s";
            $params[] = $now;
            break;

        case 'custom':
            $start = $request->get_param('start_date');
            $end   = $request->get_param('end_date');
            if ($start)
            {
                $where .= " AND event_start_date >= %s";
                $params[] = $start . (strlen($start) == 10 ? ' 00:00:00' : '');
            }
            if ($end)
            {
                $where .= " AND event_start_date <= %s";
                $params[] = $end . (strlen($end) == 10 ? ' 23:59:59' : '');
            }
            break;

        default:
            $where .= " AND event_start_date >= %s";
            $params[] = $now;
            break;
    }

    // Pagination
    $limit  = (int) $request->get_param('limit') ?: 100;
    $offset = (int) $request->get_param('offset') ?: 0;

    $sql = "SELECT * FROM $table $where ORDER BY event_start_date DESC LIMIT %d OFFSET %d";
    $params[] = $limit;
    $params[] = $offset;

    $prepared_sql = $wpdb->prepare($sql, ...$params);
    $results = $wpdb->get_results($prepared_sql);

    // Remove LIMIT/OFFSET for count query
    $count_sql = "SELECT COUNT(*) FROM $table $where";
    $total_events = (int) $wpdb->get_var($wpdb->prepare($count_sql, ...array_slice($params, 0, count($params) - 2)));

    $count        = count($results);
    $total_pages  = ($limit > 0) ? ceil($total_events / $limit) : 1;
    $current_page = ($limit > 0) ? floor($offset / $limit) + 1 : 1;

    $response = [
        'events'        => $results,
        'count'         => $count,
        'total_events'  => $total_events,
        'total_pages'   => $total_pages,
        'current_page'  => $current_page,
    ];

    return rest_ensure_response($response);
}