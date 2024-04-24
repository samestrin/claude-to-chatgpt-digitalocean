# claude-to-chatgpt-php-digital-ocean

A PHP Digital Ocean App Platform based port of [jtsang4/claude-to-chatgpt.](https://github.com/jtsang4/claude-to-chatgpt).

Digital Ocean App Platform calls offer more resources than Cloudflare Workers and may be more performant in some use cases.

This project converts the API of Anthropic's Claude model to the OpenAI Chat API format.

- ✨ Call Claude API like OpenAI ChatGPT API
- 💦 Support streaming response
- 🐻 Support claude-instant-1, claude-2 models

## Deploy to Digital Ocean

Click this button to deploy the project to your Digital Ocean account:

[![Deploy to DO](https://www.deploytodo.com/do-btn-blue.svg)](https://cloud.digitalocean.com/apps/new?repo=https://github.com/samestrin/claude-to-chatgpt-netlify/tree/main)

## Digital Ocean App Platform Setup

### Commands > Run Command

```
heroku-php-nginx -C nginx.conf .
```

## Endpoints

Once deployed, two endpoints are available:

- `/v1/models`
- `/v1/chat/completions`

## Testing your claude-to-chatgpt-php-digital-ocean Deployment

```
curl -X POST http://DO-APP-PLATFORM-SERVER.app/v1/chat/completions \
-H "Content-Type: application/json" \
-H "Authorization: MY_CLAUDE_API_KEY" \
-d '{"model": "gpt-3.5-turbo", "messages": [{"role": "user", "content": "Hello, how are you?"}]}'
```
