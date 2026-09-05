import { describe, expect, it } from 'vitest'
import {
  clienteCodeFromCanonicalHostname,
  clienteCodeFromReferrer,
  clienteCodeFromSearchParams,
  isVercelFrontDoorHostname,
  resolveLandingClienteCode,
  shouldHonorLandingCliente,
} from './partesCanonicalCliente'

describe('clienteCodeFromCanonicalHostname', () => {
  it('toma el primer label de demo y paq', () => {
    expect(clienteCodeFromCanonicalHostname('demo.partesatencion.paqsystems.com')).toBe(
      'demo',
    )
    expect(clienteCodeFromCanonicalHostname('paq.partesatencion.paqsystems.com')).toBe(
      'paq',
    )
    expect(
      clienteCodeFromCanonicalHostname('estudiogb.partesatenciones.paqsystems.com'),
    ).toBe('estudiogb')
  })

  it('acepta www.{cliente}.partesatencion', () => {
    expect(
      clienteCodeFromCanonicalHostname('www.estudiogb.partesatencion.paqsystems.com'),
    ).toBe('estudiogb')
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

describe('clienteCodeFromReferrer', () => {
  it('lee el subdominio del origen del redirect', () => {
    expect(
      clienteCodeFromReferrer('https://estudiogb.partesatencion.paqsystems.com/'),
    ).toBe('estudiogb')
    expect(clienteCodeFromReferrer('https://partesatencionpaqsystems.vercel.app/')).toBe(
      null,
    )
  })
})

describe('isVercelFrontDoorHostname', () => {
  it('detecta el FE en Vercel', () => {
    expect(isVercelFrontDoorHostname('partesatencionpaqsystems.vercel.app')).toBe(true)
    expect(isVercelFrontDoorHostname('demo.partesatencion.paqsystems.com')).toBe(false)
  })
})

describe('shouldHonorLandingCliente', () => {
  it('en Vercel honra landing aunque el build no sea DEV', () => {
    expect(
      shouldHonorLandingCliente({
        hostname: 'partesatencionpaqsystems.vercel.app',
        isDevBuild: false,
      }),
    ).toBe(true)
  })

  it('en localhost DEV no pisa el force-DEMO del SDK', () => {
    expect(
      shouldHonorLandingCliente({
        hostname: 'localhost',
        isDevBuild: true,
      }),
    ).toBe(false)
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

  it('en vercel.app usa query y, si falta, el referrer del subdominio', () => {
    expect(
      resolveLandingClienteCode({
        hostname: 'partesatencionpaqsystems.vercel.app',
        search: '?cliente=ESTUDIOGB',
      }),
    ).toBe('ESTUDIOGB')
    expect(
      resolveLandingClienteCode({
        hostname: 'partesatencionpaqsystems.vercel.app',
        search: '',
        referrer: 'https://estudiogb.partesatencion.paqsystems.com/',
      }),
    ).toBe('estudiogb')
  })
})
