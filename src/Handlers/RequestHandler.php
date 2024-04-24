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
use function ClaudeToGPTAPI\ResponseHelpers\claudeToChatGPTResponse;  // Import the response helper

/**
 * Handles API requests.
 *
 * @param mixed $vars - Variables passed to the handler.
 */
class RequestHandler {
    public static function handle($vars) {
        try {
            $maxTokens = Config::$MAX_TOKENS;
            $modelMap = Models::getModelMap();

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
            $claudeModel = $modelMap[$requestBody['model']] ?? 'claude-2';

            $prompt = self::convertMessagesToPrompt($requestBody['messages']);
            $claudeRequestBody = [
                "prompt" => $prompt,
                "model" => $claudeModel,
                "temperature" => $requestBody['temperature'] ?? "",
                "max_tokens_to_sample" => $maxTokens,
                "stop_sequences" => "stop",
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
            $transformed_role = isset($roleMap[$role]) ? $roleMap[$role] : 'Human'; // Default to 'Human' if not found in map

            $prompt .= "\n\n{$transformed_role}: {$content}";
        }

        $prompt .= "\n\nAssistant: "; // Prepares the prompt for the next assistant's response
        return $prompt;
    }
}

