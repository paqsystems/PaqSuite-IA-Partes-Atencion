<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * CHK-15..17 — imagen de fondo, logo y QR (oleada 11).
 */
final class DesignLayoutImagesHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (self::isWatermarkRequest($text)) {
            return [[
                'action' => 'setReportWatermarkImage',
                'payload' => ['source' => 'default'],
                'resultado' => 'ok',
            ]];
        }

        if (self::isLogoRequest($text)) {
            return [[
                'action' => 'setPageHeaderLogo',
                'payload' => ['source' => 'empresa'],
                'resultado' => 'ok',
            ]];
        }

        if (self::isQrClienteCodeRequest($text, $message)) {
            $placement = self::resolveBarCodePlacement($text);

            return [[
                'action' => 'addDetailBarCode',
                'payload' => [
                    'field' => 'clienteCode',
                    'symbology' => 'QRCode',
                    'placement' => $placement,
                ],
                'resultado' => 'ok',
            ]];
        }

        return [];
    }

    private static function isWatermarkRequest(string $text): bool
    {
        if (preg_match('/\bwatermark\b/u', $text) === 1) {
            return true;
        }

        return preg_match('/imagen\s+de\s+fondo/u', $text) === 1
            || preg_match('/fondo\s+con\s+imagen/u', $text) === 1
            || (preg_match('/\bfondo\b/u', $text) === 1 && preg_match('/informe/u', $text) === 1);
    }

    private static function isLogoRequest(string $text): bool
    {
        if (preg_match('/\blogo\b/u', $text) !== 1) {
            return false;
        }

        return preg_match('/empresa/u', $text) === 1
            || preg_match('/(arriba|sup|superior).*(izquierd|izq)/u', $text) === 1
            || preg_match('/insert(a|á)|agreg(a|á)|pon(e|é)/u', $text) === 1;
    }

    private static function isQrClienteCodeRequest(string $text, string $rawMessage): bool
    {
        if (preg_match('/\bqr\b|c[oó]digo\s*qr/iu', $text) !== 1) {
            return false;
        }

        if (preg_match('/clientecode|cliente\s*code|c[oó]digo\s+de\s+cliente|cliente/u', $text) === 1) {
            return true;
        }

        return stripos($rawMessage, 'clienteCode') !== false;
    }

    private static function resolveBarCodePlacement(string $text): string
    {
        if (preg_match('/pie\s+(del|de)\s+(el\s+)?informe/u', $text) === 1) {
            return 'reportFooter';
        }

        if (preg_match('/pie\s+(de\s+)?p[aá]gina|page\s*footer/u', $text) === 1) {
            return 'pageFooter';
        }

        if (preg_match('/encabezado\s+(del|de)\s+(el\s+)?informe|report\s*header/u', $text) === 1) {
            return 'reportHeader';
        }

        if (preg_match(
            '/encabezado\s+(de\s+)?p[aá]gina|page\s*header|en\s+el\s+encabezado|arriba\s+(a\s+la\s+)?(derecha|izquierda)/u',
            $text,
        ) === 1) {
            return 'pageHeader';
        }

        if (preg_match('/\ben\s+el\s+detalle\b|\bdetalle\b/u', $text) === 1) {
            return 'detail';
        }

        return 'detail';
    }
}