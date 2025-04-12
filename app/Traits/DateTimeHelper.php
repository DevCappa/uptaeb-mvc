<?php

declare(strict_types=1);

namespace App\Traits;

use DateTime;
use DateTimeZone;

trait DateTimeHelper
{
    /**
     * Formatea una fecha/hora a un formato específico, opcionalmente en una zona horaria.
     *
     * @param string|int|DateTime $dateInput Fecha como string, timestamp Unix o objeto DateTime.
     * @param string $format Formato deseado (ej: 'Y-m-d H:i:s', 'd/m/Y').
     * @param string|null $timezone Zona horaria deseada (ej: 'America/Caracas', 'UTC'). Si es null, usa la default.
     * @return string|false La fecha formateada o false en caso de error.
     */
    protected function formatDateTime(string|int|DateTime $dateInput, string $format = 'Y-m-d H:i:s', ?string $timezone = null): string|false
    {
        try {
            if ($dateInput instanceof DateTime) {
                $dateTime = clone $dateInput; // Clonar para no modificar el original
            } elseif (is_numeric($dateInput)) {
                $dateTime = new DateTime('@' . $dateInput);
            } else {
                $dateTime = new DateTime($dateInput);
            }

            if ($timezone !== null) {
                $dateTime->setTimezone(new DateTimeZone($timezone));
            }

            return $dateTime->format($format);
        } catch (\Exception $e) {
            error_log("Error formateando fecha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la fecha y hora actual en un formato y zona horaria específicos.
     *
     * @param string $format Formato deseado.
     * @param string|null $timezone Zona horaria deseada.
     * @return string|false La fecha/hora actual formateada o false en caso de error.
     */
    protected function getCurrentDateTime(string $format = 'Y-m-d H:i:s', ?string $timezone = null): string|false
    {
        return $this->formatDateTime('now', $format, $timezone);
    }
} 