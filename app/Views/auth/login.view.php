<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        label, input { display: block; margin-bottom: 10px; width: 95%; }
        input[type="submit"] { width: auto; cursor: pointer; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .success { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error">
                <?php 
                switch ($_GET['error']) {
                    case 'empty': echo "Por favor, completa todos los campos."; break;
                    case 'invalid': echo "Email o contraseña incorrectos."; break;
                    case 'auth_required': echo "Necesitas iniciar sesión para acceder a esta página."; break;
                    default: echo "Error desconocido.";
                }
                ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <p class="success">Has cerrado sesión correctamente.</p>
        <?php endif; ?>

        <form action="/uptaeb-mvc/login" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <input type="submit" value="Iniciar Sesión">
            </div>
        </form>
    </div>
</body>
</html> 