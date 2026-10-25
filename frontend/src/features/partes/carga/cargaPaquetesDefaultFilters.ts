/** Primer y último día del mes calendario local (TR-013 AC-02). */
export function defaultPaqueteFiltroMes(): { fechaDesde: string; fechaHasta: string } {
  const now = new Date()
  const y = now.getFullYear()
  const m = now.getMonth()
  const first = new Date(y, m, 1)
  const last = new Date(y, m + 1, 0)
  const pad = (n: number) => String(n).padStart(2, '0')

  return {
    fechaDesde: `${first.getFullYear()}-${pad(first.getMonth() + 1)}-${pad(first.getDate())}`,
    fechaHasta: `${last.getFullYear()}-${pad(last.getMonth() + 1)}-${pad(last.getDate())}`,
  }
}
