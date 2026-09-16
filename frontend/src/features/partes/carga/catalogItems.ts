/** Normaliza ítems de catálogo Partes para SelectBox (id numérico + isDefault). */

export function normalizeCatalogItems(items: Record<string, unknown>[]): Record<string, unknown>[] {
  return items.map((item) => {
    const idRaw = item.id
    const id = idRaw == null || idRaw === '' ? idRaw : Number(idRaw)
    return {
      ...item,
      id: typeof id === 'number' && Number.isFinite(id) ? id : item.id,
      isDefault: Boolean(item.isDefault ?? item.is_default),
    }
  })
}

export function catalogItemId(item: { id?: unknown } | null | undefined): number | undefined {
  if (item == null || item.id == null || item.id === '') {
    return undefined
  }
  const id = Number(item.id)
  return Number.isFinite(id) ? id : undefined
}

export function isDxUserEvent(e: { event?: unknown }): boolean {
  return Boolean(e.event)
}
