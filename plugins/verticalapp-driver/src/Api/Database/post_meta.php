<?php

namespace VerticalAppDriver\Api\Database;

require_once __DIR__ . '/../../Auth/apikey_checking.php';

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
    $meta_dict = [];
    foreach ($results as $row)
    {
        $key = $row['meta_key'];
        $value = maybe_unserialize($row['meta_value']);
        $meta_dict[$key] = $value;
    }
    return $meta_dict;
}