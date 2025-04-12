<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Migrator
{
    private PDO $pdo;
    private string $schemaPath;
    private string $dbName;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->schemaPath = dirname(__DIR__, 2) . '/database/schema';
        // Obtenemos el nombre de la BD de la configuración para verificar la existencia de tablas
        $dbConfig = Database::getConfig();
        $this->dbName = $dbConfig['database'];
    }

    /**
     * Ejecuta las migraciones necesarias.
     *
     * @return void
     */
    public function run(): void
    {
        echo "Iniciando migraciones...\n";

        $schemaFiles = glob($this->schemaPath . '/*.php');

        if (empty($schemaFiles)) {
            echo "No se encontraron archivos de esquema en {$this->schemaPath}\n";
            return;
        }

        foreach ($schemaFiles as $file) {
            echo "Procesando esquema: " . basename($file) . "...\n";
            $schema = require $file;

            if (!isset($schema['table_name']) || !isset($schema['sql'])) {
                echo "  -> Error: Esquema inválido (falta 'table_name' o 'sql'). Saltando.\n";
                continue;
            }

            $tableName = $schema['table_name'];

            if ($this->tableExists($tableName)) {
                echo "  -> Tabla '{$tableName}' ya existe. Saltando.\n";
            } else {
                echo "  -> Tabla '{$tableName}' no existe. Creando...\n";
                try {
                    $this->pdo->exec($schema['sql']);
                    echo "  -> Tabla '{$tableName}' creada exitosamente.\n";
                } catch (PDOException $e) {
                    echo "  -> Error al crear tabla '{$tableName}': " . $e->getMessage() . "\n";
                    // Podrías detener el proceso si una migración falla
                    // exit(1);
                }
            }
        }

        echo "Migraciones completadas.\n";
    }

    /**
     * Verifica si una tabla existe en la base de datos.
     *
     * @param string $tableName
     * @return bool
     */
    private function tableExists(string $tableName): bool
    {
        try {
            // Usamos INFORMATION_SCHEMA que es estándar SQL
            $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->dbName, $tableName]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            echo "Error verificando tabla '{$tableName}': " . $e->getMessage() . "\n";
            // Asumir que no existe si hay error para intentar crearla?
            // O lanzar una excepción? Por ahora retornamos false.
            return false;
        }
    }
} 