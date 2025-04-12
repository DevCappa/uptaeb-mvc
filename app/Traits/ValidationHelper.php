<?php

declare(strict_types=1);

namespace App\Traits;

trait ValidationHelper
{
    /**
     * Verifica si una cadena es una dirección de correo electrónico válida.
     * @param string $email
     * @return bool
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Verifica si una cadena contiene solo caracteres alfabéticos y espacios.
     * @param string $text
     * @return bool
     */
    protected function isAlphaSpace(string $text): bool
    {
        // Expresión regular: ^ indica inicio, [a-zA-Z ]+ permite letras may/min y espacios (al menos uno), $ indica fin.
        return preg_match('/^[a-zA-Z ]+$/', $text) === 1;
    }

    /**
     * Verifica si una cadena cumple con un patrón de expresión regular personalizado.
     * @param string $pattern El patrón regex (incluyendo delimitadores, ej: '/patrón/i')
     * @param string $subject La cadena a validar
     * @return bool
     */
    protected function matchesRegex(string $pattern, string $subject): bool
    {
        return preg_match($pattern, $subject) === 1;
    }

    /**
     * Verifica si un valor es numérico.
     * @param mixed $value
     * @return bool
     */
    protected function isNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }
} 