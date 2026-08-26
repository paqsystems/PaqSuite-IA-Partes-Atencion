import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { EmissionReportDesignerPage, isNativeApp } from '@paqsuite/react-core'
import i18n from '../../../i18n/i18n'
import { ReportDesignerHostPage } from './ReportDesignerHostPage'

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    isNativeApp: vi.fn(() => false),
  }
})

function envelopeOk<T>(resultado: T) {
  return {
    ok: true,
    error: 0,
    respuesta: 'ok',
    resultado,
  }
}

function mockFetchCatalog(processCode = 'partes.informes.consultaDetallada') {
  vi.stubGlobal(
    'fetch',
    vi.fn(async (input: RequestInfo | URL) => {
      const url = String(input)
      if (url.includes('/api/v1/emissions/processes') && !url.includes('/design/')) {
        return new Response(
          JSON.stringify(
            envelopeOk({
              items: [
                {
                  processCode,
                  name: 'Consulta detallada',
                  menuProcessCode: 'partes_consulta_detallada',
                },
              ],
            }),
          ),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        )
      }
      if (url.includes('/design/processes/') && url.includes('/reports')) {
        return new Response(
          JSON.stringify(
            envelopeOk({
              designer: 'stub',
              items: [{ id: 1, code: 'principal', name: 'Principal', isPrincipal: true }],
            }),
          ),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        )
      }
      return new Response(JSON.stringify(envelopeOk({})), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      })
    }),
  )
}

describe('ReportDesignerHostPage — selección de proceso GEN', () => {
  beforeEach(() => {
    vi.mocked(isNativeApp).mockReturnValue(false)
    mockFetchCatalog()
  })

  afterEach(() => {
    cleanup()
    vi.unstubAllGlobals()
    vi.clearAllMocks()
  })

  it('con ?processCode= muestra lista y no monta DX hasta confirmar', async () => {
    render(
      <I18nextProvider i18n={i18n}>
        <MemoryRouter initialEntries={['/emisiones/disenador?processCode=partes.informes.consultaDetallada']}>
          <Routes>
            <Route path="/emisiones/disenador" element={<ReportDesignerHostPage />} />
          </Routes>
        </MemoryRouter>
      </I18nextProvider>,
    )

    await waitFor(() => {
      expect(screen.getByTestId('emission.design.process')).toBeInTheDocument()
    })
    expect(screen.queryByTestId('emission.design.host')).not.toBeInTheDocument()
    expect(screen.queryByTestId('emissions.designer.dxHost')).not.toBeInTheDocument()

    fireEvent.click(screen.getByTestId('emission.design.confirmProcess'))

    await waitFor(() => {
      expect(screen.getByTestId('emission.design.host')).toBeInTheDocument()
    })
  })

  it('N=1: lista visible y DX solo tras confirmar', async () => {
    render(
      <I18nextProvider i18n={i18n}>
        <EmissionReportDesignerPage
          initialProcessCode="partes.informes.consultaDetallada"
          hasDesignPermission
          isNative={false}
          t={(key) => key}
          renderDesigner={() => <div data-testid="emissions.designer.dxHost">dx</div>}
        />
      </I18nextProvider>,
    )

    await waitFor(() => {
      expect(screen.getByTestId('emission.design.process')).toBeInTheDocument()
    })
    expect(screen.getByTestId('emission.design.confirmProcess')).toBeInTheDocument()
    expect(screen.queryByTestId('emission.design.host')).not.toBeInTheDocument()
    expect(screen.queryByTestId('emissions.designer.dxHost')).not.toBeInTheDocument()

    fireEvent.click(screen.getByTestId('emission.design.confirmProcess'))

    await waitFor(() => {
      expect(screen.getByTestId('emission.design.host')).toBeInTheDocument()
      expect(screen.getByTestId('emissions.designer.dxHost')).toBeInTheDocument()
    })
  })

  it('fuente del host no hardcodea processCode fijo', async () => {
    const { readFileSync } = await import('node:fs')
    const { dirname, join } = await import('node:path')
    const { fileURLToPath } = await import('node:url')
    const source = readFileSync(
      join(dirname(fileURLToPath(import.meta.url)), 'ReportDesignerHostPage.tsx'),
      'utf8',
    )
    expect(source).toContain('EmissionReportDesignerPage')
    expect(source).not.toMatch(/processCode=\{['"]partes\.informes\.consultaDetallada['"]\}/)
    expect(source).not.toMatch(/const PROCESS_CODE\s*=/)
  })
})
