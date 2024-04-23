<?php
if($_GET['debug'] == "true") { 
    die("<pre>".print_r($_GET,true)."</pre>");
}
// Use the Composer-generated autoloader to handle necessary imports
require_once __DIR__ . '/../vendor/autoload.php';

// Namespace to access the bootstrap functionalities
use ProjectAPI;

// Load the bootstrap file to set up the environment and error handling
require_once __DIR__ . '/../src/bootstrap.php';

// Load routing logic
require_once __DIR__ . '/../src/routes.php';

// Any additional code required to handle the request can go here.


?>