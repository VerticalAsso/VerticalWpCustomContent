<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // Specify paths to your theme directories
    $rectorConfig->paths([
        __DIR__ . '/vertical',
        __DIR__ . '/vertical-child',
    ]);

    // Define the PHP version to which you are upgrading
    $rectorConfig->phpVersion(PHP_VERSION_ID); // PHP 8.2

    // Include predefined sets for PHP upgrades
    $rectorConfig->sets([
        SetList::PHP_80, // Upgrade to PHP 8.0
        SetList::PHP_81, // Upgrade to PHP 8.1
        SetList::PHP_82, // Upgrade to PHP 8.2
    ]);

    // Optional: Skip specific rules or directories if necessary
    $rectorConfig->skip([
        // Example: __DIR__ . '/theme2/some-file.php',
    ]);
};