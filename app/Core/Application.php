<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use App\Models\UserModel;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Core\Database;
use PDOException;
// Monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PDO;

class Application
{
    private $dispatcher;
    private Logger $logger; // Propiedad para el logger

    public function __construct()
    {
        $this->initializeLogger(); // Inicializar el logger primero
        $this->loadEnvironment();
        $this->connectDatabaseAndCheckSchema(); // Llamar al método combinado
        $this->startSession();
        $this->registerRoutes();
    }

    // Método para inicializar Monolog
    private function initializeLogger(): void
    {
        $logFilePath = dirname(__DIR__, 2) . '/logs/app.log';
        $errorLogFilePath = dirname(__DIR__, 2) . '/logs/error.log';

        // Crear el logger principal
        $this->logger = new Logger('app');

        // Handler para logs generales (INFO y superior) en app.log
        $this->logger->pushHandler(new StreamHandler($logFilePath, Level::Info));

        // Handler específico para errores (ERROR y superior) en error.log
        $this->logger->pushHandler(new StreamHandler($errorLogFilePath, Level::Error));
    }

    // Método público para acceder al logger desde fuera
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    private function loadEnvironment(): void
    {
        try {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->load();
        } catch (\Dotenv\Exception\InvalidPathException $e) {
            // Usar Monolog para la advertencia
            $this->logger->warning('No se pudo cargar el archivo .env', ['exception' => $e->getMessage()]);
        }
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function registerRoutes(): void
    {
        // Carga las rutas desde el archivo web.php
        $routesCallback = require_once dirname(__DIR__, 2) . '/routes/web.php';
        // Crea el despachador usando la función de definición de rutas
        $this->dispatcher = simpleDispatcher($routesCallback);
    }

    public function run(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // --- Simulación de _method --- 
        // Si es POST y existe _method, usar ese como método HTTP para el router
        if ($requestMethod === 'POST' && !empty($_POST['_method'])) {
            $simulatedMethod = strtoupper(trim($_POST['_method']));
            if (in_array($simulatedMethod, ['PUT', 'DELETE', 'PATCH'])) { // Añadir otros si se usan
                $httpMethod = $simulatedMethod;
                $this->logger->info("Request method overridden by _method: {$httpMethod}");
            } else {
                // Método simulado no válido, usar el original y loguear advertencia
                $httpMethod = $requestMethod;
                 $this->logger->warning("Invalid _method value received: {$_POST['_method']}");
            }
        } else {
            $httpMethod = $requestMethod; // Usar el método real
        }
        // --- Fin simulación _method ---

        // Elimina la query string de la URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $uri = substr($requestUri, 0, $pos);
        } else {
            $uri = $requestUri;
        }
        $uri = rawurldecode($uri);

        $this->logger->info("Dispatching route: {$httpMethod} {$uri} (Actual method: {$requestMethod})");

        // Despacha la ruta usando el método (posiblemente simulado)
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        $this->handleRoute($routeInfo);
    }

    private function handleRoute(array $routeInfo): void
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->handleNotFound();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $this->handleMethodNotAllowed($allowedMethods);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->handleFoundRoute($handler, $vars);
                break;
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        $this->logger->notice('Route not found (404)', ['uri' => $_SERVER['REQUEST_URI']]);
        // Podrías cargar una vista 404 aquí
        echo '404 Not Found';
    }

    private function handleMethodNotAllowed(array $allowedMethods): void
    {
        http_response_code(405);
        $this->logger->notice('Method not allowed (405)', [
            'uri' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'allowed' => $allowedMethods
        ]);
        // Podrías cargar una vista 405 aquí
        echo '405 Method Not Allowed. Allowed methods: ' . implode(', ', $allowedMethods);
    }

    private function handleFoundRoute(mixed $handler, array $vars): void
    {
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                $controller = $this->resolveController($controllerClass);

                if ($controller) {
                    // Llama al método del controlador con los parámetros de la ruta
                    $this->logger->info("Routing to {$controllerClass}::{$method}");
                    try {
                        call_user_func_array([$controller, $method], $vars);
                        $this->logger->info("Successfully executed {$controllerClass}::{$method}");
                    } catch (\Throwable $e) {
                        // Capturar excepciones durante la ejecución del controlador
                        $this->handleServerError("Exception during controller execution", $controllerClass, $method, $e);
                    }
                } else {
                    // Error ya logueado en resolveController
                    // handleServerError ya fue llamado
                }
            } else {
                $this->handleServerError('Handler method not found in controller', $controllerClass, $method);
            }
        } else {
             $this->handleServerError('Invalid handler format defined in routes');
        }
    }

    private function resolveController(string $controllerClass): ?object
    {
        try {
            $pdo = Database::getInstance();
            $userModel = null;

            if ($controllerClass === AdminUserController::class) {
                $userModel = new UserModel($pdo, $this->logger);
                return new $controllerClass($userModel, $this->logger);
            } elseif ($controllerClass === AuthController::class) {
                 $userModel = new UserModel($pdo, $this->logger);
                 return new $controllerClass($userModel);
            } elseif ($controllerClass === HomeController::class) {
                 return new $controllerClass();
            } else {
                 return new $controllerClass();
            }
        } catch (PDOException $e) {
            $this->handleServerError('Database connection failed during controller resolution', $controllerClass, null, $e);
            return null;
        } catch (\Throwable $e) {
             if ($e instanceof \ReflectionException) {
                 $this->handleServerError("Reflection error resolving controller {$controllerClass}", $controllerClass, null, $e);
             } else {
                 $this->handleServerError("Controller instantiation or dependency resolution failed for {$controllerClass}", $controllerClass, null, $e);
             }
             return null;
        }
    }

    /**
     * Conecta a la BD y asegura que las tablas esenciales existan.
     * Termina la ejecución si falla la conexión o la creación de tablas esenciales.
     */
    private function connectDatabaseAndCheckSchema(): void
    {
        try {
            $pdo = Database::getInstance(); // Intenta conectar
            $this->ensureEssentialTablesExist($pdo); // Verifica/Crea tablas esenciales
        } catch (PDOException $e) {
            // Manejar error de conexión inicial (no se puede ni verificar tabla)
            $this->logger->critical("Error CRÍTICO: No se pudo conectar a la base de datos.", ['exception' => $e]);
            // Mostrar error genérico y terminar.
            // En un escenario real, podrías mostrar una página de error más amigable.
            http_response_code(503); // Service Unavailable
            echo "Error crítico: No se pudo establecer la conexión con la base de datos. Por favor, contacta al administrador.";
            exit; // Terminar ejecución
        } catch (\RuntimeException $e) {
            // Capturar errores de la creación de tablas desde ensureEssentialTablesExist
            $this->logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
            http_response_code(503);
            echo "Error crítico: " . htmlspecialchars($e->getMessage()) . " Por favor, contacta al administrador.";
            exit;
        }
    }

    /**
     * Verifica si las tablas esenciales (ej. 'users') existen y las crea si no.
     * @param PDO $pdo Instancia de PDO
     * @throws \RuntimeException Si falla la creación de una tabla esencial.
     */
    private function ensureEssentialTablesExist(PDO $pdo): void
    {
        // Podrías tener un array de archivos de schema a verificar/crear
        $schemaFiles = [
            dirname(__DIR__, 2) . '/database/schema/users.php'
            // Añadir aquí otros archivos de schema esenciales si los tienes
        ];

        foreach ($schemaFiles as $schemaFile) {
            if (!file_exists($schemaFile)) {
                $this->logger->error("Archivo de schema no encontrado: {$schemaFile}");
                continue; // O lanzar excepción si es crítico
            }

            $schemaConfig = require $schemaFile;
            $tableName = $schemaConfig['table_name'] ?? null;
            $sqlCreate = $schemaConfig['sql'] ?? null;

            if (!$tableName || !$sqlCreate) {
                $this->logger->error("Configuración de schema inválida en: {$schemaFile}");
                continue; // O lanzar excepción
            }

            try {
                // Intenta consultar la tabla
                $pdo->query("SELECT 1 FROM `" . $tableName . "` LIMIT 1");
                $this->logger->info("Tabla '{$tableName}' ya existe.");
            } catch (PDOException $e) {
                // Si falla porque no existe (chequeo del mensaje, puede variar)
                if (str_contains($e->getMessage(), 'Table') && str_contains($e->getMessage(), 'doesn\'t exist')) {
                    $this->logger->warning("Tabla '{$tableName}' no encontrada, intentando crearla desde {$schemaFile}...");
                    try {
                        $pdo->exec($sqlCreate);
                        $this->logger->info("Tabla '{$tableName}' creada exitosamente.");
                    } catch (PDOException $createException) {
                        // Error CRÍTICO al intentar crear la tabla
                        throw new \RuntimeException("Error crítico: No se pudo crear la tabla esencial '{$tableName}'.", 0, $createException);
                    }
                } else {
                    // Otro error de PDO al verificar la tabla
                     throw new \RuntimeException("Error inesperado de PDO al verificar la tabla '{$tableName}'.", 0, $e);
                }
            }
        }
    }

    // Modificar handleServerError para no intentar loguear si el logger falló (poco probable)
    private function handleServerError(string $message, ?string $class = null, ?string $method = null, ?\Throwable $exception = null): void
    {
        http_response_code(500);

        // Loguear solo si el logger está disponible
        if (isset($this->logger)) {
            $context = [
                'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'class' => $class,
                'method' => $method,
            ];
            if ($exception) {
                $context['exception_message'] = $exception->getMessage();
                $context['exception_trace'] = $exception->getTraceAsString();
            }
            $this->logger->error("Server Error: " . $message, $context);
        }

        // Mostrar mensaje (igual que antes)
        if (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
             echo "<h1>500 Internal Server Error</h1>";
             echo "<p>Error: " . htmlspecialchars($message) . "</p>";
             if ($exception) {
                echo "<pre>" . htmlspecialchars($exception->getMessage()) . "\n";
                echo htmlspecialchars($exception->getTraceAsString()) . "</pre>";
             }
        } else {
            echo '500 Internal Server Error';
        }
    }
} 