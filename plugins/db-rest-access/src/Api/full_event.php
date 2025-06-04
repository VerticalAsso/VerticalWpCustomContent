<?php

namespace DbRestAccess\Api;

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


function register_full_event_route()
{
    register_rest_route('dbrest/v1', '/full-event', [
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\get_full_event',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
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

// Retrieves all bookings for a given event
function get_full_event(WP_REST_Request $request)
{
    global $wpdb;

    $event_id = $request->get_param('event_id');

    $event_base_data = internal_get_single_event_record($event_id);
    $post_id = $event_base_data['post_id'];
    $post_metadata = internal_get_postmeta($post_id);

    // Event card already contain informations about thumbnail, title, excerpt, etc.
    // Reusing it will allow to retrieve valuable data more easily
    $event_card = internal_get_event_card($event_id);

    $bookings = internal_get_bookings_for_event($event_id);
    $comments = internal_get_comments($post_id);

    // categories
    $location_id = $event_base_data['location_id'];
    $location = internal_get_location($location_id);

    $result = [
        "event_raw" => $event_base_data,
        "event_metadata" => $post_metadata,
        "event_card" => $event_card,
        "bookings" => $bookings,
        "comments" => $comments,
        "location" => $location,
        "categories" => "not implemented"
    ];


    return rest_ensure_response($result);
}
