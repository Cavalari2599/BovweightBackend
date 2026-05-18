<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\GanaderoFacade;
use App\Strategies\OrdenarPorPeso;
use App\Strategies\OrdenarPorNombre;
use App\Strategies\OrdenarPorFechaNacimiento;

class GanaderoController extends Controller
{
    public function __construct(private GanaderoFacade $facade) {}

    public function getFincas(Request $request)
    {
        $fincas = $this->facade->getFincas($request->user()->identificacion_usuario);
        return response()->json($fincas, 200);
    }

    public function getAnimales(Request $request, int $idFinca)
    {
        $estrategia = match($request->query('ordenar', 'peso')) {
            'nombre' => new OrdenarPorNombre(),
            'fecha'  => new OrdenarPorFechaNacimiento(),
            default  => new OrdenarPorPeso(),
        };

        $animales = $this->facade->getAnimales($idFinca, $estrategia);
        return response()->json($animales, 200);
    }

    public function getPesajes(Request $request, string $nArete)
    {
        $pesajes = $this->facade->getPesajes($nArete);
        return response()->json($pesajes, 200);
    }

    public function getTratamientos(Request $request, string $nArete)
    {
        $tratamientos = $this->facade->getTratamientos($nArete);
        return response()->json($tratamientos, 200);
    }

    public function getResumenFinca(Request $request, int $idFinca)
    {
        $resumen = $this->facade->getResumenFinca($idFinca);
        return response()->json($resumen, 200);
    }
}