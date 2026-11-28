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

        $removeDetailActions = DesignLayoutRemoveDetailFieldHeuristics::tryMatch($message);
        if ($removeDetailActions !== []) {
            return [
                'replyText' => 'partes.emissionDesign.actionsPlanned',
                'actions' => $removeDetailActions,
            ];
        }

        if (DesignLayoutRemovalIntentHeuristics::matches($message)) {
            return [
                'replyText' => 'partes.emissionDesign.noMappedActions',
                'actions' => [],
            ];
        }

        $actions = [];

        $pageHeaderColumnActions = DesignLayoutPageHeaderHeuristics::tryMatchColumnLabels($message);
        if ($pageHeaderColumnActions !== []) {
            $actions = array_merge($actions, $pageHeaderColumnActions);
        }

        $pageHeaderEmpresaActions = DesignLayoutPageHeaderHeuristics::tryMatchEmpresa(
            $message,
            DesignLayoutPageHeaderLab::resolveTenantCliente(),
        );
        if ($pageHeaderEmpresaActions !== []) {
            $actions = array_merge($actions, $pageHeaderEmpresaActions);
        }

        $pageHeaderFechaActions = DesignLayoutPageHeaderHeuristics::tryMatchFecha($message);
        if ($pageHeaderFechaActions !== []) {
            $actions = array_merge($actions, $pageHeaderFechaActions);
        }

        $pageHeaderTitlePanelActions = DesignLayoutPageHeaderHeuristics::tryMatchTitlePanel($message);
        if ($pageHeaderTitlePanelActions !== []) {
            $actions = array_merge($actions, $pageHeaderTitlePanelActions);
        }

        $appearanceActions = DesignLayoutAppearanceHeuristics::tryMatch($message);
        if ($appearanceActions !== []) {
            $actions = array_merge($actions, $appearanceActions);
        }

        $staticTextActions = DesignLayoutDetailStaticTextHeuristics::tryMatch($message);
        if ($staticTextActions !== []) {
            $actions = array_merge($actions, $staticTextActions);
        }

        $multiDetailActions = DesignLayoutMultiDetailFieldHeuristics::tryMatch($message);
        if ($multiDetailActions !== []) {
            $actions = array_merge($actions, $multiDetailActions);
        }

        $detailFieldActions = $multiDetailActions === []
            ? DesignLayoutDetailFieldHeuristics::tryMatch($message)
            : [];
        if ($detailFieldActions !== []) {
            $actions = array_merge($actions, $detailFieldActions);
        }

        $reportFooterActions = DesignLayoutReportFooterHeuristics::tryMatch($message);
        if ($reportFooterActions !== []) {
            $actions = array_merge($actions, $reportFooterActions);
        }

        $imageActions = DesignLayoutImagesHeuristics::tryMatch($message);
        if ($imageActions !== []) {
            $actions = array_merge($actions, $imageActions);
        }

        // Primero verbos explícitos ("cambia el título a …"); el patrón suelto
        // `título …` no debe comerse la preposición "a" cuando el valor va entre comillas.
        $title = $this->extractTitleFromMessage($message);
        if ($title !== null && $appearanceActions === [] && $pageHeaderTitlePanelActions === []) {
            $actions[] = $this->action('setReportTitle', ['title' => mb_substr($title, 0, 200)]);
        }

        if (! $this->isSecondLevelGroupingRequest($text)) {
            if (preg_match('/agrup(a|á|ar).*(cliente)/u', $text) === 1) {
                $actions[] = $this->action('addGroupByField', ['field' => 'clienteNombre']);
            } elseif (preg_match('/agrup(a|á|ar).*(t[eé]cnico|asistente|usuario)/u', $text) === 1) {
                $actions[] = $this->action('addGroupByField', ['field' => 'usuarioNombre']);
            } elseif (preg_match('/agrup(a|á|ar).*(fecha)/u', $text) === 1) {
                $actions[] = $this->action('addGroupByField', ['field' => 'fecha']);
            }
        }

        if (preg_match('/(sum(a|á|ar)|totaliz(a|á|ar)|subtotal).*(minuto|duraci[oó]n|hora)/u', $text) === 1) {
            $actions[] = $this->action('addGroupSum', ['field' => 'duracionMinutos', 'summary' => 'sum']);
        }

        if (preg_match('/(horizontal|landscape|apaisad)/u', $text) === 1) {
            $actions[] = $this->action('setPageOrientation', ['orientation' => 'landscape']);
        } elseif (preg_match('/(vertical|portrait|retratro)/u', $text) === 1) {
            $actions[] = $this->action('setPageOrientation', ['orientation' => 'portrait']);
        }

        if ($actions === []) {
            $catalogActions = DesignLayoutChkCatalogPlanner::tryMatch($message);
            if ($catalogActions !== []) {
                $actions = $catalogActions;
            }
        }

        $actions = $this->sanitizeActions($actions);
        if ($actions === []) {
            return [
                'replyText' => $this->isSecondLevelGroupingRequest($text)
                    ? 'partes.emissionDesign.group.useTwoTurns'
                    : 'partes.emissionDesign.noMappedActions',
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
{"replyText":"string","actions":[{"action":"setReportTitle|addGroupByField|addGroupSum|setPageOrientation|setControlAppearance|addDetailField|removeDetailField|addDetailStaticText|addPageHeaderColumnLabels|setPageHeaderEmpresa|setPageHeaderFecha|wrapReportTitleInPanel|ensureReportFooter|addReportSum|addCalculatedField|addDetailCalculatedLabel|setReportWatermarkImage|setPageHeaderLogo|addDetailBarCode","payload":{}}]}
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
- removeDetailField: payload.field = name técnico; quita XRLabel del DetailBand vinculado al campo (v1 solo observacion)
- addDetailStaticText: payload.text string 1..200; texto literal en DetailBand sin binding
- addPageHeaderColumnLabels: payload.fields opcional (objeto vacío = todas las columnas del detalle)
- setPageHeaderEmpresa: payload.text string 1..200 (nombre empresa en page header fila 1)
- setPageHeaderFecha: payload.format opcional (default sidecar dd, MMMM 'de' yyyy); page header fila 1 derecha
- wrapReportTitleInPanel: payload vacío; panel pnlReportTitle fila 2 page header alrededor de lblReportTitle
- ensureReportFooter: payload vacío; crea ReportFooterBand (no PageFooter)
- addReportSum: summary count|sum; field numérico si sum; calculatedField para totalizar calculado en report footer
- addCalculatedField: kind sum|iif; fields[] numéricos (sum) o testField/trueField/falseField (iif); name opcional calc_*
- addDetailCalculatedLabel: calculatedName existente en layout
- setReportWatermarkImage: payload.source default (imagen piloto)
- setPageHeaderLogo: payload.source empresa
- addDetailBarCode: payload.field schema + symbology QRCode; placement opcional detail|pageHeader|reportHeader|reportFooter|pageFooter (default detail)
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

            if ($type === 'addDetailField' || $type === 'removeDetailField') {
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

            if ($type === 'addPageHeaderColumnLabels') {
                $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
                $validFields = [];
                foreach ($fields as $item) {
                    $name = trim((string) $item);
                    if ($name !== '' && DesignLayoutWhitelist::isSchemaField($name)) {
                        $validFields[] = $name;
                    }
                }
                $payload = $validFields === []
                    ? new \stdClass()
                    : ['fields' => array_values(array_unique($validFields))];
            }

            if ($type === 'setPageHeaderEmpresa') {
                $literal = trim((string) ($payload['text'] ?? ''));
                if ($literal === '' || mb_strlen($literal) > 200) {
                    continue;
                }
                $payload = ['text' => $literal];
            }

            if ($type === 'setPageHeaderFecha') {
                $format = trim((string) ($payload['format'] ?? ''));
                if ($format === '' || mb_strlen($format) > 80 || preg_match('/(H{1,2}|h{1,2}|:mm|:ss)/', $format) === 1) {
                    $payload = new \stdClass();
                } else {
                    $payload = ['format' => $format];
                }
            }

            if ($type === 'wrapReportTitleInPanel') {
                $payload = new \stdClass();
            }

            if ($type === 'ensureReportFooter') {
                $payload = new \stdClass();
            }

            if ($type === 'addReportSum') {
                $summary = strtolower(trim((string) ($payload['summary'] ?? 'count')));
                if (! in_array($summary, ['count', 'sum'], true)) {
                    continue;
                }
                $next = ['summary' => $summary];
                $calculatedField = trim((string) ($payload['calculatedField'] ?? ''));
                if ($calculatedField !== '') {
                    $next['calculatedField'] = $calculatedField;
                } elseif ($summary === 'sum') {
                    $field = trim((string) ($payload['field'] ?? ''));
                    if (! DesignLayoutWhitelist::isNumericField($field)) {
                        continue;
                    }
                    $next['field'] = $field;
                }
                $payload = $next;
            }

            if ($type === 'addCalculatedField') {
                $kind = strtolower(trim((string) ($payload['kind'] ?? '')));
                if (! in_array($kind, ['sum', 'iif'], true)) {
                    continue;
                }
                $next = ['kind' => $kind];
                if ($kind === 'sum') {
                    $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
                    $valid = [];
                    foreach ($fields as $f) {
                        $name = trim((string) $f);
                        if (DesignLayoutWhitelist::isNumericField($name)) {
                            $valid[] = $name;
                        }
                    }
                    if ($valid === []) {
                        continue;
                    }
                    $next['fields'] = array_values(array_unique($valid));
                } else {
                    foreach (['testField', 'trueField', 'falseField'] as $k) {
                        $f = trim((string) ($payload[$k] ?? ''));
                        if (! DesignLayoutWhitelist::isSchemaField($f)) {
                            $f = '';
                        }
                        if ($f !== '') {
                            $next[$k] = $f;
                        }
                    }
                    if (count($next) < 4) {
                        continue;
                    }
                }
                $calcName = trim((string) ($payload['name'] ?? ''));
                if ($calcName !== '' && mb_strlen($calcName) <= 80) {
                    $next['name'] = $calcName;
                }
                $payload = $next;
            }

            if ($type === 'addDetailCalculatedLabel') {
                $calcName = trim((string) ($payload['calculatedName'] ?? ''));
                if ($calcName === '' || mb_strlen($calcName) > 80) {
                    continue;
                }
                $payload = ['calculatedName' => $calcName];
            }

            if ($type === 'setReportWatermarkImage') {
                $source = strtolower(trim((string) ($payload['source'] ?? 'default')));
                if ($source !== 'default') {
                    continue;
                }
                $payload = ['source' => 'default'];
            }

            if ($type === 'setPageHeaderLogo') {
                $source = strtolower(trim((string) ($payload['source'] ?? 'empresa')));
                if ($source !== 'empresa') {
                    continue;
                }
                $payload = ['source' => 'empresa'];
            }

            if ($type === 'addDetailBarCode') {
                $field = trim((string) ($payload['field'] ?? ''));
                if (! DesignLayoutWhitelist::isSchemaField($field)) {
                    continue;
                }
                $symbology = trim((string) ($payload['symbology'] ?? 'QRCode'));
                if (strcasecmp($symbology, 'QRCode') !== 0) {
                    continue;
                }
                $placement = strtolower(trim((string) ($payload['placement'] ?? 'detail')));
                $allowedPlacements = ['detail', 'pageheader', 'reportheader', 'reportfooter', 'pagefooter'];
                if (! in_array($placement, $allowedPlacements, true)) {
                    $placement = 'detail';
                }
                if ($placement === 'pageheader') {
                    $placement = 'pageHeader';
                } elseif ($placement === 'reportheader') {
                    $placement = 'reportHeader';
                } elseif ($placement === 'reportfooter') {
                    $placement = 'reportFooter';
                } elseif ($placement === 'pagefooter') {
                    $placement = 'pageFooter';
                }
                $payload = ['field' => $field, 'symbology' => 'QRCode', 'placement' => $placement];
            }

            $out[] = [
                'action' => $type,
                'payload' => self::normalizeActionPayloadForJson($payload),
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
            'payload' => self::normalizeActionPayloadForJson($payload),
            'resultado' => 'ok',
        ];
    }

    /**
     * Sidecar Newtonsoft exige objeto JSON en payload (no array vacío `[]`).
     *
     * @param  array<string, mixed>|\stdClass  $payload
     * @return array<string, mixed>|\stdClass
     */
    private static function normalizeActionPayloadForJson(array|\stdClass $payload): array|\stdClass
    {
        if ($payload === []) {
            return new \stdClass();
        }

        return $payload;
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

    private function isSecondLevelGroupingRequest(string $text): bool
    {
        if (preg_match('/segund[oa]\s+(nivel|grupo)/u', $text) === 1) {
            return true;
        }

        if (preg_match('/segund[oa]\s+.*agrup/u', $text) === 1) {
            return true;
        }

        return preg_match('/agrup/u', $text) === 1
            && preg_match('/dentro de cada/u', $text) === 1;
    }
}
