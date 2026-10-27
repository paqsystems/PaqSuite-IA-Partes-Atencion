import { describe, expect, it } from 'vitest'
import { ko } from './dxReportingRuntimeSetup'

describe('dxReportingRuntimeSetup', () => {
  it('deja ko.observable invocable (evita (void 0) is not a function)', () => {
    expect(typeof ko.observable).toBe('function')
    const value = ko.observable(1)
    expect(value()).toBe(1)
    expect(typeof (globalThis as { ko?: typeof ko }).ko?.observable).toBe('function')
  })
})
