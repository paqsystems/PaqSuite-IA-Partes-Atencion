<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Resuelve nombre técnico de campo desde lenguaje natural (GEN-37 / schema diseñador).
 */
final class DesignLayoutSemanticFieldResolver
{
    /**
     * @return list<string>
     */
    public static function resolveFieldsFromList(string $message, string $processCode = DesignLayoutWhitelist::PROCESS_CODE): array
    {
        $segment = $message;
        if (preg_match('/:\s*(.+)$/u', $message, $matches) === 1) {
            $segment = (string) $matches[1];
        }

        $parts = preg_split('/\s*,\s*|\s*;\s*|\s+y\s+/u', $segment) ?: [];
        $fields = [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            $resolved = self::resolveToken($part, $processCode);
            if ($resolved !== null && ! in_array($resolved, $fields, true)) {
                $fields[] = $resolved;
            }
        }

        return $fields;
    }

    public static function resolveToken(string $token, string $processCode = DesignLayoutWhitelist::PROCESS_CODE): ?string
    {
        $text = mb_strtolower(trim($token), 'UTF-8');
        if ($text === '') {
            return null;
        }

        if (preg_match('/^observaci[oó]n/u', $text) === 1) {
            return 'observacion';
        }

        foreach (DesignSemanticCatalog::fieldsForProcess($processCode) as $field) {
            $name = $field['name'];
            if ($name !== '' && ($text === mb_strtolower($name, 'UTF-8') || mb_strpos($text, mb_strtolower($name, 'UTF-8'), 0, 'UTF-8') !== false)) {
                return $name;
            }

            $label = mb_strtolower($field['label'], 'UTF-8');
            if ($label !== '' && ($text === $label || mb_strpos($text, $label, 0, 'UTF-8') !== false)) {
                return $name;
            }

            foreach ($field['synonyms'] as $synonym) {
                $syn = mb_strtolower(trim($synonym), 'UTF-8');
                if ($syn === '' || mb_strlen($syn) < 3) {
                    continue;
                }
                if ($text === $syn || mb_strpos($text, $syn, 0, 'UTF-8') !== false) {
                    return $name;
                }
            }
        }

        return null;
    }

    public static function resolveFromMessage(string $message, string $processCode = DesignLayoutWhitelist::PROCESS_CODE): ?string
    {
        $text = mb_strtolower($message, 'UTF-8');

        if (preg_match('/observaci[oó]n/u', $text) === 1) {
            return 'observacion';
        }

        foreach (DesignSemanticCatalog::fieldsForProcess($processCode) as $field) {
            $name = $field['name'];
            if ($name !== '' && mb_strpos($text, mb_strtolower($name, 'UTF-8'), 0, 'UTF-8') !== false) {
                return $name;
            }

            $label = mb_strtolower($field['label'], 'UTF-8');
            if ($label !== '' && mb_strpos($text, $label, 0, 'UTF-8') !== false) {
                return $name;
            }

            foreach ($field['synonyms'] as $synonym) {
                $syn = mb_strtolower(trim($synonym), 'UTF-8');
                if ($syn !== '' && mb_strpos($text, $syn, 0, 'UTF-8') !== false) {
                    return $name;
                }
            }
        }

        return null;
    }
}