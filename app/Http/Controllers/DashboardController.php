<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'usuarios_activos'   => User::where('estado', true)->count(),
            'usuarios_inactivos' => User::where('estado', false)->count(),
            'total_fincas'       => Finca::count(),
            'total_animales'     => Animal::count(),
            'total_pesajes'      => Pesaje::count(),
            'usuarios_por_rol'   => [
                'ganaderos'    => User::where('id_rol', 1)->count(),
                'veterinarios' => User::where('id_rol', 2)->count(),
                'ayudantes'    => User::where('id_rol', 3)->count(),
                'tecnicos'     => User::where('id_rol', 4)->count(),
            ]
        ], 200);
    }
}