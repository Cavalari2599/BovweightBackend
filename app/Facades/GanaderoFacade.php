<?php

namespace App\Facades;

use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Tratamiento;
use App\Models\Atiende;
use App\Models\Ayudante;
use App\Models\User;
use App\Strategies\OrdenarAnimalesStrategy;
use App\Interfaces\HistorialServiceInterface;
use App\Services\PesajeService;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;

class GanaderoFacade
{
    public function __construct(
        private HistorialServiceInterface $historial,
        private PesajeService $pesajes,
    ) {}

    public function getFincas(int $idUsuario): Collection
    {
        return Finca::where('identificacion_usuario', $idUsuario)->get();
    }

    public function crearFinca(int $idUsuario, array $datos): Finca
    {
        return Finca::create([
            'nombre_finca'           => $datos['nombre_finca'],
            'ubicacion_finca'        => $datos['ubicacion_finca'],
            'identificacion_usuario' => $idUsuario,
        ]);
    }

    public function editarFinca(int $idFinca, array $datos): Finca
    {
        $finca = Finca::findOrFail($idFinca);
        $finca->update($datos);
        return $finca;
    }

    public function getAnimales(int $idFinca, OrdenarAnimalesStrategy $estrategia): Collection
    {
        $animales = Animal::where('id_finca', $idFinca)->get();
        return $estrategia->ordenar($animales);
    }

    public function crearAnimal(int $idFinca, array $datos): Animal
    {
        return Animal::create([
            'n_arete'         => $datos['n_arete'],
            'nombre_animal'   => $datos['nombre_animal'] ?? null,
            'raza'            => $datos['raza'] ?? null,
            'sexo'            => $datos['sexo'] ?? null,
            'edad'            => $datos['edad'] ?? null,
            'peso'            => $datos['peso'] ?? null,
            'estado'          => $datos['estado'],
            'fecha_nacimiento'=> $datos['fecha_nacimiento'] ?? null,
            'foto_animal'     => $datos['foto_animal'] ?? null,
            'id_finca'        => $idFinca,
        ]);
    }

    public function editarAnimal(string $nArete, array $datos): Animal
    {
        $animal = Animal::findOrFail($nArete);
        $animal->update($datos);
        return $animal;
    }

    public function getPesajes(string $nArete): Collection
    {
        return Pesaje::where('n_arete', $nArete)
            ->orderBy('fecha_pesaje', 'desc')
            ->get();
    }

    public function estimarPeso(UploadedFile $foto, string $sexo): float
    {
        return $this->pesajes->estimar($foto, $sexo);
    }

    public function crearPesaje(string $nArete, float $peso, ?UploadedFile $foto = null, ?string $sexo = null): Pesaje
    {
        return $this->pesajes->registrar($nArete, $peso, $foto, $sexo);
    }

    public function getTratamientos(string $nArete): Collection
    {
        return Tratamiento::with('usuario')
            ->where('n_arete', $nArete)
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    /**
     * Animales del ganadero con un pesaje programado (recordatorios activos),
     * ordenados por fecha. El frontend separa atrasados / hoy / proximos.
     */
    public function getRecordatorios(int $idUsuario): Collection
    {
        $fincaIds = Finca::where('identificacion_usuario', $idUsuario)->pluck('id_finca');

        return Animal::whereIn('id_finca', $fincaIds)
            ->whereNotNull('proximo_pesaje')
            ->where('estado', 'Activo')
            ->orderBy('proximo_pesaje')
            ->get([
                'n_arete', 'nombre_animal', 'raza', 'foto_animal', 'peso',
                'estado', 'proximo_pesaje', 'repetir_cada_dias', 'id_finca',
            ]);
    }

    /**
     * Todos los animales activos del ganadero (para el selector al agendar pesajes).
     */
    public function getTodosAnimales(int $idUsuario): Collection
    {
        $fincaIds = Finca::where('identificacion_usuario', $idUsuario)->pluck('id_finca');

        return Animal::whereIn('id_finca', $fincaIds)
            ->where('estado', 'Activo')
            ->orderBy('nombre_animal')
            ->get([
                'n_arete', 'nombre_animal', 'raza', 'foto_animal',
                'proximo_pesaje', 'repetir_cada_dias', 'id_finca',
            ]);
    }

    /**
     * Programa (o limpia, si $fecha es null) el proximo pesaje de un animal.
     */
    public function programarPesaje(string $nArete, ?string $fecha, ?int $repetir): Animal
    {
        $animal = Animal::findOrFail($nArete);
        $animal->proximo_pesaje = $fecha;
        $animal->repetir_cada_dias = $fecha ? $repetir : null;
        $animal->save();
        return $animal;
    }

    public function getResumenFinca(int $idFinca): array
    {
        $animales = Animal::where('id_finca', $idFinca)->get();
        return [
            'total_animales' => $animales->count(),
            'peso_promedio'  => round($animales->avg('peso'), 2),
            'peso_maximo'    => $animales->max('peso'),
            'peso_minimo'    => $animales->min('peso'),
        ];
    }

    public function asignarVeterinario(int $idFinca, int $idVeterinario): void
    {
        Atiende::firstOrCreate([
            'identificacion_usuario' => $idVeterinario,
            'id_finca'               => $idFinca,
        ]);
    }

    public function desasignarVeterinario(int $idFinca, int $idVeterinario): void
    {
        Atiende::where('identificacion_usuario', $idVeterinario)
            ->where('id_finca', $idFinca)
            ->get()
            ->each(fn ($atiende) => $atiende->delete()); // delete por instancia -> dispara AtiendeObserver
    }

    public function asignarAyudante(int $idFinca, int $idAyudante): void
    {
        // Un ayudante pertenece a una sola finca (PK = identificacion_usuario).
        // updateOrCreate reasigna sin violar la PK.
        Ayudante::updateOrCreate(
            ['identificacion_usuario' => $idAyudante],
            ['id_finca' => $idFinca],
        );
    }

    public function desasignarAyudante(int $idAyudante): void
    {
        Ayudante::where('identificacion_usuario', $idAyudante)
            ->get()
            ->each(fn ($ayudante) => $ayudante->delete()); // delete por instancia -> dispara AyudanteObserver
    }

    public function registrarReporte(int $idFinca, int $cantidad): void
    {
        $this->historial->registrar("Generar reporte ({$cantidad} animales)", 'reportes', $idFinca);
    }

    public function getVeterinarios(): Collection
    {
        return User::where('id_rol', 2)->where('estado', true)->get();
    }

    public function getAyudantes(): Collection
    {
        // Solo ayudantes libres (sin finca asignada).
        return User::where('id_rol', 3)
            ->where('estado', true)
            ->whereDoesntHave('ayudante')
            ->get();
    }
    public function getAnimal(string $nArete): Animal
{
    return Animal::findOrFail($nArete);
}
public function getVeterinariosAsignados(int $idFinca): Collection
{
    return \App\Models\User::whereHas('atiende', function($q) use ($idFinca) {
        $q->where('id_finca', $idFinca);
    })->get();
}

public function getAyudantesAsignados(int $idFinca): Collection
{
    return \App\Models\User::whereHas('ayudante', function($q) use ($idFinca) {
        $q->where('id_finca', $idFinca);
    })->get();
}
}