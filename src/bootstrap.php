<?php
namespace ProjectAPI;

require_once __DIR__ . '/../vendor/autoload.php';

// Load the configuration
require 'config.php';

// Set the error handler
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
