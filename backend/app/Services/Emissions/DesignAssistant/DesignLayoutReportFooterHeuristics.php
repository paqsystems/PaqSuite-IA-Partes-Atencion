<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * CHK-09 / CHK-10 / CHK-11..14 (report footer + calculados).
 */
final class DesignLayoutReportFooterHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (self::isReportFooterCountRequest($text)) {
            return [
                ['action' => 'ensureReportFooter', 'payload' => []],
                ['action' => 'addReportSum', 'payload' => ['summary' => 'count']],
            ];
        }

        if (self::isEnsureReportFooterRequest($text)) {
            return [
                ['action' => 'ensureReportFooter', 'payload' => []],
            ];
        }

        if (self::isCalculatedSumRequest($text)) {
            return [
                [
                    'action' => 'addCalculatedField',
                    'payload' => [
                        'kind' => 'sum',
                        'name' => 'calc_sumaMinutos',
                        'fields' => ['duracionMinutos'],
                    ],
                ],
            ];
        }

        if (self::isCalculatedIifRequest($text)) {
            return [
                [
                    'action' => 'addCalculatedField',
                    'payload' => [
                        'kind' => 'iif',
                        'name' => 'calc_textoCliente',
                        'testField' => 'clienteNombre',
                        'trueField' => 'clienteNombre',
                        'falseField' => 'observacion',
                    ],
                ],
            ];
        }

        if (self::isDetailCalculatedRequest($text)) {
            $name = preg_match('/suma|minuto/u', $text) === 1 ? 'calc_sumaMinutos' : 'calc_textoCliente';

            return [
                ['action' => 'addDetailCalculatedLabel', 'payload' => ['calculatedName' => $name]],
            ];
        }

        if (self::isReportCalculatedTotalRequest($text)) {
            return [
                ['action' => 'ensureReportFooter', 'payload' => []],
                [
                    'action' => 'addReportSum',
                    'payload' => ['summary' => 'sum', 'calculatedField' => 'calc_sumaMinutos'],
                ],
            ];
        }

        return [];
    }

    private static function isEnsureReportFooterRequest(string $text): bool
    {
        if (preg_match('/p[aá]gina|page\s*footer/u', $text) === 1) {
            return false;
        }

        if (preg_match('/p[aá]gina\s+footer|page\s+footer/u', $text) === 1) {
            return false;
        }

        return preg_match('/pie\s+(del|de)\s+informe/u', $text) === 1
            && preg_match('/totaliz|registros?/u', $text) !== 1;
    }

    private static function isReportFooterCountRequest(string $text): bool
    {
        if (preg_match('/grupo|cliente/u', $text) === 1 && preg_match('/registros?\s+en\s+el\s+pie/u', $text) !== 1) {
            return false;
        }

        return preg_match('/totaliz.*registros?/u', $text) === 1
            && preg_match('/pie\s+(del|de)\s+informe/u', $text) === 1;
    }

    private static function isCalculatedSumRequest(string $text): bool
    {
        return preg_match('/campo\s+calculad/u', $text) === 1
            && preg_match('/sum(e|a|á|ar)/u', $text) === 1
            && preg_match('/minuto|duraci[oó]n/u', $text) === 1;
    }

    private static function isCalculatedIifRequest(string $text): bool
    {
        return preg_match('/campo\s+calculad/u', $text) === 1
            && preg_match('/iif|i\s*if/u', $text) === 1;
    }

    private static function isDetailCalculatedRequest(string $text): bool
    {
        return preg_match('/campo\s+calculad/u', $text) === 1
            && preg_match('/(detalle|mostr)/u', $text) === 1;
    }

    private static function isReportCalculatedTotalRequest(string $text): bool
    {
        return preg_match('/totaliz.*calculad/u', $text) === 1
            && preg_match('/pie\s+(del|de)\s+informe/u', $text) === 1;
    }
}