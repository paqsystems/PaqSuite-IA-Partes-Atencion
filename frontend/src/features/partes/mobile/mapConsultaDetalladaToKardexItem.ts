import type { KardexItem } from '@paqsuite/react-core'
import { formatMinutosAsHhMm } from '../carga/partesTareaDuration'

export function mapConsultaDetalladaToKardexItem(
  row: Record<string, unknown>,
  t: (key: string) => string,
): KardexItem {
  const clienteCode = String(row.clienteCode ?? '')
  const clienteNombre = String(row.clienteNombre ?? '')
  const title =
    clienteNombre && clienteCode
      ? `${clienteCode} — ${clienteNombre}`
      : clienteCode || clienteNombre || String(row.id ?? '')

  const duracionMinutos = Number(row.duracionMinutos ?? 0)
  const fecha = String(row.fecha ?? '').slice(0, 10)

  return {
    id: String(row.id ?? `${clienteCode}-${fecha}-${duracionMinutos}`),
    title,
    subtitle: formatMinutosAsHhMm(duracionMinutos),
    fields: [
      {
        label: t('partes.informe.field.tipoTareaDescripcion'),
        value: String(row.tipoTareaDescripcion ?? row.tipoTareaCode ?? ''),
      },
      { label: t('partes.informe.field.fecha'), value: fecha },
      { label: t('partes.tarea.duracion'), value: formatMinutosAsHhMm(duracionMinutos) },
      { label: t('partes.informe.field.observacion'), value: String(row.observacion ?? '') },
      {
        label: t('partes.informe.field.cerrado'),
        value: row.cerrado ? t('partes.common.si') : t('partes.common.no'),
      },
    ],
    status: {
      text: row.cerrado ? t('partes.mobile.cerrada') : t('partes.mobile.abierta'),
      tone: row.cerrado ? 'success' : 'neutral',
    },
  }
}