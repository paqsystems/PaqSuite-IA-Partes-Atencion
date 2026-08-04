import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const frontendRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const cssDir = path.join(frontendRoot, 'node_modules', 'devextreme', 'dist', 'css')
const publicDir = path.join(frontendRoot, 'public', 'dx-themes')
const outFile = path.join(frontendRoot, 'src', 'theme', 'empresaThemeCssUrls.ts')

function copyDirRecursive(fromDir, toDir) {
  fs.mkdirSync(toDir, { recursive: true })
  for (const entry of fs.readdirSync(fromDir, { withFileTypes: true })) {
    const from = path.join(fromDir, entry.name)
    const to = path.join(toDir, entry.name)
    if (entry.isDirectory()) {
      copyDirRecursive(from, to)
    } else {
      fs.copyFileSync(from, to)
    }
  }
}

if (!fs.existsSync(cssDir)) {
  console.error(
    `gen-dx-theme-urls: no existe ${cssDir}. ¿Corriste npm install en frontend/?`,
  )
  process.exit(1)
}

const files = fs
  .readdirSync(cssDir)
  .filter(
    (f) =>
      f.startsWith('dx.') &&
      f.endsWith('.css') &&
      !f.includes('.min.') &&
      !/^dx\.(common|diagram|gantt|icons|visibility)/.test(f),
  )

function fileToKey(fileName) {
  const body = fileName.replace(/^dx\./, '').replace(/\.css$/, '')
  if (body.startsWith('material.') || body.startsWith('fluent.')) {
    return body
  }
  return `generic.${body}`
}

const entries = files
  .map((file) => ({ file, key: fileToKey(file) }))
  .sort((a, b) => a.key.localeCompare(b.key))

fs.mkdirSync(publicDir, { recursive: true })
for (const entry of entries) {
  fs.copyFileSync(path.join(cssDir, entry.file), path.join(publicDir, entry.file))
}

// Fuentes de íconos DX: el CSS usa url("icons/dxicons*.woff2") relativo al CSS.
for (const assetDir of ['icons', 'fonts']) {
  const from = path.join(cssDir, assetDir)
  if (fs.existsSync(from)) {
    copyDirRecursive(from, path.join(publicDir, assetDir))
  }
}

const map = entries.map((e) => `  '${e.key}': '/dx-themes/${e.file}',`).join('\n')

const content = `/** Generado desde devextreme/dist/css — regenerar con scripts/gen-dx-theme-urls.mjs */
export const EMPRESA_THEME_CSS_URLS: Record<string, string> = {
${map}
}

export const EMPRESA_THEME_CSS_KEYS = Object.keys(EMPRESA_THEME_CSS_URLS)
`

fs.writeFileSync(outFile, content)
console.log(`OK ${entries.length} themes + icons/fonts → public/dx-themes`)
