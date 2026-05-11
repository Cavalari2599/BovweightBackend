<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialAcciones;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $query = HistorialAcciones::with('usuario')->orderBy('fecha_accion', 'desc');

        if ($request->has('buscar') && $request->buscar !== '') {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('accion', 'like', "%$buscar%")
                  ->orWhere('tabla_afectada', 'like', "%$buscar%")
                  ->orWhere('id_registro', 'like', "%$buscar%");
            });
        }

        return response()->json($query->get(), 200);
    }
}