import { describe, expect, it } from 'vitest'
import { guestLandingPathname, searchHasResetToken } from './guestLanding'

describe('guestLanding', () => {
  it('detecta token de reset en el query', () => {
    expect(searchHasResetToken('?token=abc&locale=es')).toBe(true)
    expect(searchHasResetToken('cliente=PAQ')).toBe(false)
    expect(searchHasResetToken('')).toBe(false)
  })

  it('si hay token, aterriza en reset-password y no en login', () => {
    expect(guestLandingPathname('?token=abc&locale=es&cliente=PAQ')).toBe(
      '/reset-password',
    )
    expect(guestLandingPathname('?cliente=PAQ')).toBe('/login')
    expect(guestLandingPathname('')).toBe('/login')
  })
})
