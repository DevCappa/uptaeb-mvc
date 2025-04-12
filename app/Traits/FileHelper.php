<?php

declare(strict_types=1);

namespace App\Traits;

trait FileHelper
{
    /**
     * Sube un archivo desde $_FILES a una ruta de destino.
     *
     * @param array $fileData Datos del archivo de $_FILES['nombre_campo']
     * @param string $destinationDirectory Directorio donde guardar el archivo (sin la barra final)
     * @param array $allowedMimeTypes Mime types permitidos (ej: ['image/jpeg', 'image/png'])
     * @param int $maxSize Tamaño máximo en bytes
     * @return string|false El nombre único del archivo guardado o false en caso de error.
     */
    protected function uploadFile(array $fileData, string $destinationDirectory, array $allowedMimeTypes = [], int $maxSize = 5 * 1024 * 1024): string|false
    {
        // Validaciones básicas
        if (
            !isset($fileData['error']) || is_array($fileData['error']) ||
            !isset($fileData['tmp_name']) || !isset($fileData['size']) || !isset($fileData['name'])
        ) {
            //throw new \RuntimeException('Parámetros de archivo inválidos.');
            error_log('Parámetros de archivo inválidos.');
            return false;
        }

        // Verificar errores de subida
        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                //throw new \RuntimeException('No se envió ningún archivo.');
                error_log('No se envió ningún archivo.'); return false;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                //throw new \RuntimeException('Tamaño de archivo excedido.');
                error_log('Tamaño de archivo excedido.'); return false;
            default:
                //throw new \RuntimeException('Error de subida desconocido.');
                error_log('Error de subida desconocido.'); return false;
        }

        // Verificar tamaño
        if ($fileData['size'] > $maxSize) {
            //throw new \RuntimeException('Tamaño de archivo excede el límite de ' . ($maxSize / 1024 / 1024) . ' MB.');
             error_log('Tamaño de archivo excede el límite.'); return false;
        }

        // Verificar MIME type (forma más segura)
        if (!empty($allowedMimeTypes)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileData['tmp_name']);
            if (false === array_search($mimeType, $allowedMimeTypes, true)) {
                //throw new \RuntimeException('Tipo de archivo no permitido ('.htmlspecialchars($mimeType).').');
                error_log('Tipo de archivo no permitido: ' . $mimeType); return false;
            }
        }

        // Generar nombre único para evitar colisiones y problemas de seguridad
        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $safeFilename = bin2hex(random_bytes(16)) . '.' . strtolower($fileExtension);
        $destinationPath = $destinationDirectory . DIRECTORY_SEPARATOR . $safeFilename;

        // Crear directorio de destino si no existe
        if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0775, true)) {
            //throw new \RuntimeException('No se pudo crear el directorio de destino.');
             error_log('No se pudo crear el directorio de destino: ' . $destinationDirectory); return false;
        }

        // Mover el archivo subido
        if (!move_uploaded_file($fileData['tmp_name'], $destinationPath)) {
            //throw new \RuntimeException('No se pudo mover el archivo subido.');
            error_log('No se pudo mover el archivo subido a: ' . $destinationPath); return false;
        }

        return $safeFilename;
    }

    // Aquí podrían ir funciones para redimensionar imágenes (requiere GD/Imagick),
    // leer contenido de documentos (requiere librerías específicas), etc.
} 