<?php

namespace ClaudeToGPTAPI;

require_once __DIR__ . '/../vendor/autoload.php';

// Load the configuration
require 'Config.php';

/**
 * Sets up a global error handler to manage all errors as ErrorException.
 */
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
