<?php

/**
 * A PHP DigitalOcean App Platform based port of jtsang4/claude-to-chatgpt; it adapts Anthropic's Claude API to match OpenAI's 
 * Chat API format.
 * 
 * Copyright (c) 2024-PRESENT Sam Estrin
 * This script is licensed under the MIT License (see LICENSE for details)
 * GitHub: https://github.com/samestrin/claude-to-chatgpt-digitalocean
 */

if (($_GET['debug'] ?? 'false') === "true") {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Use the Composer-generated autoloader to handle necessary imports
require_once __DIR__ . '/vendor/autoload.php';

// Load the bootstrap file to set up the environment and error handling
require_once __DIR__ . '/src/Bootstrap.php';

// Load routing logic
require_once __DIR__ . '/src/Routes.php';


