import { describe, expect, it } from 'vitest'
import {
  readDxReportingConfig,
  resolveDxReportingClientHost,
  reportingProxyTarget,
  resolveDxDesignerReportUrl,
  DEFAULT_DX_REPORT_URL,
} from './dxReportingConfig'

describe('dxReportingConfig', () => {
  it('usa same-origin cuando hay host y no DIRECT', () => {
    const host = resolveDxReportingClientHost(
      'http://127.0.0.1:5002',
      'http://localhost:3010',
      true,
    )
    expect(host).toBe('http://localhost:3010/')
  })

  it('usa URL directa si no hay proxy same-origin', () => {
    const host = resolveDxReportingClientHost(
      'http://127.0.0.1:5002',
      'http://localhost:3010',
      false,
    )
    expect(host).toBe('http://127.0.0.1:5002/')
  })

  it('configured=false sin VITE_DX_REPORTING_HOST', () => {
    const config = readDxReportingConfig({})
    expect(config.configured).toBe(false)
    expect(config.host).toBe('')
  })

  it('configured=true con host y defaults de action/report', () => {
    const config = readDxReportingConfig({
      VITE_DX_REPORTING_HOST: 'http://127.0.0.1:5055',
    })
    expect(config.configured).toBe(true)
    expect(config.getDesignerModelAction).toBe('DXXRD/GetDesignerModel')
    expect(config.reportUrl).toBe('partes.consultaDetallada.principal')
  })

  it('reportingProxyTarget quita slash final', () => {
    expect(reportingProxyTarget({ VITE_DX_REPORTING_HOST: 'http://127.0.0.1:5055/' })).toBe(
      'http://127.0.0.1:5055',
    )
  })

  it('resolveDxDesignerReportUrl no usa id numérico', () => {
    expect(
      resolveDxDesignerReportUrl({
        reportCode: '1',
        reportUrlDefault: DEFAULT_DX_REPORT_URL,
        processCode: 'partes.informes.consultaDetallada',
      }),
    ).toBe(DEFAULT_DX_REPORT_URL)
    expect(
      resolveDxDesignerReportUrl({
        reportCode: 'partes.consultaDetallada.principal',
        reportUrlDefault: DEFAULT_DX_REPORT_URL,
        processCode: 'partes.informes.consultaDetallada',
      }),
    ).toBe('partes.consultaDetallada.principal')
  })
})
