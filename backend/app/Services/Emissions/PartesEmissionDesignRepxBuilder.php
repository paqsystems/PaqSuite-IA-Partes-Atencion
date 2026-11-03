<?php

namespace App\Services\Emissions;

/**
 * REPX mínimo con JsonDataSource para diseñador DX cuando el catálogo solo tiene layout JSON tabular.
 */
final class PartesEmissionDesignRepxBuilder
{
    public static function paqueteHorasPrincipal(): string
    {
        return self::build(
            'partes_paqueteHoras_principal',
            'Paquete de horas',
            PartesPaqueteHorasEmissionPort::designDatasetColumnNames(),
        );
    }

    /**
     * @param  list<string>  $columns
     */
    private static function build(string $reportName, string $dataSourceName, array $columns): string
    {
        $sample = [];
        $schemaNodes = '';
        foreach ($columns as $column) {
            $sample[$column] = self::sampleValue($column);
            $schemaNodes .= self::schemaNode($column);
        }

        $json = json_encode([$sample], JSON_UNESCAPED_UNICODE);
        $jsonEscaped = htmlspecialchars((string) $json, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $inner = '<?xml version="1.0" encoding="utf-8"?>'
            .'<JsonDataSource Name="'.htmlspecialchars($dataSourceName, ENT_XML1 | ENT_QUOTES, 'UTF-8').'">'
            .'<Source SourceType="DevExpress.DataAccess.Json.CustomJsonSource" Json="'.$jsonEscaped.'" />'
            .'<Schema><Node Name="root" Selected="true" NodeType="Array">'
            .$schemaNodes
            .'</Node></Schema></JsonDataSource>';

        $base64 = base64_encode($inner);

        return '<?xml version="1.0" encoding="utf-8"?>'
            .'<XtraReportsLayoutSerializer SerializerVersion="26.1.4.0" Ref="1" '
            .'ControlType="DevExpress.XtraReports.UI.XtraReport, DevExpress.XtraReports.v26.1" '
            .'Name="'.htmlspecialchars($reportName, ENT_XML1 | ENT_QUOTES, 'UTF-8').'" '
            .'PageWidthF="850" PageHeightF="1100" Version="26.1" DataSource="#Ref-0">'
            .'<Bands>'
            .'<Item1 Ref="2" ControlType="TopMarginBand" Name="TopMargin1" />'
            .'<Item2 Ref="3" ControlType="DetailBand" Name="Detail1" HeightF="23">'
            .'<Controls>'
            .'<Item1 Ref="4" ControlType="XRLabel" Name="label1" Text="label1" SizeF="200,23" LocationFloat="0,0">'
            .'<ExpressionBindings>'
            .'<Item1 Ref="5" EventName="BeforePrint" PropertyName="Text" Expression="[clienteNombre]" />'
            .'</ExpressionBindings></Item1>'
            .'</Controls></Item2>'
            .'<Item3 Ref="6" ControlType="BottomMarginBand" Name="BottomMargin1" />'
            .'</Bands>'
            .'<ComponentStorage>'
            .'<Item1 Ref="0" ObjectType="DevExpress.DataAccess.Json.JsonDataSource,DevExpress.DataAccess.v26.1" '
            .'Name="'.htmlspecialchars($dataSourceName, ENT_XML1 | ENT_QUOTES, 'UTF-8').'" Base64="'
            .$base64
            .'" /></ComponentStorage></XtraReportsLayoutSerializer>';
    }

    private static function sampleValue(string $column): mixed
    {
        return match ($column) {
            'duracionMinutos', 'saldo' => 0,
            'esTarea', 'sinCargo', 'presencial', 'cerrado' => false,
            'fecha' => '2026-01-01',
            default => $column,
        };
    }

    private static function schemaNode(string $column): string
    {
        $type = match ($column) {
            'duracionMinutos', 'saldo' => 'System.Nullable`1[System.Int64]',
            'esTarea', 'sinCargo', 'presencial', 'cerrado' => 'System.Nullable`1[System.Boolean]',
            'fecha' => 'System.Nullable`1[System.DateOnly]',
            default => 'System.String',
        };

        return '<Node Name="'.htmlspecialchars($column, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            .'" Type="'.htmlspecialchars($type, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            .'" Selected="true" NodeType="Property" />';
    }
}
