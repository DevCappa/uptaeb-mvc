<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        // Cargar la vista de inicio
        require_once dirname(__DIR__, 2) . '/app/Views/home/index.view.php';
    }
} 