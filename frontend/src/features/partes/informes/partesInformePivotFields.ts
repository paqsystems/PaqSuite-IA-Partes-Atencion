import type { TFunction } from 'i18next'
import { enrichPivotFieldsWithDateFormat } from '@paqsuite/react-core'
import { formatMinutosAsHhMm } from '../carga/partesTareaDuration'

function formatDuracionPivot(cellInfo: { value?: unknown }) {
  return formatMinutosAsHhMm(Number(cellInfo.value ?? 0))
}

/**
 * Campos del Pivot de consulta detallada.
 * `retrieveFields: false` evita captions auto (camelCase → inglés) desde el store.
 * Fechas: enriquecidas con locale i18n (regla BASE 32).
 */
export function buildConsultaDetalladaPivotFields(t: TFunction, locale = 'es') {
  return enrichPivotFieldsWithDateFormat(
    [
      {
        dataField: 'fecha',
        caption: t('partes.informe.field.fecha'),
        dataType: 'date' as const,
      },
      {
        dataField: 'diaSemana',
        caption: t('partes.informe.field.diaSemana'),
      },
      {
        dataField: 'usuarioCode',
        caption: t('partes.informe.field.usuarioCode'),
      },
      {
        dataField: 'usuarioNombre',
        caption: t('partes.informe.field.usuarioNombre'),
      },
      {
        dataField: 'clienteCode',
        caption: t('partes.informe.field.clienteCode'),
        area: 'row' as const,
      },
      {
        dataField: 'clienteNombre',
        caption: t('partes.informe.field.clienteNombre'),
      },
      {
        dataField: 'tipoTareaCode',
        caption: t('partes.informe.field.tipoTareaCode'),
        area: 'column' as const,
      },
      {
        dataField: 'tipoTareaDescripcion',
        caption: t('partes.informe.field.tipoTareaDescripcion'),
      },
      {
        dataField: 'duracionMinutos',
        caption: t('partes.informe.field.duracion'),
        dataType: 'number' as const,
        area: 'data' as const,
        summaryType: 'sum' as const,
        customizeText: formatDuracionPivot,
      },
      {
        dataField: 'observacion',
        caption: t('partes.informe.field.observacion'),
      },
      {
        dataField: 'cerrado',
        caption: t('partes.informe.field.cerrado'),
        dataType: 'boolean' as const,
      },
      {
        dataField: 'sinCargo',
        caption: t('partes.informe.field.sinCargo'),
        dataType: 'boolean' as const,
      },
      {
        dataField: 'presencial',
        caption: t('partes.informe.field.presencial'),
        dataType: 'boolean' as const,
      },
    ],
    locale
  )
}

/** Campos del Pivot de consultas agrupadas. */
export function buildConsultaAgrupadaPivotFields(t: TFunction) {
  return [
    {
      dataField: 'ejeCodigo',
      caption: t('partes.informe.field.ejeCodigo'),
      area: 'row' as const,
    },
    {
      dataField: 'ejeDescripcion',
      caption: t('partes.informe.field.ejeDescripcion'),
    },
    {
      dataField: 'diaSemana',
      caption: t('partes.informe.field.diaSemana'),
    },
    {
      dataField: 'totalMinutos',
      caption: t('partes.informe.field.duracion'),
      dataType: 'number' as const,
      area: 'data' as const,
      summaryType: 'sum' as const,
      customizeText: formatDuracionPivot,
    },
    {
      dataField: 'cantidadTareas',
      caption: t('partes.informe.field.cantidadTareas'),
      dataType: 'number' as const,
      area: 'data' as const,
      summaryType: 'sum' as const,
    },
    {
      dataField: 'cantidadSinCargo',
      caption: t('partes.informe.field.sinCargo'),
      dataType: 'number' as const,
      summaryType: 'sum' as const,
    },
    {
      dataField: 'cantidadPresencial',
      caption: t('partes.informe.field.presencial'),
      dataType: 'number' as const,
      summaryType: 'sum' as const,
    },
  ]
}
