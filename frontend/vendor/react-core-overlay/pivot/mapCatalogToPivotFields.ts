import { formatDate, formatDecimalNumber } from '../../i18n/formatters';
import type { PivotCampoCatalogDto, PivotConsultaCatalogDto, PivotSummaryType } from './pivotCatalogClient';

export type PivotFieldConfig = {
  dataField: string;
  dataType?: string;
  caption: string;
  summaryType?: PivotSummaryType;
  area?: 'row' | 'column' | 'data' | 'filter';
  allowedSummaryTypes?: PivotSummaryType[];
  /** DevExtreme format (p. ej. shortDate). */
  format?: string;
  customizeText?: (cellInfo: { value?: unknown }) => string;
  /** Fecha: sin jerarquía año/mes si es `day` (el usuario puede cambiarlo en DX). */
  groupInterval?: 'year' | 'quarter' | 'month' | 'day' | 'dayOfWeek' | 'hour';
};

const NUMERIC_TYPES = new Set(['number', 'numeric']);
const DATE_TYPES = new Set(['date', 'datetime']);

export function isPivotDateDataType(dataType: string | undefined): boolean {
  return Boolean(dataType && DATE_TYPES.has(dataType));
}

/**
 * Texto visible de fecha en Pivot según locale i18n activo (GEN-02 / regla BASE 32).
 * Día/mes/año vía `Intl.DateTimeFormat(locale)`.
 */
export function formatPivotDateCellText(
  value: unknown,
  locale: string
): string {
  if (value == null || value === '') {
    return '';
  }
  return formatDate(value as Date | string | number, locale);
}

/**
 * Aplica formato corto de fecha a todo campo `date` / `datetime` del listado
 * (catálogo o fields ad-hoc). Idempotente si ya hay customizeText propio.
 */
export function enrichPivotFieldsWithDateFormat<T extends { dataType?: string; format?: string; customizeText?: (cellInfo: { value?: unknown }) => string }>(
  fields: T[],
  locale: string
): T[] {
  return fields.map((field) => {
    if (!isPivotDateDataType(field.dataType)) {
      return field;
    }
    if (field.customizeText) {
      return { ...field, format: field.format ?? 'shortDate' };
    }
    return {
      ...field,
      format: field.format ?? 'shortDate',
      customizeText: (cellInfo: { value?: unknown }) =>
        formatPivotDateCellText(cellInfo.value, locale),
    };
  });
}

export function isPivotNumericDataType(dataType: string | undefined): boolean {
  return Boolean(dataType && NUMERIC_TYPES.has(dataType));
}

function coercePivotNumericValue(value: unknown): number | null {
  if (value == null || value === '') {
    return null;
  }
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

/**
 * Texto visible de importes/cantidades en Pivot: miles + 2 decimales (GEN-12 / CC PQ 16-09-2026).
 */
export function formatPivotNumericCellText(value: unknown, locale: string): string {
  const numeric = coercePivotNumericValue(value);
  if (numeric == null) {
    return '';
  }
  return formatDecimalNumber(numeric, locale);
}

/**
 * Aplica formato decimal a campos `number` / `numeric`. Idempotente si ya hay customizeText propio.
 */
export function enrichPivotFieldsWithNumericFormat<T extends { dataType?: string; format?: string; customizeText?: (cellInfo: { value?: unknown }) => string }>(
  fields: T[],
  locale: string
): T[] {
  return fields.map((field) => {
    if (!isPivotNumericDataType(field.dataType)) {
      return field;
    }
    if (field.customizeText) {
      return field;
    }
    return {
      ...field,
      format: field.format ?? 'decimal',
      customizeText: (cellInfo: { value?: unknown }) =>
        formatPivotNumericCellText(cellInfo.value, locale),
    };
  });
}

export function resolveAllowedSummaryTypes(
  dataType: string,
  catalogAllowed?: PivotSummaryType[]
): PivotSummaryType[] {
  if (catalogAllowed && catalogAllowed.length > 0) {
    return catalogAllowed;
  }

  if (NUMERIC_TYPES.has(dataType)) {
    return ['sum', 'avg', 'min', 'max', 'count'];
  }

  if (DATE_TYPES.has(dataType)) {
    return ['count', 'min', 'max'];
  }

  return ['count'];
}

export function resolveDefaultSummaryType(
  dataType: string,
  catalogSummaryType?: PivotSummaryType
): PivotSummaryType | undefined {
  if (catalogSummaryType) {
    return catalogSummaryType;
  }

  if (NUMERIC_TYPES.has(dataType)) {
    return 'sum';
  }

  return 'count';
}

export type MapCatalogToPivotFieldsOptions = {
  /** Locale i18n activo (p. ej. `i18n.language`). Default `es`. */
  locale?: string;
};

/**
 * Mapper catálogo → fields PivotGrid. Default numérico `sum` (CA-13).
 * Fechas `date`/`datetime`: `shortDate` + texto vía locale (regla BASE 32).
 */
export function mapCatalogToPivotFields(
  catalog: PivotConsultaCatalogDto,
  resolveCaption?: (campo: PivotCampoCatalogDto) => string,
  options: MapCatalogToPivotFieldsOptions = {}
): PivotFieldConfig[] {
  const locale = options.locale ?? 'es';
  const mapped = catalog.campos.map((campo) => {
    const dataType = campo.dataType;
    const summaryType = resolveDefaultSummaryType(dataType, campo.summaryType);
    const allowedSummaryTypes = resolveAllowedSummaryTypes(
      dataType,
      campo.allowedSummaryTypes
    );

    const field: PivotFieldConfig = {
      dataField: campo.dataField,
      dataType,
      caption: resolveCaption?.(campo) ?? campo.nombreVisible,
      allowedSummaryTypes,
    };

    if (campo.area) {
      field.area = campo.area;
    }

    if (DATE_TYPES.has(dataType)) {
      field.groupInterval = campo.groupInterval ?? 'day';
    }

    if (NUMERIC_TYPES.has(dataType) || summaryType === 'sum' || summaryType === 'avg') {
      field.summaryType = summaryType;
      if (!field.area) {
        field.area = 'data';
      }
    } else if (summaryType) {
      field.summaryType = summaryType;
    }

    return field;
  });

  return enrichPivotFieldsWithNumericFormat(
    enrichPivotFieldsWithDateFormat(mapped, locale),
    locale
  );
}
