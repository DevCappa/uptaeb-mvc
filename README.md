# Proyecto MVC Básico en PHP

Este repositorio contiene una estructura básica para una aplicación web PHP siguiendo el patrón Modelo-Vista-Controlador (MVC). Incluye características esenciales como enrutamiento, interacción con base de datos, manejo de esquemas, principios SOLID y medidas de seguridad.

## Características Principales

*   **Arquitectura MVC:** Separación clara de responsabilidades (Modelos para datos, Vistas para presentación, Controladores para lógica de aplicación).
*   **Composer:** Gestión de dependencias PHP.
*   **Enrutamiento Limpio:** Uso de `nikic/fast-route` para manejar rutas amigables (ej. `/usuarios/editar/1`) y diferentes métodos HTTP (GET, POST, PUT, DELETE).
*   **Base de Datos:**
    *   Conexión mediante PDO (PHP Data Objects).
    *   Configuración centralizada a través de variables de entorno (`.env`) y `config/database.php`.
    *   Patrón Singleton para la conexión (`App\Core\Database`).
*   **Migraciones de Esquema:**
    *   Definición de esquemas de tabla en `database/schema/`.
    *   Script `bin/migrate.php` para crear automáticamente las tablas faltantes en la base de datos.
*   **Helpers (Traits):**
    *   `ValidationHelper`: Para validaciones comunes (email, regex, etc.).
    *   `FileHelper`: Funcionalidad básica para subida de archivos.
    *   `DateTimeHelper`: Para formateo de fechas y horas.
*   **Seguridad:**
    *   **Protección CSRF:** Implementada mediante tokens en sesión y verificación en formularios.
    *   **Hashing de Contraseñas:** Uso de `password_hash()` y `password_verify()` (implementado en el controlador, listo para usar en autenticación).
    *   **Prevención XSS:** Uso de `htmlspecialchars()` en las vistas para escapar la salida.
    *   **Sentencias Preparadas (PDO):** Para prevenir inyección SQL en las consultas a la base de datos.
*   **Principios SOLID y Clean Code:**
    *   **Interfaces:** (`UserModelInterface`, `DatabaseConnectionInterface`) para definir contratos.
    *   **Inyección de Dependencias (DI):** Los controladores y modelos reciben sus dependencias (como `UserModelInterface` o `PDO`) a través del constructor, promoviendo el desacoplamiento (DIP).
    *   **Código Organizado:** Estructura de directorios clara y uso de namespaces.

## Estructura del Proyecto

```
/
├── app/                     # Núcleo de la aplicación
│   ├── Controllers/         # Clases Controladoras (lógica de solicitud)
│   │   └── Admin/           # Ejemplo: Controladores específicos de admin
│   ├── Core/                # Clases base del framework (Database, Controller, Migrator)
│   ├── Interfaces/          # Contratos (Interfaces PHP)
│   ├── Models/              # Clases de Modelo (lógica de datos, interacción BD)
│   ├── Traits/              # Traits reutilizables (Helpers)
│   └── Views/               # Archivos de Plantilla/Vista (HTML/PHP)
├── bin/                     # Scripts ejecutables desde CLI
│   └── migrate.php        # Script para ejecutar migraciones de BD
├── config/                  # Archivos de configuración
│   └── database.php       # Configuración de la base de datos
├── database/                # Relacionado con la base de datos (fuera del núcleo app)
│   └── schema/            # Archivos de definición de esquemas de tabla
├── public/                  # Directorio público (Document Root del servidor web)
│   ├── index.php          # Punto de entrada único (Front Controller)
│   └── ...                # Assets (CSS, JS, imágenes - si se añaden)
├── routes/                  # Definición de rutas de la aplicación
│   └── web.php            # Rutas web
├── vendor/                  # Dependencias de Composer (gestionado por Composer)
├── .env                     # Variables de entorno locales (¡NO versionar!)
├── .env.example             # Archivo de ejemplo para .env
├── .gitignore               # Archivos/Directorios ignorados por Git
├── composer.json            # Definición de dependencias y autoloading
├── composer.lock            # Lock de dependencias
└── README.md                # Este archivo
```

## Requisitos

*   PHP >= 8.0
*   Composer
*   Servidor de Base de Datos (Ej: MySQL, MariaDB, PostgreSQL - configurado para MySQL por defecto)
*   Servidor Web (Ej: Apache, Nginx) con soporte para reescritura de URL (mod_rewrite o equivalente).

## Instalación y Configuración

1.  **Clonar el Repositorio:**
    ```bash
    git clone <url-del-repositorio>
    cd <directorio-del-proyecto>
    ```

2.  **Instalar Dependencias:**
    ```bash
    composer install
    ```

3.  **Configurar Variables de Entorno:**
    *   Copia el archivo de ejemplo: `cp .env.example .env`
    *   Edita el archivo `.env` con tus credenciales de base de datos:
        ```dotenv
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1        # O la IP/host de tu servidor de BD
        DB_PORT=3306
        DB_DATABASE=nombre_de_tu_bd # Reemplaza con el nombre de tu base de datos
        DB_USERNAME=tu_usuario_bd   # Reemplaza con tu usuario
        DB_PASSWORD=tu_password_bd # Reemplaza con tu contraseña
        ```
    *   **Importante:** Asegúrate de que la base de datos (`DB_DATABASE`) ya exista en tu servidor. El script de migración creará las *tablas* dentro de ella, pero no la base de datos en sí.

4.  **Ejecutar Migraciones:**
    Desde la raíz del proyecto, ejecuta:
    ```bash
    php bin/migrate.php
    ```
    Esto creará la tabla `users` (y cualquier otra definida en `database/schema/`) si no existe.

5.  **Configurar Servidor Web:**
    *   Apunta el `DocumentRoot` (o raíz del sitio) de tu servidor web al directorio `public/`.
    *   Asegúrate de que la reescritura de URL esté habilitada y configurada para dirigir todas las solicitudes que no sean archivos existentes a `public/index.php` (el archivo `.htaccess` incluido funciona para Apache con `mod_rewrite` habilitado).
    *   Ejemplo configuración básica Apache (asegúrate que `AllowOverride All` esté habilitado para el directorio):
        ```apache
        <VirtualHost *:80>
            ServerName tu-dominio.local
            DocumentRoot "/ruta/completa/a/tu-proyecto/public"
            <Directory "/ruta/completa/a/tu-proyecto/public">
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
            </Directory>
            ErrorLog ${APACHE_LOG_DIR}/error.log
            CustomLog ${APACHE_LOG_DIR}/access.log combined
        </VirtualHost>
        ```

## Ejecución (Desarrollo Local)

Una vez configurado el servidor web, accede a la URL que hayas configurado (ej. `http://tu-dominio.local`).

Alternativamente, para pruebas rápidas (puede no manejar bien todas las rutas sin configuración adicional), puedes usar el servidor incorporado de PHP desde la raíz del proyecto:

```bash
php -S localhost:8000 -t public
```

Y luego acceder a `http://localhost:8000` en tu navegador.

## Despliegue (Ejemplo Conceptual)

El despliegue en un servidor de producción seguirá pasos similares a la instalación, pero con algunas consideraciones adicionales:

1.  **Transferir Código:** Usa Git (pull/clone) o herramientas de despliegue para llevar el código al servidor.
2.  **Instalar Dependencias (Producción):**
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
3.  **Crear `.env`:** **Nunca cometas el archivo `.env` de producción.** Créalo directamente en el servidor con las credenciales y configuraciones de producción.
4.  **Ejecutar Migraciones:**
    ```bash
    php bin/migrate.php
    ```
5.  **Configurar Servidor Web:** Similar a la configuración local, apunta el DocumentRoot a `public/` y habilita la reescritura de URL.
6.  **Permisos:** Asegúrate de que el servidor web tenga permisos de escritura en directorios necesarios si añades subida de archivos (`public/uploads` si se usa) o caché/logs.
7.  **Seguridad Adicional:**
    *   Deshabilitar la muestra de errores de PHP en producción (`display_errors = Off` en `php.ini`).
    *   Configurar HTTPS.
    *   Revisar permisos de archivos/directorios.

## Próximos Pasos / Mejoras Posibles

*   Implementar autenticación de usuarios completa (`AuthController`).
*   Añadir un Contenedor de Inyección de Dependencias (ej. `PHP-DI`).
*   Refinar la validación (crear clases Request o servicios Validator).
*   Implementar mensajes Flash (para mostrar notificaciones de éxito/error después de redirecciones).
*   Añadir Pruebas (Unitarias, Integración).
*   Implementar un sistema de Logging más robusto.
*   Integrar WebSockets (usando Ratchet, Swoole o servicios como Pusher).
*   Mejorar el manejo de errores y vistas de error personalizadas.

## Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo `LICENSE` (si existe) o la información en `composer.json` para más detalles. 