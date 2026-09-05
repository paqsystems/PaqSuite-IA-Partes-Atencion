import { describe, expect, it } from 'vitest'
import type { MenuNode } from '@paqsuite/react-core'
import { filterMenuForCliente } from './PartesMenuSidebar'

function stubNode(
  partial: Partial<MenuNode> & Pick<MenuNode, 'id' | 'menuKey' | 'text' | 'nodeType'>,
): MenuNode {
  return {
    labelKey: null,
    routeName: null,
    procedimiento: null,
    order: partial.id,
    iconName: null,
    processType: null,
    permissions: null,
    children: [],
    ...partial,
  }
}

describe('filterMenuForCliente', () => {
  it('oculta procesos /archivos/partes y padres vacíos', () => {
    const filtered = filterMenuForCliente([
      stubNode({
        id: 1,
        text: 'Archivos',
        menuKey: 'archivos',
        nodeType: 'group',
        children: [
          stubNode({
            id: 2,
            text: 'Asistentes',
            menuKey: 'partes_asistentes',
            routeName: '/archivos/partes/asistentes',
            nodeType: 'process',
          }),
          stubNode({
            id: 3,
            text: 'Otro',
            menuKey: 'otro',
            routeName: '/otros',
            nodeType: 'process',
          }),
        ],
      }),
    ])

    expect(filtered).toHaveLength(1)
    expect(filtered[0].children).toHaveLength(1)
    expect(filtered[0].children[0].menuKey).toBe('otro')
  })

  it('oculta procesos /partes/*', () => {
    const filtered = filterMenuForCliente([
      stubNode({
        id: 10,
        text: 'Partes',
        menuKey: 'partes',
        nodeType: 'group',
        children: [
          stubNode({
            id: 11,
            text: 'Carga',
            menuKey: 'partes_carga_diaria',
            routeName: '/partes/carga-diaria',
            nodeType: 'process',
          }),
          stubNode({
            id: 12,
            text: 'Dashboard',
            menuKey: 'partes_dashboard',
            routeName: '/partes',
            nodeType: 'process',
          }),
        ],
      }),
    ])
    expect(filtered).toHaveLength(1)
    expect(filtered[0].children).toHaveLength(1)
    expect(filtered[0].children[0].menuKey).toBe('partes_dashboard')
  })

  it('oculta seguridad, parametros y soporte tecnico', () => {
    const filtered = filterMenuForCliente([
      stubNode({
        id: 50000,
        text: 'Seguridad',
        menuKey: 'seguridad',
        nodeType: 'group',
        children: [
          stubNode({
            id: 50100,
            text: 'Roles',
            menuKey: 'admin_roles',
            routeName: '/admin/roles',
            nodeType: 'process',
          }),
        ],
      }),
      stubNode({
        id: 60000,
        text: 'Parámetros',
        menuKey: 'parametros',
        nodeType: 'group',
        children: [
          stubNode({
            id: 60100,
            text: 'Auth',
            menuKey: 'parametros_auth',
            routeName: '/parametros/Auth',
            nodeType: 'process',
          }),
        ],
      }),
      stubNode({
        id: 10000,
        text: 'Inicio',
        menuKey: 'inicio',
        nodeType: 'group',
        children: [
          stubNode({
            id: 10100,
            text: 'Dashboard',
            menuKey: 'partes_dashboard',
            routeName: '/partes',
            nodeType: 'process',
          }),
        ],
      }),
      stubNode({
        id: 70000,
        text: 'Soporte Técnico',
        menuKey: 'soporte_tecnico',
        nodeType: 'group',
        children: [
          stubNode({
            id: 70100,
            text: 'Diseñador',
            menuKey: 'partes_disenador_emisiones',
            routeName: '/emisiones/disenador',
            nodeType: 'process',
          }),
        ],
      }),
    ])
    expect(filtered).toHaveLength(1)
    expect(filtered[0].menuKey).toBe('inicio')
  })
})
