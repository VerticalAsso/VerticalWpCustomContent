<?php

namespace DbRestAccess\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';


use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * @api {get} /wp-json/dbrest/v1/postmeta Get post metadata
 * @apiName GetPostmeta
 * @apiGroup Posts
 *
 * @apiDescription
 * Retrieve all postmeta key/value pairs for a specific post (e.g., an event).
 *
 * @apiParam {Number} post_id The ID of the post (required).
 *
 * @apiSuccess {Object[]} meta List of meta records (each has meta_id, post_id, meta_key, meta_value).
 * @apiSuccess {Number} count Number of meta records returned.
 * @apiSuccess {Number} post_id The post ID requested.
 *
 * @apiExample {curl} Example usage:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/postmeta?post_id=456"
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "meta": [
 *             {
 *                 "meta_id": 123,
 *                 "post_id": 456,
 *                 "meta_key": "_thumbnail_id",
 *                 "meta_value": "789"
 *             }
 *         ],
 *         "count": 1,
 *         "post_id": 456
 *     }
 */
function register_postmeta_route()
{
    register_rest_route('dbrest/v1', '/postmeta', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_postmeta',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
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
 */
function validate_post_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

/**
 * Handles the postmeta REST API endpoint.
 */
function get_postmeta(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');

    $results = internal_get_postmeta($post_id);
    return rest_ensure_response($results);
}

/**
 * Returns associative array of meta_key => meta_value (unserialized)
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
    $meta_dict = [];
    foreach ($results as $row)
    {
        $key = $row['meta_key'];
        $value = maybe_unserialize($row['meta_value']);
        $meta_dict[$key] = $value;
    }
    return $meta_dict;
}
