<?php

namespace DbRestAccess\Api;

require_once __DIR__ . '/event_bookings.php';
require_once __DIR__ . '/event_card.php';
require_once __DIR__ . '/event_tickets.php';
require_once __DIR__ . '/post_content.php';
require_once __DIR__ . '/post_meta.php';
require_once __DIR__ . '/events.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/user_meta.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/event_location.php';
require_once __DIR__ . '/full_event.php';

/**
 * @brief registers all REST API routes upon activation.
 * @see dbrest-access.php for rest api init callback registration
 */
function register_all_routes()
{
    register_event_tickets_route();
    register_events_route();
    register_post_content_route();
    register_postmeta_route();
    register_event_bookings_route();
    register_user_route();
    register_user_metadata_route();
    register_comments_route();
    register_event_location_route();

    // Higher level apis (can reconstruct objects)
    register_event_card_route();
    register_full_event_route();
}
