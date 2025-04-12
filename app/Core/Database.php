<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use App\Interfaces\DatabaseConnectionInterface;

class Database implements DatabaseConnectionInterface
{
    private static ?PDO $instance = null;
    private static array $config = [];

    // Constructor privado para prevenir instanciación directa.
    private function __construct() {}

    // Clonación privada para prevenir duplicación.
    private function __clone() {}

    // Deserialización privada para prevenirla.
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Obtiene la instancia única de PDO (Singleton).
     * Implementa el método de la interfaz.
     *
     * @return PDO
     * @throws PDOException Si la conexión falla.
     * @throws \Exception Si la configuración no se ha cargado.
     */
    public function getConnection(): PDO
    {
        return self::getInstance();
    }

    /**
     * Obtiene la instancia única de PDO (Singleton).
     * Mantenemos getInstance para compatibilidad interna y con Migrator
     *
     * @return PDO
     * @throws PDOException Si la conexión falla.
     * @throws \Exception Si la configuración no se ha cargado.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                self::loadConfig();
            }

            $config = self::$config;
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                (int)$config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // En un entorno real, loguear el error detallado y lanzar una excepción más genérica o mostrar una página de error.
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                throw new PDOException("No se pudo conectar a la base de datos.", (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    /**
     * Carga la configuración de la base de datos desde el archivo config.
     *
     * @throws \Exception Si el archivo de configuración no existe o no es un array.
     */
    private static function loadConfig(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config/database.php';
        if (!file_exists($configPath)) {
            throw new \Exception("El archivo de configuración de la base de datos no existe: {$configPath}");
        }
        $config = require $configPath;
        if (!is_array($config)) {
            throw new \Exception("El archivo de configuración de la base de datos no devuelve un array.");
        }
        self::$config = $config;
    }

     /**
     * Método estático para obtener la configuración cargada.
     * Útil para el Migrator u otros componentes que necesiten la configuración.
     *
     * @return array
     * @throws \Exception Si la configuración no se ha cargado.
     */
    public static function getConfig(): array
    {
        if (empty(self::$config)) {
            self::loadConfig();
        }
        return self::$config;
    }
} 