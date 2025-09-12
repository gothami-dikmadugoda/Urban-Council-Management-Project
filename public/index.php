<?php
require_once __DIR__ . '/../app/Router.php';

// Initialize router
$router = new Router();

// Define routes
$router->addRoute('/', 'HomeController', 'index');
$router->addRoute('/appointments', 'AppointmentController', 'index');
$router->addRoute('/appointments/process', 'AppointmentController', 'process');
$router->addRoute('/visitors', 'VisitorController', 'index');
$router->addRoute('/reports', 'ReportController', 'index');

// Get the current path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/public', '', $path); // Remove /public from path if using public directory

// Dispatch the route
$router->dispatch($path); 