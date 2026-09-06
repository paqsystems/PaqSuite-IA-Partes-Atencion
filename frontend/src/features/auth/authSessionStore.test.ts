import { afterEach, describe, expect, it } from 'vitest'
import {
  clearAuthSession,
  getAuthSession,
  invalidateSessionIfClienteMismatch,
  saveLoginSession,
} from './authSessionStore'
import type { LoginSessionResultado } from './authTypes'

function baseResultado(): LoginSessionResultado {
  return {
    token: 'tok-1',
    user: { id: 1, usuario: 'PQ' },
    firstLogin: false,
    minutosWeb: 60,
    tenancy: 'single',
    db: 'unified',
    empresas: [{ id: 1, nombreEmpresa: 'Acme' }],
  }
}

afterEach(() => {
  clearAuthSession()
})

describe('invalidateSessionIfClienteMismatch', () => {
  it('invalida cuando el tenant de landing no coincide con el de la sesion', () => {
    saveLoginSession(baseResultado(), 'PAQ')
    expect(invalidateSessionIfClienteMismatch('ESTUDIOGB')).toBe(true)
    expect(getAuthSession()).toBeNull()
  })

  it('conserva la sesion si el tenant coincide', () => {
    saveLoginSession(baseResultado(), 'ESTUDIOGB')
    expect(invalidateSessionIfClienteMismatch('estudiogb')).toBe(false)
    expect(getAuthSession()?.cliente).toBe('ESTUDIOGB')
  })

  it('invalida sesiones viejas sin cliente al entrar con tenant', () => {
    saveLoginSession(baseResultado())
    expect(getAuthSession()?.cliente).toBeUndefined()
    expect(invalidateSessionIfClienteMismatch('ESTUDIOGB')).toBe(true)
    expect(getAuthSession()).toBeNull()
  })
})
