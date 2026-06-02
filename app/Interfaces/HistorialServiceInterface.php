<?php

namespace App\Interfaces;

interface HistorialServiceInterface
{
    public function registrar(string $accion, string $tabla, $id): void;
}