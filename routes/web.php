<?php

use FastRoute\RouteCollector;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\Admin\UserController as AdminUserController;

// Función que será llamada por FastRoute\simpleDispatcher
return function(RouteCollector $r) {
    // Rutas GET
    $prefix = '/uptaeb-mvc';
    $r->addRoute('GET', $prefix, [HomeController::class, 'index']);
    $r->addRoute('GET', $prefix . '/login', [AuthController::class, 'showLoginForm']);
    $r->addRoute('GET', $prefix . '/logout', [AuthController::class, 'logout']);
    $r->addRoute('GET', $prefix . '/admin/users', [AdminUserController::class, 'index']);
    $r->addRoute('GET', $prefix . '/admin/users/create', [AdminUserController::class, 'create']);
    $r->addRoute('GET', $prefix . '/admin/users/{id:[0-9]+}/edit', [AdminUserController::class, 'edit']);
    // Ejemplo de ruta con parámetro opcional
    // $r->addRoute('GET', '/user[/{name}]', 'get_user_handler');

    // Rutas POST
    $r->addRoute('POST', $prefix . '/login', [AuthController::class, 'login']);
    $r->addRoute('POST', $prefix . '/admin/users', [AdminUserController::class, 'store']);

    // Rutas PUT/PATCH (Ejemplo)
    $r->addRoute('PUT', $prefix . '/admin/users/{id:[0-9]+}', [AdminUserController::class, 'update']);
    // $r->addRoute('PATCH', $prefix . '/admin/users/{id:[0-9]+}', [AdminUserController::class, 'partialUpdate']);

    // Rutas DELETE (Ejemplo)
    $r->addRoute('DELETE', $prefix . '/admin/users/{id:[0-9]+}', [AdminUserController::class, 'destroy']);

    // Ejemplo de grupo de rutas
    // $r->addGroup('/admin', function (RouteCollector $r) {
    //     $r->addRoute('GET', '/dashboard', 'admin_dashboard_handler');
    //     $r->addRoute('GET', '/settings', 'admin_settings_handler');
    // });
}; 