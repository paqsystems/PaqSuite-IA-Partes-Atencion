import { copyFileSync, existsSync, mkdirSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const frontendRoot = join(dirname(fileURLToPath(import.meta.url)), '..')
const overlayRoot = join(frontendRoot, 'vendor', 'react-core-overlay')
const sdkExcelRoot = join(
  frontendRoot,
  'node_modules',
  '@paqsuite',
  'react-core',
  'src',
  'features',
  'excelImport',
)
const sdkGridRoot = join(
  frontendRoot,
  'node_modules',
  '@paqsuite',
  'react-core',
  'src',
  'ui',
  'grid',
)

const files = [
  'ExcelImportModal.tsx',
  'useExcelImport.ts',
  'listXlsxSheetNames.ts',
  'excelImportApiClient.ts',
]

if (!existsSync(sdkExcelRoot)) {
  console.warn('overlay-react-core-framework: no hay node_modules/@paqsuite/react-core; se omite.')
  process.exit(0)
}

mkdirSync(sdkExcelRoot, { recursive: true })

for (const fileName of files) {
  const from = join(overlayRoot, 'excelImport', fileName)
  if (!existsSync(from)) {
    console.warn(`overlay-react-core-framework: falta ${from}`)
    process.exit(1)
  }
  copyFileSync(from, join(sdkExcelRoot, fileName))
}

const gridLayoutOverlay = join(overlayRoot, 'grid', 'useGridLayouts.ts')
if (existsSync(sdkGridRoot) && existsSync(gridLayoutOverlay)) {
  copyFileSync(gridLayoutOverlay, join(sdkGridRoot, 'useGridLayouts.ts'))
  console.log('overlay-react-core-framework: GEN-11 useGridLayouts overlay aplicado.')
}

const sdkPivotRoot = join(
  frontendRoot,
  'node_modules',
  '@paqsuite',
  'react-core',
  'src',
  'ui',
  'pivot',
)
const pivotFiles = [
  'mapCatalogToPivotFields.ts',
  'pivotCatalogClient.ts',
  'usePivotLayouts.ts',
  'PivotGridBlock.tsx',
]
if (existsSync(sdkPivotRoot)) {
  for (const fileName of pivotFiles) {
    const from = join(overlayRoot, 'pivot', fileName)
    if (!existsSync(from)) {
      console.warn(`overlay-react-core-framework: falta ${from}`)
      process.exit(1)
    }
    copyFileSync(from, join(sdkPivotRoot, fileName))
  }
  console.log('overlay-react-core-framework: GEN-12 pivot overlay aplicado.')
}

console.log('overlay-react-core-framework: GEN-14 hoja Excel overlay aplicado.')
