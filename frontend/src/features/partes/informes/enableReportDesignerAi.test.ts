import { describe, expect, it } from 'vitest'
import { enableReportDesignerAiServices } from './enableReportDesignerAi'

describe('enableReportDesignerAiServices', () => {
  it('invoca AIServicesEnabled(false) — camino B desactiva Prompt-to-Report', () => {
    let enabled: boolean | undefined
    ;(
      globalThis as typeof globalThis & {
        DevExpress?: {
          Reporting?: {
            Designer?: { Settings?: { AIServicesEnabled?: (v: boolean) => void } }
          }
        }
      }
    ).DevExpress = {
      Reporting: {
        Designer: {
          Settings: {
            AIServicesEnabled: (v) => {
              enabled = v
            },
          },
        },
      },
    }

    enableReportDesignerAiServices()
    expect(enabled).toBe(false)
  })

  it('no falla si DevExpress no está cargado', () => {
    ;(
      globalThis as typeof globalThis & { DevExpress?: unknown }
    ).DevExpress = undefined
    expect(() => enableReportDesignerAiServices()).not.toThrow()
  })
})
