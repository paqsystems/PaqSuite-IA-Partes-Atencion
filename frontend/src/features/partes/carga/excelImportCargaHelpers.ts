export type ImportCompleteLike = {
  status: string
  processedRows: number
}

/** Regla TR-009 CA-10 / queued: refresh solo si hubo altas. */
export function shouldRefreshCargaAfterImport(payload: ImportCompleteLike): boolean {
  return (
    (payload.status === 'done' || payload.status === 'partial') &&
    payload.processedRows > 0
  )
}
