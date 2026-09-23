import { describe, expect, it } from 'vitest'
import { resolveWebApiBaseUrlFromHostname } from './resolveWebApiBaseUrl'

describe('resolveWebApiBaseUrlFromHostname', () => {
  it('resuelve prod para vercel y dominios canónicos', () => {
    expect(resolveWebApiBaseUrlFromHostname('partesatencionpaqsystems.vercel.app')).toBe(
      'https://backendpartesatencionpaqsystems.on-forge.com/api/v1',
    )
    expect(resolveWebApiBaseUrlFromHostname('demo.partesatencion.paqsystems.com')).toBe(
      'https://backendpartesatencionpaqsystems.on-forge.com/api/v1',
    )
    expect(resolveWebApiBaseUrlFromHostname('paq.partesatencion.paqsystems.com')).toBe(
      'https://backendpartesatencionpaqsystems.on-forge.com/api/v1',
    )
  })

  it('resuelve develop para front dev', () => {
    expect(resolveWebApiBaseUrlFromHostname('partesatencionpaqsystemsdev.vercel.app')).toBe(
      'https://backenddevpartesatencionpaqsystems.on-forge.com/api/v1',
    )
    expect(resolveWebApiBaseUrlFromHostname('desarrollo.partesatencion.paqsystems.com')).toBe(
      'https://backenddevpartesatencionpaqsystems.on-forge.com/api/v1',
    )
  })

  it('no fuerza backend en local', () => {
    expect(resolveWebApiBaseUrlFromHostname('localhost')).toBeNull()
    expect(resolveWebApiBaseUrlFromHostname('127.0.0.1')).toBeNull()
  })
})
