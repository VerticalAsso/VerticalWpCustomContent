<?php

namespace VerticalAppDriver\Api\Events;

require_once __DIR__ . '/../../Auth/apikey_checking.php';
require_once __DIR__ . '/../Database/Composite/full_event.php';

use VerticalAppDriver\Api\Database\Composite as Composite;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;

function register_events_comment_routes()
{
    register_rest_route('vdriver/v1', '/events/comment', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\Events\post_comment_to_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'event_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'comment' => [
                'required' => true,
                'type' => 'string',
            ],
            'parent_id' => [
                'required' => false,
                'type' => 'integer',
            ],
        ],
    ]);

    register_rest_route('vdriver/v1', '/events/comment/delete', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\Events\delete_comment_from_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'comment_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ],
    ]);

    register_rest_route('vdriver/v1', '/events/comment/delete-all-by-user', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\Events\delete_all_comments_by_user_from_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ],
    ]);

    register_rest_route('vdriver/v1', '/events/comment/delete-all', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\Events\delete_all_comments_from_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'event_id' => [
                'required' => true,
                'type' => 'integer',
            ]
        ],
    ]);

    register_rest_route('vdriver/v1', '/events/comment/update', [
        'methods' => 'POST',
        'callback' => 'VerticalAppDriver\Api\Events\update_comment_on_event',
        'permission_callback' => '\\VerticalAppDriver\\Auth\\verify_api_key',
        'args' => [
            'comment_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'user_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'comment' => [
                'required' => true,
                'type' => 'string',
            ],
        ],
    ]);
}

/**
 * @brief deletes a comment from an event.
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 */
function delete_comment_from_event(WP_REST_Request $request)
{
    $comment_id = $request['comment_id'];
    $user_id = $request['user_id'];

    // Validate user existence
    $user = get_user_by('ID', $user_id);
    if (!$user)
    {
        return new WP_Error('invalid_user', 'User not found', array('status' => 404));
    }

    $comment = get_comment($comment_id);
    if (!$comment)
    {
        return new WP_Error('invalid_comment', 'Comment not found', array('status' => 404));
    }

    if (!user_can($user_id, 'delete_comment', $comment_id))
    {
        return new WP_Error('permission_denied', 'You do not have permission to delete this comment', array('status' => 403));
    }
    $success = wp_delete_comment($comment_id, true);
    if(!$success)
    {
        return new WP_Error('deletion_failed', 'Failed to delete comment', array('status' => 500));
    }

    return new WP_REST_Response("Comment deleted", 200);
}


/**
 * @brief deletes all comment made by a user from an event.
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 */
function delete_all_comments_by_user_from_event(WP_REST_Request $request)
{
    $event_id = $request['event_id'];
    $user_id = $request['user_id'];

    // Validate user existence
    $user = get_user_by('ID', $user_id);
    if (!$user)
    {
        return new WP_Error('invalid_user', 'User not found', array('status' => 404));
    }


    $full_event = Composite\internal_get_full_event($event_id);
    if ($full_event instanceof WP_REST_Response && $full_event->get_status() === 404)
    {
        return new WP_Error('invalid_event', 'Event not found', array('status' => 404));
    }

    $all_comments = $full_event->comments;
    if (empty($all_comments))
    {
        return new WP_REST_Response("No comments to delete", 200);
    }

    // Delete all comments made by this user on this event
    $failed_deletions = 0;
    foreach ($all_comments as $comment)
    {
        if ((int) $comment['user_id'] == $user_id) // Debug line
        {
            $comment_id = $comment['comment_id'];

            // Ignore errors for individual deletions
            $success = wp_delete_comment($comment_id, true);
            if (!$success) $failed_deletions++;
        }
    }

    return new WP_REST_Response("All comments by user deleted", 200);
}

/**
 * @brief deletes all comments from an event.
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 */
function delete_all_comments_from_event(WP_REST_Request $request)
{
    $event_id = $request['event_id'];

    $full_event = Composite\internal_get_full_event($event_id);
    if ($full_event instanceof WP_REST_Response && $full_event->get_status() === 404)
    {
        return new WP_Error('invalid_event', 'Event not found', array('status' => 404));
    }

    $all_comments = $full_event->comments;
    if (empty($all_comments))
    {
        return new WP_REST_Response("No comments to delete", 200);
    }

    // Delete all comments made by this user on this event
    foreach ($all_comments as $comment)
    {
        $comment_id = $comment['comment_id'];
        wp_delete_comment($comment_id, true);
    }

    return new WP_REST_Response("All comments deleted for event.", 200);
}


/**
 * Updates a comment on an event.
 *
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 */
function update_comment_on_event(WP_REST_Request $request)
{
    $comment_id = $request['comment_id'];
    $user_id = $request['user_id'];
    $comment_content = $request['comment'];

    $user_agent = $request->get_header('User-Agent') ?? 'Unknown';

    // Validate user existence
    $user = get_user_by('ID', $user_id);
    if (!$user)
    {
        return new WP_Error('invalid_user', 'User not found', array('status' => 404));
    }

    if (!user_can($user_id, 'edit_comment', $comment_id))
    {
        return new WP_Error('permission_denied', 'You do not have permission to edit this comment', array('status' => 403));
    }

    $success = wp_update_comment([
        'comment_ID' => $comment_id,
        'comment_content' => $comment_content,
        "comment_agent" => $user_agent
    ]);

    if (!$success)
    {
        return new WP_Error('update_failed', 'Failed to update comment', array('status' => 500));
    }

    $comment = get_comment($comment_id); // Refresh comment data if needed
    $result = [
        'success' => true,
        'comment_id' => $comment_id,
        'message' => 'Comment updated successfully',
        'comment_content' => $comment->comment_content
    ];
    return new WP_REST_Response($result, 200);
}

/**
 * Posts a comment to an event.
 *
 * @param WP_REST_Request $request The REST request object containing parameters.
 * @return WP_REST_Response|WP_Error Success message or WP_Error on failure.
 */
function post_comment_to_event(WP_REST_Request $request)
{
    $user_id = $request['user_id'];
    $event_id = $request['event_id'];
    $comment_content = $request['comment'];
    $parent_id = $request->get_param('parent_id', 0);

    // Validate user existence
    $user = get_user_by('ID', $user_id);
    if (!$user)
    {
        return new WP_Error('invalid_user', 'User not found', array('status' => 404));
    }

    // Validate event existence
    $event = em_get_event($event_id);
    if (!$event)
    {
        return new WP_Error('invalid_event', 'Event not found', array('status' => 404));
    }

    // Validate comment content
    $comment_content = sanitize_textarea_field($comment_content);

    // Prepare comment data
    $comment_data = [
        'comment_post_ID' => $event->post_id, // Assuming the event is linked to a post
        'comment_author' => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_content' => $comment_content,
        'user_id' => $user_id,
        'comment_approved' => 1, // Auto-approve for simplicity; adjust as needed
        'comment_parent' => $parent_id ?? 0,
        'comment_agent' => $request->get_header('User-Agent') ?? 'Unknown',
        'comment_type' => 'comment', // Custom comment type for event comments
    ];

    // Insert the comment
    $comment_id = wp_insert_comment($comment_data);
    if (is_wp_error($comment_id))
    {
        return new WP_Error('comment_failed', 'Failed to post comment', array('status' => 500));
    }

    $result = [
        'success' => true,
        'comment_id' => $comment_id,
        'message' => 'Comment posted successfully',
        'comment_data' => $comment_data
    ];
    return new WP_REST_Response($result, 200);
}
