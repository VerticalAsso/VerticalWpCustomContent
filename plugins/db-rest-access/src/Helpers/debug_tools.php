<?php

namespace DbRestAccess\Helpers;


// Prevent direct access to the file
if (!defined('ABSPATH'))
{
    exit;
}

function add_php_info_page()
{
    add_submenu_page(
        'tools.php',           // Parent page
        'Xdebug Info',         // Menu title
        'Xdebug Info',         // Page title
        'manage_options',      // user "role"
        'php-info-page',       // page slug
        __NAMESPACE__ . '\\php_info_page_body'
    ); // callback function
}

function php_info_page_body()
{
    $message = '<h2>No Xdebug enabled</h2>';
    if (function_exists('xdebug_info'))
    {
        xdebug_info();
    }
    else
    {
        echo $message;
    }
}
