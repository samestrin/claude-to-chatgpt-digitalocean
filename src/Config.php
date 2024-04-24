<?php
namespace ClaudeToGPTAPI;

class Config {
    public static $version = "0.0.1";
    public static $CLAUDE_API_KEY; // Declaration without initialization
    public static $CLAUDE_BASE_URL = "https://api.anthropic.com";
    public static $MAX_TOKENS = 9016;

    public static function init() {     
        self::$CLAUDE_API_KEY = getenv('CLAUDE_API_KEY') ?: 'your_api_key_here';
    }
    
    public static function setApiKey($key=false) {
        if ($key) { self::$CLAUDE_API_KEY = $key; }
    }

    public static function getApiKey() {
        return self::$CLAUDE_API_KEY; 
    }

}

// Initialize the configuration
Config::init();
