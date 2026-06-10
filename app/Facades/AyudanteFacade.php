<?php

namespace App\Facades;

use App\Models\Animal;
use App\Models\Ayudante;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Tratamiento;
use App\Services\PesajeService;
use App\Strategies\OrdenarAnimalesStrategy;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;

/**
 * Logica del ayudante: solo lectura sobre los animales de SU finca asignada,
 * mas el modulo de pesaje. Todo queda acotado a la finca del ayudante.
 */
class AyudanteFacade
{
    public function __construct(private PesajeService $pesajes) {}

    private function fincaId(int $idUsuario): int
    {
        $ayudante = Ayudante::find($idUsuario);
        abort_if($ayudante === null, 403, 'No estas asignado a ninguna finca.');
        return $ayudante->id_finca;
    }

    private function animalDeSuFinca(int $idUsuario, string $nArete): Animal
    {
        $animal = Animal::findOrFail($nArete);
        abort_if($animal->id_finca != $this->fincaId($idUsuario), 403, 'Ese animal no pertenece a tu finca.');
        return $animal;
    }

    public function getFinca(int $idUsuario): Finca
    {
        return Finca::findOrFail($this->fincaId($idUsuario));
    }

    public function getAnimales(int $idUsuario, OrdenarAnimalesStrategy $estrategia): Collection
    {
        $animales = Animal::where('id_finca', $this->fincaId($idUsuario))->get();
        return $estrategia->ordenar($animales);
    }

    public function getAnimal(int $idUsuario, string $nArete): Animal
    {
        return $this->animalDeSuFinca($idUsuario, $nArete);
    }

    public function getPesajes(int $idUsuario, string $nArete): Collection
    {
        $this->animalDeSuFinca($idUsuario, $nArete);
        return Pesaje::where('n_arete', $nArete)
            ->orderBy('fecha_pesaje', 'desc')
            ->get();
    }

    public function getTratamientos(int $idUsuario, string $nArete): Collection
    {
        $this->animalDeSuFinca($idUsuario, $nArete);
        return Tratamiento::with('usuario')
            ->where('n_arete', $nArete)
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    public function estimarPeso(UploadedFile $foto, string $sexo): float
    {
        return $this->pesajes->estimar($foto, $sexo);
    }

    public function crearPesaje(int $idUsuario, string $nArete, float $peso, ?UploadedFile $foto = null, ?string $sexo = null): Pesaje
    {
        $this->animalDeSuFinca($idUsuario, $nArete);
        return $this->pesajes->registrar($nArete, $peso, $foto, $sexo);
    }
}
