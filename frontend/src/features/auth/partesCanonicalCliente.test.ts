import { describe, expect, it } from 'vitest'
import {
  clienteCodeFromCanonicalHostname,
  clienteCodeFromSearchParams,
  resolveLandingClienteCode,
} from './partesCanonicalCliente'

describe('clienteCodeFromCanonicalHostname', () => {
  it('toma el primer label de demo y paq', () => {
    expect(clienteCodeFromCanonicalHostname('demo.partesatencion.paqsystems.com')).toBe(
      'demo',
    )
    expect(clienteCodeFromCanonicalHostname('paq.partesatencion.paqsystems.com')).toBe(
      'paq',
    )
  })

  it('ignora vercel, localhost y labels reservados', () => {
    expect(clienteCodeFromCanonicalHostname('partesatencionpaqsystems-dev.vercel.app')).toBe(
      null,
    )
    expect(clienteCodeFromCanonicalHostname('localhost')).toBe(null)
    expect(clienteCodeFromCanonicalHostname('www.partesatencion.paqsystems.com')).toBe(
      null,
    )
    expect(clienteCodeFromCanonicalHostname('backend.partesatencion.paqsystems.com')).toBe(
      null,
    )
  })
})

describe('clienteCodeFromSearchParams', () => {
  it('lee ?cliente=', () => {
    expect(clienteCodeFromSearchParams('?cliente=PAQ')).toBe('PAQ')
    expect(clienteCodeFromSearchParams('locale=es')).toBe(null)
  })
})

describe('resolveLandingClienteCode', () => {
  it('override gana, luego query, luego hostname', () => {
    expect(
      resolveLandingClienteCode({
        hostname: 'paq.partesatencion.paqsystems.com',
        search: '?cliente=demo',
        overrideTenant: 'ACME',
      }),
    ).toBe('ACME')
    expect(
      resolveLandingClienteCode({
        hostname: 'paq.partesatencion.paqsystems.com',
        search: '?cliente=demo',
      }),
    ).toBe('demo')
    expect(
      resolveLandingClienteCode({
        hostname: 'paq.partesatencion.paqsystems.com',
        search: '',
      }),
    ).toBe('paq')
  })
})
