<?php

namespace App\Services\Emissions\DesignAssistant;

use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;

/**
 * Interpreta el mensaje en acciones whitelist (heurística + LLM opcional).
 */
final class DesignLayoutActionPlanner
{
    public function __construct(
        private readonly LlmChatCompletionClient $completionClient,
    ) {}

    /**
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>}
     */
    public function plan(string $message, LlmCredentialContext $credential): array
    {
        $heuristic = $this->fromHeuristics($message);
        if ($heuristic['actions'] !== []) {
            return $heuristic;
        }

        return $this->fromLlm($message, $credential);
    }

    /**
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>}
     */
    public function fromHeuristics(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');
        $actions = [];

        if (preg_match('/t[ií]tulo\s*[:=]?\s*[«"\']?([^«"\']+)[»"\']?/u', $message, $m) === 1) {
            $title = trim($m[1]);
            if ($title !== '') {
                $actions[] = $this->action('setReportTitle', ['title' => mb_substr($title, 0, 200)]);
            }
        } elseif (preg_match('/(pon[eé]|cambi[aá]|set(ea)?)\s+(el\s+)?t[ií]tulo\s+(a|como|en)\s+[«"\']?([^«"\']+)[»"\']?/u', $message, $m) === 1) {
            $title = trim($m[5] ?? '');
            if ($title !== '') {
                $actions[] = $this->action('setReportTitle', ['title' => mb_substr($title, 0, 200)]);
            }
        }

        if (preg_match('/agrup(a|á|ar).*(cliente)/u', $text) === 1) {
            $actions[] = $this->action('addGroupByField', ['field' => 'clienteNombre']);
        } elseif (preg_match('/agrup(a|á|ar).*(t[eé]cnico|asistente|usuario)/u', $text) === 1) {
            $actions[] = $this->action('addGroupByField', ['field' => 'usuarioNombre']);
        } elseif (preg_match('/agrup(a|á|ar).*(fecha)/u', $text) === 1) {
            $actions[] = $this->action('addGroupByField', ['field' => 'fecha']);
        }

        if (preg_match('/(sum(a|á|ar)|totaliz(a|á|ar)|subtotal).*(minuto|duraci[oó]n|hora)/u', $text) === 1) {
            $actions[] = $this->action('addGroupSum', ['field' => 'duracionMinutos', 'summary' => 'sum']);
        }

        if (preg_match('/(horizontal|landscape|apaisad)/u', $text) === 1) {
            $actions[] = $this->action('setPageOrientation', ['orientation' => 'landscape']);
        } elseif (preg_match('/(vertical|portrait|retratro)/u', $text) === 1) {
            $actions[] = $this->action('setPageOrientation', ['orientation' => 'portrait']);
        }

        $actions = $this->sanitizeActions($actions);
        if ($actions === []) {
            return [
                'replyText' => 'partes.emissionDesign.noMappedActions',
                'actions' => [],
            ];
        }

        return [
            'replyText' => 'partes.emissionDesign.actionsPlanned',
            'actions' => $actions,
        ];
    }

    /**
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>}
     */
    private function fromLlm(string $message, LlmCredentialContext $credential): array
    {
        $fields = implode(', ', DesignLayoutWhitelist::SCHEMA_FIELDS);
        $types = implode(', ', DesignLayoutWhitelist::TYPES);
        $system = <<<PROMPT
Sos el asistente de diseño de informes Partes (Smart Capture bajo DevExpress).
Respondé SOLO JSON válido (sin markdown):
{"replyText":"string","actions":[{"action":"setReportTitle|addGroupByField|addGroupSum|setPageOrientation","payload":{}}]}
Reglas:
- Solo actions de: {$types}
- Campos técnicos permitidos: {$fields}
- addGroupByField/addGroupSum: payload.field = name técnico
- setReportTitle: payload.title string 1..200
- setPageOrientation: payload.orientation = portrait|landscape
- Edición incremental; no inventes campos
- Si el pedido no mapea, actions=[] y replyText explica la limitación en español
PROMPT;

        $raw = $this->completionClient->complete(
            $credential,
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
            [],
        );

        $decoded = $this->decodeJson($raw);
        $reply = is_string($decoded['replyText'] ?? null) ? trim((string) $decoded['replyText']) : '';
        $rawActions = is_array($decoded['actions'] ?? null) ? $decoded['actions'] : [];
        $normalized = [];
        foreach ($rawActions as $item) {
            if (! is_array($item)) {
                continue;
            }
            $action = trim((string) ($item['action'] ?? $item['type'] ?? ''));
            $payload = is_array($item['payload'] ?? null) ? $item['payload'] : [];
            if ($action === 'setReportTitle' && isset($item['title']) && ! isset($payload['title'])) {
                $payload['title'] = $item['title'];
            }
            if (($action === 'addGroupByField' || $action === 'addGroupSum') && isset($item['field']) && ! isset($payload['field'])) {
                $payload['field'] = $item['field'];
            }
            if ($action === 'setPageOrientation' && isset($item['orientation']) && ! isset($payload['orientation'])) {
                $payload['orientation'] = $item['orientation'];
            }
            if ($action === '') {
                continue;
            }
            $normalized[] = $this->action($action, $payload);
        }

        $actions = $this->sanitizeActions($normalized);

        return [
            'replyText' => $reply !== '' ? $reply : ($actions === [] ? 'partes.emissionDesign.noMappedActions' : 'partes.emissionDesign.actionsPlanned'),
            'actions' => $actions,
        ];
    }

    /**
     * @param  list<array{action: string, payload: array<string, mixed>, resultado: string}>  $actions
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public function sanitizeActions(array $actions): array
    {
        $out = [];
        foreach ($actions as $item) {
            $type = (string) ($item['action'] ?? '');
            if (! DesignLayoutWhitelist::isAllowedType($type)) {
                continue;
            }
            $payload = is_array($item['payload'] ?? null) ? $item['payload'] : [];

            if ($type === 'setReportTitle') {
                $title = trim((string) ($payload['title'] ?? ''));
                if ($title === '' || mb_strlen($title) > 200) {
                    continue;
                }
                $payload = ['title' => $title];
            }

            if ($type === 'addGroupByField' || $type === 'addGroupSum') {
                $field = trim((string) ($payload['field'] ?? ''));
                if (! DesignLayoutWhitelist::isSchemaField($field)) {
                    continue;
                }
                if ($type === 'addGroupSum' && ! DesignLayoutWhitelist::isNumericField($field)) {
                    continue;
                }
                $payload = ['field' => $field];
                if ($type === 'addGroupSum') {
                    $payload['summary'] = 'sum';
                }
            }

            if ($type === 'setPageOrientation') {
                $orientation = strtolower(trim((string) ($payload['orientation'] ?? '')));
                if (! in_array($orientation, ['portrait', 'landscape'], true)) {
                    continue;
                }
                $payload = ['orientation' => $orientation];
            }

            $out[] = [
                'action' => $type,
                'payload' => $payload,
                'resultado' => 'ok',
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{action: string, payload: array<string, mixed>, resultado: string}
     */
    private function action(string $action, array $payload): array
    {
        return [
            'action' => $action,
            'payload' => $payload,
            'resultado' => 'ok',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        $json = trim($raw);
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $json, $fenced) === 1) {
            $json = $fenced[1];
        } elseif (preg_match('/\{.*\}/s', $raw, $m) === 1) {
            $json = $m[0];
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
