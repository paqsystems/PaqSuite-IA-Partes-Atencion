import {
  parseDevExtremeFilter,
  type DevExtremeEqualsValue,
} from '../../../shared/grid/devExtremeFilterContains'

export type MasivoGridLoadOptions = {
  skip?: number
  take?: number
  filter?: unknown
  sort?: unknown
  group?: unknown
}

export type MasivoGridQuery = {
  page: number
  pageSize: number
  clienteCode?: string
  clienteNombre?: string
  tipoTareaCode?: string
  tipoTareaDescripcion?: string
  sortField?: string
  sortOrder?: 'asc' | 'desc'
  distinctField?: string
  eq?: Record<string, DevExtremeEqualsValue[]>
}

const BOOLEAN_FIELDS = new Set(['sinCargo', 'presencial', 'cerrado'])

function firstDescriptor(value: unknown): { selector?: string; desc?: boolean } | null {
  const item = Array.isArray(value) ? value[0] : value
  if (typeof item === 'string' && item.trim() !== '') {
    return { selector: item }
  }
  if (item && typeof item === 'object') {
    const selector = (item as { selector?: unknown }).selector
    if (typeof selector === 'string' && selector.trim() !== '') {
      return {
        selector,
        desc: Boolean((item as { desc?: unknown }).desc),
      }
    }
  }
  return null
}

export function coerceHeaderFilterKey(field: string, value: unknown): DevExtremeEqualsValue {
  if (value === null || value === undefined || value === '') {
    return null
  }
  if (BOOLEAN_FIELDS.has(field)) {
    return value === true || value === 1 || value === '1' || value === 'true'
  }
  if (field === 'fecha') {
    return String(value).slice(0, 10)
  }
  if (field === 'duracionHoras' || field === 'duracionMinutos') {
    const numeric = Number(value)
    return Number.isFinite(numeric) ? numeric : null
  }
  if (typeof value === 'boolean' || typeof value === 'number') {
    return value
  }
  return String(value)
}

export function masivoGridLoadOptionsToQuery(loadOptions: MasivoGridLoadOptions): MasivoGridQuery {
  const pageSize = loadOptions.take != null && loadOptions.take > 0 ? loadOptions.take : 20
  const skip = loadOptions.skip ?? 0
  const page = Math.floor(skip / pageSize) + 1
  const parsed = parseDevExtremeFilter(loadOptions.filter)
  const contains = parsed.contains
  const sort = firstDescriptor(loadOptions.sort)
  const group = firstDescriptor(loadOptions.group)
  const eq = Object.keys(parsed.equals).length > 0 ? parsed.equals : undefined
  return {
    page,
    pageSize,
    ...(contains.clienteCode ? { clienteCode: contains.clienteCode } : {}),
    ...(contains.clienteNombre ? { clienteNombre: contains.clienteNombre } : {}),
    ...(contains.tipoTareaCode ? { tipoTareaCode: contains.tipoTareaCode } : {}),
    ...(contains.tipoTareaDescripcion ? { tipoTareaDescripcion: contains.tipoTareaDescripcion } : {}),
    ...(sort?.selector ? { sortField: sort.selector, sortOrder: sort.desc ? 'desc' : 'asc' } : {}),
    ...(group?.selector ? { distinctField: group.selector } : {}),
    ...(eq ? { eq } : {}),
  }
}
