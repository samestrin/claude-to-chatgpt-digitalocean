<?php
namespace ClaudeToGPTAPI;

class Config {
    public static $version = "0.0.1";
    public static $CLAUDE_API_KEY; // Declaration without initialization
    public static $CLAUDE_BASE_URL = "https://api.anthropic.com";
    public static $MAX_TOKENS = 9016;

    /**
     * Initializes configuration settings by loading environment variables.
     */
    public static function init() {     
        self::$CLAUDE_API_KEY = getenv('CLAUDE_API_KEY') ?: 'your_api_key_here';
    }

    /**
     * Sets the Claude API key if provided.
     *
     * @param string|null $key The new API key to set, if provided.
     */   
    public static function setApiKey($key=false) {
        if ($key) { self::$CLAUDE_API_KEY = $key; }
    }

    /**
     * Retrieves the currently set Claude API key.
     *
     * @return string The Claude API key.
     */
    public static function getApiKey() {
        return self::$CLAUDE_API_KEY; 
    }

}

// Initialize the configuration
Config::init();
