<?php
namespace ProjectAPI;

class Config {
    public static $version = "0.0.1";
    public static $CLAUDE_API_KEY = "your_api_key_here"; // Set this from environment or statically
    public static $CLAUDE_BASE_URL = "https://api.anthropic.com";
    public static $MAX_TOKENS = 9016;
}
