import { describe, expect, it } from 'vitest'
import { formatVersionLabel } from './formatVersion'

describe('formatVersionLabel', () => {
  it('devuelve prefijo y version', () => {
    expect(formatVersionLabel('1.2.3')).toBe('v1.2.3')
  })
})
