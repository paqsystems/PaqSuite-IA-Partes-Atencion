import { formatDate, formatDecimalNumber } from '../../i18n/formatters';
import type { PivotCampoCatalogDto, PivotConsultaCatalogDto, PivotSummaryType } from './pivotCatalogClient';

export type PivotFieldConfig = {
  dataField: string;
  /** Distingue campos calculados (año/mes) que reutilizan el mismo `dataField`. */
  name?: string;
  dataType?: string;
  caption: string;
  summaryType?: PivotSummaryType;
  area?: 'row' | 'column' | 'data' | 'filter';
  allowedSummaryTypes?: PivotSummaryType[];
  /** DevExtreme format (p. ej. shortDate). */
  format?: string;
  customizeText?: (cellInfo: { value?: unknown }) => string;
  /** Normaliza el valor de fila (p. ej. ISO fecha → día calendario local). */
  selector?: (data: Record<string, unknown>) => unknown;
};

const NUMERIC_TYPES = new Set(['number', 'numeric']);
const DATE_TYPES = new Set(['date', 'datetime']);

export function isPivotDateDataType(dataType: string | undefined): boolean {
  return Boolean(dataType && DATE_TYPES.has(dataType));
}

/**
 * Fecha calendario local. ISO `YYYY-MM-DD` no se parsea como UTC (evita correr el día en UTC-3).
 * Números chicos (día del mes / año de groupInterval DX) no se tratan como timestamp.
 */
export function toLocalCalendarDate(value: unknown): Date | null {
  if (value == null || value === '') {
    return null;
  }
  if (value instanceof Date) {
    if (Number.isNaN(value.getTime())) {
      return null;
    }
    return new Date(value.getFullYear(), value.getMonth(), value.getDate());
  }
  if (typeof value === 'number' && Number.isFinite(value)) {
    if (value < 1e11) {
      return null;
    }
    const fromTs = new Date(value);
    if (Number.isNaN(fromTs.getTime())) {
      return null;
    }
    return new Date(fromTs.getFullYear(), fromTs.getMonth(), fromTs.getDate());
  }
  const raw = String(value).trim();
  const isoDate = /^(\d{4})-(\d{2})-(\d{2})/.exec(raw);
  if (isoDate) {
    return new Date(Number(isoDate[1]), Number(isoDate[2]) - 1, Number(isoDate[3]));
  }
  const parsed = new Date(raw);
  if (Number.isNaN(parsed.getTime())) {
    return null;
  }
  return new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate());
}

export function toIsoDateOnly(value: unknown): string | null {
  const local = toLocalCalendarDate(value);
  if (!local) {
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value.trim())) {
      return value.trim();
    }
    return null;
  }
  const year = String(local.getFullYear()).padStart(4, '0');
  const month = String(local.getMonth() + 1).padStart(2, '0');
  const day = String(local.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/**
 * Texto visible de fecha en Pivot según locale i18n activo (GEN-02 / regla BASE 32).
 * Día/mes/año vía `Intl.DateTimeFormat(locale)`.
 */
export function formatPivotDateCellText(
  value: unknown,
  locale: string
): string {
  const local = toLocalCalendarDate(value);
  if (!local) {
    return value == null || value === '' ? '' : String(value);
  }
  return new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(local);
}

const dateGroupSuffix: Record<string, { year: string; month: string }> = {
  es: { year: 'año', month: 'mes' },
  en: { year: 'year', month: 'month' },
  pt: { year: 'ano', month: 'mês' },
  fr: { year: 'année', month: 'mois' },
  it: { year: 'anno', month: 'mese' },
};

function dateGroupCaption(caption: string, kind: 'year' | 'month', locale: string): string {
  const lang = locale.slice(0, 2).toLowerCase();
  const suffix = (dateGroupSuffix[lang] ?? dateGroupSuffix.es)[kind];
  return `${caption} (${suffix})`;
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
 * Fechas: un valor por día calendario (string ISO local). No usar `groupInterval: 'day'`
 * (en DX es día del mes 1–31 y `new Date(n)` cae en 31/12/1969). Año/mes van como
 * campos extra en el Field Chooser, sin área inicial.
 */
export function mapCatalogToPivotFields(
  catalog: PivotConsultaCatalogDto,
  resolveCaption?: (campo: PivotCampoCatalogDto) => string,
  options: MapCatalogToPivotFieldsOptions = {}
): PivotFieldConfig[] {
  const locale = options.locale ?? 'es';
  const mapped = catalog.campos.flatMap((campo) => {
    const dataType = campo.dataType;
    const summaryType = resolveDefaultSummaryType(dataType, campo.summaryType);
    const allowedSummaryTypes = resolveAllowedSummaryTypes(
      dataType,
      campo.allowedSummaryTypes
    );
    const caption = resolveCaption?.(campo) ?? campo.nombreVisible;
    const dataField = campo.dataField;

    if (DATE_TYPES.has(dataType)) {
      const calendarDay: PivotFieldConfig = {
        dataField,
        name: dataField,
        dataType: 'string',
        caption,
        allowedSummaryTypes: ['count'],
        selector: (row) => toIsoDateOnly(row[dataField]),
        customizeText: (cellInfo) => formatPivotDateCellText(cellInfo.value, locale),
      };
      if (campo.area) {
        calendarDay.area = campo.area;
      }
      const yearField: PivotFieldConfig = {
        dataField,
        name: `${dataField}__year`,
        dataType: 'number',
        caption: dateGroupCaption(caption, 'year', locale),
        allowedSummaryTypes: ['count', 'min', 'max'],
        selector: (row) => toLocalCalendarDate(row[dataField])?.getFullYear() ?? null,
        customizeText: (cellInfo) =>
          cellInfo.value == null || cellInfo.value === '' ? '' : String(cellInfo.value),
      };
      const monthField: PivotFieldConfig = {
        dataField,
        name: `${dataField}__month`,
        dataType: 'number',
        caption: dateGroupCaption(caption, 'month', locale),
        allowedSummaryTypes: ['count', 'min', 'max'],
        selector: (row) => {
          const local = toLocalCalendarDate(row[dataField]);
          return local ? local.getMonth() + 1 : null;
        },
        customizeText: (cellInfo) =>
          cellInfo.value == null || cellInfo.value === '' ? '' : String(cellInfo.value),
      };
      return [calendarDay, yearField, monthField];
    }

    const field: PivotFieldConfig = {
      dataField,
      dataType,
      caption,
      allowedSummaryTypes,
    };

    if (campo.area) {
      field.area = campo.area;
    }

    if (NUMERIC_TYPES.has(dataType) || summaryType === 'sum' || summaryType === 'avg') {
      field.summaryType = summaryType;
      if (!field.area) {
        field.area = 'data';
      }
    } else if (summaryType) {
      field.summaryType = summaryType;
    }

    return [field];
  });

  return enrichPivotFieldsWithNumericFormat(
    enrichPivotFieldsWithDateFormat(mapped, locale),
    locale
  );
}
