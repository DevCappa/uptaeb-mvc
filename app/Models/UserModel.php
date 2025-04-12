<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\UserModelInterface;
use PDO;
use PDOException;
use Monolog\Logger;

class UserModel implements UserModelInterface
{
    private PDO $db;
    private Logger $logger;

    public function __construct(PDO $pdo, Logger $logger)
    {
        $this->db = $pdo;
        $this->logger = $logger;
    }

    /**
     * Obtiene todos los usuarios de la base de datos.
     * @return array
     */
    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, name, email, created_at, updated_at FROM users ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error en UserModel::getAllUsers", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Obtiene un usuario por ID de la base de datos.
     * @param int $id
     * @return array|null
     */
    public function findUserById(int $id): ?array
    {
        try {
            $sql = "SELECT id, name, email, created_at, updated_at FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            $this->logger->error("Error en UserModel::findUserById", ['id' => $id, 'exception' => $e]);
            return null;
        }
    }

    /**
     * Busca un usuario por email. Devuelve todos los campos, incluyendo el hash de la contraseña.
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, name, email, password, created_at, updated_at FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            $this->logger->error("Error en UserModel::findUserByEmail", ['email' => $email, 'exception' => $e]);
            return null;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param string $name
     * @param string $email
     * @param string $passwordHash Hash de la contraseña
     * @return int|false El ID del nuevo usuario o false en caso de error.
     */
    public function createUser(string $name, string $email, string $passwordHash): int|false
    {
        try {
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();
            $newId = $this->db->lastInsertId();
            return $newId ? (int)$newId : false;
        } catch (PDOException $e) {
            $context = ['name' => $name, 'email' => $email, 'exception' => $e];
            if ($e->getCode() == 1062) {
                $this->logger->warning("Intento de crear usuario con email duplicado.", $context);
            } else {
                $this->logger->error("Error en UserModel::createUser", $context);
            }
            return false;
        }
    }

    /**
     * Actualiza nombre y email de un usuario existente.
     * @param int $id
     * @param string $name
     * @param string $email
     * @return bool True si fue exitoso, false en caso contrario.
     */
    public function updateUser(int $id, string $name, string $email): bool
    {
        try {
            $sql = "UPDATE users SET name = :name, email = :email, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $context = ['id' => $id, 'name' => $name, 'email' => $email, 'exception' => $e];
            if ($e->getCode() == 1062) {
                $this->logger->warning("Intento de actualizar usuario a email duplicado.", $context);
            } else {
                $this->logger->error("Error en UserModel::updateUser", $context);
            }
            return false;
        }
    }

    /**
     * Actualiza la contraseña de un usuario existente.
     * @param int $id
     * @param string $newPasswordHash Nuevo hash de contraseña
     * @return bool True si fue exitoso, false en caso contrario.
     */
    public function updateUserPassword(int $id, string $newPasswordHash): bool
    {
        try {
            $sql = "UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $newPasswordHash);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logger->error("Error en UserModel::updateUserPassword", ['id' => $id, 'exception' => $e]);
            return false;
        }
    }

    /**
     * Elimina un usuario por ID.
     * @param int $id
     * @return bool True si fue exitoso, false en caso contrario.
     */
    public function deleteUser(int $id): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logger->error("Error en UserModel::deleteUser", ['id' => $id, 'exception' => $e]);
            return false;
        }
    }
} 