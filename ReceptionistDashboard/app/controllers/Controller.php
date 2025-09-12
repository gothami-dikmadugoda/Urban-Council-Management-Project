<?php

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }
    
    protected function model($model) {
        require_once __DIR__ . "/../models/{$model}.php";
        return new $model();
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
} 