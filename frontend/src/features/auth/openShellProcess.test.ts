import { describe, expect, it } from 'vitest'
import { resolveProcessHref } from './openShellProcess'

describe('resolveProcessHref', () => {
  it('conserva ?cliente= de la ventana actual', () => {
    expect(
      resolveProcessHref('/archivos/partes/clientes', {
        origin: 'https://partes.paqsystems.com',
        search: '?cliente=PAQ',
      }),
    ).toBe('/archivos/partes/clientes?cliente=PAQ')
  })

  it('inyecta el cliente de sesión si la URL actual no lo trae', () => {
    expect(
      resolveProcessHref('/partes/carga-diaria', {
        origin: 'https://partes.paqsystems.com',
        search: '',
      }, 'demo'),
    ).toBe('/partes/carga-diaria?cliente=demo')
  })

  it('no duplica cliente si la ruta ya lo trae', () => {
    expect(
      resolveProcessHref('/partes?cliente=PAQ', {
        origin: 'https://partes.paqsystems.com',
        search: '?cliente=DEMO',
      }, 'OTRO'),
    ).toBe('/partes?cliente=PAQ')
  })
})
