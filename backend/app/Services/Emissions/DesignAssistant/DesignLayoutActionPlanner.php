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

        if (DesignLayoutRemovalIntentHeuristics::matches($message)) {
            return [
                'replyText' => 'partes.emissionDesign.noMappedActions',
                'actions' => [],
            ];
        }

        $actions = [];

        $appearanceActions = DesignLayoutAppearanceHeuristics::tryMatch($message);
        if ($appearanceActions !== []) {
            $actions = array_merge($actions, $appearanceActions);
        }

        $staticTextActions = DesignLayoutDetailStaticTextHeuristics::tryMatch($message);
        if ($staticTextActions !== []) {
            $actions = array_merge($actions, $staticTextActions);
        }

        $detailFieldActions = DesignLayoutDetailFieldHeuristics::tryMatch($message);
        if ($detailFieldActions !== []) {
            $actions = array_merge($actions, $detailFieldActions);
        }

        // Primero verbos explícitos ("cambia el título a …"); el patrón suelto
        // `título …` no debe comerse la preposición "a" cuando el valor va entre comillas.
        $title = $this->extractTitleFromMessage($message);
        if ($title !== null && $appearanceActions === []) {
            $actions[] = $this->action('setReportTitle', ['title' => mb_substr($title, 0, 200)]);
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
        $vocabulary = DesignSemanticCatalog::promptVocabulary(DesignLayoutWhitelist::PROCESS_CODE);
        $system = <<<PROMPT
Sos el asistente de diseño de informes Partes (Smart Capture bajo DevExpress).
Respondé SOLO JSON válido (sin markdown):
{"replyText":"string","actions":[{"action":"setReportTitle|addGroupByField|addGroupSum|setPageOrientation|setControlAppearance|addDetailField|addDetailStaticText","payload":{}}]}
Reglas:
- Solo actions de: {$types}
- Campos técnicos permitidos: {$fields}
- Catálogo semántico GEN-37 (resolvé lenguaje natural → name técnico; usá synonyms/label):
{$vocabulary}
- addGroupByField/addGroupSum: payload.field = name técnico
- setReportTitle: payload.title string 1..200
- setPageOrientation: payload.orientation = portrait|landscape
- setControlAppearance: payload.target {kind:field|reportTitle|controlName, fieldName?, name?} + fontFamily, fontSizePt, bold, italic, underline, textAlign, verticalAlign, foreColor (#hex), backColor, textAngle
- addDetailField: payload.field = name técnico existente en schema; agrega XRLabel en DetailBand (no PageHeader)
- addDetailStaticText: payload.text string 1..200; texto literal en DetailBand sin binding
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
            if ($action === 'addDetailField' && isset($item['field']) && ! isset($payload['field'])) {
                $payload['field'] = $item['field'];
            }
            if ($action === 'addDetailStaticText' && isset($item['text']) && ! isset($payload['text'])) {
                $payload['text'] = $item['text'];
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

            if ($type === 'setControlAppearance') {
                $payload = $this->sanitizeAppearancePayload($payload);
                if ($payload === null) {
                    continue;
                }
            }

            if ($type === 'addDetailField') {
                $field = trim((string) ($payload['field'] ?? ''));
                if (! DesignLayoutWhitelist::isSchemaField($field)) {
                    continue;
                }
                $payload = ['field' => $field];
            }

            if ($type === 'addDetailStaticText') {
                $literal = trim((string) ($payload['text'] ?? ''));
                if ($literal === '' || mb_strlen($literal) > 200) {
                    continue;
                }
                $payload = ['text' => $literal];
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
     * Extrae el texto de título pedido por el usuario (heurística).
     */
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function sanitizeAppearancePayload(array $payload): ?array
    {
        $target = is_array($payload['target'] ?? null) ? $payload['target'] : [];
        $kind = strtolower(trim((string) ($target['kind'] ?? '')));
        if ($kind === '') {
            return null;
        }

        $normalizedTarget = ['kind' => $kind];
        if ($kind === 'field') {
            $fieldName = trim((string) ($target['fieldName'] ?? $target['field'] ?? ''));
            if (! DesignLayoutWhitelist::isSchemaField($fieldName)) {
                return null;
            }
            $normalizedTarget['fieldName'] = $fieldName;
        } elseif ($kind === 'controlname' || $kind === 'control') {
            $name = trim((string) ($target['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 80) {
                return null;
            }
            $normalizedTarget['kind'] = 'controlName';
            $normalizedTarget['name'] = $name;
        } elseif ($kind !== 'reporttitle' && $kind !== 'report_title') {
            return null;
        } else {
            $normalizedTarget['kind'] = 'reportTitle';
        }

        $out = ['target' => $normalizedTarget];
        $hasStyle = false;

        if (isset($payload['fontFamily'])) {
            $family = trim((string) $payload['fontFamily']);
            if ($family !== '' && mb_strlen($family) <= 80) {
                $out['fontFamily'] = $family;
                $hasStyle = true;
            }
        }

        if (isset($payload['fontSizePt'])) {
            $size = (float) $payload['fontSizePt'];
            if ($size >= 6 && $size <= 72) {
                $out['fontSizePt'] = abs($size - round($size)) < 0.01 ? (int) round($size) : $size;
                $hasStyle = true;
            }
        }

        foreach (['bold', 'italic', 'underline'] as $flag) {
            if (array_key_exists($flag, $payload)) {
                $out[$flag] = (bool) $payload[$flag];
                $hasStyle = true;
            }
        }

        if (isset($payload['textAlign'])) {
            $align = strtolower(trim((string) $payload['textAlign']));
            if (in_array($align, ['left', 'center', 'right', 'justify'], true)) {
                $out['textAlign'] = $align;
                $hasStyle = true;
            }
        }

        if (isset($payload['verticalAlign'])) {
            $valign = strtolower(trim((string) $payload['verticalAlign']));
            if (in_array($valign, ['top', 'middle', 'bottom'], true)) {
                $out['verticalAlign'] = $valign;
                $hasStyle = true;
            }
        }

        foreach (['foreColor', 'backColor'] as $colorKey) {
            if (! isset($payload[$colorKey])) {
                continue;
            }
            $raw = trim((string) $payload[$colorKey]);
            $resolved = DesignLayoutColorCatalog::resolve($raw) ?? $raw;
            if (preg_match('/^#[0-9a-f]{6}$/i', $resolved) === 1) {
                $out[$colorKey] = strtolower($resolved);
                $hasStyle = true;
            }
        }

        if (isset($payload['textAngle'])) {
            $angle = (float) $payload['textAngle'];
            if ($angle >= -360 && $angle <= 360) {
                $out['textAngle'] = $angle;
                $hasStyle = true;
            }
        }

        return $hasStyle ? $out : null;
    }

    private function extractTitleFromMessage(string $message): ?string
    {
        $patterns = [
            // "cambia/pone/setea el título a|como|en|: «valor»"
            '/(?:pon[eé]|cambi[aá]|seteá?|set)\s+(?:el\s+)?t[ií]tulo\s+(?:a|como|:)\s*[«"\']?([^«"\'\n]+?)[»"\']?\s*$/iu',
            // "título: valor" / "título = valor" (exige : o = para no confundir "título a …")
            '/t[ií]tulo\s*[:=]\s*[«"\']?([^«"\'\n]+?)[»"\']?\s*$/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message), $m) !== 1) {
                continue;
            }
            $title = trim((string) ($m[1] ?? ''), " \t\n\r\0\x0B\"'«»");
            if ($title !== '') {
                return $title;
            }
        }

        return null;
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
