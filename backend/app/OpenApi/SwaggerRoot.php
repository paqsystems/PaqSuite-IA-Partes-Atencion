<?php

namespace App\OpenApi;

/**
 * Metadatos OpenAPI (L5-Swagger).
 *
 * @OA\Info(
 *   title="PaqSuite IA Partes Atención API",
 *   version="1.1.0",
 *   description="Scaffold MONO — Laravel 10 + Sanctum."
 * )
 *
 * @OA\Tag(name="Auth", description="Autenticación API (Bearer Sanctum)")
 * @OA\Tag(name="System", description="Estado y salud del sistema")
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="sanctum",
 *   description="Token personal Sanctum"
 * )
 */
abstract class SwaggerRoot
{
}
