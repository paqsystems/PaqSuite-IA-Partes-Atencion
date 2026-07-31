import { describe, expect, it } from 'vitest'
import { isPartesMobileRouteAllowed, partesMobilePolicy } from './partesMobilePolicy'

describe('partesMobilePolicy', () => {
  it('permite dashboard, kardex e informe paquete', () => {
    expect(isPartesMobileRouteAllowed('/partes')).toBe(true)
    expect(isPartesMobileRouteAllowed('/partes/consulta')).toBe(true)
    expect(isPartesMobileRouteAllowed('/partes/informes/paquete-horas')).toBe(true)
  })

  it('deniega ABM, masivo y consultas pivot web', () => {
    expect(isPartesMobileRouteAllowed('/archivos/partes/asistentes')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/proceso-masivo')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/informes/consulta-detallada')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/informes/consultas-agrupadas')).toBe(false)
  })

  it('filtra menú', () => {
    const filtered = partesMobilePolicy.filterItems([
      {
        menuKey: 'root',
        routeName: null,
        children: [
          { menuKey: 'a', routeName: '/archivos/partes/asistentes', children: [] },
          { menuKey: 'd', routeName: '/partes', children: [] },
          { menuKey: 'm', routeName: '/partes/proceso-masivo', children: [] },
        ],
      },
    ])
    expect(filtered).toHaveLength(1)
    expect(filtered[0].children).toHaveLength(1)
    expect(filtered[0].children?.[0].routeName).toBe('/partes')
  })
})
