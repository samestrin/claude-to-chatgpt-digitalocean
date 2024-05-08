<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../Models.php'; // Include the Models.php to access $modelsList

use ClaudeToGPTAPI\Models; // Use the Models class

class ModelsHandler {

    /**
     * Handles requests for retrieving a list of models.
     *
     * @param array $vars Variables extracted from the request context.
     * @return void Outputs the list of models in JSON format.
     */
    public static function handle($vars) {
        header('Content-Type: application/json');  // Sets the header for content type to JSON

        // Echoing out the JSON encoded list of models
        echo json_encode(Models::getModelsList());
    }
}