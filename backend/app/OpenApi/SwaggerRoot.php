<?php

namespace App\OpenApi;

/**
 * Metadatos OpenAPI (L5-Swagger). Escaneo: `config/l5-swagger.php` → `app/`.
 *
 * @OA\Info(
 *   title="PaqSuite IA Partes Atención API",
 *   version="1.1.0",
 *   description="API REST Partes Atención (MONO). Respuestas envelope: error / respuesta / resultado."
 * )
 *
 * @OA\Server(url="/", description="Backend Laravel (paths incluyen /api/v1)")
 *
 * @OA\Tag(name="Admin", description="Seguridad ABM + menú/preferencias/empresas del usuario")
 * @OA\Tag(name="Auth", description="Autenticación API (Bearer Sanctum)")
 * @OA\Tag(name="Chat Assistant", description="Asistente IA documental")
 * @OA\Tag(name="Excel Import", description="Importación Excel (GEN-14)")
 * @OA\Tag(name="FrameWork", description="Health y estado del sistema (scaffold / plataforma)")
 * @OA\Tag(name="Grid Layouts", description="Layouts de grilla DevExtreme")
 * @OA\Tag(name="LLM", description="Credenciales LLM (BYOK)")
 * @OA\Tag(name="Parametros", description="Parámetros generales")
 * @OA\Tag(name="Partes Informes", description="Dashboard e informes")
 * @OA\Tag(name="Partes Maestros", description="Maestros Partes (clientes, asistentes, tipos)")
 * @OA\Tag(name="Partes Tareas", description="Carga diaria y proceso masivo de tareas")
 * @OA\Tag(name="Pivot Layouts", description="Layouts de pivot")
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Sanctum",
 *   description="Token Bearer obtenido en POST /api/v1/auth/login"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="tenant",
 *   type="apiKey",
 *   in="header",
 *   name="X-Paq-Cliente",
 *   description="Cliente MONO (ej. desarrollo, demo)"
 * )
 *
 * @OA\Schema(
 *   schema="ApiEnvelope",
 *   type="object",
 *   required={"error","respuesta","resultado"},
 *   @OA\Property(property="error", type="integer", example=0, description="0 = OK"),
 *   @OA\Property(property="respuesta", type="string", example="ok"),
 *   @OA\Property(property="resultado", type="object", description="Payload; vacío = {}")
 * )
 */
abstract class SwaggerRoot
{
}
