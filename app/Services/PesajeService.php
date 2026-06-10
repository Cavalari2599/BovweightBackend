<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Pesaje;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Logica de pesaje compartida por ganadero y ayudante (SRP / evita duplicar G5).
 * Estima el peso via el servicio ML y persiste el pesaje.
 */
class PesajeService
{
    /**
     * Estima el peso (kg) enviando la foto al servicio ML (FastAPI).
     * No persiste nada: solo devuelve el estimado.
     */
    public function estimar(UploadedFile $foto, string $sexo): float
    {
        $url = rtrim(config('services.ml.url'), '/') . '/predict';

        $resp = Http::timeout(60)
            ->attach('file', file_get_contents($foto->getRealPath()), $foto->getClientOriginalName())
            ->post($url, ['sexo' => $sexo]);

        if ($resp->failed()) {
            throw new RuntimeException('El servicio de estimacion no respondio correctamente.');
        }

        $data = $resp->json();
        if (!isset($data['peso_estimado_kg'])) {
            throw new RuntimeException($data['error'] ?? 'No se pudo estimar el peso de la imagen.');
        }

        return (float) $data['peso_estimado_kg'];
    }

    /**
     * Registra un pesaje (historial) y actualiza el peso actual del animal.
     * Si llega $sexo, lo persiste en el animal para futuras estimaciones.
     */
    public function registrar(string $nArete, float $peso, ?UploadedFile $foto = null, ?string $sexo = null): Pesaje
    {
        $animal = Animal::findOrFail($nArete);

        $rutaFoto = $foto ? $foto->store('pesajes', 'public') : null;

        $pesaje = Pesaje::create([
            'foto_pesaje'     => $rutaFoto,
            'fecha_pesaje'    => now()->toDateString(),
            'peso_aproximado' => $peso,
            'n_arete'         => $nArete,
        ]);

        $animal->peso = $peso;
        if ($sexo) {
            $animal->sexo = $sexo;
        }
        // Pesar completa el recordatorio: si repite, reprograma; si no, lo limpia.
        $animal->proximo_pesaje = $animal->repetir_cada_dias
            ? now()->addDays($animal->repetir_cada_dias)->toDateString()
            : null;
        $animal->save();

        return $pesaje;
    }
}
