import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'
import { menuSidebarLabelsFromT } from './shellI18n'

describe('PA-07 menuSidebarLabelsFromT', () => {
  it('mapea chrome GEN a t(menu.*) sin literales', () => {
    const labels = menuSidebarLabelsFromT((key) => `T:${key}`)
    expect(labels.controls).toBe('T:menu.controls')
    expect(labels.searchPlaceholder).toBe('T:menu.searchPlaceholder')
    expect(labels.retry).toBe('T:menu.retry')
  })

  it('shellI18n no incrusta captions ES de chrome GEN', () => {
    const source = readFileSync(join(dirname(fileURLToPath(import.meta.url)), 'shellI18n.ts'), 'utf8')
    expect(source).not.toContain('Controles de menú')
    expect(source).not.toContain('Buscar en el menú')
    expect(source).not.toContain('defaultMenuSidebarLabels')
  })
})
