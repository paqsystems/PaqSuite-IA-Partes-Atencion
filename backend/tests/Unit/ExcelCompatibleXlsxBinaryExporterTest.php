<?php

namespace Tests\Unit;

use App\Services\ExcelImport\ExcelCompatibleXlsxBinaryExporter;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportColumn;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportProcess;
use PaqSuite\LaravelCore\ExcelImport\MinimalXlsxExcelImportBinaryExporter;
use PaqSuite\LaravelCore\ExcelImport\ZipXmlExcelWorkbookParser;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ExcelCompatibleXlsxBinaryExporterTest extends TestCase
{
    public function test_plantilla_es_xlsx_compatible_con_excel(): void
    {
        $process = new ExcelImportProcess(
            'partes.tareas.import',
            'partes_carga_diaria',
            true,
            [
                new ExcelImportColumn('cliente', 'cliente', 'string', true, null, 'Código de cliente'),
                new ExcelImportColumn('fecha', 'fecha', 'date', true, null, 'Fecha dd/mm/yyyy'),
                new ExcelImportColumn('sin_cargo', 'sin_cargo', 'bool', true, null, 'verdadero o falso'),
            ],
            'Hoja1',
            null,
            true,
            ExcelImportProcess::BOOLEAN_FORMAT_VERDADERO_FALSO,
        );

        $binary = (new ExcelCompatibleXlsxBinaryExporter(new MinimalXlsxExcelImportBinaryExporter()))
            ->template($process, ['locale' => 'es']);

        $this->assertSame('PK', substr($binary, 0, 2));

        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        file_put_contents($path, $binary);

        try {
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($path) === true);
            $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $workbook = (string) $zip->getFromName('xl/workbook.xml');
            $vml = (string) $zip->getFromName('xl/drawings/vmlDrawing1.vml');
            $comments = (string) $zip->getFromName('xl/comments1.xml');
            $zip->close();

            $this->assertStringContainsString('<sheetViews>', $sheet);
            $this->assertStringContainsString('<fileVersion', $workbook);
            $this->assertStringNotContainsString('<?xml', $vml);
            $this->assertStringContainsString('id="_x0000_s1025"', $vml);
            $this->assertStringContainsString('<rPr>', $comments);
            $this->assertStringContainsString('xml:space="preserve"', $comments);

            $parsed = (new ZipXmlExcelWorkbookParser())->parse($path, 'Hoja1');
            $this->assertSame(['cliente', 'fecha', 'sin_cargo'], $parsed->headers);
        } finally {
            @unlink($path);
        }
    }
}
