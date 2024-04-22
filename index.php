<?php
require './vendor/autoload.php';

// Configuration and constants
$version = "0.0.1";
$CLAUDE_API_KEY = getenv("CLAUDE_API_KEY");
$CLAUDE_BASE_URL = "https://api.anthropic.com";
$MAX_TOKENS = 9016;

// Error Handling
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Handle request
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute("POST", "/", "handleRequest");
    $r->addRoute("OPTIONS", "/", "handleOPTIONS");
    $r->addRoute("GET", "/v1/models", "handleGetModels");
});

// Routing
$httpMethod = $_SERVER["REQUEST_METHOD"];
$uri = explode('?', $_SERVER["REQUEST_URI"], 2)[0];
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func($handler, $vars);
        break;
}

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

/**
 * Retrieves the API key from request headers or uses the default environment key.
 *
 * @param array $headers - Array of request headers.
 * @return string The API key.
 * @example
 *
 * // Example usage
 * $apiKey = getAPIKey($_SERVER);
 */

function getAPIKey(array $headers): string
{
    $authorization = $headers["authorization"] ?? '';
    if (strpos($authorization, "Bearer ") === 0) {
        return substr($authorization, 7);
    }
    global $CLAUDE_API_KEY;
    return $CLAUDE_API_KEY;
}

/**
 * Validates the presence of required fields in the request body and checks for non-empty 'messages' field.
 *
 * @param array $requestBody - The body of the request to validate.
 * @return array An array of validation error messages.
 * @example
 *
 * // Example usage
 * $errors = validateRequestBody($requestBody);
 */

function validateRequestBody(array $requestBody): array
{
    $errors = [];
    $requiredFields = ['model', 'messages', 'temperature', 'stop', 'stream'];
    foreach ($requiredFields as $field) {
        if (!isset($requestBody[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }
    if (empty($requestBody['messages'])) {
        $errors[] = "The 'messages' field cannot be empty";
    }
    return $errors;
}

/**
 * Convert an array of messages into a formatted prompt string for the Claude API.
 *
 * @param array $messages Array of messages, each containing 'role' and 'content'.
 * @return string Formatted prompt for API request.
 * @example
 *
 * // Example usage
 * $prompt = convertMessagesToPrompt([
 *     ['role' => 'user', 'content' => 'Hello'],
 *     ['role' => 'assistant', 'content' => 'Hi there!']
 * ]);
 */

function convertMessagesToPrompt(array $messages): string
{
    global $roleMap;
    $prompt = "";
    foreach ($messages as $message) {
        $role = $message['role'];
        $content = $message['content'];
        $transformedRole = $roleMap[$role] ?? "Human";
        $prompt .= "\n\n$transformedRole: $content";
    }
    $prompt .= "\n\nAssistant: ";
    return $prompt;
}

/**
 * Converts a response from the Claude API into an object format similar to a ChatGPT response.
 *
 * @param object $claudeResponse Response object from the Claude API.
 * @param bool $stream Indicates if the response is from a streaming endpoint.
 * @return object Formatted response similar to ChatGPT API responses.
 * @throws Exception If the response format is invalid.
 * @example
 *
 * // Example usage
 * $response = claudeToChatGPTResponse(json_decode('{
 *     "completion": "Hello, how can I help you today?",
 *     "stop_reason": "length"
 * }'), false);
 */

function claudeToChatGPTResponse(object $claudeResponse, bool $stream = false): object
{
    global $stopReasonMap;

    $completion = $claudeResponse->completion;
    $timestamp = time();
    $completionTokens = count(explode(" ", $completion));

    $result = new stdClass();
    $result->id = "chatcmpl-" . $timestamp;
    $result->created = $timestamp;
    $result->model = "gpt-3.5-turbo-0613";
    $result->usage = new stdClass();
    $result->usage->prompt_tokens = 0;
    $result->usage->completion_tokens = $completionTokens;
    $result->usage->total_tokens = $completionTokens;
    $result->choices = [];

    $choice = new stdClass();
    $choice->index = 0;
    $choice->finish_reason = isset($claudeResponse->stop_reason) ? $stopReasonMap[$claudeResponse->stop_reason] : null;

    $message = new stdClass();
    $message->role = "assistant";
    $message->content = $completion;

    if (!$stream) {
        $result->object = "chat.completion";
        $choice->message = $message;
    } else {
        $result->object = "chat.completion.chunk";
        $choice->delta = $message;
    }

    array_push($result->choices, $choice);

    return $result;
}


/**
 * Sends a request to the Claude API and returns the response as an object.
 *
 * @param string $apiKey API key for authentication.
 * @param array $claudeRequestBody The body of the request for the Claude API.
 * @return object Decoded JSON response from the Claude API as an object.
 * @throws Exception If there is an error with the request or response handling.
 * @example
 *
 * // Example usage
 * $response = makeClaudeRequest('your_api_key_here', [
 *     'prompt' => 'Hello, how can I assist?',
 *     'model' => 'claude-2',
 *     'temperature' => 0.5,
 *     'max_tokens_to_sample' => 150,
 *     'stop_sequences' => ['.'],
 *     'stream' => false
 * ]);
 */
function makeClaudeRequest(string $apiKey, array $claudeRequestBody): object
{
    global $CLAUDE_BASE_URL;
    $url = $CLAUDE_BASE_URL . '/v1/complete';

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ];

    $options = [
        'http' => [
            'header'  => implode("\r\n", $headers),
            'method'  => 'POST',
            'content' => json_encode($claudeRequestBody),
        ],
    ];

    $context = stream_context_create($options);
    $responseBody = file_get_contents($url, false, $context);
    if ($responseBody === false) {
        throw new Exception("Failed to make API request to Claude.");
    }

    $responseData = json_decode($responseBody);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding Claude API response: " . json_last_error_msg());
    }

    return $responseData;
}

$roleMap = ["system" => "Human", "user" => "Human", "assistant" => "Assistant"];
$stopReasonMap = ["stop_sequence" => "stop", "max_tokens" => "length"];
$modelsList = [
    [
        "id" => "gpt-3.5-turbo",
        "object" => "model",
        "created" => 1677610602,
        "owned_by" => "openai",
        "permission" => [
            [
                "id" => "modelperm-YO9wdQnaovI4GD1HLV59M0AV",
                "object" => "model_permission",
                "created" => 1683753011,
                "allow_create_engine" => false,
                "allow_sampling" => true,
                "allow_logprobs" => true,
                "allow_search_indices" => false,
                "allow_view" => true,
                "allow_fine_tuning" => false,
                "organization" => "",
                "group" => null,
                "is_blocking" => false,
            ],
        ],
        "root" => "gpt-3.5-turbo",
        "parent" => null,
    ],
    [
        "id" => "gpt-3.5-turbo-0613",
        "object" => "model",
        "created" => 1677649963,
        "owned_by" => "openai",
        "permission" => [
            [
                "id" => "modelperm-tsdKKNwiNtHfnKWWTkKChjoo",
                "object" => "model_permission",
                "created" => 1683753015,
                "allow_create_engine" => false,
                "allow_sampling" => true,
                "allow_logprobs" => true,
                "allow_search_indices" => false,
                "allow_view" => true,
                "allow_fine_tuning" => false,
                "organization" => "",
                "group" => null,
                "is_blocking" => false,
            ],
        ],
        "root" => "gpt-3.5-turbo-0613",
        "parent" => null,
    ],
    [
        "id" => "gpt-4",
        "object" => "model",
        "created" => 1678604602,
        "owned_by" => "openai",
        "permission" => [
            [
                "id" => "modelperm-nqKDpzYoZMlqbIltZojY48n9",
                "object" => "model_permission",
                "created" => 1683768705,
                "allow_create_engine" => false,
                "allow_sampling" => false,
                "allow_logprobs" => false,
                "allow_search_indices" => false,
                "allow_view" => false,
                "allow_fine_tuning" => false,
                "organization" => "",
                "group" => null,
                "is_blocking" => false,
            ],
        ],
        "root" => "gpt-4",
        "parent" => null,
    ],
    [
        "id" => "gpt-4-0613",
        "object" => "model",
        "created" => 1678604601,
        "owned_by" => "openai",
        "permission" => [
            [
                "id" => "modelperm-PGbNkIIZZLRipow1uFL0LCvV",
                "object" => "model_permission",
                "created" => 1683768678,
                "allow_create_engine" => false,
                "allow_sampling" => false,
                "allow_logprobs" => false,
                "allow_search_indices" => false,
                "allow_view" => false,
                "allow_fine_tuning" => false,
                "organization" => "",
                "group" => null,
                "is_blocking" => false,
            ],
        ],
        "root" => "gpt-4-0613",
        "parent" => null,
    ],
];
$modelMap = [
    "gpt-3.5-turbo" => "claude-instant-1",
    "gpt-3.5-turbo-0613" => "claude-instant-1",
    "gpt-4" => "claude-2",
    "gpt-4-0613" => "claude-2",
];

?>