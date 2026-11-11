export type DevExtremeEqualsValue = string | number | boolean | null

export type ParsedDevExtremeFilter = {
  contains: Record<string, string>
  equals: Record<string, DevExtremeEqualsValue[]>
}

function normalizeEqualsValue(raw: unknown): DevExtremeEqualsValue | undefined {
  if (raw === null || raw === undefined) {
    return null
  }
  if (typeof raw === 'boolean' || typeof raw === 'number') {
    return raw
  }
  if (raw instanceof Date && !Number.isNaN(raw.getTime())) {
    return raw.toISOString().slice(0, 10)
  }
  if (typeof raw === 'string') {
    const trimmed = raw.trim()
    return trimmed === '' ? null : trimmed
  }
  return undefined
}

function pushEquals(out: Record<string, DevExtremeEqualsValue[]>, field: string, raw: unknown) {
  const value = normalizeEqualsValue(raw)
  if (value === undefined) {
    return
  }
  const current = out[field] ?? []
  if (!current.some((item) => item === value)) {
    current.push(value)
  }
  out[field] = current
}

/**
 * Extrae contains/startswith (FilterRow) y equals/anyof (HeaderFilter) del árbol filter DX.
 */
export function parseDevExtremeFilter(filter: unknown): ParsedDevExtremeFilter {
  const contains: Record<string, string> = {}
  const equals: Record<string, DevExtremeEqualsValue[]> = {}

  function walk(node: unknown): void {
    if (node == null) {
      return
    }
    if (!Array.isArray(node)) {
      return
    }
    if (node.length >= 3 && typeof node[0] === 'string' && typeof node[1] === 'string') {
      const field = node[0]
      const op = String(node[1]).toLowerCase()
      const value = node[2]
      if (
        (op === 'contains' || op === 'startswith') &&
        (typeof value === 'string' || typeof value === 'number')
      ) {
        const text = String(value).trim()
        if (text !== '') {
          contains[field] = text
        }
        return
      }
      if (op === '=' || op === '==' || op === 'equal' || op === 'equals') {
        pushEquals(equals, field, value)
        return
      }
      if (op === 'anyof' && Array.isArray(value)) {
        for (const item of value) {
          pushEquals(equals, field, item)
        }
        return
      }
    }
    for (const part of node) {
      if (part !== 'and' && part !== 'or') {
        walk(part)
      }
    }
  }

  walk(filter)
  return { contains, equals }
}

/**
 * Extrae filtros "contains" / "startswith" simples del árbol filter de DevExtreme DataGrid.
 */
export function devExtremeFilterToContains(filter: unknown): Record<string, string> {
  return parseDevExtremeFilter(filter).contains
}
