<?php
namespace ClaudeToGPTAPI\Handlers;

/**
 * Handles OPTIONS requests for CORS preflight checks.
 * This class is responsible for returning the necessary CORS headers.
 */
class OptionsHandler {
    /**
     * Handles the incoming OPTIONS request and sends appropriate CORS headers.
     */
    public static function handle() {
        // Setting headers for CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials: true');

        // Sending a 204 No Content status code as there's no content to return
        http_response_code(204);
    }
}
