<?php
namespace ClaudeToGPTAPI;

require_once __DIR__ . '/../vendor/autoload.php';

// Import necessary classes from FastRoute
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('POST', '/v1/chat/completions', 'ClaudeToGPTAPI\Handlers\RequestHandler::handle');
    $r->addRoute('OPTIONS', '/', 'ClaudeToGPTAPI\Handlers\OptionsHandler::handle');
    $r->addRoute('GET', '/v1/models', 'ClaudeToGPTAPI\Handlers\ModelsHandler::handle');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "Not Found";
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Method Not Allowed";
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func($handler, $vars);
        break;
}

