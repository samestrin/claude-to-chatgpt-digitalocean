# claude-to-chatgpt-php-digital-ocean

A PHP Digital Ocean App Platform based port of [jtsang4/claude-to-chatgpt.](https://github.com/jtsang4/claude-to-chatgpt).

Digital Ocean App Platform calls offer more resources than Cloudflare Workers and may be more performant in some use cases.

This project converts the API of Anthropic's Claude model to the OpenAI Chat API format.

- ✨ Call Claude API like OpenAI ChatGPT API
- 💦 Support streaming response
- 🐻 Support claude-instant-1, claude-2 models

## Digital Ocean App Platform Setup

### Commands > Run Command

```
heroku-php-nginx -C nginx.conf .
```

## Testing

```
curl -X POST http://DO-APP-PLATFORM-SERVER.app/v1/chat/completions \
-H "Content-Type: application/json" \
-H "Authorization: MY_CLAUDE_API_KEY" \
-d '{"model": "gpt-3.5-turbo", "messages": [{"role": "user", "content": "Hello, how are you?"}]}'
```

Currently working on implementation logic.
