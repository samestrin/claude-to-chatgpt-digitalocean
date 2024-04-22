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

function handleRequest() {
    $input = file_get_contents('php://input');
    $requestBody = json_decode($input, true);
    
    // Extract request parameters
    $model = $requestBody['model'];
    $messages = $requestBody['messages'];
    $temperature = $requestBody['temperature'];
    $stop = $requestBody['stop'];
    $stream = $requestBody['stream'];
    
    // Your implementation logic here
    
    // Example response
    $response = [
        'id' => 'chatcmpl-' . time(),
        'created' => time(),
        'model' => 'gpt-3.5-turbo-0613',
        'usage' => [
            'prompt_tokens' => 0,
            'completion_tokens' => 100,
            'total_tokens' => 100,
        ],
        'choices' => [
            [
                'index' => 0,
                'finish_reason' => 'stop', // or null
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Assistant response',
                ],
            ],
        ],
    ];
    
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

        echo json_encode([
            'object' => 'list',
            'data' => $models_list,
        ]);
    } else {
        http_response_code(404);
        echo "Not Found";
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