<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Resuelve nombre técnico de campo desde lenguaje natural (GEN-37 / schema diseñador).
 */
final class DesignLayoutSemanticFieldResolver
{
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