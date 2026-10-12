import { apiRequest, type ApiClientResult } from '../../http/apiClient';
import { resolveRequestUrl } from '../../mobile/resolveApiBaseUrl';
import { parseEnvelope, transportI18nKey } from '../../http/parseEnvelope';
import type { PaqSuiteEnvelope } from '../../http/parseEnvelope';
import type { ExcelImportBatchDto, ExcelImportRowErrorDto } from './types';

export const excelImportBasePath = '/api/v1/excel-import';

export type ExcelImportRowErrorsResult = {
  items: ExcelImportRowErrorDto[];
  page: number;
  pageSize: number;
  total?: number;
};

const xlsxAccept =
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,*/*';

async function assertXlsxBlob(response: Response): Promise<Blob> {
  if (!response.ok) {
    throw new Error(`excelImport.templateHttp.${response.status}`);
  }
  const blob = await response.blob();
  const header = new Uint8Array(await blob.slice(0, 2).arrayBuffer());
  // ZIP/OOXML signature "PK"
  if (header.length < 2 || header[0] !== 0x50 || header[1] !== 0x4b) {
    throw new Error('excelImport.templateInvalid');
  }
  return blob;
}

/** `GET .../template` — descarga binaria `.xlsx` (fuera del envelope, C1-14-06). */
export async function downloadTemplate(processCode: string): Promise<Blob> {
  const response = await fetch(
    resolveRequestUrl(
      `${excelImportBasePath}/processes/${encodeURIComponent(processCode)}/template`
    ),
    {
      method: 'GET',
      headers: { Accept: xlsxAccept },
    }
  );
  return assertXlsxBlob(response);
}

/**
 * `POST .../batches` — carga + validación (multipart). Se usa `fetch` directo
 * porque `apiRequest` fuerza `Content-Type: application/json`, lo que rompe el
 * boundary de `multipart/form-data`. El envelope se clasifica igual.
 */
export async function uploadBatch(
  processCode: string,
  file: File,
  sheetName?: string
): Promise<ApiClientResult<ExcelImportBatchDto>> {
  const form = new FormData();
  form.append('file', file);
  form.append('processCode', processCode);
  if (sheetName !== undefined && sheetName.trim() !== '') {
    form.append('sheetName', sheetName.trim());
  }

  let response: Response;
  try {
    response = await fetch(resolveRequestUrl(`${excelImportBasePath}/batches`), {
      method: 'POST',
      body: form,
    });
  } catch (cause) {
    return { kind: 'transportError', i18nKey: transportI18nKey, cause };
  }

  let parsed: unknown;
  try {
    const text = await response.text();
    parsed = text === '' ? null : JSON.parse(text);
  } catch (cause) {
    return { kind: 'transportError', httpStatus: response.status, i18nKey: transportI18nKey, cause };
  }

  const envelope = parseEnvelope(parsed);
  if (!envelope) {
    return { kind: 'transportError', httpStatus: response.status, i18nKey: transportI18nKey, cause: parsed };
  }
  if (envelope.error !== 0 || response.status >= 400) {
    return { kind: 'envelopeError', httpStatus: response.status, envelope };
  }
  return {
    kind: 'ok',
    httpStatus: response.status,
    envelope: envelope as PaqSuiteEnvelope<ExcelImportBatchDto>,
  };
}

/** `GET .../batches/{batchId}`. */
export function getBatch(batchId: string): Promise<ApiClientResult<ExcelImportBatchDto>> {
  return apiRequest<ExcelImportBatchDto>(`${excelImportBasePath}/batches/${batchId}`, {
    method: 'GET',
  });
}

/** `GET .../batches/{batchId}/errors`. */
export function getBatchErrors(
  batchId: string,
  page = 1,
  pageSize = 25
): Promise<ApiClientResult<ExcelImportRowErrorsResult>> {
  return apiRequest<ExcelImportRowErrorsResult>(
    `${excelImportBasePath}/batches/${batchId}/errors?page=${page}&pageSize=${pageSize}`,
    { method: 'GET' }
  );
}

/** `GET .../batches/{batchId}/errors/export` — binario `.xlsx` (C1-14-15). */
export async function exportBatchErrors(batchId: string): Promise<Blob> {
  const response = await fetch(
    resolveRequestUrl(`${excelImportBasePath}/batches/${encodeURIComponent(batchId)}/errors/export`),
    {
      method: 'GET',
      headers: { Accept: xlsxAccept },
    }
  );
  return assertXlsxBlob(response);
}

/** `POST .../batches/{batchId}/process` — disparo explícito de Procesar. */
export function processBatch(
  batchId: string
): Promise<ApiClientResult<ExcelImportBatchDto>> {
  return apiRequest<ExcelImportBatchDto>(
    `${excelImportBasePath}/batches/${batchId}/process`,
    { method: 'POST' }
  );
}
