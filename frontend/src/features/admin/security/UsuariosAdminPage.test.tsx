import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))

describe('UsuariosAdminPage wrapper GEN-06 CC #16', () => {
  it('monta PartesUsuariosAdminPage sin toolbar masivo', () => {
    const source = readFileSync(join(dir, 'UsuariosAdminPage.tsx'), 'utf8')
    expect(source).toContain('PartesUsuariosAdminPage')
    expect(source).toContain('i18nNamespace="common"')
    expect(source).not.toContain('NotifyUsuarioAccessToolbar')
    expect(source).not.toContain('UsuariosAdminPageSdk')
  })

  it('el formulario host define notify junto al email', () => {
    const form = readFileSync(join(dir, 'partesUsuariosFormPopup.tsx'), 'utf8')
    expect(form).toContain('adminUsuariosNotifyEmail')
    expect(form).toContain('adminUsuariosNotifyFormError')
    expect(form).toContain('notifyAdminUsuarioAccess')
    expect(form).not.toContain('admin.usuarios.form.password')
  })
})
