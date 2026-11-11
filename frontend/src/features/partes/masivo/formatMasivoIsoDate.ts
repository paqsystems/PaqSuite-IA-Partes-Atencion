import { resolvePartesDateDisplayFormat } from '../carga/partesTareaDuration'

/** Presenta YYYY-MM-DD según locale i18n (regla 35: separadores visibles). */
export function formatMasivoIsoDate(iso: string, locale?: string | null): string {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso.trim())
  if (!match) {
    return iso
  }
  const [, year, month, day] = match
  if (resolvePartesDateDisplayFormat(locale) === 'MM/dd/yyyy') {
    return `${month}/${day}/${year}`
  }

  return `${day}/${month}/${year}`
}
