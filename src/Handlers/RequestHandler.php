<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Models.php';
require_once __DIR__ . '/../ApiHelpers/ApiHelpers.php';
use ClaudeToGPTAPI\Config;
use ClaudeToGPTAPI\Models;
use function ClaudeToGPTAPI\ApiHelpers\validateRequestBody;
use function ClaudeToGPTAPI\ApiHelpers\getAPIKey;
use function ClaudeToGPTAPI\ApiHelpers\makeClaudeRequest;
use function ClaudeToGPTAPI\ResponseHelpers\claudeToChatGPTResponse;

/**
 * Handles API requests.
 *
 * @param mixed $vars - Variables passed to the handler.
 */

class RequestHandler {
    public static function handle($vars) {
        try {
            $input = file_get_contents("php://input");
            $headers = self::getRequestHeaders(); // Fetch all headers
            $apiKey = getAPIKey($headers['Authorization']); // Get or default to configured API key

            // Set API key in configuration dynamically
            Config::setApiKey($apiKey);

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

            $claudeModel = Models::getModelMap()[$requestBody['model']] ?? 'claude-2';
            $prompt = self::convertMessagesToPrompt($requestBody['messages']);
            $claudeRequestBody = [
                "prompt" => $prompt,
                "model" => $claudeModel,
                "temperature" => $requestBody['temperature'] ?? 0.5,
                "max_tokens_to_sample" => Config::$MAX_TOKENS,
                "stop_sequences" => ["stop"],
                "stream" => $requestBody['stream'] ?? false,
            ];

            $claudeResponse = makeClaudeRequest($apiKey, $claudeRequestBody);
            $response = claudeToChatGPTResponse($claudeResponse, $requestBody['stream']);
            echo json_encode($response);

        } catch (\Exception $e) {
            http_response_code(500);
            echo "Server Error: " . $e->getMessage();
        }
    }

    /**
     * Converts an array of message objects into a formatted prompt for Claude model.
     *
     * @param array $messages
     * @return string
     */

    private static function convertMessagesToPrompt($messages) {
        $prompt = '';
        $roleMap = Models::getRoleMap();
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'];
            $transformed_role = isset($roleMap[$role]) ? $roleMap[$role] : 'Human';
            $prompt .= "\n\n{$transformed_role}: {$content}";
        }
        $prompt .= "\n\nAssistant: ";
        return $prompt;
    }

    /**
     * Gets all headers from the request.
     *
     * @return array
     */

    private static function getRequestHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
