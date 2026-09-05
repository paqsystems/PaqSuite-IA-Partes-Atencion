import { describe, expect, it } from 'vitest'
import { parseDxReportSavedArgs } from './parseDxReportSavedArgs'

describe('parseDxReportSavedArgs', () => {
  it('lee Url y serialize del objeto único', () => {
    const parsed = parseDxReportSavedArgs({
      Url: 'Nuevo diseño',
      Report: { serialize: () => '<XtraReportsLayoutSerializer/>' },
    })
    expect(parsed.url).toBe('Nuevo diseño')
    expect(parsed.layoutDefinition).toBe('<XtraReportsLayoutSerializer/>')
  })

  it('lee el segundo argumento estilo sender, args', () => {
    const parsed = parseDxReportSavedArgs([
      { sender: true },
      { url: 'Alt.v2', report: { serialize: () => '<xml/>' } },
    ])
    expect(parsed.url).toBe('Alt.v2')
    expect(parsed.layoutDefinition).toBe('<xml/>')
  })

  it('lee el envelope React 26.1 { sender, args: { Url, Report } }', () => {
    const parsed = parseDxReportSavedArgs([
      {
        sender: {},
        args: {
          Url: 'Diseño agosto',
          Report: { serialize: () => '<XtraReportsLayoutSerializer/>' },
        },
        component: undefined,
      },
    ])
    expect(parsed.url).toBe('Diseño agosto')
    expect(parsed.layoutDefinition).toBe('<XtraReportsLayoutSerializer/>')
  })

  it('desenvuelve Url observable (función / peek)', () => {
    const parsed = parseDxReportSavedArgs({
      Url: () => 'Copia 1',
      Report: { serialize: () => '<xml/>' },
    })
    expect(parsed.url).toBe('Copia 1')

    const withPeek = parseDxReportSavedArgs({
      url: { peek: () => 'Copia 2' },
      report: { serialize: () => '<xml/>' },
    })
    expect(withPeek.url).toBe('Copia 2')
  })
})
