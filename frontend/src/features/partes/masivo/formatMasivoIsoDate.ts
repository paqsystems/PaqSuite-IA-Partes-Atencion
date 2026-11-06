/** Presenta YYYY-MM-DD como dd/MM/yyyy (regla 35: separadores visibles). */
export function formatMasivoIsoDate(iso: string): string {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso.trim())
  if (!match) {
    return iso
  }
  return `${match[3]}/${match[2]}/${match[1]}`
}
