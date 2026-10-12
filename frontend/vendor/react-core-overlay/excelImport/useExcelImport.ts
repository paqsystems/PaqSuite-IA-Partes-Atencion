import { useCallback, useState } from 'react';
import {
  downloadTemplate,
  exportBatchErrors,
  getBatchErrors,
  processBatch,
  uploadBatch,
} from './excelImportApiClient';
import { listXlsxSheetNames } from './listXlsxSheetNames';
import {
  buildCompletePayload,
  excelImportErrorEffect,
  excelImportErrorKey,
  resolveProcessEnablement,
} from './excelImportPolicy';
import type {
  ExcelImportBatchDto,
  ExcelImportCompletePayload,
  ExcelImportRowErrorDto,
  ExcelImportUiState,
} from './types';

export type UseExcelImportOptions = {
  processCode: string;
  onComplete: (payload: ExcelImportCompletePayload) => void;
  /** Preferencia de hoja (catálogo host); se preselecciona si existe en el archivo. */
  sheetName?: string;
};

export type UseExcelImportState = {
  uiState: ExcelImportUiState;
  batch: ExcelImportBatchDto | null;
  rowErrors: ExcelImportRowErrorDto[];
  errorKey: string | null;
  /** Nombre del archivo elegido (para feedback en drop zone). */
  selectedFileName: string | null;
  sheetNames: string[];
  selectedSheetName: string | null;
  processEnabled: boolean;
  processDisabledReasonKey: string | null;
  selectFile: (file: File) => void;
  selectSheet: (sheetName: string | null) => void;
  validate: () => Promise<void>;
  process: () => Promise<void>;
  getTemplate: () => Promise<Blob>;
  exportErrors: () => Promise<Blob | null>;
  reset: () => void;
};

/**
 * Máquina de estados FE del import embebido (GEN-14): `idle → fileSelected →
 * validating → validated|invalid → processing → completed`. Validar nunca
 * procesa; Procesar exige clic explícito. `onComplete` en done/partial/failed/queued.
 */
export function useExcelImport(options: UseExcelImportOptions): UseExcelImportState {
  const { processCode, onComplete, sheetName: preferredSheetName } = options;
  const [uiState, setUiState] = useState<ExcelImportUiState>('idle');
  const [file, setFile] = useState<File | null>(null);
  const [batch, setBatch] = useState<ExcelImportBatchDto | null>(null);
  const [rowErrors, setRowErrors] = useState<ExcelImportRowErrorDto[]>([]);
  const [errorKey, setErrorKey] = useState<string | null>(null);
  const [sheetNames, setSheetNames] = useState<string[]>([]);
  const [selectedSheetName, setSelectedSheetName] = useState<string | null>(null);

  const reset = useCallback(() => {
    setUiState('idle');
    setFile(null);
    setBatch(null);
    setRowErrors([]);
    setErrorKey(null);
    setSheetNames([]);
    setSelectedSheetName(null);
  }, []);

  const selectSheet = useCallback((nextSheet: string | null) => {
    const trimmed = nextSheet?.trim() || null;
    setSelectedSheetName(trimmed);
    if (trimmed) {
      setErrorKey((current) => (current === 'excelImport.sheet.required' ? null : current));
    }
  }, []);

  const selectFile = useCallback(
    (selected: File) => {
      setFile(selected);
      setUiState('fileSelected');
      setErrorKey(null);
      setBatch(null);
      setRowErrors([]);
      setSheetNames([]);
      setSelectedSheetName(null);
      void listXlsxSheetNames(selected)
        .then((names) => {
          setSheetNames(names);
          if (names.length === 0) {
            setErrorKey('excelImport.sheet.required');
            return;
          }
          const preferred = preferredSheetName?.trim() ?? '';
          setSelectedSheetName(
            preferred !== '' && names.includes(preferred) ? preferred : names[0],
          );
        })
        .catch(() => {
          setSheetNames([]);
          setSelectedSheetName(null);
          setErrorKey(excelImportErrorKey(4606));
          setUiState('invalid');
        });
    },
    [preferredSheetName],
  );

  const validate = useCallback(async () => {
    if (!file) {
      return;
    }
    const sheetToSend = selectedSheetName?.trim() ?? '';
    if (sheetToSend === '') {
      setErrorKey('excelImport.sheet.required');
      return;
    }
    setUiState('validating');
    setErrorKey(null);
    const result = await uploadBatch(processCode, file, sheetToSend);
    if (result.kind !== 'ok') {
      const codigo =
        result.kind === 'envelopeError' ? result.envelope.error : 'transport';
      setErrorKey(excelImportErrorKey(codigo));
      setUiState('idle');
      return;
    }
    const dto = result.envelope.resultado;
    setBatch(dto);
    if (dto.status === 'invalid') {
      setUiState('invalid');
      return;
    }
    if (dto.errorRows > 0) {
      const errorsResult = await getBatchErrors(dto.batchId);
      if (errorsResult.kind === 'ok') {
        setRowErrors(errorsResult.envelope.resultado.items ?? []);
      }
    }
    setUiState('validated');
  }, [file, processCode, selectedSheetName]);

  const process = useCallback(async () => {
    if (!batch) {
      return;
    }
    setUiState('processing');
    const result = await processBatch(batch.batchId);
    if (result.kind !== 'ok') {
      const codigo =
        result.kind === 'envelopeError' ? result.envelope.error : 'transport';
      setErrorKey(excelImportErrorKey(codigo));
      setUiState('validated');
      return;
    }
    const dto = result.envelope.resultado;
    setBatch(dto);
    setUiState('completed');
    onComplete(buildCompletePayload(dto));
  }, [batch, onComplete]);

  const getTemplate = useCallback(() => downloadTemplate(processCode), [processCode]);

  const exportErrors = useCallback(async () => {
    if (!batch) {
      return null;
    }
    return exportBatchErrors(batch.batchId);
  }, [batch]);

  const enablement = batch
    ? resolveProcessEnablement(batch)
    : { enabled: false, reasonKey: null };

  return {
    uiState,
    batch,
    rowErrors,
    errorKey,
    selectedFileName: file?.name ?? null,
    sheetNames,
    selectedSheetName,
    processEnabled: enablement.enabled,
    processDisabledReasonKey: enablement.reasonKey,
    selectFile,
    selectSheet,
    validate,
    process,
    getTemplate,
    exportErrors,
    reset,
  };
}

/** Efecto UI del código de error (4604 oculta, 4603 deshabilita). */
export { excelImportErrorEffect };
