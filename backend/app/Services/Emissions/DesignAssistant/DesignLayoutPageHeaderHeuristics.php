<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Heurísticas oleada 5 — page header (T1 CHK-04, T2 CHK-06, T3 CHK-07, T4 CHK-05).
 */
final class DesignLayoutPageHeaderHeuristics
{
    /**
     * CHK-06: nombre de empresa en fila 1 sup. izquierda.
     *
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatchEmpresa(string $message, ?string $tenantCliente = null): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        $empresaHeader =
            (preg_match('/\bempresa\b/u', $text) === 1
                && preg_match('/(arriba|sup|superior).*(izquierd|izq)/u', $text) === 1)
            || preg_match('/nombre\s+de\s+la\s+empresa/u', $text) === 1
            || preg_match('/nombre\s+empresa/u', $text) === 1;

        if (! $empresaHeader) {
            return [];
        }

        $extracted = self::extractEmpresaTextFromMessage($message);
        if ($extracted === null) {
            if (! DesignLayoutPageHeaderLab::allowsDemoEmpresaDefault($tenantCliente)) {
                return [];
            }

            $extracted = DesignLayoutPageHeaderLab::DEMO_EMPRESA_TEXT;
        }

        $extracted = mb_substr(trim($extracted), 0, 200);
        if ($extracted === '') {
            return [];
        }

        return [[
            'action' => 'setPageHeaderEmpresa',
            'payload' => ['text' => $extracted],
            'resultado' => 'ok',
        ]];
    }

    private static function extractEmpresaTextFromMessage(string $message): ?string
    {
        $patterns = [
            '/[«"\']([^«"\'\n]{1,200})[»"\']/u',
            '/empresa\s+(?:se\s+llama|llamada|denominada)\s+([^\n,.]{1,200})/iu',
            '/empresa\s*[:=]\s*([^\n,.]{1,200})/iu',
            '/de\s+la\s+empresa\s+([^\n,.]{1,200})/iu',
            '/empresa\s+([A-ZÁÉÍÓÚÑ][^\n,.]{0,199})/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message), $m) !== 1) {
                continue;
            }
            $candidate = trim((string) ($m[1] ?? ''), " \t\n\r\0\x0B\"'«»");
            if ($candidate !== '' && ! self::isEmpresaPlacementNoise($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function isEmpresaPlacementNoise(string $candidate): bool
    {
        $lower = mb_strtolower($candidate, 'UTF-8');

        return preg_match('/^(arriba|izquierda|izq|nombre|sup|superior|encabezado)/u', $lower) === 1;
    }

    /**
     * CHK-07: fecha de impresión en fila 1 sup. derecha (XRPageInfo).
     *
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatchFecha(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/agrup(a|á|ar).*(fecha)/u', $text) === 1) {
            return [];
        }

        if (preg_match('/(detalle|columna).*(fecha)/u', $text) === 1
            && preg_match('/agreg(a|á|ar|ue)|incorpor/u', $text) === 1) {
            return [];
        }

        $fechaHeader =
            (preg_match('/\bfecha\b/u', $text) === 1
                && preg_match('/(derecha|der).*(arriba|sup|superior)/u', $text) === 1)
            || (preg_match('/\bfecha\b/u', $text) === 1
                && preg_match('/(arriba|sup|superior).*(derecha|der)/u', $text) === 1)
            || (preg_match('/\bfecha\b/u', $text) === 1 && preg_match('/mes en letras/u', $text) === 1);

        if (! $fechaHeader) {
            return [];
        }

        return [[
            'action' => 'setPageHeaderFecha',
            'payload' => new \stdClass(),
            'resultado' => 'ok',
        ]];
    }

    /**
     * CHK-05: título del informe dentro de un panel (fila 2 page header).
     *
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatchTitlePanel(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        $titlePanel =
            preg_match('/encerr(a|á|ar|e).*(t[ií]tulo).*(panel|recuadro)/u', $text) === 1
            || preg_match('/(panel|recuadro).*(t[ií]tulo)/u', $text) === 1
            || preg_match('/envolv(e|é|er).*(t[ií]tulo).*(panel|recuadro)/u', $text) === 1;

        if (! $titlePanel) {
            return [];
        }

        return [[
            'action' => 'wrapReportTitleInPanel',
            'payload' => new \stdClass(),
            'resultado' => 'ok',
        ]];
    }

    /**
     * CHK-04: títulos de columna en encabezado de página (fila 3).
     *
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatchColumnLabels(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/(detalle|detall).*(columna|campo)/u', $text) === 1
            && preg_match('/agreg(a|á|ar|ue)|incorpor/u', $text) === 1) {
            return [];
        }

        $columnHeader =
            (preg_match('/encabezado de p[aá]gina/u', $text) === 1 && preg_match('/columna/u', $text) === 1)
            || preg_match('/t[ií]tulo.*(encima|sobre).*(cada\s+)?columna/u', $text) === 1
            || preg_match('/(encima|sobre).*(cada\s+)?columna.*encabezado/u', $text) === 1
            || preg_match('/t[ií]tulos?\s+de\s+columna.*encabezado/u', $text) === 1;

        if (! $columnHeader) {
            return [];
        }

        $fields = self::resolveNamedFields($message);
        $payload = $fields === []
            ? new \stdClass()
            : ['fields' => $fields];

        return [[
            'action' => 'addPageHeaderColumnLabels',
            'payload' => $payload,
            'resultado' => 'ok',
        ]];
    }

    /**
     * @return list<string>
     */
    private static function resolveNamedFields(string $message): array
    {
        $field = DesignLayoutSemanticFieldResolver::resolveFromMessage($message);
        if ($field !== null && DesignLayoutWhitelist::isSchemaField($field)) {
            return [$field];
        }

        return [];
    }
}