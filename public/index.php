<?php

declare(strict_types=1);

// --- Habilitar reporte de errores detallado para depuración ---
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// --- Fin reporte de errores ---

// Carga el autoloader de Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Importa la clase Application
use App\Core\Application;

// Crea una instancia de la aplicación
// El constructor de Application se encarga de cargar .env, iniciar sesión y registrar rutas
$app = new Application();

// Ejecuta la aplicación
// El método run() se encarga de despachar la ruta y ejecutar el controlador
$app->run(); 