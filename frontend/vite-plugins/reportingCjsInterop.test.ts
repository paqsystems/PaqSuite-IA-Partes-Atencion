import { describe, expect, it } from 'vitest'
import {
  KNOCKOUT_NAMED_EXPORTS,
  aceEsmExportSuffix,
  knockoutEsmExportSuffix,
  transformReportingCjsModule,
} from './reportingCjsInterop'

describe('reportingCjsInterop', () => {
  it('exporta observable y el resto de named exports que usa Reporting', () => {
    const suffix = knockoutEsmExportSuffix()
    expect(suffix).toContain('export default __ns')
    expect(suffix).toContain('export const observable = __ns && __ns.observable')
    expect(suffix).toContain('export const applyBindings = __ns && __ns.applyBindings')
    expect(KNOCKOUT_NAMED_EXPORTS).toContain('observable')
    expect(KNOCKOUT_NAMED_EXPORTS).toContain('bindingHandlers')
  })

  it('resuelve ace desde module.exports o globalThis.ace', () => {
    const suffix = aceEsmExportSuffix()
    expect(suffix).toContain('globalThis.ace = __ns')
    expect(suffix).toContain('export default __ns')
  })

  it('transforma knockout-latest.js agregando named exports', () => {
    const result = transformReportingCjsModule(
      '(function(){})();',
      '/app/node_modules/knockout/build/output/knockout-latest.js',
    )
    expect(result?.code).toContain('export const observable = __ns && __ns.observable')
    expect(result?.code).toContain('export default __ns')
  })
})
