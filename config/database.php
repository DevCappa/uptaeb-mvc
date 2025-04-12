<?php

declare(strict_types=1);

return [
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'      => $_ENV['DB_PORT'] ?? '3306',
    'database'  => $_ENV['DB_DATABASE'] ?? 'uptaeb_mvc',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => [
        // Opciones de PDO
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devolver arrays asociativos por defecto
        PDO::ATTR_EMULATE_PREPARES   => false, // Usar preparaciones nativas
    ],
]; 