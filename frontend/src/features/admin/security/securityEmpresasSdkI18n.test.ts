import { describe, expect, it } from 'vitest'
import {
  empresasAdminSdkFallbackEs,
  translateEmpresasAdminSdk,
} from './securityEmpresasSdkI18n'

describe('translateEmpresasAdminSdk', () => {
  it('usa mapeo legacy cuando el host ya tiene la clave', () => {
    const catalog: Record<string, string> = {
      'admin.empresas.editTitle': 'Editar empresa',
      'admin.empresas.field.nombre': 'Nombre',
    }
    const t = (key: string) => catalog[key] ?? key

    expect(translateEmpresasAdminSdk('admin.empresas.form.editTitle', t)).toBe(
      'Editar empresa',
    )
    expect(translateEmpresasAdminSdk('admin.empresas.form.nombre', t)).toBe('Nombre')
  })

  it('usa clave SDK directa cuando existe en i18n', () => {
    const catalog: Record<string, string> = {
      'admin.empresas.form.save': 'Guardar',
      'admin.empresas.form.cancel': 'Cancelar',
    }
    const t = (key: string) => catalog[key] ?? key

    expect(translateEmpresasAdminSdk('admin.empresas.form.save', t)).toBe('Guardar')
    expect(translateEmpresasAdminSdk('admin.empresas.form.cancel', t)).toBe('Cancelar')
  })

  it('no devuelve claves crudas: cae al fallback ES', () => {
    const t = (key: string) => key

    expect(translateEmpresasAdminSdk('admin.empresas.title', t)).toBe(
      empresasAdminSdkFallbackEs['admin.empresas.title'],
    )
    expect(translateEmpresasAdminSdk('admin.empresas.form.editTitle', t)).toBe(
      'Editar empresa',
    )
    expect(translateEmpresasAdminSdk('admin.empresas.monoNote', t)).toBe(
      empresasAdminSdkFallbackEs['admin.empresas.monoNote'],
    )
    expect(translateEmpresasAdminSdk('admin.empresas.applyTheme', t)).toBe('Aplicar')
  })
})
