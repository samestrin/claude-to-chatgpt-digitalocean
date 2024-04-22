<?php
require './vendor/autoload.php';

$version = "0.0.1";

// Constants and mappings
$CLAUDE_API_KEY = getenv("CLAUDE_API_KEY") ?: ""; // Retrieve API key from environment variable
$CLAUDE_BASE_URL = "https://api.anthropic.com";
$MAX_TOKENS = 9016;

// Handle request
$dispatcher = FastRoute\simpleDispatcher(function (
    FastRoute\RouteCollector $r
) {
    $r->addRoute("POST", "/", "handleRequest");
    $r->addRoute("OPTIONS", "/", "handleOPTIONS");
    $r->addRoute("GET", "/v1/models", "handleGetModels");
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

// Strip query string (?foo=bar) and decode URI
if (false !== ($pos = strpos($uri, "?"))) {
    $uri = substr($uri, 0, $pos);
}
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
 * Handle the request
 *
 * @param array $vars
 * @return void
 */
function handleRequest(array $vars): void
{
    try {
        $input = file_get_contents("php://input");
        $requestBody = json_decode($input, true);
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error: Failed to read request body.";
        return;
    }

    // Validate input
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
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            echo "Error decoding Claude API response: " . json_last_error_msg();
            return;
        }
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
}

/**
 * Handle OPTIONS request
 *
 * @param array $vars
 * @return void
 */
function handleOPTIONS(array $vars): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Credentials: true");
}

/**
 * Handle GET request for /v1/models
 *
 * @param array $vars
 * @return void
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
 * Get the API key from headers or the default value
 *
 * @param array $headers
 * @return string
 */
function getAPIKey(array $headers): string
{
    if (isset($headers["authorization"])) {
        $authorization = $headers["authorization"];
        $parts = explode(" ", $authorization);
        return isset($parts[1]) ? $parts[1] : "";
    }
    global $CLAUDE_API_KEY;
    return $CLAUDE_API_KEY;
}

/**
 * Convert Claude API response to ChatGPT format
 *
 * @param array $claudeResponse
 * @param bool $stream
 * @return array
 */
function claudeToChatGPTResponse(
    array $claudeResponse,
    bool $stream = false
): array {
    global $stopReasonMap;

    $completion = $claudeResponse["completion"];
    $timestamp = time();
    $completionTokens = count(explode(" ", $completion));
    $result = [
        "id" => "chatcmpl-" . $timestamp,
        "created" => $timestamp,
        "model" => "gpt-3.5-turbo-0613",
        "usage" => [
            "prompt_tokens" => 0,
            "completion_tokens" => $completionTokens,
            "total_tokens" => $completionTokens,
        ],
        "choices" => [
            [
                "index" => 0,
                "finish_reason" => isset($claudeResponse["stop_reason"])
                    ? $stopReasonMap[$claudeResponse["stop_reason"]]
                    : null,
            ],
        ],
    ];
    $message = [
        "role" => "assistant",
        "content" => $completion,
    ];
    if (!$stream) {
        $result["object"] = "chat.completion";
        $result["choices"][0]["message"] = $message;
    } else {
        $result["object"] = "chat.completion.chunk";
        $result["choices"][0]["delta"] = $message;
    }
    return $result;
}

/**
 * Convert messages to a prompt for the Claude API
 *
 * @param array $messages
 * @return string
 */
function convertMessagesToPrompt(array $messages): string
{
    global $roleMap;
    $prompt = "";
    foreach ($messages as $message) {
        $role = $message["role"];
        $content = $message["content"];
        $transformedRole = isset($roleMap[$role]) ? $roleMap[$role] : "Human";
        $prompt .= "\n\n$transformedRole: $content";
    }
    $prompt .= "\n\nAssistant: ";
    return $prompt;
}

/**
 * Make a request to the Claude API
 *
 * @param string $apiKey
 * @param array $claudeRequestBody
 * @return array
 * @throws Exception
 */
function makeClaudeRequest(string $apiKey, array $claudeRequestBody): array
{
    global $CLAUDE_BASE_URL;
    $url = $CLAUDE_BASE_URL . '/v1/complete';

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ];

    if (class_exists('HTTPResponse')) {
        $client = new HTTPRequest($url, HTTPRequest::METHOD_POST);
        $client->setHeaders($headers);
        $client->setBody(json_encode($claudeRequestBody));

        try {
            $response = $client->send();
        } catch (HTTPRequestException $e) {
            throw new Exception("Error making Claude API request: " . $e->getMessage());
        }

        if ($response->getResponseCode() !== 200) {
            throw new Exception("Claude API request failed with status code: " . $response->getResponseCode());
        }

        $responseBody = $response->getBody();
    } else {
        $options = [
            'http' => [
                'header'  => implode("\r\n", $headers),
                'method'  => 'POST',
                'content' => json_encode($claudeRequestBody),
            ],
        ];

        $context = stream_context_create($options);

        try {
            $responseBody = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            throw new Exception("Error making Claude API request: " . $e->getMessage());
        }
    }

    $responseData = json_decode($responseBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding Claude API response: " . json_last_error_msg());
    }

    return $responseData;
}

/**
 * Validate the request body
 *
 * @param array $requestBody
 * @return array
 */
function validateRequestBody(array $requestBody): array
{
    $errors = [];

    $requiredFields = ['model', 'messages', 'temperature', 'stop', 'stream'];
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $requestBody)) {
            $errors[] = "Missing required field: $field";
        }
    }

    if (empty($requestBody['messages'])) {
        $errors[] = "The 'messages' field cannot be empty";
    }

    return $errors;
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
