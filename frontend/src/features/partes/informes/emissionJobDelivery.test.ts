import { afterEach, describe, expect, it, vi } from 'vitest'
import { downloadBlob } from '@paqsuite/react-core'
import {
  defaultEmissionFileName,
  deliverEmissionJob,
  printPdfBlob,
} from './emissionJobDelivery'

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    downloadBlob: vi.fn(),
  }
})

describe('emissionJobDelivery', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
    vi.mocked(downloadBlob).mockReset()
    document.body.innerHTML = ''
  })

  it('defaultEmissionFileName usa el nombre del job o la extensión del canal', () => {
    expect(defaultEmissionFileName('pdf', 'consulta.pdf')).toBe('consulta.pdf')
    expect(defaultEmissionFileName('excel', null)).toBe('emision.xlsx')
    expect(defaultEmissionFileName('csv', '  ')).toBe('emision.csv')
    expect(defaultEmissionFileName('print', null)).toBe('emision.pdf')
  })

  it('deliverEmissionJob descarga el blob con nombre de archivo', async () => {
    const blob = new Blob(['xlsx'], { type: 'application/vnd.ms-excel' })
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => new Response(blob, { status: 200, headers: { 'Content-Type': blob.type } })),
    )

    await expect(
      deliverEmissionJob({ jobId: 'job-1', channel: 'excel', fileName: 'partes.xlsx' }),
    ).resolves.toBe('downloaded')
    expect(downloadBlob).toHaveBeenCalledWith(expect.any(Blob), 'partes.xlsx')
  })

  it('deliverEmissionJob en print monta iframe y dispara print', async () => {
    const blob = new Blob(['%PDF'], { type: 'application/pdf' })
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => new Response(blob, { status: 200, headers: { 'Content-Type': 'application/pdf' } })),
    )
    const createObjectURL = vi.fn(() => 'blob:print-url')
    const revokeObjectURL = vi.fn()
    vi.stubGlobal('URL', { createObjectURL, revokeObjectURL })

    await expect(deliverEmissionJob({ jobId: 'job-2', channel: 'print' })).resolves.toBe('printed')

    const iframe = document.querySelector('[data-testid="emissions.printFrame"]') as HTMLIFrameElement
    expect(iframe).toBeTruthy()
    const print = vi.fn()
    Object.defineProperty(iframe, 'contentWindow', {
      value: { focus: vi.fn(), print },
      configurable: true,
    })
    iframe.onload?.(new Event('load'))
    expect(print).toHaveBeenCalled()
  })

  it('printPdfBlob no falla sin document', () => {
    expect(() => printPdfBlob(new Blob(['x']))).not.toThrow()
  })
})
