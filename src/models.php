<?php
namespace ClaudeToGPTAPI;

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