import { describe, expect, it } from 'vitest'
import type { AuthSession } from '../../auth/authTypes'
import { resolveNativeMobileHomePath } from './resolveNativeMobileHomePath'

function session(partial: Partial<AuthSession['partes']> = {}): AuthSession {
  return {
    firstLogin: false,
    tenancy: 'single',
    empresas: [{ id: 1, nombre: 'E', theme: 'paqsuite.orange.light' }],
    user: { usuario: 'u', locale: 'es' },
    partes: { tipoFuncional: 'asistente', esSupervisor: false, asistenteId: 1, ...partial },
  } as AuthSession
}

describe('resolveNativeMobileHomePath', () => {
  it('asistente prioriza carga', () => {
    expect(resolveNativeMobileHomePath(session())).toBe('/partes/carga')
  })

  it('cliente omite carga', () => {
    expect(resolveNativeMobileHomePath(session({ tipoFuncional: 'cliente' }))).toBe(
      '/partes/informes/consulta-detallada',
    )
  })
})