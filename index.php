<?php


// Use the Composer-generated autoloader to handle necessary imports
require_once __DIR__ . '/vendor/autoload.php';

// Load the bootstrap file to set up the environment and error handling
require_once __DIR__ . '/src/bootstrap.php';

// Load routing logic
require_once __DIR__ . '/src/routes.php';

// Check if the 'debug' query parameter is set
if (($_GET['debug'] ?? 'false') === "true") {

    die("<pre>" . print_r($httpMethod) . "\r\n" . print_r($uri) . "\r\n" . print_r($routeInfo, true) . "</pre>");
  }

?>
