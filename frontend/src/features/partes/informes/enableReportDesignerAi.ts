/** Asegura servicios IA del Web Report Designer (Prompt-to-Report) cuando el sidecar los registró. */
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

  settings?.AIServicesEnabled?.(true)
}
