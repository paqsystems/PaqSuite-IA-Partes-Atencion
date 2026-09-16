/**
 * DX 26.1 usa clave LCP. El JWT eyJ de 25.2 y placeholders de Vite/Vercel
 * no licencian y disparan el banner de evaluación.
 */
export function parseDevExtremeLicenseKey(
  candidates: Array<string | undefined | null>,
): string {
  for (const raw of candidates) {
    const key = String(raw ?? '')
      .trim()
      .replace(/^['"]|['"]$/g, '')
      .replace(/[\r\n\t]/g, '')
    if (!key || key === 'undefined' || key === 'null') {
      continue
    }
    if (key.startsWith('eyJ')) {
      continue
    }
    if (!key.startsWith('LCP')) {
      continue
    }
    return key
  }
  return ''
}
