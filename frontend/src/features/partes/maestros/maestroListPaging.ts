import { devExtremeFilterToContains } from '../../../shared/grid/devExtremeFilterContains'

export type MaestroGridLoadOptions = {
  skip?: number
  take?: number
  filter?: unknown
}

export function maestroGridLoadOptionsToQuery(loadOptions: MaestroGridLoadOptions): {
  page: number
  pageSize: number
  code?: string
  nombre?: string
  descripcion?: string
  usuario?: string
  email?: string
} {
  const pageSize = loadOptions.take != null && loadOptions.take > 0 ? loadOptions.take : 20
  const skip = loadOptions.skip ?? 0
  const page = Math.floor(skip / pageSize) + 1
  const contains = devExtremeFilterToContains(loadOptions.filter)
  const usuarioFilter = contains.usuarioCodigo ?? contains.usuario
  return {
    page,
    pageSize,
    ...(contains.code ? { code: contains.code } : {}),
    ...(contains.nombre ? { nombre: contains.nombre } : {}),
    ...(contains.descripcion ? { descripcion: contains.descripcion } : {}),
    ...(usuarioFilter ? { usuario: usuarioFilter } : {}),
    ...(contains.email ? { email: contains.email } : {}),
  }
}
