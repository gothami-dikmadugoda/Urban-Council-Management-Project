<?php

class Router {
    private $routes = [];
    
    public function addRoute($path, $controller, $action) {
        $this->routes[$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($path) {
        if (isset($this->routes[$path])) {
            $controller = $this->routes[$path]['controller'];
            $action = $this->routes[$path]['action'];
            
            require_once __DIR__ . "/controllers/{$controller}.php";
            $controllerInstance = new $controller();
            $controllerInstance->$action();
        } else {
            // Handle 404
            header("HTTP/1.0 404 Not Found");
            require_once __DIR__ . "/views/404.php";
        }
    }
} 