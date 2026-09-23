import type { KardexItem } from '@paqsuite/react-core'
import type { PartesTareaItem } from '../carga/partesTareaApi'
import { formatMinutosAsHhMm } from '../carga/partesTareaDuration'

function formatTipoTareaLine(row: PartesTareaItem): string {
  const code = row.tipoTareaCode?.trim() ?? ''
  const descripcion = row.tipoTareaDescripcion?.trim() ?? ''
  if (code && descripcion) {
    return `${code} — ${descripcion}`
  }
  return code || descripcion || String(row.tipoTareaId)
}

function formatAsistenteLine(row: PartesTareaItem): string {
  const code = row.usuarioCode?.trim() ?? ''
  const nombre = row.usuarioNombre?.trim() ?? ''
  if (code && nombre) {
    return `${code} — ${nombre}`
  }
  return nombre || code
}

export function mapPartesTareaToKardexItem(
  row: PartesTareaItem,
  t: (key: string) => string,
  formatMinutos: (minutos: number) => string = formatMinutosAsHhMm,
): KardexItem {
  const clienteNombre = row.clienteNombre?.trim() ?? ''
  const title = clienteNombre
    ? `${row.clienteCode ?? ''} — ${clienteNombre}`.trim()
    : String(row.clienteCode ?? row.clienteId)

  const fields: KardexItem['fields'] = [
    { label: t('partes.informe.field.tipoTareaDescripcion'), value: formatTipoTareaLine(row) },
    { label: t('partes.tarea.duracion'), value: formatMinutos(row.duracionMinutos) },
    { label: t('partes.informe.field.fecha'), value: String(row.fecha).slice(0, 10) },
  ]

  const asistente = formatAsistenteLine(row)
  if (asistente) {
    fields.push({ label: t('partes.informe.filtro.asistente'), value: asistente })
  }

  fields.push(
    { label: t('partes.informe.field.observacion'), value: row.observacion ?? '' },
    {
      label: t('partes.informe.field.sinCargo'),
      value: row.sinCargo ? t('partes.common.si') : t('partes.common.no'),
    },
    {
      label: t('partes.informe.field.presencial'),
      value: row.presencial ? t('partes.common.si') : t('partes.common.no'),
    },
  )

  return {
    id: String(row.id),
    title,
    subtitle: formatMinutos(row.duracionMinutos),
    fields,
    status: {
      text: row.cerrado ? t('partes.mobile.cerrada') : t('partes.mobile.abierta'),
      tone: row.cerrado ? 'success' : 'neutral',
    },
  }
}

export function mapDashboardTopToKardexItem(
  row: { codigo: string; descripcion: string; totalMinutos: number; cantidadTareas: number },
  t: (key: string) => string,
  formatMinutos: (minutos: number) => string,
): KardexItem {
  return {
    id: row.codigo,
    title: `${row.codigo} — ${row.descripcion}`,
    subtitle: formatMinutos(row.totalMinutos),
    fields: [
      { label: t('dashboard.colTareas'), value: String(row.cantidadTareas) },
    ],
  }
}

export function mapDesgloseToKardexItem(
  row: { id: string; title: string; totalMinutos: number; cantidad: number },
  t: (key: string) => string,
  formatMinutos: (minutos: number) => string,
): KardexItem {
  return {
    id: row.id,
    title: row.title,
    subtitle: formatMinutos(row.totalMinutos),
    fields: [{ label: t('dashboard.colTareas'), value: String(row.cantidad) }],
  }
}
