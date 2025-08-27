<?php

namespace VerticalAppDriver\Api\Database\Core;

require_once __DIR__ . '/../../../Auth/apikey_checking.php';

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Registers the /roles REST API endpoint for retrieving all available roles in the website (from database).
 *
 * @api {get} /wp-json/vdriver/v1/roles Get all available roles in the website (from database)
 * @apiName GetRoles
 * @apiGroup Roles
 * @apiVersion 1.0.0
 *
 * @apiDescription Retrieves all available roles in the website (from database).
 * @return void
 */
function register_roles_routes()
{
    register_rest_route('vdriver/v1', '/roles', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\get_roles',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args'                => [
            'with_meta' => [
                'required'    => false,
                'default'     => false,
                'description' => 'If true, includes Ultimate Member roles meta information (if Ultimate Member plugin is installed)',
                'type'        => 'boolean',
            ],
        ],
    ]);
}

/**
 * Callback for the /roles endpoint.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error List of comments or error.
 */
function get_roles(WP_REST_Request $request)
{
    $with_meta = $request->get_param('with_meta');
    $results = internal_get_roles($with_meta);
    $um_roles = internal_get_ultimate_member_roles($with_meta);

    $output[] = [
        'wordpress_roles' => $results,
        'ultimate_member_roles' => $um_roles
    ];
    return rest_ensure_response($output);
}

/**
 * Retrieves wordpress user roles from the database.
 * @param bool $with_meta Whether to include meta information. Here, "capabilities" are considered meta.
 * @return array List of roles as associative arrays.
 */
function internal_get_roles(bool $with_meta = false)
{
    global $wp_roles;
    $output = [];

    if (!isset($wp_roles))
    {
        $wp_roles = new \WP_Roles();
    }

    foreach ($wp_roles->roles as $role_key => $role_data)
    {
        $data =

        $output[] = [
            'role_key' => $role_key,
            'name'     => $role_data['name'],
            'capabilities' => $with_meta ? $role_data['capabilities'] : null,
            'source'   => 'wordpress'
        ];
    }

    return $output;
}

/**
 * Retrieves user roles from the database.
 * @return array List of roles as associative arrays.
 */
function internal_get_ultimate_member_roles(bool $with_meta = false)
{
    $um_roles = get_option('um_roles');
    $output = [];

    // Um roles is the master key, then each role has its own meta key serialized as an option on wp_options table (um_role_{role_slug}_meta)
    if (is_array($um_roles))
    {
        foreach ($um_roles as $role_slug)
        {
            $meta_key = 'um_role_' . $role_slug . '_meta';
            $meta_value = get_option($meta_key);

            // If not found, skip.
            // Probably a leftover after a role deletion
            if (!$meta_value) continue;

            // Unserialize if needed (sometimes already unserialized)
            if (is_string($meta_value))
            {
                $meta = @unserialize($meta_value);
                if ($meta === false && $meta_value !== 'b:0;')
                {
                    // Not a serialized string, fallback to raw
                    $meta = $meta_value;
                }
            }
            else
            {
                $meta = $meta_value;
            }

            $output[] = [
                'role_key' => $role_slug,
                'name'     => isset($meta['name']) ? $meta['name'] : $role_slug,
                'meta'     => $with_meta ? $meta : null,
            ];
        }
    }

    return $output;
}
