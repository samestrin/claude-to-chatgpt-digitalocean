<?php
namespace ClaudeToGPTAPI\Handlers;

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Models.php';
require_once __DIR__ . '/../ApiHelpers/ApiHelpers.php';
require_once __DIR__ . '/../ResponseHelpers/ResponseHelper.php';

use ClaudeToGPTAPI\Config;
use ClaudeToGPTAPI\Models;
use function ClaudeToGPTAPI\ApiHelpers\validateRequestBody;
use function ClaudeToGPTAPI\ApiHelpers\makeClaudeRequest;
use function ClaudeToGPTAPI\ResponseHelpers\claudeToChatGPTResponse;

class RequestHandler {

    /**
     * Handles incoming requests, processes them, and formulates responses.
     *
     * @param array $vars Variables extracted from the request URL.
     * @return void Outputs the response based on the request.
     * @throws \Exception If processing the request or generating a response fails.
     */
    public static function handle($vars) {
        try {
            $input = file_get_contents("php://input");
            $headers = self::getRequestHeaders(); // Fetch all headers            

            $apiKey = $headers['Authorization'] ?? false; // Get or default to configured API key
            if (!$apiKey) {
                $apiKey = Config::getApiKey();
            }

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

            $stream = $requestBody['stream'] ?? false;
            $temperature = $requestBody['stream'] ?? 0.5;            

            $claudeRequestBody = [
                "prompt" => $prompt,
                "model" => $claudeModel,
                "temperature" => $temperature,
                "max_tokens_to_sample" => Config::$MAX_TOKENS,
                "stop_sequences" => ["stop"],
                "stream" => $stream,
            ];            

            $claudeResponse = makeClaudeRequest($apiKey, $claudeRequestBody);
            $response = claudeToChatGPTResponse($claudeResponse, $stream);
            echo json_encode($response);

        } catch (\Exception $e) {
            http_response_code(500);
            echo "Server Error: " . $e->getMessage();
        }
    }

    /**
     * Converts a series of message objects into a formatted prompt string suitable for processing.
     *
     * @param array $messages Array of message objects from the request.
     * @return string A formatted prompt string.
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
     * Retrieves HTTP request headers and formats them into an associative array.
     *
     * @return array An associative array of headers.
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
