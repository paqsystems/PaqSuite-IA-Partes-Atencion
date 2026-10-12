import { afterEach, describe, expect, it } from 'vitest'
import { buildPlatformHeaders, clienteHeaderName, companyHeaderName } from '@paqsuite/react-core'
import { clearAuthSession, saveLoginSession } from './authSessionStore'
import { buildAuthHttpHeaders, buildAuthPlatformHeaders } from './platformContext'
import type { LoginSessionResultado } from './authTypes'

function baseResultado(tenancy: 'single' | 'multi' = 'single'): LoginSessionResultado {
  return {
    token: 'tok-1',
    user: { id: 1, usuario: 'PQ' },
    firstLogin: false,
    minutosWeb: 60,
    tenancy,
    db: 'unified',
    empresas: [{ id: 99, nombreEmpresa: 'Acme' }],
  }
}

afterEach(() => {
  clearAuthSession()
})

describe('PA-10 buildAuthPlatformHeaders', () => {
  it('en MONO no envía companyId (X-Paq-Cliente ≠ id de empresa)', () => {
    saveLoginSession(baseResultado('single'), 'PAQ')
    const input = buildAuthPlatformHeaders()
    expect(input.tenancy).toBe('single')
    expect(input.companyId).toBeUndefined()
    const headers = buildPlatformHeaders(input)
    expect(headers[companyHeaderName]).toBeUndefined()
    expect(headers[clienteHeaderName]).toBeTruthy()
    expect(headers[clienteHeaderName]).not.toBe('99')
  })

  it('en MULTI incluye companyId para X-Company-Id', () => {
    saveLoginSession(baseResultado('multi'), 'PAQ')
    const input = buildAuthPlatformHeaders()
    expect(input.companyId).toBe(99)
    expect(buildAuthHttpHeaders()[companyHeaderName]).toBe('99')
  })
})
