import { describe, expect, it } from 'vitest'
import { localizeMenuTree, type MenuNode } from '@paqsuite/react-core'
import { productI18nCatalogs } from '../auth/productI18n'
import { createAppTranslator } from '../auth/shellI18n'

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

describe('localizeMenuTree (Partes productI18n)', () => {
  it('traduce consulta detallada en francés sin re-fetch', () => {
    const t = createAppTranslator('fr', productI18nCatalogs.fr)
    const tree = localizeMenuTree(
      [
        stubNode({
          id: 40100,
          menuKey: 'partes_consulta_detallada',
          labelKey: 'menu.partes_consulta_detallada',
          text: 'Consulta detallada',
          nodeType: 'process',
          routeName: '/partes/informes/consulta-detallada',
        }),
      ],
      (key) => t(key),
    )
    expect(tree[0].text).toBe('Consultation détaillée')
  })

  it('traduce Soporte Técnico y sus procesos en francés', () => {
    const t = createAppTranslator('fr', productI18nCatalogs.fr)
    const tree = localizeMenuTree(
      [
        stubNode({
          id: 60000,
          menuKey: 'parametros',
          labelKey: 'menu.parametros',
          text: 'Parámetros',
          nodeType: 'group',
          children: [
            stubNode({
              id: 60300,
              menuKey: 'soporte_tecnico',
              labelKey: 'menu.soporte_tecnico',
              text: 'Soporte Técnico',
              nodeType: 'group',
              children: [
                stubNode({
                  id: 60310,
                  menuKey: 'partes_disenador_emisiones',
                  labelKey: 'menu.partes_disenador_emisiones',
                  text: 'Diseñador de emisiones',
                  nodeType: 'process',
                  routeName: '/emisiones/disenador',
                }),
                stubNode({
                  id: 60320,
                  menuKey: 'tareas_programadas',
                  labelKey: 'menu.tareas_programadas',
                  text: 'Tareas programadas',
                  nodeType: 'process',
                  routeName: '/tareas-programadas',
                }),
              ],
            }),
          ],
        }),
      ],
      (key) => t(key),
    )

    expect(tree[0].text).toBe('Paramètres')
    expect(tree[0].children[0].text).toBe('Support technique')
    expect(tree[0].children[0].children[1].text).toBe('Tâches planifiées')
  })
})
