<?php

declare(strict_types=1);

namespace App\Interfaces;

use PDO;

/**
 * Interfaz simple para obtener una conexión PDO.
 */
interface DatabaseConnectionInterface
{
    public function getConnection(): PDO;
} 