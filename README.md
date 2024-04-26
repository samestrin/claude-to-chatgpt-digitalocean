# claude-to-chatgpt-php-digital-ocean

[![Star on GitHub](https://img.shields.io/github/stars/samestrin/claude-to-chatgpt-php-digital-ocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-php-digital-ocean/stargazers) [![Fork on GitHub](https://img.shields.io/github/forks/samestrin/claude-to-chatgpt-php-digital-ocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-php-digital-ocean/network/members) [![Watch on GitHub](https://img.shields.io/github/watchers/samestrin/claude-to-chatgpt-php-digital-ocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-php-digital-ocean/watchers)

![Version 1.0.8](https://img.shields.io/badge/Version-1.0.10-blue) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT) [![Built with PHP](https://img.shields.io/badge/Built%20with-PHP-green)](https://php.net/)

A PHP Digital Ocean App Platform based port of [jtsang4/claude-to-chatgpt](https://github.com/jtsang4/claude-to-chatgpt)'s cloudflare-worker.js.

Digital Ocean App Platform calls offer more resources than Cloudflare Workers and may be more performant in some use cases.

This project converts the API of Anthropic's Claude model to the OpenAI Chat API format.

- ✨ Call Claude API like OpenAI ChatGPT API
- 💦 Support streaming response
- 🐻 Support claude-instant-1, claude-2 models

## Deploy to Digital Ocean

Click this button to deploy the project to your Digital Ocean account:

[![Deploy to DO](https://www.deploytodo.com/do-btn-blue.svg)](https://cloud.digitalocean.com/apps/new?repo=https://github.com/samestrin/claude-to-chatgpt-php-digital-ocean/tree/main&refcode=2d3f5d7c5fbe)

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

## License

This project is licensed under the MIT License - see the LICENSE file for details.
