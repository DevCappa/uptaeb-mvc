<?php

declare(strict_types=1);

namespace App\Interfaces;

interface UserModelInterface
{
    public function getAllUsers(): array;

    public function findUserById(int $id): ?array;

    public function findUserByEmail(string $email): ?array;

    public function createUser(string $name, string $email, string $passwordHash): int|false;

    public function updateUser(int $id, string $name, string $email): bool;

    public function updateUserPassword(int $id, string $newPasswordHash): bool;

    public function deleteUser(int $id): bool;

    // Podríamos añadir más métodos necesarios, como:
    // public function findUserByEmail(string $email): ?array;
} 