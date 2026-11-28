import { describe, expect, it } from 'vitest'
import { isPartesMobileRouteAllowed, partesMobilePolicy } from './partesMobilePolicy'

describe('partesMobilePolicy', () => {
  it('permite rutas v1 y auth', () => {
    expect(isPartesMobileRouteAllowed('/partes/carga')).toBe(true)
    expect(isPartesMobileRouteAllowed('/partes/informes/consulta-detallada')).toBe(true)
    expect(isPartesMobileRouteAllowed('/partes/informes/paquete-horas')).toBe(true)
    expect(isPartesMobileRouteAllowed('/change-password')).toBe(true)
    expect(isPartesMobileRouteAllowed('/select-empresa')).toBe(true)
  })

  it('deniega dashboard, chat, raíz y consulta legacy', () => {
    expect(isPartesMobileRouteAllowed('/partes')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/consulta')).toBe(false)
    expect(isPartesMobileRouteAllowed('/dashboard')).toBe(false)
    expect(isPartesMobileRouteAllowed('/chat-assistant')).toBe(false)
  })

  it('deniega ABM, masivo, consultas pivot web y emisiones', () => {
    expect(isPartesMobileRouteAllowed('/archivos/partes/asistentes')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/proceso-masivo')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/carga-diaria')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/carga-paquetes')).toBe(false)
    expect(isPartesMobileRouteAllowed('/partes/informes/consultas-agrupadas')).toBe(false)
    expect(isPartesMobileRouteAllowed('/emisiones/disenador')).toBe(false)
    expect(isPartesMobileRouteAllowed('/emisiones')).toBe(false)
    expect(isPartesMobileRouteAllowed('/admin/usuarios')).toBe(false)
    expect(isPartesMobileRouteAllowed('/parametros/Partes')).toBe(false)
    expect(isPartesMobileRouteAllowed('/tareas-programadas')).toBe(false)
  })

  it('filtra menú conservando solo rutas allowlist', () => {
    const filtered = partesMobilePolicy.filterItems([
      {
        menuKey: 'root',
        routeName: null,
        children: [
          { menuKey: 'a', routeName: '/archivos/partes/asistentes', children: [] },
          { menuKey: 'd', routeName: '/partes/carga', children: [] },
          { menuKey: 'm', routeName: '/partes/proceso-masivo', children: [] },
        ],
      },
    ])
    expect(filtered).toHaveLength(1)
    expect(filtered[0].children).toHaveLength(1)
    expect(filtered[0].children?.[0].routeName).toBe('/partes/carga')
  })
})