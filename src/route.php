<?php

require 'handlers.php';

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute("POST", "/", "handleRequest");
    $r->addRoute("OPTIONS", "/", "handleOPTIONS");
    $r->addRoute("GET", "/v1/models", "handleGetModels");
});

$httpMethod = $_SERVER["REQUEST_METHOD"];
$uri = explode('?', $_SERVER["REQUEST_URI"], 2)[0];
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func($handler, $vars);
        break;
}

?>