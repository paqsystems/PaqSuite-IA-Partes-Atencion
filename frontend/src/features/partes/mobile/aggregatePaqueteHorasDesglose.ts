export type PaqueteHorasDesgloseItem = {
  id: string
  title: string
  totalMinutos: number
  cantidad: number
}

function signedMinutos(esTarea: boolean, duracionMinutos: number): number {
  return esTarea ? duracionMinutos : -duracionMinutos
}

export function aggregatePaqueteHorasDesglose(
  rows: Record<string, unknown>[],
  eje: 'cliente' | 'tipo',
): PaqueteHorasDesgloseItem[] {
  const map = new Map<string, PaqueteHorasDesgloseItem>()
  for (const row of rows) {
    if (row.esSaldoInicial) {
      continue
    }
    const code =
      eje === 'cliente' ? String(row.clienteCode ?? '') : String(row.tipoTareaCode ?? '')
    const nombre =
      eje === 'cliente'
        ? String(row.clienteNombre ?? '')
        : String(row.tipoTareaDescripcion ?? '')
    const key = code || String(row.clienteId ?? row.tipoTareaId ?? '')
    if (!key) {
      continue
    }
    const esTarea = row.esTarea !== false
    const minutos = signedMinutos(Boolean(esTarea), Number(row.duracionMinutos ?? 0))
    const current = map.get(key) ?? {
      id: key,
      title: nombre ? `${code} — ${nombre}` : code,
      totalMinutos: 0,
      cantidad: 0,
    }
    current.totalMinutos += minutos
    if (esTarea) {
      current.cantidad += 1
    }
    map.set(key, current)
  }
  return [...map.values()].sort((a, b) => b.totalMinutos - a.totalMinutos)
}
