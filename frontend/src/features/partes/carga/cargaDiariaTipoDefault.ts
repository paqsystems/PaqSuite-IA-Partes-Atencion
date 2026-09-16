/** Resuelve el tipo de tarea marcado como default en un catálogo usable. */

export function resolveDefaultTipoId(items: Record<string, unknown>[] | undefined): number | null {
  if (!items || items.length === 0) {
    return null
  }
  const defaultTipo = items.find((item) => Boolean(item.isDefault) || Boolean(item.is_default))
  if (!defaultTipo || defaultTipo.id == null || defaultTipo.id === '') {
    return null
  }
  const id = Number(defaultTipo.id)
  return Number.isFinite(id) ? id : null
}
