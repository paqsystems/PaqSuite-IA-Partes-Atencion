/**
 * Prompt-to-Report DX (camino A) — desactivado en Partes oleada 2 B (TR-015-update-b D-08).
 * Se mantiene el export para no romper imports; no habilita AIServices.
 */
export function enableReportDesignerAiServices(): void {
  const settings = (
    globalThis as typeof globalThis & {
      DevExpress?: {
        Reporting?: {
          Designer?: {
            Settings?: {
              AIServicesEnabled?: (enabled: boolean) => void
            }
          }
        }
      }
    }
  ).DevExpress?.Reporting?.Designer?.Settings

  // L-B-05 / D-08: forzar off si la API existe.
  settings?.AIServicesEnabled?.(false)
}
