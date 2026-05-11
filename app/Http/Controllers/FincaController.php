<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Finca;

class FincaController extends Controller
{
    public function index(Request $request)
    {
        $query = Finca::with('usuario');

        if ($request->has('buscar') && $request->buscar !== '') {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_finca', 'like', "%$buscar%")
                  ->orWhere('ubicacion_finca', 'like', "%$buscar%")
                  ->orWhereHas('usuario', function($q) use ($buscar) {
                      $q->where('nombre_usuario', 'like', "%$buscar%")
                        ->orWhere('apellido1_usuario', 'like', "%$buscar%")
                        ->orWhere('correo', 'like', "%$buscar%");
                  });
            });
        }

        return response()->json($query->get(), 200);
    }
}