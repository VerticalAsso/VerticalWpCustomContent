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
 * Registers the /post REST API endpoint for retrieving post content by ID.
 *
 * @api {get} /wp-json/dbrest/v1/post Get post content by ID
 * @apiName GetPost
 * @apiGroup Posts
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve all data for a specific post by its database ID.
 *
 * @apiParam {Number} post_id The ID of the post (required).
 *
 * @return void
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
 * Callback for the /post endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_post_content(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');
    $result = internal_get_post_content($post_id);
    return rest_ensure_response($result);
}

/**
 * Retrieves the post row as an associative array, or null if not found.
 *
 * @param int $post_id The ID of the post.
 * @return array|null The post data or null.
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
