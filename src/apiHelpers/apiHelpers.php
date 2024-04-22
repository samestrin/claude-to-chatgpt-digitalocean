<?php
namespace ProjectAPI\ApiHelpers;

use ProjectAPI\Config;
use stdClass;

/**
 * Retrieves the API key from request headers or uses the default environment key.
 *
 * @param array $headers - Array of request headers.
 * @return string The API key.
 */
function getAPIKey(array $headers): string
{
    $authorization = $headers["Authorization"] ?? '';
    if (strpos($authorization, "Bearer ") === 0) {
        return substr($authorization, 7);
    }
    return Config::$CLAUDE_API_KEY;  // Using a config variable
}

/**
 * Validates the presence of required fields in the request body and checks for non-empty 'messages' field.
 *
 * @param array $requestBody - The body of the request to validate.
 * @return array An array of validation error messages.
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
 * Sends a request to the Claude API and returns the response as an object.
 *
 * @param string $apiKey API key for authentication.
 * @param array $claudeRequestBody The body of the request for the Claude API.
 * @return stdClass Decoded JSON response from the Claude API as an object.
 * @throws Exception If there is an error with the request or response handling.
 */
function makeClaudeRequest(string $apiKey, array $claudeRequestBody): stdClass
{
    $url = Config::$CLAUDE_BASE_URL . '/v1/complete';

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
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
        throw new \Exception("Failed to make API request to Claude.");
    }

    $responseData = json_decode($responseBody);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Error decoding Claude API response: " . json_last_error_msg());
    }

    return $responseData;
}
