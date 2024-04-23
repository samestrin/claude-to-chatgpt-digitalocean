<?php
// Check if the 'debug' query parameter is set and print the contents of $_GET for debugging purposes
if ($_GET['debug'] ?? false === "true") {
    die("<pre>" . print_r($_GET, true) . "</pre>");
}

// Use the Composer-generated autoloader to handle necessary imports
require_once __DIR__ . '/../vendor/autoload.php';

// Namespace to access the bootstrap functionalities
use ClaudeToGPTAPI;

// Load the bootstrap file to set up the environment and error handling
require_once __DIR__ . '/../src/bootstrap.php';

// Load routing logic
require_once __DIR__ . '/../src/routes.php';

// Any additional code required to handle the request can go here.
?>
