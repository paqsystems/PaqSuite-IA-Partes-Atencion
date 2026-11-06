import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))

describe('UsuariosAdminPage wrapper GEN-06', () => {
  it('monta el export SDK con namespace common y toolbar host Notificar', () => {
    const source = readFileSync(join(dir, 'UsuariosAdminPage.tsx'), 'utf8')
    expect(source).toContain('UsuariosAdminPage as UsuariosAdminPageSdk')
    expect(source).toContain('i18nNamespace="common"')
    expect(source).toContain('NotifyUsuarioAccessToolbar')
    expect(source).toContain('isNativeApp')
    expect(source).not.toContain('ProcessDataGrid')
    expect(source).not.toContain('createAdminUsuario')
  })

  it('no copia el listado GEN-06: la acción Notificar vive junto al wrapper', () => {
    const dialog = readFileSync(join(dir, 'NotifyUsuarioAccessDialog.tsx'), 'utf8')
    expect(dialog).toContain('adminUsuariosNotify')
    expect(dialog).toContain('adminUsuariosNotifyFormError')
    expect(dialog).toContain('notifyAdminUsuarioAccess')
    expect(dialog).not.toContain('UsuariosAdminPageSdk')
  })
})
