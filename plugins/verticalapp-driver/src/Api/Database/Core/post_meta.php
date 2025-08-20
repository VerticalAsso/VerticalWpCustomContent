<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * Registers the /postmeta REST API endpoint for retrieving post metadata.
 *
 * @api {get} /wp-json/vdriver/v1/postmeta Get post metadata
 * @apiName GetPostmeta
 * @apiGroup Posts
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve all postmeta key/value pairs for a specific post by its database ID.
 *
 * @apiParam {Number} post_id The ID of the post (required).
 *
 * @return void
 */
function register_postmeta_route()
{
    register_rest_route('vdriver/v1', '/postmeta', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_postmeta',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'post_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_post_id',
            ]
        ]
    ]);
}

/**
 * Validate that post_id is a positive integer.
 *
 * @param mixed $param
 * @return bool
 */
function validate_post_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

/**
 * Callback for the /postmeta endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_postmeta(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');

    $results = internal_get_postmeta($post_id);
    return rest_ensure_response($results);
}

/**
 * Returns associative array of meta_key => meta_value (unserialized)
 *
 * @param int $post_id The ID of the post.
 * @return array Associative array of post meta.
 */
function internal_get_postmeta(int $post_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'postmeta';
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT meta_key, meta_value FROM $table WHERE post_id = %d",
            $post_id
        ),
        ARRAY_A
    );

    // Define keys that should be converted to boolean - some of them sometimes are stored as "1"/"0" strings instead
    // of regular booleans (because of plugins upgrades) and that's causing issues for consumers of this API.
    $boolean_keys = [
        '_um_custom_access_settings',
        '_um_access_hide_from_queries',
        '_event_rsvp',
        '_custom_booking_form',
        '_event_all_day',
        '_event_active_status',
        '_um_restrict_by_custom_message'
    ];

    // Same idea for known numeric keys that should be treated as Numbers
    $numeric_keys = [
        '_event_id',
        '_event_spaces',
        '_event_rsvp_spaces',
        '_thumbnail_id',
        '_edit_lock',
        '_edit_last',
        '_location_id',
        '_um_accessible',
        '_um_noaccess_action',
        '_um_access_redirect',
    ];

    $meta_dict = [];
    foreach ($results as $row) {
        $key = $row['meta_key'];
        $value = maybe_unserialize($row['meta_value']);

        // Handle both direct values and nested arrays
        if(is_array($value)) {
            $meta_dict[$key] = convert_types($value, $boolean_keys, $numeric_keys);
        } else {
            if(in_array($key, $boolean_keys)) {
                $meta_dict[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } else if(in_array($key, $numeric_keys)) {
                $meta_dict[$key] = is_numeric($value) ? (int)$value : $value;
            } else {
                $meta_dict[$key] = $value;
            }
        }
    }
    return $meta_dict;
}

/**
 * Recursively converts boolean and numeric values in arrays
 *
 * @param mixed $value The value to convert
 * @param array $boolean_keys Array of keys that should be treated as booleans
 * @param array $numeric_keys Array of keys that should be treated as integers
 * @return mixed The converted value
 */
function convert_types($value, array $boolean_keys, array $numeric_keys)
{
    foreach ($value as $k => $v) {
        if (in_array($k, $boolean_keys)) {
            $value[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
        } else if (in_array($k, $numeric_keys)) {
            $value[$k] = is_numeric($v) ? (int)$v : $v;
        } else if (is_array($v)) {
            $value[$k] = convert_types($v, $boolean_keys, $numeric_keys);
        }
    }
    return $value;
}