import { setCachedResolvedApiBaseUrl } from '@paqsuite/react-core'
import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { rewriteApiFetchInput } from './rewriteApiFetchInput'


describe('rewriteApiFetchInput', () => {
  beforeEach(() => {
    setCachedResolvedApiBaseUrl(null)
  })


  afterEach(() => {
    setCachedResolvedApiBaseUrl(null)
  })


  it('deja la URL igual sin base cacheada', () => {
    const path = '/api/v1/excel-import/processes/partes.tareas.import/template'
    const result = rewriteApiFetchInput(path, path)
    expect(result.url).toBe(path)
    expect(result.input).toBe(path)
  })


  it('reescribe fetch relativo cuando hay base absoluta', () => {
    setCachedResolvedApiBaseUrl('https://partesatencion-paqsystems.on-forge.com/api/v1')
    const path = '/api/v1/excel-import/processes/partes.tareas.import/template'
    const result = rewriteApiFetchInput(path, path)
    expect(result.url).toBe(
      'https://partesatencion-paqsystems.on-forge.com/api/v1/excel-import/processes/partes.tareas.import/template',
    )
    expect(result.input).toBe(result.url)
  })


  it('reescribe Request con la misma URL resuelta', () => {
    setCachedResolvedApiBaseUrl('https://backend.example.com/api/v1')
    const path = '/api/v1/emissions/jobs/job-1/download'
    const request = new Request(`http://localhost${path}`, { method: 'GET' })
    const result = rewriteApiFetchInput(request, path)
    expect(result.url).toBe('https://backend.example.com/api/v1/emissions/jobs/job-1/download')
    expect(result.input).toBeInstanceOf(Request)
    expect((result.input as Request).url).toBe(result.url)
  })
})
