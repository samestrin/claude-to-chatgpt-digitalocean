<?php

$version = '0.4.0';

// Constants and mappings
$CLAUDE_API_KEY = ''; // Insert your API key here
$CLAUDE_BASE_URL = 'https://api.anthropic.com';
$MAX_TOKENS = 9016;

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleRequest();
} elseif ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    handleOPTIONS();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGET();
} else {
    http_response_code(405);
    echo "Method not allowed";
}

function handleRequest()
{
$input = file_get_contents('php://input');
$requestBody = json_decode($input, true);

// Your implementation logic here

// 1. Get the API from headers or use the default
global $CLAUDE_API_KEY;
$apiKey = getAPIKey($_SERVER) ?: $CLAUDE_API_KEY;

// 2. extract parameters from requestBody

$model = $requestBody['model'];
$messages = $requestBody['messages'];
$temperature = $requestBody['temperature'];
$stop = $requestBody['stop'];
$stream = $requestBody['stream'];

global $model_map;
$claudeModel = $model_map[$model] ?? 'claude-2';

// 3. convert messages to prompt
global $role_map;
$prompt = convertMessagesToPrompt($messages, $role_map);

// 4. build claude request body
global $MAX_TOKENS;
$claudeRequestBody = [
'prompt' => $prompt,
'model' => $claudeModel,
'temperature' => $temperature,
'max_tokens_to_sample' => $MAX_TOKENS,
'stop_sequences' => $stop,
'stream' => $stream,
];

// 5. make claude request
global $CLAUDE_BASE_URL;
$claudeResponse = makeClaudeRequest($apiKey, $claudeRequestBody);

// 6. handle response

if (!$stream) {
$claudeResponseBody = json_decode($claudeResponse->getBody(), true);
$openAIResponseBody = claudeToChatGPTResponse($claudeResponseBody);
$response = [
'status' => $claudeResponse->getStatusCode(),
'headers' => ['Content-Type' => 'application/json'],
'body' => json_encode($openAIResponseBody)
];
} else {
$stream = $claudeResponse->getBody()->getContents();
$response = [
'headers' => [
'Content-Type' => 'text/event-stream',
'Access-Control-Allow-Origin' => '*',
'Access-Control-Allow-Methods' => '*',
'Access-Control-Allow-Headers' => '*',
'Access-Control-Allow-Credentials' => 'true'
],
'body' => $stream
];
}


echo json_encode($response);
}

function handleOPTIONS() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Credentials: true');
}

function handleGET() {
    $path = $_SERVER['REQUEST_URI'];
    if ($path === '/v1/models') {
        
        global $models_list;
        
        echo json_encode([
            'object' => 'list',
            'data' => $models_list,
        ]);
    } else {
        http_response_code(404);
        echo "Not Found";
    }
}

function getAPIKey($headers) {
    if (isset($headers['authorization'])) {
        $authorization = $headers['authorization'];
        $parts = explode(' ', $authorization);
        return isset($parts[1]) ? $parts[1] : '';
    }
    global $CLAUDE_API_KEY;
    return $CLAUDE_API_KEY;
}

function claudeToChatGPTResponse($claudeResponse, $stream = false) {
    global $stop_reason_map;
    
    $completion = $claudeResponse['completion'];
    $timestamp = time();
    $completionTokens = count(explode(' ', $completion));
    $result = [
        'id' => 'chatcmpl-' . $timestamp,
        'created' => $timestamp,
        'model' => 'gpt-3.5-turbo-0613',
        'usage' => [
            'prompt_tokens' => 0,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $completionTokens,
        ],
        'choices' => [
            [
                'index' => 0,
                'finish_reason' => isset($claudeResponse['stop_reason']) ? $stop_reason_map[$claudeResponse['stop_reason']] : null,
            ],
        ],
    ];
    $message = [
        'role' => 'assistant',
        'content' => $completion,
    ];
    if (!$stream) {
        $result['object'] = 'chat.completion';
        $result['choices'][0]['message'] = $message;
    } else {
        $result['object'] = 'chat.completion.chunk';
        $result['choices'][0]['delta'] = $message;
    }
    return $result;
}

function convertMessagesToPrompt($messages, $role_map) {
    $prompt = '';
    foreach ($messages as $message) {
        $role = $message['role'];
        $content = $message['content'];
        $transformed_role = isset($role_map[$role]) ? $role_map[$role] : 'Human';
        $prompt .= "\n\n$transformed_role: $content";
    }
    $prompt .= "\n\nAssistant: ";
    return $prompt;
}

function makeClaudeRequest($apiKey, $claudeRequestBody) {
    // Initialize curl session
    $ch = curl_init();

    // Set the URL for the request
    global $CLAUDE_BASE_URL;
    $url = $CLAUDE_BASE_URL . '/v1/complete';
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set the request method to POST
    curl_setopt($ch, CURLOPT_POST, true);

    // Set the request headers
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Set the request body
    $requestBodyJson = json_encode($claudeRequestBody);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBodyJson);

    // Set other options as needed
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute the request and capture the response
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    // Close curl session
    curl_close($ch);

    // Decode the JSON response
    $decodedResponse = json_decode($response);

    // Check if JSON decoding was successful
    if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        echo 'Error decoding JSON: ' . json_last_error_msg();
        return false; // Return false indicating an error
    }

    // Return the decoded JSON response
    return $decodedResponse;
}

function streamJsonResponseBodies($response, $writable) {
    $reader = $response->getBody()->getReader();
    $writer = $writable->getWriter();

    $buffer = '';
    while (true) {
        // Error handling for reading from the response body
        try {
            $chunk = $reader->read()->wait();
        } catch (Exception $e) {
            // Handle the exception, log or return an error response
            return; // For simplicity, returning without handling the error
        }
        
        if ($chunk['done']) {
            // Properly handle the end of stream
            try {
                $writer->write("data: [DONE]\n")->wait();
                $writer->close()->wait();
            } catch (Exception $e) {
                // Handle the exception
                return;
            }
            break;
        }

        // Properly decode the chunk's value
        $currentText = $chunk['value']; // No need for utf8_decode

        // Buffer handling
        $buffer .= preg_replace('/event: (completion|ping)\s*|\r/i', '', $currentText);
        $substr = explode("\n\n", $buffer);
        $lastMsg = count($substr) - 1;
        if (strlen($substr[$lastMsg]) !== 0) {
            $buffer = $substr[$lastMsg];
        } else {
            $buffer = '';
        }

        // Stream writing optimization
        for ($i = 0; $i < $lastMsg; $i++) {
            try {
                $decodedLine = json_decode(substr($substr[$i], 5), true);
                $completion = $decodedLine['completion'] ?? '';
                $stop_reason = $decodedLine['stop_reason'] ?? null;
                $transformedLine = $stop_reason ? claudeToChatGPTResponse(['completion' => '', 'stop_reason' => $stop_reason], true) :
                    claudeToChatGPTResponse(['completion' => $completion], true);
                $writer->write("data: " . json_encode($transformedLine) . "\n\n")->wait();
            } catch (Exception $e) {
                // Handle the exception
            }
        }
    }
}

$role_map = [
    'system' => 'Human',
    'user' => 'Human',
    'assistant' => 'Assistant',
];
$stop_reason_map = [
    'stop_sequence' => 'stop',
    'max_tokens' => 'length',
];

// Define the models_list array
$models_list = [
    [
        'id' => 'gpt-3.5-turbo',
        'object' => 'model',
        'created' => 1677610602,
        'owned_by' => 'openai',
        'permission' => [
            [
                'id' => 'modelperm-YO9wdQnaovI4GD1HLV59M0AV',
                'object' => 'model_permission',
                'created' => 1683753011,
                'allow_create_engine' => false,
                'allow_sampling' => true,
                'allow_logprobs' => true,
                'allow_search_indices' => false,
                'allow_view' => true,
                'allow_fine_tuning' => false,
                'organization' => '*',
                'group' => null,
                'is_blocking' => false,
            ],
        ],
        'root' => 'gpt-3.5-turbo',
        'parent' => null,
    ],
    [
        'id' => 'gpt-3.5-turbo-0613',
        'object' => 'model',
        'created' => 1677649963,
        'owned_by' => 'openai',
        'permission' => [
            [
                'id' => 'modelperm-tsdKKNwiNtHfnKWWTkKChjoo',
                'object' => 'model_permission',
                'created' => 1683753015,
                'allow_create_engine' => false,
                'allow_sampling' => true,
                'allow_logprobs' => true,
                'allow_search_indices' => false,
                'allow_view' => true,
                'allow_fine_tuning' => false,
                'organization' => '*',
                'group' => null,
                'is_blocking' => false,
            ],
        ],
        'root' => 'gpt-3.5-turbo-0613',
        'parent' => null,
    ],
    [
        'id' => 'gpt-4',
        'object' => 'model',
        'created' => 1678604602,
        'owned_by' => 'openai',
        'permission' => [
            [
                'id' => 'modelperm-nqKDpzYoZMlqbIltZojY48n9',
                'object' => 'model_permission',
                'created' => 1683768705,
                'allow_create_engine' => false,
                'allow_sampling' => false,
                'allow_logprobs' => false,
                'allow_search_indices' => false,
                'allow_view' => false,
                'allow_fine_tuning' => false,
                'organization' => '*',
                'group' => null,
                'is_blocking' => false,
            ],
        ],
        'root' => 'gpt-4',
        'parent' => null,
    ],
    [
        'id' => 'gpt-4-0613',
        'object' => 'model',
        'created' => 1678604601,
        'owned_by' => 'openai',
        'permission' => [
            [
                'id' => 'modelperm-PGbNkIIZZLRipow1uFL0LCvV',
                'object' => 'model_permission',
                'created' => 1683768678,
                'allow_create_engine' => false,
                'allow_sampling' => false,
                'allow_logprobs' => false,
                'allow_search_indices' => false,
                'allow_view' => false,
                'allow_fine_tuning' => false,
                'organization' => '*',
                'group' => null,
                'is_blocking' => false,
            ],
        ],
        'root' => 'gpt-4-0613',
        'parent' => null,
    ],
];


$model_map = [
    'gpt-3.5-turbo' => 'claude-instant-1',
    'gpt-3.5-turbo-0613' => 'claude-instant-1',
    'gpt-4' => 'claude-2',
    'gpt-4-0613' => 'claude-2',
];