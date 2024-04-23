<?php
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

// Check if the 'debug' query parameter is set
if (($_GET['debug'] ?? 'false') === "true") {
    error_log(print_r(get_included_files()));
}

?>
