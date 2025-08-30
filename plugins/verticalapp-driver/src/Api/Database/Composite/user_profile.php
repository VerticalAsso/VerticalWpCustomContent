<?php

namespace VerticalAppDriver\Api\Database\Composite;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

// Required to use internal events retrieval functions
require_once __DIR__ . '/../Core/user.php';
require_once __DIR__ . '/../Core/user_meta.php';

use VerticalAppDriver\Api\Database\Core as Core;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;
use VerticalAppDriver\Api\Database\Core\UserMetadata;

/**
 * Registers the /user-profile REST API endpoints for retrieving user profile data.
 * Includes :
 *   - user-profile/by-id => Retrieve a complete user profile by its ID
 *   - user-profile/picture => downloads the profile picture by user ID
 * @return void
 */
function register_user_profile_route()
{
    register_user_profile_by_id_route();
    register_user_profile_by_email_route();
    register_user_profile_get_picture_by_id();
}


/**
 * Registers the /user-profile REST API endpoint for retrieving user profile data.
 *
 * @api {get} /wp-json/vdriver/v1/user-profile/by-id Get user profile data by user ID
 * @apiName GetUserProfileById
 * @apiGroup UserProfile
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve the profile data for a user by their ID.
 *
 * @apiParam {Number} user_id The ID of the user (required).
 *
 * @apiError (Error 400) InvalidUserId The user_id parameter is required and must be a positive integer.
 * @apiError (Error 404) NotFound No user found for the given user_id.
 *
 * @return void
 */
function register_user_profile_by_id_route()
{
    register_rest_route('vdriver/v1', '/user-profile/by-id', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user_profile',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'validate_callback' => 'VerticalAppDriver\\Api\\Database\\Core\\validate_user_id',
            ]
        ]
    ]);
}

/**
 * Registers the /user-profile REST API endpoint for retrieving user profile data from their email.
 * This is a convenience endpoint that first looks up the user ID from the email, then calls the same logic as /user-profile/by-id.
 *
 * @api {get} /wp-json/vdriver/v1/user-profile/by-id Get user profile data by user ID
 * @apiName GetUserProfileById
 * @apiGroup UserProfile
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve the profile data for a user by their ID.
 *
 * @apiParam {Number} user_id The ID of the user (required).
 *
 * @apiError (Error 400) InvalidUserId The user_id parameter is required and must be a positive integer.
 * @apiError (Error 404) NotFound No user found for the given user_id.
 *
 * @return void
 */
function register_user_profile_by_email_route()
{
    register_rest_route('vdriver/v1', '/user-profile/by-email', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user_profile_by_email',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'email' => [
                'required' => true,
                'validate_callback' => 'VerticalAppDriver\\Api\\Database\\Core\\validate_email',
            ]
        ]
    ]);
}


/**
 * Registers the /user-profile REST API endpoint for retrieving user profile picture.
 *
 * @api {get} /wp-json/vdriver/v1/user-profile/by-id Get user profile data by user ID
 * @apiName GetUserProfileById
 * @apiGroup UserProfile
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieve the profile data for a user by their ID.
 *
 * @apiParam {Number} user_id The ID of the user (required).
 *
 * @apiError (Error 400) InvalidUserId The user_id parameter is required and must be a positive integer.
 * @apiError (Error 404) NotFound No user found for the given user_id.
 *
 * @return void
 */
function register_user_profile_get_picture_by_id()
{
    register_rest_route('vdriver/v1', '/user-profile/picture', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_user_profile_picture',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'validate_callback' => 'VerticalAppDriver\\Api\\Database\\Core\\validate_user_id',
            ]
        ]
    ]);
}



// To be verified against Ultimate Member / WordPress account statuses
enum AccountStatus: string
{
    case Approved = 'approved';                         // Default state for active accounts
    case PendingAdmin = 'awaiting_admin_review';        // Waiting for admin approval
    case PendingEmail = 'awaiting_email_confirmation';  // Waiting for email verification
    case Rejected = 'rejected';                         // Account rejected by admin
    case Inactive = 'inactive';                         // Account deactivated
    case Disabled = 'disabled';                         // Account disabled (usually for violations)
}

class UmMemberDirectoryData
{
    public AccountStatus $status;
    public bool $hide_in_members;
    public bool $has_profile_picture;
    public bool $has_cover_picture;
    public bool $verified;

    public function __construct(
        AccountStatus $status = AccountStatus::Inactive,
        bool $hide_in_members = false,
        bool $has_profile_picture = false,
        bool $has_cover_picture = false,
        bool $verified = false
    )
    {
        $this->status = $status;
        $this->hide_in_members = $hide_in_members;
        $this->has_profile_picture = $has_profile_picture;
        $this->has_cover_picture = $has_cover_picture;
        $this->verified = $verified;
    }
}

/**
 * Represents a complete user profile including WordPress and Ultimate Member data
 */
class UserProfile
{
    // WordPress core user data
    public int $id;
    public string $login;
    public string $nice_name;
    public string $email;
    public string $registered;
    public string $display_name;

    // User meta data
    public ?string $last_login;
    public AccountStatus $status;
    public ?string $address;
    public ?string $date_of_birth;
    public ?string $postal_code;
    public ?string $city;
    public ?string $phone;
    public string $first_name;
    public string $last_name;
    public string $full_name;
    public ?string $nickname;

    // Additional data
    public UmMemberDirectoryData $directory_data;
    public array $roles;  // From wp_capabilities
    public int $user_level;

    public function __construct()
    {
        $this->directory_data = new UmMemberDirectoryData();
        $this->roles = [];
        $this->user_level = 0;
    }
}




/**
 * Callback for the /user-profile/by-id endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error User profile or error.
 */
function get_user_profile(WP_REST_Request $request)
{
    $user_id = (int) $request->get_param('user_id');
    if ($user_id <= 0)
    {
        return new WP_Error('invalid_user_id', 'The user_id parameter is required and must be a positive integer.', ['status' => 400]);
    }

    $result = internal_get_user_profile($user_id);
    if ($result == null)
    {
        return new WP_REST_Response("Requested user id does not exist", 404);
    }
    return rest_ensure_response($result);
}

/**
 * Callback for the /user-profile/by-email endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error User profile or error.
 */
function get_user_profile_by_email(WP_REST_Request $request)
{
    $email = (string) $request->get_param('email');
    $user = get_user_by('email', $email);
    if ($user === false)
    {
        return new WP_REST_Response("Could not find any user record matching requested email.", 404);
    }

    $user_id = (int) $user->ID;
    $result = internal_get_user_profile($user_id);
    if ($result == null)
    {
        return new WP_REST_Response("Requested user id does not exist", 404);
    }
    return rest_ensure_response($result);
}

function internal_convert_metadata_um_directory(UserMetadata $meta) : UmMemberDirectoryData | null
{
    if ($meta->um_member_directory_data != null && is_array($meta->um_member_directory_data))
    {
        $um_directory_data = new UmMemberDirectoryData();

        // Mapping Ultimate Member directory data fields to the UmMemberDirectoryData class
        if(isset($meta->um_member_directory_data['account_status']))
        {
            $um_directory_data->status = AccountStatus::tryFrom($meta->um_member_directory_data['account_status']);
        }
        else
        {
            $um_directory_data->status = AccountStatus::Inactive;
        }
        $um_directory_data->hide_in_members = $meta->um_member_directory_data['hide_in_members'] ?? false;
        $um_directory_data->has_profile_picture = $meta->um_member_directory_data['profile_photo'] ?? false;
        $um_directory_data->has_cover_picture = $meta->um_member_directory_data['cover_photo'] ?? false;
        $um_directory_data->verified = $meta->um_member_directory_data['verified'] ?? false;

        return $um_directory_data;
    }

    return null;
}

/**
 * Retrieves the user profile by user ID.
 *
 * @param int $user_id The ID of the user.
 * @return ?UserProfile User profile or null if not found.
 *
 */
function internal_get_user_profile(int $user_id): ?UserProfile
{
    $user_data = Core\internal_get_user_data($user_id);
    if ($user_data == null)
    {
        return null;
    }

    $user_meta = Core\internal_get_user_metadata($user_id);
    if ($user_meta == null)
    {
        return null;
    }


    // Populate the UserProfile object
    $user_profile = new UserProfile();
    $user_profile->id = $user_data->id;
    $user_profile->login = $user_data->user_login;
    $user_profile->nice_name = $user_data->user_nicename;
    $user_profile->email = $user_data->user_email;
    $user_profile->registered = $user_data->user_registered;
    $user_profile->display_name = $user_data->display_name;

    // From user metadata
    $user_profile->last_login = $user_meta->_um_last_login ?? null;
    $user_profile->status = AccountStatus::tryFrom($user_meta->account_status) ?? AccountStatus::Inactive;

    // Conversion from French to English field names and normalization - translating field names where applicable
    $user_profile->address = $user_meta->adresse ?? null;
    $user_profile->date_of_birth = $user_meta->birth_date ?? null;
    $user_profile->postal_code = $user_meta->code_postal ?? null;
    $user_profile->city = $user_meta->ville ?? null;
    $user_profile->phone = $user_meta->mobile_number ?? null;
    $user_profile->first_name = $user_meta->first_name ?? '';
    $user_profile->last_name = $user_meta->last_name ?? '';
    $user_profile->full_name = $user_meta->full_name ?? '';
    $user_profile->nickname = $user_meta->nickname ?? null;
    $user_profile->user_level = $user_meta->v34a_user_level ?? null;


    // Directory data
    $user_profile->directory_data = internal_convert_metadata_um_directory($user_meta);

    // Roles
    if ($user_meta->v34a_capabilities != null)
    {
        $capabilities = maybe_unserialize($user_meta->v34a_capabilities);
        if (is_array($capabilities))
        {
            $user_profile->roles = array_keys(array_filter($capabilities, function ($val)
            {
                return $val === true;
            }));
        }
    }

    return $user_profile;
}


/**
 * Callback for the /user-profile/picture endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error User profile picture or error.
 */
function get_user_profile_picture(WP_REST_Request $request)
{
    $user_id = (int) $request->get_param('user_id');
    if ($user_id <= 0)
    {
        return new WP_Error('invalid_user_id', 'The user_id parameter is required and must be a positive integer.', ['status' => 400]);
    }

    $user_meta = Core\internal_get_user_metadata($user_id);
    if ($user_meta == null)
    {
        return new WP_REST_Response("Requested user id does not exist", 404);
    }

    $um_directory_data = internal_convert_metadata_um_directory($user_meta);

    // This use case should be covered by whatever consumer is calling this endpoint.
    // WordPress website will display a placeholder image for that profile.
    if ($um_directory_data == null || !$um_directory_data->has_profile_picture)
    {
        return new WP_REST_Response("User does not have a profile picture.", 404);
    }

    $photo_name = $user_meta->profile_photo;
    // $photo_url = UM()->uploader()->get_upload_user_base_url($user_id) . "/" . $photo_name;

    // Get the actual file path instead of URL
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/ultimatemember/' . $user_id . '/' . $photo_name;

    // Get MIME type
    $mime_type = mime_content_type($file_path);
    if (!$mime_type) {
        $mime_type = 'application/octet-stream';
    }

    $filename = basename($file_path);

    // Store file info to pass to the closure
    $GLOBALS['myplugin_profile_picture'] = [
        'file_path' => $file_path,
        'mime_type' => $mime_type,
        'filename'  => $filename,
    ];

    add_filter('rest_pre_serve_request', function($served, $result, $request, $server) {
        if (!isset($GLOBALS['myplugin_profile_picture'])) return $served;
        $info = $GLOBALS['myplugin_profile_picture'];
        header('Content-Type: ' . $info['mime_type']);
        header('Content-Length: ' . filesize($info['file_path']));
        header('Content-Disposition: inline; filename="' . $info['filename'] . '"');
        readfile($info['file_path']);
        // Prevent WordPress from sending anything else
        return true;
    }, 10, 4);

    // The REST API expects a response, but the filter will handle output
    return new WP_REST_Response(null, 200);
}
