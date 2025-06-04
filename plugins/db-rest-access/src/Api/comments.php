<?php

namespace DbRestAccess\Api;

require_once __DIR__ . '/../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * @api {get} /wp-json/dbrest/v1/user Get user data from database
 *
 * @apiDescription
 * Retrieves user data from vertical database, with minimal modifications for compatibility purposes.
 *
 * @apiParam {Number} post_id : The ID of the user (required).
 */
function register_comments_route()
{
    register_rest_route('dbrest/v1', '/comments', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_comments',
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
 * Handles the postmeta REST API endpoint.
 */
function get_comments(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');

    $results = internal_get_comments($post_id);
    return rest_ensure_response($results);
}

/**
 * Returns a JSON object matching the template structure.
 * For missing keys, sets the value to null.
 * For nested arrays/objects, handles recursion as needed.
 */
function internal_get_comments(int $post_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'comments';
    $sql_request = $wpdb->prepare("SELECT * FROM $table WHERE comment_post_ID = %d", $post_id);

    $results = $wpdb->get_results($sql_request, ARRAY_A);

    // The JSON template keys and structures you want to enforce
    $output = [];
    foreach ($results as $row)
    {
        $data = [
            "comment_id" => $row['comment_ID'],
            "post_id" => $row['comment_post_ID'],
            "author" => $row['comment_author'],
            "email" => $row['comment_author_email'],
            "user_id" => $row['user_id'],
            "date" => $row['comment_date'],
            "approved" => $row['comment_approved'],
            "agent" => $row['comment_agent'],
            "type" => $row['comment_type'],
            "parent" => $row['comment_parent'],
        ];
        array_push($output, $data);
    }

    return $output;
}
