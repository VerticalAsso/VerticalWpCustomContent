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

class EventMetadata
{
    public UmContentRestrictions | null $um_content_restrictions;
    public array $misc_meta; // To capture any other meta keys that might be present
}

class UmAccessRole
{
    public string $role_name;
    public bool $allowed;   // Either set to "1" or "0" in DB
}

/**
 * @brief Represents the structure of User Meta related to content restrictions.
 */
class UmContentRestrictions
{
    public bool $custom_access_settings;

    /**
     * See /var/www/html/wp-content/plugins/ultimate-member/includes/admin/core/class-admin-metabox.php : line 446
     * - '0' => 'Everyone'
     * - '1' => 'Logged out users'
     * - '2' => 'Logged in users'
     */
    public int $accessible;

    /**
     *  @var UmAccessRole[] $access_roles List of roles with access permissions
     */
    public array $access_roles;
    public string | null $restrict_custom_message;
    public array $misc_meta; // To capture any other meta keys that might be present

    /**
     * Creates a UmContentRestrictions object from raw metadata
     *
     * @param array|null $root_um_content Raw um_content_restriction metadata
     * @return UmContentRestrictions|null
     */
    public static function create(?array $root_um_content): ?UmContentRestrictions
    {
        if (!$root_um_content)
        {
            return null;
        }

        $restrictions = new self();
        $restrictions->misc_meta = [];
        $restrictions->custom_access_settings = $root_um_content['_um_custom_access_settings'] ?? false;
        $restrictions->accessible = $root_um_content['_um_accessible'];
        $restrictions->access_roles = [];

        if (isset($root_um_content['_um_access_roles']) && is_array($root_um_content['_um_access_roles']))
        {
            foreach ($root_um_content['_um_access_roles'] as $role_name => $allowed)
            {
                $role = new UmAccessRole();
                $role->role_name = $role_name;
                $role->allowed = filter_var($allowed, FILTER_VALIDATE_BOOLEAN);
                $restrictions->access_roles[] = $role;
            }
        }

        $restrictions->restrict_custom_message = $root_um_content['_um_restrict_custom_message'];

        // Capture miscellaneous meta keys
        foreach ($root_um_content as $k => $v)
        {
            if (!in_array($k, ['_um_custom_access_settings', '_um_accessible', '_um_access_roles', '_um_restrict_custom_message']))
            {
                $restrictions->misc_meta[$k] = $v;
            }
        }

        return $restrictions;
    }
}

/**
 * Returns associative array of meta_key => meta_value (unserialized)
 *
 * @param int $post_id The ID of the post.
 * @return EventMetadata|WP_REST_Response The post metadata or a WP_REST_Response with a 404 status if not found.
 */
function internal_get_postmeta(int $post_id) : EventMetadata | WP_REST_Response
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

    if ($results == null)
    {
        return new WP_REST_Response("Could not find any metadata record matching requested post_id.", 404);
    }

    // Define keys that should be converted to boolean - some of them sometimes are stored as "1"/"0" strings instead
    // of regular booleans (because of plugins upgrades) and that's causing issues for consumers of this API.
    $boolean_keys = [
        '_um_custom_access_settings',
        '_um_access_hide_from_queries',
        '_event_rsvp',
        '_custom_booking_form',
        '_event_all_day',
        '_event_active_status',
        '_um_restrict_by_custom_message'
    ];

    // Same idea for known numeric keys that should be treated as Numbers
    $numeric_keys = [
        '_event_id',
        '_event_spaces',
        '_event_rsvp_spaces',
        '_thumbnail_id',
        '_edit_lock',
        '_edit_last',
        '_location_id',
        '_um_accessible',
        '_um_noaccess_action',
        '_um_access_redirect',
    ];

    $meta_dict = [];
    foreach ($results as $row)
    {
        $key = $row['meta_key'];
        $value = maybe_unserialize($row['meta_value']);

        // Handle both direct values and nested arrays
        if (is_array($value))
        {
            $meta_dict[$key] = convert_types($value, $boolean_keys, $numeric_keys);
        }
        else
        {
            if (in_array($key, $boolean_keys))
            {
                $meta_dict[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            else if (in_array($key, $numeric_keys))
            {
                $meta_dict[$key] = is_numeric($value) ? (int)$value : $value;
            }
            else
            {
                $meta_dict[$key] = $value;
            }
        }
    }

    // Convert metadata into structured format
    $converted_meta = new EventMetadata();

    // Extract and assign um_content_restrictions if present
    if (isset($meta_dict['um_content_restriction']))
    {
        $converted_meta->um_content_restrictions = UmContentRestrictions::create($meta_dict['um_content_restriction']);
        unset($meta_dict['um_content_restriction']);
    }
    else
    {
        $converted_meta->um_content_restrictions = null;
    }

    // Assign the rest as misc_meta
    $converted_meta->misc_meta = $meta_dict;

    return $converted_meta;
}

/**
 * Recursively converts boolean and numeric values in arrays
 *
 * @param mixed $value The value to convert
 * @param array $boolean_keys Array of keys that should be treated as booleans
 * @param array $numeric_keys Array of keys that should be treated as integers
 * @return mixed The converted value
 */
function convert_types($value, array $boolean_keys, array $numeric_keys)
{
    foreach ($value as $k => $v)
    {
        if (in_array($k, $boolean_keys))
        {
            $value[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
        }
        else if (in_array($k, $numeric_keys))
        {
            $value[$k] = is_numeric($v) ? (int)$v : $v;
        }
        else if (is_array($v))
        {
            $value[$k] = convert_types($v, $boolean_keys, $numeric_keys);
        }
    }
    return $value;
}
