import { describe, expect, it } from 'vitest'
import {
  isBinaryDownloadUrl,
  isValidBinaryArtifact,
  isZipBinary,
} from './binaryDownloadUrl'

describe('binaryDownloadUrl', () => {
  it('detecta rutas de descarga binaria', () => {
    expect(
      isBinaryDownloadUrl('/api/v1/excel-import/processes/partes.tareas.import/template'),
    ).toBe(true)
    expect(
      isBinaryDownloadUrl('/api/v1/emissions/jobs/job-1/download'),
    ).toBe(true)
    expect(isBinaryDownloadUrl('/api/v1/partes/tareas')).toBe(false)
  })

  it('valida firma ZIP para plantillas excel', () => {
    const zip = new Uint8Array([0x50, 0x4b, 0x03, 0x04]).buffer
    const html = new TextEncoder().encode('<!doctype html>').buffer
    const url = '/api/v1/excel-import/processes/partes.tareas.import/template'

    expect(isZipBinary(zip)).toBe(true)
    expect(isValidBinaryArtifact(zip, url)).toBe(true)
    expect(isValidBinaryArtifact(html, url)).toBe(false)
  })
})
