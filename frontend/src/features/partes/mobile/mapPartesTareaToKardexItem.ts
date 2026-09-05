import type { KardexItem } from '@paqsuite/react-core'
import type { PartesTareaItem } from '../carga/partesTareaApi'

export function mapPartesTareaToKardexItem(
  row: PartesTareaItem,
  t: (key: string) => string,
): KardexItem {
  const clienteNombre = row.clienteNombre?.trim() ?? ''
  const title = clienteNombre
    ? `${row.clienteCode ?? ''} — ${clienteNombre}`.trim()
    : String(row.clienteCode ?? row.clienteId)

  return {
    id: String(row.id),
    title,
    subtitle: `${row.tipoTareaCode ?? ''} · ${row.duracionMinutos} min`.trim(),
    fields: [
      { label: t('partes.informe.field.observacion'), value: row.observacion ?? '' },
      { label: t('partes.informe.field.fecha'), value: String(row.fecha).slice(0, 10) },
    ],
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
