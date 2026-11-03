import { devExtremeFilterToContains } from '../../../shared/grid/devExtremeFilterContains'

export type MasivoGridLoadOptions = {
  skip?: number
  take?: number
  filter?: unknown
}

export function masivoGridLoadOptionsToQuery(loadOptions: MasivoGridLoadOptions): {
  page: number
  pageSize: number
  clienteCode?: string
  clienteNombre?: string
  tipoTareaCode?: string
  tipoTareaDescripcion?: string
} {
  const pageSize = loadOptions.take != null && loadOptions.take > 0 ? loadOptions.take : 20
  const skip = loadOptions.skip ?? 0
  const page = Math.floor(skip / pageSize) + 1
  const contains = devExtremeFilterToContains(loadOptions.filter)
  return {
    page,
    pageSize,
    ...(contains.clienteCode ? { clienteCode: contains.clienteCode } : {}),
    ...(contains.clienteNombre ? { clienteNombre: contains.clienteNombre } : {}),
    ...(contains.tipoTareaCode ? { tipoTareaCode: contains.tipoTareaCode } : {}),
    ...(contains.tipoTareaDescripcion ? { tipoTareaDescripcion: contains.tipoTareaDescripcion } : {}),
  }
}
