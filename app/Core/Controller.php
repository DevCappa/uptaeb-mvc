<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Carga una vista y le pasa datos.
     *
     * @param string $view El nombre del archivo de vista (sin .view.php) Ej: 'admin/users/index'
     * @param array $data Datos para pasar a la vista (se extraerán como variables)
     * @return void
     */
    protected function view(string $view, array $data = []): void
    {
        // Construye la ruta completa al archivo de vista
        $viewPath = dirname(__DIR__) . '/Views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.view.php';

        if (file_exists($viewPath)) {
            // Pasar la instancia del controlador a la vista
            $data['controller'] = $this;

            // Extrae los datos del array asociativo a variables individuales
            // Ejemplo: ['users' => [...]] se convierte en $users = [...]
            extract($data);

            // Incluye el archivo de vista, que ahora tendrá acceso a las variables extraídas
            require $viewPath;
        } else {
            // Manejo básico de error si la vista no existe
            echo "Error: La vista '{$viewPath}' no fue encontrada.";
            // En una aplicación real, aquí lanzarías una excepción o mostrarías una página de error 500.
            http_response_code(500);
        }
    }

    /**
     * Genera o recupera el token CSRF de la sesión.
     * @return string El token CSRF.
     */
    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Genera un campo input oculto con el token CSRF.
     * @return string El HTML del campo input.
     */
    protected function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . $this->csrfToken() . '">';
    }

    /**
     * Verifica si el token CSRF enviado es válido.
     * @return bool True si es válido, False en caso contrario.
     */
    protected function verifyCsrfToken(): bool
    {
        $submittedToken = $_POST['_csrf_token'] ?? '';
        if (empty($submittedToken) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $submittedToken);
    }

    // Podrías añadir otros métodos útiles aquí, como redirecciones, etc.
    // protected function redirect(string $url): void
} 