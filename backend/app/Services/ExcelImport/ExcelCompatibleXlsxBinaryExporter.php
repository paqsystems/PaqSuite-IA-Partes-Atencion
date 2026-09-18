<?php

namespace App\Services\ExcelImport;

use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportBinaryExporter;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportProcess;
use PaqSuite\LaravelCore\ExcelImport\MinimalXlsxExcelImportBinaryExporter;
use ZipArchive;

/**
 * Post-procesa el XLSX mínimo GEN-14 para que Excel de escritorio lo abra
 * sin reparación (VML sin declaración XML, shapeId >= 1025, sheetViews, comentarios rich).
 */
final class ExcelCompatibleXlsxBinaryExporter implements ExcelImportBinaryExporter
{
    public function __construct(
        private readonly MinimalXlsxExcelImportBinaryExporter $inner,
    ) {
    }

    public function template(ExcelImportProcess $process, ?array $descriptor = null): string
    {
        return $this->makeExcelCompatible($this->inner->template($process, $descriptor));
    }

    public function errors(array $errors): string
    {
        return $this->makeExcelCompatible($this->inner->errors($errors));
    }

    public function workbookFromRows(array $rows, string $sheetName = 'Hoja1'): string
    {
        return $this->makeExcelCompatible($this->inner->workbookFromRows($rows, $sheetName));
    }

    private function makeExcelCompatible(string $binary): string
    {
        $inPath = $this->tempXlsxPath('xlsx_in');
        $outPath = $this->tempXlsxPath('xlsx_out');
        file_put_contents($inPath, $binary);

        $in = new ZipArchive();
        if ($in->open($inPath) !== true) {
            @unlink($inPath);

            throw new \RuntimeException('excelImport.xlsxZipOpenFailed');
        }

        $parts = [];
        for ($i = 0; $i < $in->numFiles; $i++) {
            $name = str_replace('\\', '/', (string) $in->getNameIndex($i));
            $parts[$name] = (string) $in->getFromIndex($i);
        }
        $in->close();

        if (isset($parts['xl/drawings/vmlDrawing1.vml'])) {
            $parts['xl/drawings/vmlDrawing1.vml'] = $this->fixVml($parts['xl/drawings/vmlDrawing1.vml']);
        }
        if (isset($parts['xl/comments1.xml'])) {
            $parts['xl/comments1.xml'] = $this->fixComments($parts['xl/comments1.xml']);
        }
        if (isset($parts['xl/worksheets/sheet1.xml'])) {
            $parts['xl/worksheets/sheet1.xml'] = $this->fixSheet($parts['xl/worksheets/sheet1.xml']);
        }
        if (isset($parts['xl/workbook.xml'])) {
            $parts['xl/workbook.xml'] = $this->fixWorkbook($parts['xl/workbook.xml']);
        }

        $parts = $this->orderZipParts($parts);

        @unlink($outPath);
        $out = new ZipArchive();
        if ($out->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($inPath);

            throw new \RuntimeException('excelImport.xlsxZipCreateFailed');
        }
        foreach ($parts as $name => $content) {
            if ($out->addFromString($name, $content) !== true) {
                $out->close();
                @unlink($inPath);
                @unlink($outPath);

                throw new \RuntimeException('excelImport.xlsxZipWriteFailed');
            }
        }
        $out->close();

        $fixed = (string) file_get_contents($outPath);
        @unlink($inPath);
        @unlink($outPath);

        return $fixed !== '' ? $fixed : $binary;
    }

    private function fixVml(string $vml): string
    {
        $vml = preg_replace('/^<\?xml[^?]*\?>\s*/', '', $vml) ?? $vml;
        $shapeId = 1025;
        $vml = preg_replace_callback(
            '/id="_x0000_s\d+"/',
            static function () use (&$shapeId): string {
                return 'id="_x0000_s'.$shapeId++.'"';
            },
            $vml,
        ) ?? $vml;

        return $vml;
    }

    private function fixComments(string $xml): string
    {
        return preg_replace_callback(
            '/<comment ([^>]*)><text><t>(.*?)<\/t><\/text><\/comment>/s',
            static function (array $match): string {
                $inner = $match[2];
                $space = (
                    str_contains($inner, "\n")
                    || str_contains($inner, "\t")
                    || $inner !== trim($inner)
                ) ? ' xml:space="preserve"' : '';

                return '<comment '.$match[1].'><text><r><rPr><sz val="11"/><rFont val="Calibri"/></rPr><t'
                    .$space.'>'.$inner.'</t></r></text></comment>';
            },
            $xml,
        ) ?? $xml;
    }

    private function fixSheet(string $xml): string
    {
        if (str_contains($xml, '<sheetViews>')) {
            return $xml;
        }

        $inject = '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>';

        if (preg_match('/<dimension\b[^>]*\/>/', $xml) === 1) {
            return preg_replace('/<dimension\b[^>]*\/>/', '$0'.$inject, $xml, 1) ?? $xml;
        }

        return preg_replace('/(<worksheet\b[^>]*>)/', '$1'.$inject, $xml, 1) ?? $xml;
    }

    private function fixWorkbook(string $xml): string
    {
        if (! str_contains($xml, '<fileVersion')) {
            $head = '<fileVersion appName="xl" lastEdited="5" lowestEdited="5" rupBuild="9303"/>'
                .'<workbookPr/>'
                .'<bookViews><workbookView/></bookViews>';
            $xml = preg_replace('/<sheets>/', $head.'<sheets>', $xml, 1) ?? $xml;
        }

        if (! str_contains($xml, '<calcPr')) {
            $xml = str_replace('</workbook>', '<calcPr calcId="0"/></workbook>', $xml);
        }

        return $xml;
    }

    /**
     * OOXML: [Content_Types].xml y _rels/.rels primero (Excel desktop es estricto).
     *
     * @param  array<string, string>  $parts
     * @return array<string, string>
     */
    private function orderZipParts(array $parts): array
    {
        $priority = [
            '[Content_Types].xml' => 0,
            '_rels/.rels' => 1,
        ];
        uksort($parts, static function (string $a, string $b) use ($priority): int {
            $pa = $priority[$a] ?? 100;
            $pb = $priority[$b] ?? 100;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return strcmp($a, $b);
        });

        return $parts;
    }

    private function tempXlsxPath(string $prefix): string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix);
        if ($tmp === false) {
            throw new \RuntimeException('excelImport.xlsxTempFailed');
        }
        $path = $tmp.'.xlsx';
        @unlink($tmp);

        return $path;
    }
}
