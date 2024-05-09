# claude-to-chatgpt-digitalocean

[![Star on GitHub](https://img.shields.io/github/stars/samestrin/claude-to-chatgpt-digitalocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-digitalocean/stargazers) [![Fork on GitHub](https://img.shields.io/github/forks/samestrin/claude-to-chatgpt-digitalocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-digitalocean/network/members) [![Watch on GitHub](https://img.shields.io/github/watchers/samestrin/claude-to-chatgpt-digitalocean?style=social)](https://github.com/samestrin/claude-to-chatgpt-digitalocean/watchers)

![Version 0.0.1](https://img.shields.io/badge/Version-0.0.1-blue) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT) [![Built with PHP](https://img.shields.io/badge/Built%20with-PHP-green)](https://php.net/)

A PHP DigitalOcean App Platform based port of [jtsang4/claude-to-chatgpt](https://github.com/jtsang4/claude-to-chatgpt)'s cloudflare-worker.js. This project converts the API of Anthropic's Claude model to the OpenAI Chat API format.

DigitalOcean App Platform offers more resources than Cloudflare Workers and will be more performant in most use cases.

A Node.js port, designed to deploy on Netlify, is available [samestrin/claude-to-chatgpt-netlify](https://github.com/samestrin/claude-to-chatgpt-netlify) here.

## Dependencies

- **PHP**: Server-side scripting language (PHP 8.0 or later recommended).
- **nikic/fast-route**: A fast request router for PHP, used for efficient routing of API requests. 

## Features

- **API Compatibility**: Enables Claude model integration by mimicking the OpenAI ChatGPT API structure.
- **Streaming Support**: Facilitates real-time interaction with the Claude model through streaming responses.
- **Model Flexibility**: Supports various configurations of Claude models including claude-instant-1 and claude-2.
- **Performance Optimization**: Utilizes the enhanced capabilities of DigitalOcean's infrastructure for improved performance over alternatives like Cloudflare Workers.

## Deploy to DigitalOcean

Click this button to deploy the project to your DigitalOcean account:

[![Deploy to DO](https://www.deploytodo.com/do-btn-blue.svg)](https://cloud.digitalocean.com/apps/new?repo=https://github.com/samestrin/claude-to-chatgpt-digitalocean/tree/main&refcode=2d3f5d7c5fbe)

## DigitalOcean App Platform Setup

1. Navigate to your project in the DigitalOcean App Platform.
2. Go to the "Commands" section.
3. Under "Run Command", enter the following:

```
heroku-nginx -C nginx.conf .
```

## **Endpoints**

### **Chat Completion**

**Endpoint:** `/v1/chat/completions`  
**Method:** POST

Simulate ChatGPT-like interaction by sending a message to the Claude model.

#### **Parameters**

- `model`: The OpenAI model (e.g., 'gpt-3.5-turbo') or Claude model (e.g.,'claude-instant-1') to use. (OpenAI models are automatically mapped to Claude models.)
- `messages`: An array of message objects where each message has a `role` ('user' or 'assistant') and `content`.

#### **Example Usage**

Use a tool like Postman or curl to make a request:

```bash
curl -X POST http://localhost:[PORT]/v1/chat/completions \
-H "Content-Type: application/json" \
-d '{
    "model": "claude-instant-1",
    "messages": [
        {"role": "user", "content": "Hello, how are you?"}
    ]
}'
```

The server will process the request and return the model's response in JSON format.

### **Model Information**

**Endpoint:** `/v1/models`  
**Method:** GET

Retrieve information about the available models.

#### **Example Usage**

Use curl to make a request:

```bash
curl http://localhost:[PORT]/v1/models
```

The server will return a list of available models and their details in JSON format.

### **CORS Pre-flight Request**

**Endpoint:** `/`  
**Method:** OPTIONS

Handle pre-flight requests for CORS (Cross-Origin Resource Sharing). This endpoint provides necessary headers in response to pre-flight checks performed by browsers to ensure that the server accepts requests from allowed origins.

#### **Example Usage**

This is typically used by browsers automatically before sending actual requests, but you can manually test CORS settings using curl:

```bash
curl -X OPTIONS http://localhost:[PORT]/ \
-H "Access-Control-Request-Method: POST" \
-H "Origin: http://example.com"
```

The server responds with appropriate CORS headers such as Access-Control-Allow-Origin.

## Options

This application can be configured with various options through environment variables:

- **CLAUDE_API_KEY**: Your API key for accessing Claude API.
- **CLAUDE_BASE_URL**: The endpoint URL for the Claude API.

## Contribute

Contributions to this project are welcome. Please fork the repository and submit a pull request with your changes or improvements.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Share

[![Twitter](https://img.shields.io/badge/X-Tweet-blue)](https://twitter.com/intent/tweet?text=Check%20out%20this%20awesome%20project!&url=https://github.com/samestrin/claude-to-chatgpt-digitalocean) [![Facebook](https://img.shields.io/badge/Facebook-Share-blue)](https://www.facebook.com/sharer/sharer.php?u=https://github.com/samestrin/claude-to-chatgpt-digitalocean) [![LinkedIn](https://img.shields.io/badge/LinkedIn-Share-blue)](https://www.linkedin.com/sharing/share-offsite/?url=https://github.com/samestrin/claude-to-chatgpt-digitalocean)
