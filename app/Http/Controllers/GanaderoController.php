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

    public function crearFinca(Request $request)
    {
        $request->validate([
            'nombre_finca'    => 'required|string|max:150',
            'ubicacion_finca' => 'required|string|max:255',
        ]);

        $finca = $this->facade->crearFinca($request->user()->identificacion_usuario, $request->all());
        return response()->json(['message' => 'Finca creada correctamente.', 'finca' => $finca], 201);
    }

    public function editarFinca(Request $request, int $idFinca)
    {
        $request->validate([
            'nombre_finca'    => 'sometimes|string|max:150',
            'ubicacion_finca' => 'sometimes|string|max:255',
        ]);

        $finca = $this->facade->editarFinca($idFinca, $request->all());
        return response()->json(['message' => 'Finca actualizada correctamente.', 'finca' => $finca], 200);
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

    public function crearAnimal(Request $request, int $idFinca)
    {
        $request->validate([
            'n_arete'          => 'required|string|max:50|unique:animales,n_arete',
            'nombre_animal'    => 'nullable|string|max:100',
            'raza'             => 'nullable|string|max:100',
            'sexo'             => 'nullable|in:M,F',
            'edad'             => 'nullable|integer',
            'peso'             => 'nullable|numeric',
            'estado'           => 'required|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'foto_animal'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $datos = $request->except('foto_animal');
        if ($request->hasFile('foto_animal')) {
            $datos['foto_animal'] = $request->file('foto_animal')->store('animales', 'public');
        }
        if (!empty($datos['fecha_nacimiento'])) {
            $datos['edad'] = \Carbon\Carbon::parse($datos['fecha_nacimiento'])->age;
        }

        $animal = $this->facade->crearAnimal($idFinca, $datos);
        return response()->json(['message' => 'Animal creado correctamente.', 'animal' => $animal], 201);
    }

    public function editarAnimal(Request $request, string $nArete)
    {
        $request->validate([
            'nombre_animal'    => 'nullable|string|max:100',
            'raza'             => 'nullable|string|max:100',
            'sexo'             => 'nullable|in:M,F',
            'edad'             => 'nullable|integer',
            'peso'             => 'nullable|numeric',
            'estado'           => 'sometimes|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'foto_animal'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $datos = $request->except(['foto_animal', '_method']);
        if ($request->hasFile('foto_animal')) {
            $datos['foto_animal'] = $request->file('foto_animal')->store('animales', 'public');
        }
        if (!empty($datos['fecha_nacimiento'])) {
            $datos['edad'] = \Carbon\Carbon::parse($datos['fecha_nacimiento'])->age;
        }

        $animal = $this->facade->editarAnimal($nArete, $datos);
        return response()->json(['message' => 'Animal actualizado correctamente.', 'animal' => $animal], 200);
    }

    public function getPesajes(Request $request, string $nArete)
    {
        $pesajes = $this->facade->getPesajes($nArete);
        return response()->json($pesajes, 200);
    }

    public function estimarPeso(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192',
            'sexo' => 'required|in:M,F',
        ]);

        try {
            $peso = $this->facade->estimarPeso($request->file('foto'), $request->sexo);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['peso_estimado_kg' => $peso], 200);
    }

    public function crearPesaje(Request $request, string $nArete)
    {
        $request->validate([
            'peso_aproximado' => 'required|numeric|min:1',
            'foto'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
            'sexo'            => 'nullable|in:M,F',
        ]);

        $pesaje = $this->facade->crearPesaje(
            $nArete,
            (float) $request->peso_aproximado,
            $request->file('foto'),
            $request->sexo,
        );

        return response()->json(['message' => 'Pesaje registrado correctamente.', 'pesaje' => $pesaje], 201);
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

    public function getRecordatorios(Request $request)
    {
        return response()->json($this->facade->getRecordatorios($request->user()->identificacion_usuario), 200);
    }

    public function getTodosAnimales(Request $request)
    {
        return response()->json($this->facade->getTodosAnimales($request->user()->identificacion_usuario), 200);
    }

    public function programarPesaje(Request $request, string $nArete)
    {
        $request->validate([
            'proximo_pesaje'    => 'nullable|date',
            'repetir_cada_dias' => 'nullable|integer|in:7,15,30,60,90',
        ]);

        $animal = $this->facade->programarPesaje(
            $nArete,
            $request->proximo_pesaje,
            $request->repetir_cada_dias,
        );

        return response()->json(['message' => 'Recordatorio actualizado.', 'animal' => $animal], 200);
    }

    public function getVeterinarios()
    {
        return response()->json($this->facade->getVeterinarios(), 200);
    }

    public function getAyudantes()
    {
        return response()->json($this->facade->getAyudantes(), 200);
    }

    public function asignarVeterinario(Request $request, int $idFinca)
    {
        $request->validate(['id_veterinario' => 'required|integer|exists:users,identificacion_usuario']);
        $this->facade->asignarVeterinario($idFinca, $request->id_veterinario);
        return response()->json(['message' => 'Veterinario asignado correctamente.'], 200);
    }

    public function desasignarVeterinario(Request $request, int $idFinca)
    {
        $request->validate(['id_veterinario' => 'required|integer|exists:users,identificacion_usuario']);
        $this->facade->desasignarVeterinario($idFinca, $request->id_veterinario);
        return response()->json(['message' => 'Veterinario desasignado correctamente.'], 200);
    }

    public function asignarAyudante(Request $request, int $idFinca)
    {
        $request->validate(['id_ayudante' => 'required|integer|exists:users,identificacion_usuario']);
        $this->facade->asignarAyudante($idFinca, $request->id_ayudante);
        return response()->json(['message' => 'Ayudante asignado correctamente.'], 200);
    }

    public function desasignarAyudante(Request $request)
    {
        $request->validate(['id_ayudante' => 'required|integer|exists:users,identificacion_usuario']);
        $this->facade->desasignarAyudante($request->id_ayudante);
        return response()->json(['message' => 'Ayudante desasignado correctamente.'], 200);
    }
    public function getAnimal(Request $request, string $nArete)
{
    $animal = $this->facade->getAnimal($nArete);
    return response()->json($animal, 200);
}

public function registrarReporte(Request $request)
{
    $request->validate([
        'id_finca'  => 'required|integer|exists:fincas,id_finca',
        'cantidad'  => 'required|integer|min:1',
    ]);
    $this->facade->registrarReporte($request->id_finca, $request->cantidad);
    return response()->json(['message' => 'Reporte registrado en historial.'], 201);
}

public function getFoto(Request $request, string $nArete)
{
    $animal = $this->facade->getAnimal($nArete);

    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    if (!$animal->foto_animal || !$disk->exists($animal->foto_animal)) {
        abort(404);
    }

    return $disk->response($animal->foto_animal);
}
public function getVeterinariosAsignados(Request $request, int $idFinca)
{
    return response()->json($this->facade->getVeterinariosAsignados($idFinca), 200);
}

public function getAyudantesAsignados(Request $request, int $idFinca)
{
    return response()->json($this->facade->getAyudantesAsignados($idFinca), 200);
}
}