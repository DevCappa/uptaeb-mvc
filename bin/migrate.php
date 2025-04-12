<?php

// Script para ejecutar las migraciones de base de datos

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cargar variables de entorno (necesarias para Database::getInstance)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    echo "Advertencia: No se pudo cargar el archivo .env. " . $e->getMessage() . "\n";
    // Continuar de todos modos, puede que las variables estén definidas en el sistema
}

use App\Core\Migrator;

// Verificar que se ejecuta desde CLI
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ser ejecutado desde la línea de comandos (CLI).\n");
}

try {
    $migrator = new Migrator();
    $migrator->run();
    exit(0); // Éxito
} catch (\PDOException $e) {
    echo "Error de base de datos durante la migración: " . $e->getMessage() . "\n";
    exit(1); // Error
} catch (\Exception $e) {
    echo "Error inesperado durante la migración: " . $e->getMessage() . "\n";
    exit(1); // Error
} 