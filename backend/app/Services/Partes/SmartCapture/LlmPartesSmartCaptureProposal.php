<?php

namespace App\Services\Partes\SmartCapture;

use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;

/**
 * Interpreta el mensaje vía LLM (JSON) para Smart Capture Partes.
 */
final class LlmPartesSmartCaptureProposal implements PartesSmartCaptureProposalPort
{
    public function __construct(
        private readonly LlmChatCompletionClient $completionClient,
    ) {}

    public function propose(
        string $message,
        array $draftContext,
        ?array $pendingChoice,
        array $images,
        object $credentialContext,
    ): array {
        if (! $credentialContext instanceof LlmCredentialContext) {
            return $this->emptyProposal('partes.smartCapture.sinCredencial');
        }

        $system = <<<'PROMPT'
Sos el asistente operativo Smart Capture de Partes de Atención.
Respondé SOLO con un JSON válido (sin markdown) con esta forma:
{"replyText":"string","save":false,"fields":{"cliente":null,"asistente":null,"tipoTarea":null,"fecha":null,"duracionMinutos":null,"observacion":null,"sinCargo":null,"presencial":null}}
Reglas:
- Completá solo campos mencionados o inferibles; el resto null.
- cliente/asistente/tipoTarea: texto libre (código o descripción).
- fecha: yyyy-MM-dd si se menciona.
- duracionMinutos: entero en MINUTOS. Reloj h:mm (ej. 1:25) = 85 minutos, NO 1.25 horas. Horas decimales (1.25 h / 1,25 horas) = 75 minutos.
- Si la duración no es múltiplo del tramo (típicamente 15), dejá duracionMinutos null.
- save=true SOLO si el usuario expresa intención clara de grabar (guardar/confirmar/procesar/grabar/registrar/aceptar/ok/dale/guardar tarea/confirmar tarea) como intención de persistir la tarea, no por substring accidental en otra frase.
- No inventes ids numéricos de maestros.
- Los campos que completes deben ir en "fields" (no alcanza con mencionarlos en replyText).
- Si el usuario escribe "asistente PQ cliente LACAPOL duracion 1:25", fields.asistente="PQ", fields.cliente="LACAPOL", fields.duracionMinutos="1:25".
- Si hay imágenes, inferí los mismos fields desde lo visible.
PROMPT;

        $userContent = json_encode([
            'message' => $message,
            'draftContext' => $draftContext,
            'pendingChoice' => $pendingChoice,
        ], JSON_UNESCAPED_UNICODE);

        $raw = $this->completionClient->complete(
            $credentialContext,
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => (string) $userContent],
            ],
            $images,
        );

        return $this->parseProposal($raw);
    }

    /**
     * @return array{replyText: string, save: bool, fields: array<string, mixed>}
     */
    private function parseProposal(string $raw): array
    {
        $json = trim($raw);
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $json, $fenced) === 1) {
            $json = $fenced[1];
        } elseif (preg_match('/\{.*\}/s', $raw, $m) === 1) {
            $json = $m[0];
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return $this->emptyProposal(trim($raw) !== '' ? trim($raw) : 'partes.smartCapture.noEntendi');
        }

        $fields = is_array($decoded['fields'] ?? null) ? $decoded['fields'] : [];
        if ($fields === []) {
            foreach (['cliente', 'asistente', 'tipoTarea', 'fecha', 'duracionMinutos', 'observacion', 'sinCargo', 'presencial'] as $topLevel) {
                if (array_key_exists($topLevel, $decoded) && $decoded[$topLevel] !== null && $decoded[$topLevel] !== '') {
                    $fields[$topLevel] = $decoded[$topLevel];
                }
            }
        }

        return [
            'replyText' => is_string($decoded['replyText'] ?? null) && $decoded['replyText'] !== ''
                ? (string) $decoded['replyText']
                : 'partes.smartCapture.ok',
            'save' => (bool) ($decoded['save'] ?? false),
            'fields' => $fields,
        ];
    }

    /**
     * @return array{replyText: string, save: bool, fields: array<string, mixed>}
     */
    private function emptyProposal(string $replyText): array
    {
        return [
            'replyText' => $replyText,
            'save' => false,
            'fields' => [],
        ];
    }
}
