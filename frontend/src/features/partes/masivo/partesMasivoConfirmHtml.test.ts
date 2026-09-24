import { describe, expect, it } from 'vitest'
import { buildMasivoConfirmHtml, buildMasivoSelectAllConfirmHtml } from './partesMasivoConfirmHtml'

describe('partesMasivoConfirmHtml', () => {
  it('genera un bloque por etiqueta', () => {
    const html = buildMasivoConfirmHtml([
      { label: 'Registros', value: '3 parte(s)' },
      { label: 'Tipo de tarea', value: '(sin cambio)' },
    ])
    expect(html).toContain('<strong>Registros:</strong> 3 parte(s)')
    expect(html).toContain('<strong>Tipo de tarea:</strong> (sin cambio)')
    expect(html).not.toContain('<ul>')
    expect(html).not.toContain('\n')
  })

  it('escapa HTML en valores', () => {
    const html = buildMasivoConfirmHtml([{ label: 'Obs', value: '<script>' }])
    expect(html).toContain('&lt;script&gt;')
    expect(html).not.toContain('<script>')
  })

  it('select all incluye total y pregunta', () => {
    const html = buildMasivoSelectAllConfirmHtml(120)
    expect(html).toContain('<strong>Registros a seleccionar:</strong> 120')
    expect(html).toContain('¿Desea continuar?')
  })
})
