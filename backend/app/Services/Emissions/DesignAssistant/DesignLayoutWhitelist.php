<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Whitelist v1 TR-015-update-b §5.1.
 */
final class DesignLayoutWhitelist
{
    public const PROCESS_CODE = 'partes.informes.consultaDetallada';

    /** @var list<string> */
    public const TYPES = [
        'setReportTitle',
        'addGroupByField',
        'addGroupSum',
        'setPageOrientation',
    ];

    /** @var list<string> columnas schema Consulta detallada (TR-011 / TR-015). */
    public const SCHEMA_FIELDS = [
        'fecha',
        'clienteCode',
        'clienteNombre',
        'usuarioCode',
        'usuarioNombre',
        'tipoTareaCode',
        'tipoTareaDescripcion',
        'duracionMinutos',
        'sinCargo',
        'presencial',
        'cerrado',
        'observacion',
        'erpCliente',
        'erpArticulo',
    ];

    /** @var list<string> */
    public const NUMERIC_FIELDS = [
        'duracionMinutos',
    ];

    public static function isAllowedType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public static function isSchemaField(string $field): bool
    {
        return in_array($field, self::SCHEMA_FIELDS, true);
    }

    public static function isNumericField(string $field): bool
    {
        return in_array($field, self::NUMERIC_FIELDS, true);
    }
}
