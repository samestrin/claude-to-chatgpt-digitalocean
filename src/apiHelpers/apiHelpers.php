<?php
require 'models.php'; // Includes necessary data structures like $modelMap and $roleMap

/**
 * Retrieves the API key from request headers or uses the default environment key.
 *
 * @param array $headers - Array of request headers.
 * @return string The API key.
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
 * Sends a request to the Claude API and returns the response as an object.
 *
 * @param string $apiKey API key for authentication.
 * @param array $claudeRequestBody The body of the request for the Claude API.
 * @return object Decoded JSON response from the Claude API as an object.
 * @throws Exception If there is an error with the request or response handling.
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
