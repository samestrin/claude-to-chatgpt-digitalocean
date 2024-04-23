<?php
namespace ClaudeToGPTAPI\ApiHelpers;
use ClaudeToGPTAPI\Config;
use stdClass;

/**
 * Retrieves the API key from the headers or uses the default.
 *
 * @param array $headers - Array containing request headers.
 * @returns string - The API key.
 */
function getAPIKey(array $headers): string {
    $authorization = $headers["Authorization"] ?? '';
    if (strpos($authorization, "Bearer ") === 0) {
        return substr($authorization, 7);
    }
    return Config::$CLAUDE_API_KEY; 
}

/**
 * Validates the request body for required fields.
 *
 * @param array $requestBody - Array containing the body of the request.
 * @returns array - List of validation errors.
 */
function validateRequestBody(array $requestBody): array {
    $errors = [];
    $requiredFields = ['model', 'messages', 'temperature', 'stop', 'stream'];
    foreach ($requiredFields as $field) {
        if (!isset($requestBody[$field]) || !is_correct_type($field, $requestBody[$field])) {
            $errors[] = "Invalid or missing field: $field";
        }
    }
    if (empty($requestBody['messages'])) {
        $errors[] = "The 'messages' field cannot be empty";
    }
    return $errors;
}


/**
 * Checks if the type of a given value matches the expected type for a specified field.
 *
 * @param string $field - The name of the field.
 * @param mixed $value - The value to check.
 * @returns bool - True if the type is correct, false otherwise.
 */
function is_correct_type($field, $value): bool {
    switch ($field) {
        case 'model':
        case 'stop':
            return is_string($value);
        case 'messages':
            return is_array($value);
        case 'temperature':
            return is_float($value) || is_int($value);
        case 'stream':
            return is_bool($value);
        default:
            return false;
    }
}

/**
 * Makes a request to the Claude API.
 *
 * @param string $apiKey - The API key for authentication.
 * @param array $claudeRequestBody - The request body for Claude API.
 * @returns stdClass - The response from the Claude API.
 * @throws Exception - Throws an exception if the API request fails.
 */
function makeClaudeRequest(string $apiKey, array $claudeRequestBody): stdClass {
    $url = Config::$CLAUDE_BASE_URL . '/v1/complete';
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ];
    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'POST',
            'content' => json_encode($claudeRequestBody),
            'ignore_errors' => true  // Important to handle HTTP errors
        ],
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        throw new \Exception("Network error or no data returned from API");
    }

    $statusCode = $http_response_header[0] ?? null;
    if (!preg_match("/200 OK/", $statusCode)) {  // Check for HTTP 200 OK
        throw new \Exception("Unexpected response status: " . $statusCode);
    }

    $responseData = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Error decoding JSON response: " . json_last_error_msg());
    }

    return $responseData;
}

