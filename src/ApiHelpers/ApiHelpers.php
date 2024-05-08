<?php
namespace ClaudeToGPTAPI\ApiHelpers;

use ClaudeToGPTAPI\Config;
use stdClass;

/**
 * Retrieves the API key from the request headers or defaults to the configured key.
 *
 * @param array $headers The headers of the incoming HTTP request.
 * @return string The API key extracted or the default.
 * @throws \InvalidArgumentException If the headers do not contain the API key and no default is set.
 */
function getAPIKey(array $headers): string {
    $authorization = $headers["Authorization"] ?? '';
    if (strpos($authorization, "Bearer ") === 0) {
        return substr($authorization, 7);
    }
    return Config::$CLAUDE_API_KEY; 
}

/**
 * Validates the necessary fields and their types in the request body.
 *
 * @param array $requestBody The body of the HTTP request.
 * @return array An array of validation error messages, if any.
 * @throws \UnexpectedValueException If required fields are missing or in incorrect format.
 */
function validateRequestBody(array $requestBody): array {
    $errors = [];
    $requiredFields = ['model', 'messages'];
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
 * Checks if the value type matches the expected type for a specific field.
 *
 * @param string $field The field name.
 * @param mixed $value The value to check.
 * @return bool True if the type is correct, false otherwise.
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
 * Sends a request to the Claude API and handles the response.
 *
 * @param string $apiKey The API key for authentication.
 * @param array $claudeRequestBody The request body to be sent to Claude API.
 * @return stdClass The response object from the Claude API.
 * @throws \RuntimeException If the network request fails or the API returns an error status.
 */
function makeClaudeRequest(string $apiKey, array $claudeRequestBody): stdClass {
    $url = Config::$CLAUDE_BASE_URL . '/v1/complete';
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
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

