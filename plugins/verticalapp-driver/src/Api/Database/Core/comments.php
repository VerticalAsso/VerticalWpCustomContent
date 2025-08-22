<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;

/**
 * Registers the /comments REST API endpoint for retrieving comments by post.
 *
 * @api {get} /wp-json/vdriver/v1/comments Get comments for a post
 * @apiName GetComments
 * @apiGroup Comments
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieves all comments for a given post.
 *
 * @apiParam {Number} post_id The ID of the post (required).
 *
 * @apiError (Error 400) MissingPostId The post_id parameter is required.
 * @apiError (Error 404) NoCommentsFound No comments found for the given post_id.
 *
 * @return void
 */
function register_comments_route()
{
    register_rest_route('vdriver/v1', '/comments', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_comments',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args'                => [
            'post_id' => [
                'required'          => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_post_id',
            ]
        ]
    ]);
}

/**
 * Callback for the /comments endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error List of comments or error.
 */
function get_comments(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');

    if (empty($post_id) || !is_numeric($post_id)) {
        return new WP_Error('missing_post_id', 'The post_id parameter is required and must be numeric.', ['status' => 400]);
    }

    $results = internal_get_comments((int)$post_id);

    if (empty($results)) {
        return new WP_Error('no_comments_found', 'No comments found for the given post_id.', ['status' => 404]);
    }

    return rest_ensure_response($results);
}

/**
 * Retrieves comments from the database for the given post ID.
 *
 * @param int $post_id The ID of the post.
 * @return array List of comments as associative arrays.
 */
function internal_get_comments(int $post_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'comments';
    $sql_request = $wpdb->prepare("SELECT * FROM $table WHERE comment_post_ID = %d", $post_id);

    $results = $wpdb->get_results($sql_request, ARRAY_A);

    $output = [];
    foreach ($results as $row) {
        $data = [
            "comment_id" => $row['comment_ID'],
            "post_id"    => $row['comment_post_ID'],
            "content"    => $row['comment_content'],
            "author"     => $row['comment_author'],
            "email"      => $row['comment_author_email'],
            "user_id"    => $row['user_id'],
            "date"       => $row['comment_date'],
            "approved"   => $row['comment_approved'],
            "agent"      => $row['comment_agent'],
            "type"       => $row['comment_type'],
            "parent"     => $row['comment_parent'],
        ];
        array_push($output, $data);
    }

    return $output;
}