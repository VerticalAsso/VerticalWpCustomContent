<?php

namespace VerticalAppDriver\Api\Events;

require_once __DIR__ . '/events_comment.php';

use VerticalAppDriver\Api\Events as Events;
/**
 * @brief registers all REST API routes upon activation.
 * @see verticalapp-driver.php for rest api init callback registration
 */
function register_all_routes()
{
    Events\register_events_comment_routes();
}
