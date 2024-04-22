<?php

/**
 * Converts a response from the Claude API into an object format similar to a ChatGPT response.
 *
 * @param object $claudeResponse Response object from the Claude API.
 * @param bool $stream Indicates if the response is from a streaming endpoint.
 * @return object Formatted response similar to ChatGPT API responses.
 * @throws Exception If the response format is invalid.
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

