<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

use WP_REST_Request;
use WP_REST_Response;

// Prevent direct access
if (! defined('ABSPATH'))
{
    exit;
}

/**
 * Registers the /usermeta REST API endpoint for retrieving user metadata by user ID.
 *
 * @api {get} /wp-json/vdriver/v1/usermeta Get user metadata by ID
 * @apiName GetUserMeta
 * @apiGroup Users
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve selected user meta key/value pairs for a specific user by their database ID.
 *
 * @apiParam {Number} user_id The ID of the user (required).
 *
 * @return void
 */
function register_user_metadata_route()
{
    register_rest_route('vdriver/v1', '/usermeta', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user_metadata',
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
 * Callback for the /usermeta endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_user_metadata(WP_REST_Request $request)
{
    $user_id = $request->get_param('user_id');

    $results = internal_get_user_metadata($user_id);
    if ($results == null)
    {
        return new WP_REST_Response("Could not find any metadata record matching requested user_id.", 404);
    }

    return rest_ensure_response($results);
}

class UserMetadata
{
    public ?array $_application_passwords;
    public ?string $_um_last_login;
    public ?string $account_status;
    public ?string $adresse;
    public ?string $birth_date;
    public ?string $code_postal;
    // public ?string $description;
    public ?string $first_name;
    public ?string $full_name;
    public ?string $last_name;
    public ?string $mobile_number;
    public ?string $nickname;
    public ?string $profile_photo;
    // public ?string $synced_gravatar_hashed_id;
    public ?array $um_member_directory_data;
    public ?array $v34a_capabilities;
    public ?string $v34a_user_level;
    public ?string $ville;
}


/**
 * Returns a JSON object with selected user meta keys. For missing keys, sets the value to null.
 *
 * @param int $user_id The ID of the user.
 * @return UserMetadata|null Associative array of user meta.
 */
function internal_get_user_metadata(int $user_id): UserMetadata | null
{
    global $wpdb;
    $table = $wpdb->prefix . 'usermeta';
    $sql_request = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE user_id = %d", $user_id);

    $results = $wpdb->get_results($sql_request, ARRAY_A);

    // The JSON template keys and structures to enforce
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

    // Build the final array based on the template
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

    if (empty($results))
    {
        return null;
    }

    $user_metadata = new UserMetadata();
    foreach ($output as $key => $value)
    {
        $user_metadata->$key = $value;
    }

    return $user_metadata;
}
