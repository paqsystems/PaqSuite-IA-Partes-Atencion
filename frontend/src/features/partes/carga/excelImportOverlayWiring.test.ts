import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))
const overlayModal = join(
  dir,
  '../../../../vendor/react-core-overlay/excelImport/ExcelImportModal.tsx',
)

describe('GEN-14 overlay hoja Excel', () => {
  it('el overlay de deploy incluye el SelectBox de hoja', () => {
    const source = readFileSync(overlayModal, 'utf8')
    expect(source).toContain('excelImport.sheet')
    expect(source).toContain('excelImport.sheetField')
  })
})
