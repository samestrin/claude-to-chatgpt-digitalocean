<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../Models.php'; // Include the Models.php to access $modelsList

use ClaudeToGPTAPI\Models; // Use the Models class

/**
 * Handles requests for the "/v1/models" route.
 * This class is responsible for returning a JSON response containing the available models.
 */

class ModelsHandler {
    /**
     * Handles the incoming request and sends a JSON response with the models list.
     * 
     * @param array $vars Variables passed to the handler, not used in this context.
     */
    public static function handle($vars) {
        header('Content-Type: application/json');  // Sets the header for content type to JSON

        // Echoing out the JSON encoded list of models
        echo json_encode(Models::getModelsList());
    }
}