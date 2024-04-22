require './vendor/autoload.php';
require 'config.php';

// Error Handling
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
