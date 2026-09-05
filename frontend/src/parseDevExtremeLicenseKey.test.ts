import { describe, expect, it } from 'vitest'
import { parseDevExtremeLicenseKey } from './parseDevExtremeLicenseKey'

describe('parseDevExtremeLicenseKey', () => {
  it('acepta una clave LCP 26.1', () => {
    expect(parseDevExtremeLicenseKey(['LCP-abc'])).toBe('LCP-abc')
  })

  it('ignora JWT 25.2, placeholders y claves vacias', () => {
    expect(
      parseDevExtremeLicenseKey(['eyJhbGciOiJIUzI1NiJ9', 'undefined', 'null', '', '  ']),
    ).toBe('')
  })

  it('prefiere LCP aunque haya JWT en otra variable', () => {
    expect(parseDevExtremeLicenseKey(['eyJhbGciOiJIUzI1NiJ9', 'LCP-ok'])).toBe('LCP-ok')
  })
})
