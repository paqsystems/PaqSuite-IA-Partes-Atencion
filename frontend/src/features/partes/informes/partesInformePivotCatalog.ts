import type {
  PivotCampoCatalogDto,
  PivotCampoDataType,
  PivotConsultaCatalogDto,
  PivotSummaryType,
} from '@paqsuite/react-core'
import type { TFunction } from 'i18next'
import {
  buildConsultaAgrupadaPivotFields,
  buildConsultaDetalladaPivotFields,
  buildPaqueteHorasPivotFields,
} from './partesInformePivotFields'

const SUMMARY_TYPES = new Set<PivotSummaryType>(['sum', 'avg', 'min', 'max', 'count'])

type PivotFieldLike = {
  dataField: string
  caption?: string
  dataType?: string
  summaryType?: string
}

function normalizeDataType(value?: string): PivotCampoDataType {
  const raw = String(value ?? 'string').toLowerCase()
  if (raw === 'number' || raw === 'numeric' || raw === 'decimal' || raw === 'int') {
    return 'number'
  }
  if (raw === 'date') {
    return 'date'
  }
  if (raw === 'datetime' || raw === 'timestamp') {
    return 'datetime'
  }
  if (raw === 'boolean' || raw === 'bool') {
    return 'boolean'
  }
  return 'string'
}

function mapPivotFieldToCatalog(field: PivotFieldLike): PivotCampoCatalogDto {
  const summaryRaw = String(field.summaryType ?? '').toLowerCase()
  const summaryType = SUMMARY_TYPES.has(summaryRaw as PivotSummaryType)
    ? (summaryRaw as PivotSummaryType)
    : undefined

  return {
    dataField: field.dataField,
    dataType: normalizeDataType(field.dataType),
    nombreVisible: field.caption ?? field.dataField,
    summaryType,
  }
}

function buildCatalog(
  consultaId: string,
  fields: PivotFieldLike[],
): PivotConsultaCatalogDto {
  return {
    consultaId,
    mostrarGrillaYPivot: true,
    soloPivot: false,
    campos: fields.map(mapPivotFieldToCatalog),
  }
}

export function buildPartesConsultaDetalladaPivotCatalog(
  t: TFunction,
  locale: string,
): PivotConsultaCatalogDto {
  return buildCatalog(
    'partes.consultaDetallada',
    buildConsultaDetalladaPivotFields(t, locale),
  )
}

export function buildPartesConsultasAgrupadasPivotCatalog(t: TFunction): PivotConsultaCatalogDto {
  return buildCatalog('partes.consultasAgrupadas', buildConsultaAgrupadaPivotFields(t))
}

export function buildPartesPaqueteHorasPivotCatalog(
  t: TFunction,
  locale: string,
): PivotConsultaCatalogDto {
  return buildCatalog(
    'partes.informes.paqueteHoras',
    buildPaqueteHorasPivotFields(t, locale),
  )
}
