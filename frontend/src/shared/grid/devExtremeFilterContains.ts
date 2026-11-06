/**
 * Extrae filtros "contains" / "startswith" simples del árbol filter de DevExtreme DataGrid.
 */
export function devExtremeFilterToContains(filter: unknown): Record<string, string> {
  const out: Record<string, string> = {}

  function walk(node: unknown): void {
    if (node == null) {
      return
    }
    if (Array.isArray(node)) {
      if (
        node.length >= 3 &&
        typeof node[0] === 'string' &&
        (node[1] === 'contains' || node[1] === 'startswith') &&
        (typeof node[2] === 'string' || typeof node[2] === 'number')
      ) {
        const value = String(node[2]).trim()
        if (value !== '') {
          out[node[0]] = value
        }
        return
      }
      for (const part of node) {
        if (part !== 'and' && part !== 'or') {
          walk(part)
        }
      }
    }
  }

  walk(filter)
  return out
}
