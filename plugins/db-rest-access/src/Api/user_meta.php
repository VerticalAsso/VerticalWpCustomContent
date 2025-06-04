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
 * @apiParam {Number} user_id : The ID of the user (required).
 */
function register_user_metadata_route()
{
    register_rest_route('dbrest/v1', '/usermeta', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user_metadata',
        'permission_callback' => '\\DbRestAccess\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'validate_callback' => __NAMESPACE__ . '\\validate_user_id',
            ]
        ]
    ]);
}

/**
 * Handles the postmeta REST API endpoint.
 */
function get_user_metadata(WP_REST_Request $request)
{
    $user_id = $request->get_param('user_id');

    $results = internal_get_user_metadata($user_id);
    return rest_ensure_response($results);
}

/**
 * Returns a JSON object matching the template structure.
 * For missing keys, sets the value to null.
 * For nested arrays/objects, handles recursion as needed.
 */
function internal_get_user_metadata(int $user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'usermeta';
    $sql_request = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE user_id = %d", $user_id);

    $results = $wpdb->get_results($sql_request, ARRAY_A);

    // The JSON template keys and structures you want to enforce
    $template = [
        "_application_passwords" => null,
        "_um_last_login" => null,
        "account_status" => null,
        "adresse" => null,
        "birth_date" => null,
        "code_postal" => null,
        // "description" => null,
        "first_name" => null,
        "full_name" => null,
        "last_name" => null,
        "mobile_number" => null,
        "nickname" => null,
        "profile_photo" => null,
        // "synced_gravatar_hashed_id" => null,
        "um_member_directory_data" => null,
        "v34a_capabilities" => null,
        "v34a_user_level" => null,
        "ville" => null,
    ];

    // Build meta_dict as before
    $meta_dict = [];
    foreach ($results as $row)
    {
        $key = $row['meta_key'];
        $value = maybe_unserialize($row['meta_value']);
        $meta_dict[$key] = $value;
    }

    // Now, build the final array based on the template
    $output = [];
    foreach ($template as $key => $default)
    {
        if (array_key_exists($key, $meta_dict))
        {
            $output[$key] = $meta_dict[$key];
        }
        else
        {
            $output[$key] = null;
        }
    }

    return $output;
}
