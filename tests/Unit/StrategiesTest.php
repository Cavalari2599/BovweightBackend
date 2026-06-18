<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Strategies\OrdenarPorPeso;
use App\Strategies\OrdenarPorNombre;
use App\Strategies\OrdenarPorFechaNacimiento;
use Illuminate\Support\Collection;

class StrategiesTest extends TestCase
{
    private function coleccionAnimales(): Collection
    {
        return collect([
            (object) ['n_arete' => 'A1', 'nombre_animal' => 'Zorro', 'peso' => 500, 'fecha_nacimiento' => '2020-01-01'],
            (object) ['n_arete' => 'A2', 'nombre_animal' => 'Abeja', 'peso' => 200, 'fecha_nacimiento' => '2022-06-01'],
            (object) ['n_arete' => 'A3', 'nombre_animal' => 'Mango', 'peso' => 350, 'fecha_nacimiento' => '2021-03-15'],
        ]);
    }

    // ─── OrdenarPorPeso ───────────────────────────────────────────────────────

    public function test_ordenar_por_peso_descendente(): void
    {
        $estrategia = new OrdenarPorPeso();
        $resultado  = $estrategia->ordenar($this->coleccionAnimales());

        $this->assertEquals('A1', $resultado[0]->n_arete); // 500 - mayor peso primero
        $this->assertEquals('A3', $resultado[1]->n_arete); // 350
        $this->assertEquals('A2', $resultado[2]->n_arete); // 200
    }

    public function test_ordenar_por_peso_coleccion_vacia(): void
    {
        $estrategia = new OrdenarPorPeso();
        $resultado  = $estrategia->ordenar(collect([]));

        $this->assertCount(0, $resultado);
    }

    // ─── OrdenarPorNombre ─────────────────────────────────────────────────────

    public function test_ordenar_por_nombre_alfabetico_ascendente(): void
    {
        $estrategia = new OrdenarPorNombre();
        $resultado  = $estrategia->ordenar($this->coleccionAnimales());

        $nombres = $resultado->pluck('nombre_animal')->values()->toArray();
        $this->assertEquals(['Abeja', 'Mango', 'Zorro'], $nombres);
    }

    public function test_ordenar_por_nombre_coleccion_un_elemento(): void
    {
        $estrategia = new OrdenarPorNombre();
        $col        = collect([(object) ['n_arete' => 'X', 'nombre_animal' => 'Solo', 'peso' => 100, 'fecha_nacimiento' => null]]);
        $resultado  = $estrategia->ordenar($col);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Solo', $resultado[0]->nombre_animal);
    }

    // ─── OrdenarPorFechaNacimiento ────────────────────────────────────────────

    public function test_ordenar_por_fecha_nacimiento_descendente(): void
    {
        $estrategia = new OrdenarPorFechaNacimiento();
        $resultado  = $estrategia->ordenar($this->coleccionAnimales());

        // sortByDesc → fecha más reciente primero
        $this->assertEquals('A2', $resultado[0]->n_arete); // 2022
        $this->assertEquals('A3', $resultado[1]->n_arete); // 2021
        $this->assertEquals('A1', $resultado[2]->n_arete); // 2020
    }
}