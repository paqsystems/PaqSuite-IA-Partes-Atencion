import { describe, expect, it } from 'vitest'
import commonEn from './locales/en/common.json'
import commonEs from './locales/es/common.json'
import commonFr from './locales/fr/common.json'
import commonIt from './locales/it/common.json'
import commonPt from './locales/pt/common.json'

const catalogs = [commonEs, commonEn, commonPt, commonFr, commonIt]

function keysMatching(prefix: string): string[] {
  return catalogs.flatMap((catalog) =>
    Object.keys(catalog).filter((key) => key === prefix || key.startsWith(`${prefix}.`)),
  )
}

describe('host JSON no duplica catálogos GEN-06 (PA-05 / PA-06)', () => {
  it('no contiene admin.empresas.* ni appearance.paqsuite.*', () => {
    expect(keysMatching('admin.empresas')).toEqual([])
    expect(keysMatching('appearance.paqsuite')).toEqual([])
  })

  it('no contiene admin.roles.* (SDK usa security.roles.*)', () => {
    expect(keysMatching('admin.roles')).toEqual([])
  })
})
