<?php
namespace ClaudeToGPTAPI;

class Config {
    public static $version = "0.0.1";
    public static $CLAUDE_API_KEY; // Declaration without initialization
    public static $CLAUDE_BASE_URL = "https://api.anthropic.com";
    public static $MAX_TOKENS = 9016;

    public static function init() {
        // Initialization using runtime values
        self::$CLAUDE_API_KEY = getenv('CLAUDE_API_KEY') ?: 'your_api_key_here';
    }
}

// Initialize the configuration
Config::init();
