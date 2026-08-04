<?php

namespace App\OpenApi;

/**
 * LLM BYOK + Chat assistant (TR-008).
 *
 * @OA\Get(
 *   path="/api/v1/llm-credentials/active",
 *   operationId="llmCredentialsActive",
 *   tags={"LLM"},
 *   summary="Credencial LLM activa",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Put(
 *   path="/api/v1/llm-credentials/active",
 *   operationId="llmCredentialsSetActive",
 *   tags={"LLM"},
 *   summary="Definir credencial LLM activa",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(@OA\Property(property="id", type="integer", nullable=true))),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/llm-credentials",
 *   operationId="llmCredentialsIndex",
 *   tags={"LLM"},
 *   summary="Listar credenciales LLM",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/llm-credentials",
 *   operationId="llmCredentialsStore",
 *   tags={"LLM"},
 *   summary="Alta credencial LLM",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="provider", type="string"),
 *     @OA\Property(property="label", type="string"),
 *     @OA\Property(property="apiKey", type="string"),
 *     @OA\Property(property="model", type="string", nullable=true)
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Patch(
 *   path="/api/v1/llm-credentials/{id}",
 *   operationId="llmCredentialsUpdate",
 *   tags={"LLM"},
 *   summary="Actualizar credencial LLM",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(@OA\JsonContent(
 *     @OA\Property(property="label", type="string"),
 *     @OA\Property(property="apiKey", type="string"),
 *     @OA\Property(property="model", type="string", nullable=true)
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Delete(
 *   path="/api/v1/llm-credentials/{id}",
 *   operationId="llmCredentialsDestroy",
 *   tags={"LLM"},
 *   summary="Eliminar credencial LLM",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/chat-assistant/turns",
 *   operationId="chatAssistantTurns",
 *   tags={"Chat Assistant"},
 *   summary="Turno de chat documental",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(@OA\JsonContent(
 *     required={"message"},
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="conversationId", type="string", nullable=true),
 *     @OA\Property(property="locale", type="string", nullable=true)
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 */
abstract class OpenApiPathsLlmChat
{
}
