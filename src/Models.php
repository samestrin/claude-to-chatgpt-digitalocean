<?php
namespace ClaudeToGPTAPI;

class Models {
    /**
     * Retrieves a mapping of user roles to their corresponding system roles.
     *
     * @return array An associative array of user roles to system roles.
     */    
    public static function getRoleMap() { return ["system" => "Human", "user" => "Human", "assistant" => "Assistant"]; }
    
    /**
     * Provides mappings for different stop reasons used within the API responses.
     *
     * @return array An associative array of stop reason identifiers to their descriptions.
     */    
    public static function getStopReasonMap() { return ["stop_sequence" => "stop", "max_tokens" => "length"]; }
    
    /**
     * Provides a mapping from external model names to internal Claude model identifiers.
     *
     * @return array An associative array mapping external model names to internal identifiers.
     */
    public static function getModelMap() { 
        return 
            [
                "gpt-3.5-turbo" => "claude-instant-1",
                "gpt-3.5-turbo-0613" => "claude-instant-1",
                "gpt-4" => "claude-2",
                "gpt-4-0613" => "claude-2",
            ];
    }  
          
    /**
     * Retrieves a list of available models along with their details.
     *
     * @return array An array of objects, each representing a model and its details.
     */    
    public static function getModelsList() {
        return [
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
    }
}
