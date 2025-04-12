<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form { display: inline; }
        .actions button, .add-link { padding: 5px 10px; text-decoration: none; border-radius: 3px; cursor: pointer; }
        .actions .edit-link { background-color: #ffc107; color: black; border: none; }
        .actions .delete-button { background-color: #dc3545; color: white; border: none; }
        .add-link { background-color: #28a745; color: white; display: inline-block; margin-bottom: 15px; }
        .logout-link { float: right; }
        .success { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Administrar Usuarios</h2>
        <a href="/uptaeb-mvc/logout" class="logout-link">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>)</a>

        <?php if (isset($_GET['success'])): ?>
            <p class="success">
                <?php 
                switch ($_GET['success']) {
                    case 'created': echo "Usuario creado exitosamente."; break;
                    case 'updated': echo "Usuario actualizado exitosamente."; break;
                    case 'deleted': echo "Usuario eliminado exitosamente."; break;
                }
                ?>
            </p>
        <?php endif; ?>

        <a href="/uptaeb-mvc/admin/users/create" class="add-link">Añadir Nuevo Usuario</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)$user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="actions">
                                <a href="/uptaeb-mvc/admin/users/<?php echo $user['id']; ?>/edit" class="edit-link">Editar</a>
                                
                                <?php // No permitir borrar al usuario actual ?>
                                <?php if (isset($_SESSION['user_id']) && $user['id'] !== $_SESSION['user_id']): ?>
                                    <form action="/uptaeb-mvc/admin/users/<?php echo $user['id']; ?>" method="POST" style="display:inline;">
                                        <?php // Simulación de método DELETE ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <?php echo $controller->csrfField(); // <-- Añadir campo CSRF ?>
                                        <button type="submit" class="delete-button" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?');">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 