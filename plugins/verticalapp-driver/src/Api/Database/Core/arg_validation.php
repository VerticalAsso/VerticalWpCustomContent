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

function validate_email($param): bool
{
    return is_string($param) && filter_var($param, FILTER_VALIDATE_EMAIL) !== false;
}
