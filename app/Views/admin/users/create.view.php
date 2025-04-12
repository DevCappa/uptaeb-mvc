<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Usuario</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        label, input { display: block; margin-bottom: 10px; width: 95%; }
        input[type="submit"], .back-link { width: auto; cursor: pointer; padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block; margin-top: 10px; }
        input[type="submit"] { background-color: #28a745; color: white; border: none; }
        .back-link { background-color: #6c757d; color: white; margin-left: 10px; }
        .error-message { color: red; font-size: 0.9em; margin-top: -5px; margin-bottom: 10px; }
        .field-error input { border-color: red; }
        .field-error label { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crear Nuevo Usuario</h2>

        <form action="/uptaeb-mvc/admin/users" method="POST">
             <?php // Campo CSRF (si se usa) ?>
             <?php echo $controller->csrfField(); // <-- Añadir campo CSRF ?>

            <div class="<?php echo isset($errors['name']) ? 'field-error' : ''; ?>">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>" required>
                <?php if (isset($errors['name'])): ?><p class="error-message"><?php echo $errors['name']; ?></p><?php endif; ?>
            </div>

            <div class="<?php echo isset($errors['email']) ? 'field-error' : ''; ?>">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                 <?php if (isset($errors['email'])): ?><p class="error-message"><?php echo $errors['email']; ?></p><?php endif; ?>
            </div>

            <div class="<?php echo isset($errors['password']) ? 'field-error' : ''; ?>">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?><p class="error-message"><?php echo $errors['password']; ?></p><?php endif; ?>
            </div>

            <div>
                <input type="submit" value="Crear Usuario">
                <a href="/uptaeb-mvc/admin/users" class="back-link">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html> 