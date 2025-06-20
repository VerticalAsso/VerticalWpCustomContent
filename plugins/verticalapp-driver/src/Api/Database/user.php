<?php

namespace VerticalAppDriver\Api\Database;

require_once __DIR__ . '/../../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * Registers the /user REST API endpoint for retrieving user data by user ID.
 *
 * @api {get} /wp-json/vdriver/v1/user Get user data from database
 * @apiName GetUser
 * @apiGroup Users
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieves user data from the vertical database, with minimal modifications for compatibility purposes.
 *
 * @apiParam {Number} user_id The ID of the user (required).
 *
 * @return void
 */
function register_user_route()
{
    register_rest_route('vdriver/v1', '/user', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_user_id',
            ]
        ]
    ]);
}

/**
 * Validate that user_id is a positive integer.
 *
 * @param mixed $param
 * @return bool
 */
function validate_user_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

/**
 * Callback for the /user endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_user(WP_REST_Request $request)
{
    $user_id = $request->get_param('user_id');

    $results = internal_get_user_data($user_id);
    return rest_ensure_response($results);
}

/**
 * Returns user data as an associative array, without exposing sensitive fields.
 *
 * @param int $user_id The ID of the user.
 * @return array|null User data or null if not found.
 */
function internal_get_user_data(int $user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'users';
    $sql_request = $wpdb->prepare("SELECT * FROM $table WHERE ID = %d", $user_id);
    $data = $wpdb->get_results($sql_request);

    if (empty($data))
    {
        return null;
    }

    // Using the first result (there should only be one hit anyway)
    $data = $data[0];

    // Dropping the user_pass field, I don't want this to be exposed.
    $results = [
        'id' => $data->ID,
        'user_login' => $data->user_login,
        'user_nicename' => $data->user_nicename,
        'user_email' => $data->user_email,
        //'user_url' => $data->user_url,            // Usually left blank
        'user_registered' => $data->user_registered,
        //'user_status' => $data->user_status,      // Always set to 0, seems unused
        'display_name' => $data->display_name,
        //'user_adresse' => $data->user_adresse,    // Usually left blank
        //'user_cp' => $data->user_cp,              // Usually left blank
        //'user_ville' => $data->user_ville         // Usually left blank
    ];
    return $results;
}