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
})
