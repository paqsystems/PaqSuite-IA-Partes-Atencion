import type { TareaIdItem } from './partesMasivoApi'
import type { PartesTareaItem } from '../carga/partesTareaApi'

export type MasivoSelectionEvent = {
  selectedRowKeys?: Array<string | number>
  selectedRowsData?: PartesTareaItem[]
  currentSelectedRowKeys?: Array<string | number>
  currentDeselectedRowKeys?: Array<string | number>
  component?: {
    selectRows?: (keys: Array<number | string>, preserve: boolean) => void
  }
}

export type MasivoSelectionState = {
  keys: number[]
  map: Record<number, TareaIdItem>
}

function toNumberKeys(keys: Array<string | number> | undefined): number[] {
  return (keys ?? []).map((key) => Number(key)).filter((id) => Number.isFinite(id) && id > 0)
}

/**
 * Ignora el vaciado espurio de DX/GEN (remount, apply layout via `instance.state()`).
 * `SelectionChangedEvent` no trae `event` de usuario; el destilde real se marca
 * con `userIntent` (pointerdown en `.dx-command-select`).
 */
export function isSpuriousMasivoClear(
  prevKeys: number[],
  e: MasivoSelectionEvent,
  userIntent = false
): boolean {
  const nextKeys = toNumberKeys(e.selectedRowKeys)
  if (nextKeys.length !== 0 || prevKeys.length === 0) {
    return false
  }
  if (userIntent) {
    return false
  }
  return true
}

function toTareaIdItem(row: PartesTareaItem): TareaIdItem {
  return {
    id: row.id,
    rowVersion: row.rowVersion,
    fecha: String(row.fecha).slice(0, 10),
    usuarioCode: row.usuarioCode,
  }
}

/**
 * Fusiona la selección de la página visible con claves off-page
 * (p. ej. «seleccionar todos del filtro»).
 */
export function reduceMasivoSelection(
  prev: MasivoSelectionState,
  e: MasivoSelectionEvent,
  currentPageIds: number[],
  userIntent = false
): MasivoSelectionState {
  if (isSpuriousMasivoClear(prev.keys, e, userIntent)) {
    return prev
  }

  const nextFromGrid = toNumberKeys(e.selectedRowKeys)
  const deselected = toNumberKeys(e.currentDeselectedRowKeys)
  const pageIds = new Set(currentPageIds)
  const offPage = prev.keys.filter((id) => !pageIds.has(id) && !deselected.includes(id))
  const onPage = nextFromGrid.filter((id) => pageIds.has(id))
  const keys: number[] = []
  const seen = new Set<number>()
  for (const id of [...offPage, ...onPage]) {
    if (seen.has(id)) {
      continue
    }
    seen.add(id)
    keys.push(id)
  }

  const map: Record<number, TareaIdItem> = {}
  for (const id of keys) {
    if (prev.map[id]) {
      map[id] = prev.map[id]
    }
  }
  ;(e.selectedRowsData ?? []).forEach((row) => {
    map[row.id] = toTareaIdItem(row)
  })

  return { keys, map }
}
