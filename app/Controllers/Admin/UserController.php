<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Interfaces\UserModelInterface;
use Psr\Log\LoggerInterface;
use App\Traits\ValidationHelper;
use App\Traits\FileHelper;
use App\Traits\DateTimeHelper;
use PDOException;

class UserController extends Controller
{
    use ValidationHelper;
    use FileHelper;
    use DateTimeHelper;

    private UserModelInterface $userModel;
    private LoggerInterface $logger;

    public function __construct(UserModelInterface $userModel, LoggerInterface $logger)
    {
        $this->userModel = $userModel;
        $this->logger = $logger;
        //$this->checkAuthentication();
    }

    private function checkAuthentication(): void
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->logger->warning('Acceso no autenticado a UserController denegado.');
            header('Location: /uptaeb-mvc/login?error=auth_required');
            exit;
        }
    }

    /**
     * Muestra la lista de usuarios.
     */
    public function index(): void
    {
        try {
            $users = $this->userModel->getAllUsers();
            $this->view('admin/users/index', ['users' => $users]);
        } catch (\Exception $e) {
            $this->logger->error("Error en UserController::index", ['exception' => $e]);
            http_response_code(500);
            echo "Error interno del servidor al cargar usuarios.";
        }
    }

    public function create(): void
    {
        $this->view('admin/users/create');
    }

    public function store(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(403);
            echo "Error: Token CSRF inválido.";
            return;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($name) || !$this->isAlphaSpace($name)) {
            $errors['name'] = 'El nombre es inválido (solo letras y espacios).';
        }
        if (!$this->isValidEmail($email)) {
            $errors['email'] = 'El formato del correo electrónico no es válido.';
        }
        if (empty($password) || strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            $this->logger->info('Error de validación al crear usuario.', ['errors' => $errors, 'data' => $_POST]);
            $this->view('admin/users/create', ['errors' => $errors, 'old' => $_POST]);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            $this->logger->critical("Error crítico al hacer hash de la contraseña.");
            http_response_code(500);
            echo "Error interno del servidor.";
            return;
        }

        try {
            $userId = $this->userModel->createUser($name, $email, $passwordHash);

            if ($userId === false) {
                http_response_code(409);
                $errors['email'] = 'El correo electrónico ya está registrado.';
                $this->view('admin/users/create', ['errors' => $errors, 'old' => $_POST]);
            } else {
                $this->logger->info("Usuario creado exitosamente.", ['user_id' => $userId, 'name' => $name]);
                header('Location: /uptaeb-mvc/admin/users?success=created');
                exit;
            }
        } catch (PDOException $e) {
            $this->logger->error("PDOException en UserController::store", ['exception' => $e]);
            http_response_code(500);
            echo "Error de base de datos al crear el usuario.";
        } catch (\Exception $e) {
            $this->logger->error("Exception en UserController::store", ['exception' => $e]);
            http_response_code(500);
            echo "Error interno inesperado.";
        }
    }

    public function edit(string $id): void
    {
        try {
            $userId = (int)$id;
            $user = $this->userModel->findUserById($userId);

            if (!$user) {
                http_response_code(404);
                $this->logger->warning("Intento de editar usuario no encontrado.", ['id' => $id]);
                echo "Usuario no encontrado";
                return;
            }

            $this->view('admin/users/edit', ['user' => $user]);
        } catch (\Exception $e) {
            $this->logger->error("Error en UserController::edit", ['id' => $id, 'exception' => $e]);
            http_response_code(500);
            echo "Error interno al cargar el usuario.";
        }
    }

    public function update(string $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(403);
            echo "Error: Token CSRF inválido.";
            return;
        }

        if (strtoupper($_POST['_method'] ?? '') !== 'PUT') {
            http_response_code(405);
            echo "Método no permitido.";
            return;
        }

        $userId = (int)$id;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($name) || !$this->isAlphaSpace($name)) {
            $errors['name'] = 'El nombre es inválido.';
        }
        if (!$this->isValidEmail($email)) {
            $errors['email'] = 'El email no es válido.';
        }

        if (!empty($password) && strlen($password) < 8) {
            $errors['password'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            $this->logger->info('Error de validación al actualizar usuario.', ['id' => $userId, 'errors' => $errors, 'data' => $_POST]);
            $currentUser = $this->userModel->findUserById($userId);
            $this->view('admin/users/edit', [
                'user' => $currentUser,
                'errors' => $errors
            ]);
            return;
        }

        try {
            $success = $this->userModel->updateUser($userId, $name, $email);

            if ($success && !empty($password)) {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
                if ($newPasswordHash === false) {
                    $this->logger->critical("Error crítico al hacer hash de la nueva contraseña.", ['user_id' => $userId]);
                    $success = false;
                } else {
                    $success = $this->userModel->updateUserPassword($userId, $newPasswordHash);
                    if (!$success) {
                        $this->logger->error("Error al actualizar la contraseña del usuario.", ['user_id' => $userId]);
                    }
                }
            }

            if ($success) {
                $this->logger->info("Usuario actualizado exitosamente.", ['user_id' => $userId, 'name' => $name]);
                header('Location: /uptaeb-mvc/admin/users?success=updated');
                exit;
            } else {
                http_response_code(500);
                $errors['general'] = 'No se pudo actualizar el usuario (posible error de BD o hash).';
                $currentUser = $this->userModel->findUserById($userId);
                $this->view('admin/users/edit', [
                    'user' => $currentUser,
                    'errors' => $errors
                ]);
            }
        } catch (PDOException $e) {
            $this->logger->error("PDOException en UserController::update", ['id' => $userId, 'exception' => $e]);
            http_response_code(500);
            echo "Error de base de datos al actualizar.";
        } catch (\Exception $e) {
            $this->logger->error("Exception en UserController::update", ['id' => $userId, 'exception' => $e]);
            http_response_code(500);
            echo "Error interno inesperado.";
        }
    }

    public function destroy(string $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(403);
            echo "Error: Token CSRF inválido.";
            return;
        }

        if (strtoupper($_POST['_method'] ?? '') !== 'DELETE') {
            http_response_code(405);
            echo "Método no permitido.";
            return;
        }

        $userId = (int)$id;

        if (isset($_SESSION['user_id']) && $userId === $_SESSION['user_id']) {
            http_response_code(403);
            $this->logger->warning("Intento de auto-eliminación denegado.", ['user_id' => $userId]);
            echo "No puedes eliminar tu propia cuenta.";
            exit;
        }

        try {
            $success = $this->userModel->deleteUser($userId);

            if ($success) {
                $this->logger->info("Usuario eliminado exitosamente.", ['user_id' => $userId]);
                header('Location: /uptaeb-mvc/admin/users?success=deleted');
                exit;
            } else {
                http_response_code(404);
                $this->logger->error("No se pudo eliminar el usuario.", ['user_id' => $userId]);
                echo "No se pudo eliminar el usuario (quizás ya fue eliminado o no existe).";
            }
        } catch (PDOException $e) {
            $this->logger->error("PDOException en UserController::destroy", ['id' => $userId, 'exception' => $e]);
            http_response_code(500);
            echo "Error de base de datos al eliminar.";
        } catch (\Exception $e) {
            $this->logger->error("Exception en UserController::destroy", ['id' => $userId, 'exception' => $e]);
            http_response_code(500);
            echo "Error interno inesperado.";
        }
    }
} 