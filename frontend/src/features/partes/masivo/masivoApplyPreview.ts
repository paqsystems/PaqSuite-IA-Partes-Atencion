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

export function buildMasivoApplyPreview(input: {
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
}): MasivoApplyPreview {
  const tipoLabel =
    input.applyTipoTareaId == null
      ? '(sin cambio)'
      : (() => {
          const tipo = input.tiposTarea.find((row) => Number(row.id) === input.applyTipoTareaId)
          return tipo
            ? `${String(tipo.code ?? '')} — ${String(tipo.descripcion ?? '')}`
            : String(input.applyTipoTareaId)
        })()

  const asistenteLabel =
    input.applyUsuarioId == null
      ? '(sin cambio)'
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
      { label: 'Partes', value: String(input.itemCount) },
      { label: 'Tipo de tarea', value: tipoLabel },
      {
        label: 'Sin cargo',
        value: input.touchSinCargo ? (input.applySinCargo ? 'Sí' : 'No') : '(sin cambio)',
      },
      {
        label: 'Presencial',
        value: input.touchPresencial ? (input.applyPresencial ? 'Sí' : 'No') : '(sin cambio)',
      },
      { label: 'Asistente', value: asistenteLabel },
      {
        label: 'Fecha',
        value: input.touchFecha && input.applyFecha ? input.applyFecha : '(sin cambio)',
      },
      { label: 'Rango filtro', value: `${input.fechaDesde} → ${input.fechaHasta}` },
    ],
    muestra,
  }
}
