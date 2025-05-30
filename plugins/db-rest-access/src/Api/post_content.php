<?php

namespace DbRestAccess\Api;
require_once __DIR__ . '/../Auth/apikey_checking.php';


use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/**
 * @api {get} /wp-json/dbrest/v1/post Get post content by ID
 * @apiName GetPost
 * @apiGroup Posts
 *
 * @apiDescription
 * Retrieve all data for a specific post (event, page, etc.) by its database ID.
 *
 * @apiParam {Number} post_id The ID of the post (required).
 *
 * @apiSuccess {Object} post All columns from the posts table for the given post.
 * @apiSuccess {Number} post_id The post ID requested.
 *
 * @apiExample {curl} Example usage:
 *     curl -X GET "https://yourdomain.com/wp-json/dbrest/v1/post?post_id=17258"
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "post": {
 *             "ID": "17258",
 *             "post_author": "2",
 *             "post_date": "2024-05-01 10:00:00",
 *             "post_content": "...",
 *             "post_title": "Your Post Title",
 *             // ...all other columns
 *         },
 *         "post_id": "17258"
 *     }
 */
function register_post_content_route()
{
    register_rest_route('dbrest/v1', '/post', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_post_content',
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
 * Handles the post content REST API endpoint.
 */
function get_post_content(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');
    $result = internal_get_post_content($post_id);
    return rest_ensure_response($result);
}

/**
 * Returns post row as associative array, or null
 */
function internal_get_post_content(int $post_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'posts';
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE ID = %d",
            $post_id
        ),
        ARRAY_A
    );
    return $row ?: null;
}