<?php

namespace App\Services\ExcelImport;

use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\ExcelImport\SpExcelImportRepository;
use App\Repositories\Sp\Partes\PartesTareaRepository;
use App\Services\Partes\PartesTareaException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportHandler;
use PaqSuite\LaravelCore\ExcelImport\Dto\BatchProcessResult;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportBatch;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportContext;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportRowError;
use PaqSuite\LaravelCore\ExcelImport\Dto\RowValidationResult;
use PaqSuite\LaravelCore\Parametros\Contracts\ParametroRepository;

/**
 * Handler host processCode=partes.tareas.import (TR-009).
 */
final class PartesTareasImportHandler implements ExcelImportHandler
{
    public function __construct(
        private readonly SpExcelImportRepository $excelRepository,
        private readonly PartesTareaRepository $tareaRepository,
        private readonly ParametroRepository $parametros,
    ) {
    }

    public function validateRow(array $row, int $rowNumber, ExcelImportContext $context): RowValidationResult
    {
        if ($this->isEmptyRow($row)) {
            return new RowValidationResult([]);
        }

        $errors = [];
        $partes = $this->actorPartes();
        $esSupervisor = (bool) ($partes['esSupervisor'] ?? false);
        $actorAsistenteId = isset($partes['asistenteId']) ? (int) $partes['asistenteId'] : 0;
        $actorCode = $actorAsistenteId > 0
            ? (string) (DB::table('PQ_PARTES_USUARIOS')->where('id', $actorAsistenteId)->value('code') ?? '')
            : '';

        $clienteCode = $this->cell($row, 'cliente');
        $asistenteCode = $this->cell($row, 'asistente');
        $tipoCode = $this->cell($row, 'tipo_tarea');
        $fechaRaw = $row['fecha'] ?? null;
        $duracionRaw = $this->cell($row, 'duracion');
        $sinCargoRaw = $this->cell($row, 'sin_cargo');
        $presencialRaw = $this->cell($row, 'presencial');
        $descripcion = $this->cell($row, 'descripcion');

        $clienteId = null;
        if ($clienteCode === '') {
            $errors[] = new ExcelImportRowError($rowNumber, 'cliente', 'partes.import.clienteRequerido');
        } else {
            $cliente = DB::table('PQ_PARTES_CLIENTES')
                ->where('code', $clienteCode)
                ->where('activo', true)
                ->where('inhabilitado', false)
                ->first();
            if ($cliente === null) {
                $errors[] = new ExcelImportRowError($rowNumber, 'cliente', 'partes.import.clienteInvalido');
            } else {
                $clienteId = (int) $cliente->id;
            }
        }

        $usuarioId = $actorAsistenteId;
        if ($esSupervisor) {
            if ($asistenteCode === '') {
                $errors[] = new ExcelImportRowError($rowNumber, 'asistente', 'partes.import.asistenteRequerido');
            } else {
                $asistente = DB::table('PQ_PARTES_USUARIOS')
                    ->where('code', $asistenteCode)
                    ->where('activo', true)
                    ->where('inhabilitado', false)
                    ->first();
                if ($asistente === null) {
                    $errors[] = new ExcelImportRowError($rowNumber, 'asistente', 'partes.import.asistenteInvalido');
                } else {
                    $usuarioId = (int) $asistente->id;
                }
            }
        } elseif ($asistenteCode !== '' && strcasecmp($asistenteCode, $actorCode) !== 0) {
            $errors[] = new ExcelImportRowError($rowNumber, 'asistente', 'partes.import.asistenteDistintoSesion');
        }

        $tipoId = null;
        if ($tipoCode === '') {
            $errors[] = new ExcelImportRowError($rowNumber, 'tipo_tarea', 'partes.import.tipoRequerido');
        } elseif ($clienteId !== null) {
            $tipo = DB::table('PQ_PARTES_TIPOS_TAREA')
                ->where('code', $tipoCode)
                ->where('activo', true)
                ->where('inhabilitado', false)
                ->first();
            if ($tipo === null) {
                $errors[] = new ExcelImportRowError($rowNumber, 'tipo_tarea', 'partes.import.tipoFueraUniverso');
            } elseif (! (bool) $tipo->is_generico) {
                $asig = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
                    ->where('cliente_id', $clienteId)
                    ->where('tipo_tarea_id', (int) $tipo->id)
                    ->exists();
                if (! $asig) {
                    $errors[] = new ExcelImportRowError($rowNumber, 'tipo_tarea', 'partes.import.tipoFueraUniverso');
                } else {
                    $tipoId = (int) $tipo->id;
                }
            } else {
                $tipoId = (int) $tipo->id;
            }
        }

        $fechaIso = $this->parseFecha($fechaRaw, $this->importerLocale());
        if ($fechaIso === null) {
            $errors[] = new ExcelImportRowError($rowNumber, 'fecha', 'partes.import.fechaInvalida');
        }

        $duracionMinutos = $this->parseDuracion($duracionRaw);
        $tramo = $this->tramoMinutos();
        if ($duracionMinutos === null || $duracionMinutos <= 0 || $duracionMinutos > 1440 || ($duracionMinutos % $tramo) !== 0) {
            $errors[] = new ExcelImportRowError($rowNumber, 'duracion', 'partes.import.duracionInvalida', [
                'tramo' => $tramo,
            ]);
        }

        $sinCargo = $this->parseBool($sinCargoRaw);
        if ($sinCargo === null) {
            $errors[] = new ExcelImportRowError($rowNumber, 'sin_cargo', 'partes.import.booleanoInvalido');
        }
        $presencial = $this->parseBool($presencialRaw);
        if ($presencial === null) {
            $errors[] = new ExcelImportRowError($rowNumber, 'presencial', 'partes.import.booleanoInvalido');
        }

        if ($descripcion === '') {
            $errors[] = new ExcelImportRowError($rowNumber, 'descripcion', 'partes.import.descripcionRequerida');
        }

        if ($errors !== []) {
            return new RowValidationResult([], $errors);
        }

        return new RowValidationResult([
            'clienteId' => $clienteId,
            'usuarioId' => $usuarioId,
            'tipoTareaId' => $tipoId,
            'fecha' => $fechaIso,
            'duracionMinutos' => $duracionMinutos,
            'sinCargo' => $sinCargo,
            'presencial' => $presencial,
            'observacion' => $descripcion,
        ]);
    }

    public function processBatch(ExcelImportBatch $batch, ExcelImportContext $context): BatchProcessResult
    {
        $rows = $this->excelRepository->validRows($batch->batchId);
        $partes = $this->actorPartes();
        $actorParams = [
            'p_actor_tipo_funcional' => $partes['tipoFuncional'] ?? 'asistente',
            'p_actor_asistente_id' => $partes['asistenteId'] ?? null,
            'p_actor_cliente_id' => $partes['clienteId'] ?? null,
            'p_actor_es_supervisor' => (bool) ($partes['esSupervisor'] ?? false),
        ];

        try {
            DB::transaction(function () use ($rows, $actorParams): void {
                foreach ($rows as $row) {
                    $n = $row['normalized'];
                    $this->tareaRepository->getOne('pq_sp_partes_tarea_upsert', array_merge($actorParams, [
                        'p_id' => null,
                        'p_usuario_id' => $n['usuarioId'],
                        'p_cliente_id' => $n['clienteId'],
                        'p_tipo_tarea_id' => $n['tipoTareaId'],
                        'p_fecha' => $n['fecha'],
                        'p_duracion_minutos' => $n['duracionMinutos'],
                        'p_observacion' => $n['observacion'],
                        'p_sin_cargo' => $n['sinCargo'],
                        'p_presencial' => $n['presencial'],
                        'p_confirmar_fecha_futura' => true,
                    ]));
                }
            });
        } catch (PartesTareaException|\Throwable) {
            return new BatchProcessResult(
                'failed',
                $batch->totalRows,
                0,
                count($rows),
                [],
                'excelImport.processingFailed',
            );
        }

        $status = $batch->errorRows > 0 ? 'partial' : 'done';

        return new BatchProcessResult(
            $status,
            $batch->totalRows,
            count($rows),
            $batch->errorRows,
            ['imported' => count($rows)],
            'excelImport.processed',
        );
    }

    public function describeTemplate(ExcelImportContext $context): ?array
    {
        return ['sheetName' => 'Hoja1'];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function cell(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'verdadero' : 'falso';
        }

        return trim((string) $value);
    }

    private function parseBool(string $raw): ?bool
    {
        $v = strtolower(trim($raw));
        if (in_array($v, ['verdadero', 'true', '1', 's', 'si', 'sí'], true)) {
            return true;
        }
        if (in_array($v, ['falso', 'false', '0', 'n', 'no'], true)) {
            return false;
        }

        return null;
    }

    private function parseDuracion(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // hh:mm — ej. 00:30 (media hora), 01:15
        if (preg_match('/^(\d{1,2}):([0-5]\d)$/', $raw, $m)) {
            $total = ((int) $m[1]) * 60 + (int) $m[2];

            return $total > 0 && $total <= 1440 ? $total : null;
        }

        // Minutos enteros — ej. 30
        if (ctype_digit($raw)) {
            $n = (int) $raw;

            return $n > 0 && $n <= 1440 ? $n : null;
        }

        // Serial de hora Excel (fracción de día, 0 < x < 1) — ej. 00:30 ≈ 0.020833
        if (is_numeric($raw)) {
            $fraction = (float) $raw;
            if ($fraction > 0 && $fraction < 1) {
                $n = (int) round($fraction * 1440);

                return $n > 0 && $n <= 1440 ? $n : null;
            }
        }

        return null;
    }

    private function parseFecha(mixed $raw, string $locale): ?string
    {
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d');
        }
        if (is_int($raw) || is_float($raw)) {
            // Serial Excel (días desde 1899-12-30).
            $unix = (int) round(((float) $raw - 25569) * 86400);

            return gmdate('Y-m-d', $unix);
        }
        $text = trim((string) $raw);
        if ($text === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            return $text;
        }

        $patterns = match (strtolower(substr($locale, 0, 2))) {
            'en' => ['m/d/Y', 'm-d-Y'],
            default => ['d/m/Y', 'd-m-Y'],
        };
        foreach ($patterns as $pattern) {
            $dt = \DateTimeImmutable::createFromFormat('!'.$pattern, $text);
            if ($dt instanceof \DateTimeImmutable) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($text);

        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function importerLocale(): string
    {
        $user = Auth::user();
        $locale = is_object($user) ? ($user->locale ?? null) : null;

        return is_string($locale) && $locale !== '' ? $locale : 'es';
    }

    private function tramoMinutos(): int
    {
        $row = $this->parametros->get('Partes', 'PartesDuracionTramoMin');
        $value = (int) ($row['valorInt'] ?? $row['valor'] ?? 15);

        return $value > 0 ? $value : 15;
    }

    /**
     * @return array<string, mixed>
     */
    private function actorPartes(): array
    {
        $attr = request()->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR);
        if (is_array($attr)) {
            return $attr;
        }

        return [];
    }
}
