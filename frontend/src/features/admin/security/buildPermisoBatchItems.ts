export type PermisoBulkMode = 'byUser' | 'byRole' | 'byCompany'

export type PermisoBatchItem = {
  userId: number
  empresaId: number
  rolId: number
}

export type PermisoBulkSelections = {
  userIds?: number[]
  rolIds?: number[]
  empresaIds?: number[]
}

/**
 * Cartesiano FE para POST /admin/permisos/batch (TR-GEN-06-permisos).
 * `empresaIdMono` se usa cuando hay una sola empresa (sin grilla de empresas).
 */
export function buildPermisoBatchItems(
  mode: PermisoBulkMode,
  anchorId: number,
  selections: PermisoBulkSelections,
  empresaIdMono?: number
): PermisoBatchItem[] {
  const empresaIds =
    selections.empresaIds && selections.empresaIds.length > 0
      ? selections.empresaIds
      : empresaIdMono !== undefined
        ? [empresaIdMono]
        : []

  if (mode === 'byUser') {
    const rolIds = selections.rolIds ?? []
    if (anchorId <= 0 || rolIds.length === 0 || empresaIds.length === 0) {
      return []
    }
    return empresaIds.flatMap((empresaId) =>
      rolIds.map((rolId) => ({ userId: anchorId, empresaId, rolId }))
    )
  }

  if (mode === 'byRole') {
    const userIds = selections.userIds ?? []
    if (anchorId <= 0 || userIds.length === 0 || empresaIds.length === 0) {
      return []
    }
    return empresaIds.flatMap((empresaId) =>
      userIds.map((userId) => ({ userId, empresaId, rolId: anchorId }))
    )
  }

  const userIds = selections.userIds ?? []
  const rolIds = selections.rolIds ?? []
  if (anchorId <= 0 || userIds.length === 0 || rolIds.length === 0) {
    return []
  }
  return userIds.flatMap((userId) =>
    rolIds.map((rolId) => ({ userId, empresaId: anchorId, rolId }))
  )
}
