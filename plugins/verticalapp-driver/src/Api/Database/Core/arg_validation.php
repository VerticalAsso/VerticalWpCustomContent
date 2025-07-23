<?php
namespace VerticalAppDriver\Api\Database\Core;


/**
 * Validate that event_id is a positive integer.
 *
 * @param mixed $param
 * @return bool
 */
function validate_event_id($param): bool
{
    return is_numeric($param) && $param > 0;
}

