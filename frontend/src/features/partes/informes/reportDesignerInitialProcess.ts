/**
 * Mapea `?processCode=` a preselección GEN (`initialProcessCode`).
 * Solo preselecciona; no confirma el proceso (C1-15-38 / TR-011 RN-TR-12).
 */
export function readInitialProcessCodeFromSearch(
  search: string | URLSearchParams,
): string | undefined {
  const params = typeof search === 'string' ? new URLSearchParams(search) : search
  const raw = params.get('processCode')?.trim()
  return raw || undefined
}
