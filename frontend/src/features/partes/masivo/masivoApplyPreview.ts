import type { TFunction } from 'i18next'
import type { TareaIdItem } from './partesMasivoApi'

export type MasivoApplyPreviewRow = {
  label: string
  value: string
}

export type MasivoApplyPreview = {
  rows: MasivoApplyPreviewRow[]
  muestra: string[]
}

type CatalogRow = Record<string, unknown>

export function buildMasivoApplyPreview(
  input: {
    itemCount: number
    applyTipoTareaId: number | null
    tiposTarea: CatalogRow[]
    touchSinCargo: boolean
    applySinCargo: boolean
    touchPresencial: boolean
    applyPresencial: boolean
    applyUsuarioId: number | null
    asistentes: CatalogRow[]
    touchFecha: boolean
    applyFecha: string
    fechaDesde: string
    fechaHasta: string
    items: TareaIdItem[]
  },
  t: TFunction,
): MasivoApplyPreview {
  const sinCambio = t('partes.common.sinCambio')
  const si = t('partes.common.si')
  const no = t('partes.common.no')

  const tipoLabel =
    input.applyTipoTareaId == null
      ? sinCambio
      : (() => {
          const tipo = input.tiposTarea.find((row) => Number(row.id) === input.applyTipoTareaId)
          return tipo
            ? `${String(tipo.code ?? '')} — ${String(tipo.descripcion ?? '')}`
            : String(input.applyTipoTareaId)
        })()

  const asistenteLabel =
    input.applyUsuarioId == null
      ? sinCambio
      : (() => {
          const asistente = input.asistentes.find((row) => Number(row.id) === input.applyUsuarioId)
          return asistente
            ? `${String(asistente.code ?? '')} — ${String(asistente.nombre ?? '')}`
            : String(input.applyUsuarioId)
        })()

  const muestra = input.items
    .slice(0, 5)
    .map((item) => `#${item.id} ${item.fecha ?? ''} ${item.usuarioCode ?? ''}`.trim())

  return {
    rows: [
      { label: t('partes.masivo.preview.partes'), value: String(input.itemCount) },
      { label: t('partes.informe.filtro.tipoTarea'), value: tipoLabel },
      {
        label: t('partes.informe.field.sinCargo'),
        value: input.touchSinCargo ? (input.applySinCargo ? si : no) : sinCambio,
      },
      {
        label: t('partes.informe.field.presencial'),
        value: input.touchPresencial ? (input.applyPresencial ? si : no) : sinCambio,
      },
      { label: t('partes.informe.filtro.asistente'), value: asistenteLabel },
      {
        label: t('partes.informe.field.fecha'),
        value: input.touchFecha && input.applyFecha ? input.applyFecha : sinCambio,
      },
      {
        label: t('partes.masivo.preview.rangoFiltro'),
        value: `${input.fechaDesde} → ${input.fechaHasta}`,
      },
    ],
    muestra,
  }
}
