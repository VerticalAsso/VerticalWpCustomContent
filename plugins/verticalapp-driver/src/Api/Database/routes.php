<?php

namespace VerticalAppDriver\Api\Database;

require_once __DIR__ . '/Core/event_bookings.php';
require_once __DIR__ . '/Core/event_tickets.php';
require_once __DIR__ . '/Core/post_content.php';
require_once __DIR__ . '/Core/post_meta.php';
require_once __DIR__ . '/Core/events.php';
require_once __DIR__ . '/Core/user.php';
require_once __DIR__ . '/Core/user_meta.php';
require_once __DIR__ . '/Core/comments.php';
require_once __DIR__ . '/Core/event_location.php';
require_once __DIR__ . '/Core/roles.php';

require_once __DIR__ . '/Composite/event_card.php';
require_once __DIR__ . '/Composite/full_event.php';
require_once __DIR__ . '/Composite/user_profile.php';


use VerticalAppDriver\Api\Database\Core as Core;
use VerticalAppDriver\Api\Database\Composite as Composite;


/**
 * @brief registers all REST API routes upon activation.
 * @see verticalapp-driver.php for rest api init callback registration
 */
function register_all_routes()
{
    Core\register_event_tickets_route();
    Core\register_events_route();
    Core\register_post_content_route();
    Core\register_postmeta_route();
    Core\register_event_bookings_route();
    Core\register_user_route();
    Core\register_user_metadata_route();
    Core\register_comments_route();
    Core\register_event_location_route();
    Core\register_roles_routes();

    // Higher level apis (can reconstruct objects)
    Composite\register_event_card_route();
    Composite\register_full_event_route();
    Composite\register_user_profile_route();
}
