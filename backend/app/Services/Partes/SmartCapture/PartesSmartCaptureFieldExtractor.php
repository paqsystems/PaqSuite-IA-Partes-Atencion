<?php

namespace App\Services\Partes\SmartCapture;

/**
 * Extrae campos de un texto libre cuando el LLM responde prosa sin JSON `fields`.
 */
final class PartesSmartCaptureFieldExtractor
{
    private const STOP = 'asistente|empleado|cliente|tipo(?:\s+de)?\s*tarea|\btipo\b|fecha|duraci[oó]n|observaci[oó]n|nota|descripci[oó]n|sin\s+cargo|presencial|guardar|grabar';

    /**
     * @return array{fields: array<string, mixed>, save: bool}
     */
    public static function fromText(string $text): array
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($normalized === '') {
            return ['fields' => [], 'save' => false];
        }

        $fields = [];
        $asistente = self::firstCatalogToken(self::labeledValue($normalized, 'asistente|empleado'));
        if ($asistente !== null) {
            $fields['asistente'] = $asistente;
        }
        $cliente = self::firstCatalogToken(self::labeledValue($normalized, 'cliente'));
        if ($cliente !== null) {
            $fields['cliente'] = $cliente;
        }
        $tipo = self::firstCatalogToken(self::labeledValue($normalized, 'tipo(?:\s+de)?\s*tarea|\btipo\b'));
        if ($tipo !== null) {
            $fields['tipoTarea'] = $tipo;
        }
        $fecha = self::labeledValue($normalized, 'fecha');
        if ($fecha !== null) {
            $fields['fecha'] = $fecha;
        }
        $duracion = self::labeledValue($normalized, 'duraci[oó]n(?:\s+de)?');
        if ($duracion !== null) {
            $fields['duracionMinutos'] = self::normalizeDurationToken($duracion);
        }
        $observacion = self::labeledValue($normalized, 'observaci[oó]n|nota|descripci[oó]n');
        if ($observacion !== null) {
            $fields['observacion'] = $observacion;
        }
        if (preg_match('/\bsin\s+cargo\b/iu', $normalized) === 1) {
            $fields['sinCargo'] = true;
        }
        if (preg_match('/\bno\s+presencial\b/iu', $normalized) === 1) {
            $fields['presencial'] = false;
        } elseif (preg_match('/\bpresencial\b/iu', $normalized) === 1) {
            $fields['presencial'] = true;
        }

        $save = preg_match(
            '/\b(?:guardar|grabar|registrar|procesar)\b|\bconfirmar\s+tarea\b/iu',
            $normalized
        ) === 1;

        return ['fields' => $fields, 'save' => $save];
    }

    /**
     * Capas posteriores pisan solo valores no vacíos (el JSON del LLM gana).
     *
     * @param  array<string, mixed>  ...$layers
     * @return array<string, mixed>
     */
    public static function mergeFields(array ...$layers): array
    {
        $merged = [];
        foreach ($layers as $layer) {
            foreach ($layer as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private static function labeledValue(string $text, string $labelPattern): ?string
    {
        $pattern = '/(?:^|[\s,;:¿¡])(?:'.$labelPattern.')\s+(.+?)(?=\s+(?:'.self::STOP.')\b|$)/iu';
        if (preg_match($pattern, $text, $match) !== 1) {
            return null;
        }
        $value = trim((string) $match[1], " \t.,;:\"'«»");

        return $value !== '' ? $value : null;
    }

    private static function firstCatalogToken(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (preg_match('/^([A-Za-z0-9][A-Za-z0-9_.-]*)/u', $raw, $match) !== 1) {
            return null;
        }

        return $match[1];
    }

    private static function normalizeDurationToken(string $raw): int|string
    {
        $trimmed = trim($raw);
        if (preg_match('/^(\d+)\s*(?:min|mins|minuto|minutos)\b/iu', $trimmed, $match) === 1) {
            return (int) $match[1];
        }
        if (preg_match('/^(\d{1,2}:[0-5]\d)\s*(?:h|hs|hrs|hora|horas)?$/iu', $trimmed, $clock) === 1) {
            return $clock[1];
        }

        return $trimmed;
    }
}
