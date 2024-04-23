<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../vendor/autoload.php';
use function ClaudeToGPTAPI\ApiHelpers\validateRequestBody;
use function ClaudeToGPTAPI\ApiHelpers\getAPIKey;
use function ClaudeToGPTAPI\ApiHelpers\makeClaudeRequest;

/**
 * Handles API requests.
 *
 * @param mixed $vars - Variables passed to the handler.
 */
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
            $response = makeClaudeRequest($apiKey, $requestBody);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Server Error: " . $e->getMessage();
        }
    }
}
