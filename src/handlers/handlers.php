<?php

require 'apiHelpers.php';
require 'responseHelpers.php';

/**
 * Handles the API request by processing input data, validating, making a Claude API request, and formatting the response.
 *
 * @param array $vars - Variables from the routing that may contain parameters passed to the handler.
 * @throws Exception If JSON input is invalid or if required fields are missing in the request body.
 * @example
 *
 * // Example usage
 * handleRequest($vars);
 */

function handleRequest(array $vars): void
{
    try {
        $input = file_get_contents("php://input");
        $requestBody = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON");
        }

        $validationErrors = validateRequestBody($requestBody);
        if (!empty($validationErrors)) {
            http_response_code(400);
            echo json_encode(["errors" => $validationErrors]);
            return;
        }

        $apiKey = getAPIKey($_SERVER);
        $model = $requestBody["model"];
        $messages = $requestBody["messages"];
        $temperature = $requestBody["temperature"];
        $stop = $requestBody["stop"];
        $stream = $requestBody["stream"];

        global $modelMap;
        $claudeModel = $modelMap[$model] ?? "claude-2";

        $prompt = convertMessagesToPrompt($messages);
        global $MAX_TOKENS;
        $claudeRequestBody = [
            "prompt" => $prompt,
            "model" => $claudeModel,
            "temperature" => $temperature,
            "max_tokens_to_sample" => $MAX_TOKENS,
            "stop_sequences" => $stop,
            "stream" => $stream,
        ];

        $claudeResponse = makeClaudeRequest($apiKey, $claudeRequestBody);
        if (!$stream) {        
            $openAIResponseBody = claudeToChatGPTResponse($claudeResponse);
            $response = [
                "status" => 200,
                "headers" => ["Content-Type" => "application/json"],
                "body" => json_encode($openAIResponseBody),
            ];
        } else {
            $stream = $claudeResponse->getBody()->getContents();
            $response = [
                "headers" => [
                    "Content-Type" => "text/event-stream",
                    "Access-Control-Allow-Origin" => "*",
                    "Access-Control-Allow-Methods" => "*",
                    "Access-Control-Allow-Headers" => "*",
                    "Access-Control-Allow-Credentials" => "true",
                ],
                "body" => $stream,
            ];
        }

        foreach ($response["headers"] as $header => $value) {
            header("$header: $value");
        }
        echo $response["body"];
    } catch (Exception $e) {
        http_response_code(500);
        echo "Server Error: " . $e->getMessage();
    }
}
/**
 * Sets headers to handle CORS by allowing all origins, methods, and headers.
 *
 * @param array $vars - Variables passed from the route dispatcher, unused in this function.
 * @example
 *
 * // Example usage
 * handleOPTIONS($vars);
 */

function handleOPTIONS(array $vars): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Credentials: true");
}

/**
 * Sends a JSON response containing a list of available models.
 *
 * @param array $vars - Variables passed from the route dispatcher, unused in this function.
 * @returns void Outputs a JSON encoded list of models.
 * @example
 *
 * // Example usage
 * handleGetModels($vars);
 */

function handleGetModels(array $vars): void
{
    global $modelsList;
    echo json_encode([
        "object" => "list",
        "data" => $modelsList,
    ]);
}

?>
