<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\UserModelInterface;

class AuthController
{
    private UserModelInterface $userModel;

    public function __construct(UserModelInterface $userModel)
    {
        $this->userModel = $userModel;
    }

    public function showLoginForm(): void
    {
        // Pasar variable de error si existe
        $error = $_GET['error'] ?? null;
        // Cargar la vista del formulario de login
        require_once dirname(__DIR__, 2) . '/app/Views/auth/login.view.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Opcional: Manejar o redirigir si no es POST
            header('Location: /uptaeb-mvc/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validación básica
        if (empty($email) || empty($password)) {
            header('Location: /uptaeb-mvc/login?error=empty');
            exit;
        }

        // Buscar usuario por email
        $user = $this->userModel->findUserByEmail($email);

        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // ¡Autenticación exitosa!
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);

            // Guardar información del usuario en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['logged_in'] = true;

            // Redirigir a una página protegida (ej. dashboard o admin users)
            header('Location: /uptaeb-mvc/admin/users');
            exit;
        } else {
            // Fallo de autenticación
            header('Location: /uptaeb-mvc/login?error=invalid');
            exit;
        }
    }

    public function logout(): void
    {
        // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Destruir la sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Redirigir a la página de login
        header('Location: /uptaeb-mvc/login?logout=success');
        exit;
    }
} 