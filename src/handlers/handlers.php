<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../vendor/autoload.php';

// Assuming functions are used directly from the namespace
use function ClaudeToGPTAPI\ApiHelpers\validateRequestBody;
use function ClaudeToGPTAPI\ApiHelpers\getAPIKey;
use function ClaudeToGPTAPI\ApiHelpers\makeClaudeRequest;

class RequestHandler {
    public static function handle($vars) {
        try {
            $input = file_get_contents("php://input");
            $requestBody = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON");
            }

            $validationErrors = validateRequestBody($requestBody);
            if (!empty($validationErrors)) {
                http_response_code(400);
                echo json_encode(["errors" => $validationErrors]);
                return;
            }

            $apiKey = getAPIKey($_SERVER);
            $response = makeClaudeRequest($apiKey, $requestBody); // Assumes this function returns the response correctly formatted
            echo json_encode($response);

        } catch (\Exception $e) {
            http_response_code(500);
            echo "Server Error: " . $e->getMessage();
        }
    }
}

class OptionsHandler {
    public static function handle($vars) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Credentials: true");
    }
}

class ModelsHandler {
    public static function handle($vars) {
        global $modelsList; // Assuming $modelsList is defined globally
        echo json_encode([
            "object" => "list",
            "data" => $modelsList,
        ]);
    }
}
