<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiEnvelope
{
    public static function ok(mixed $resultado = null, mixed $respuesta = null): JsonResponse
    {
        return response()->json([
            'error' => null,
            'respuesta' => $respuesta,
            'resultado' => $resultado,
        ]);
    }

    /**
     * @param  array<string, mixed>  $error
     */
    public static function fail(array $error, int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'respuesta' => null,
            'resultado' => null,
        ], $status);
    }
}
