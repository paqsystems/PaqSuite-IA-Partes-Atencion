import { describe, expect, it } from 'vitest'
import i18n from '../../../i18n/i18n'

const locales = ['es', 'en', 'pt', 'fr', 'it'] as const

const requiredKeys = [
  'excelImport.sheet.label',
  'excelImport.sheet.placeholder',
  'excelImport.sheet.required',
  'partes.import.clienteRequerido',
  'partes.import.clienteInvalido',
  'partes.import.asistenteRequerido',
  'partes.import.asistenteInvalido',
  'partes.import.asistenteDistintoSesion',
  'partes.import.tipoRequerido',
  'partes.import.tipoFueraUniverso',
  'partes.import.fechaInvalida',
  'partes.import.duracionInvalida',
  'partes.import.booleanoInvalido',
  'partes.import.descripcionRequerida',
] as const

describe('excel import i18n (TR-009-update)', () => {
  it('resuelve excelImport.sheet.label y partes.import.tipoFueraUniverso en es y en', async () => {
    await i18n.changeLanguage('es')
    expect(i18n.t('excelImport.sheet.label')).toBe('Hoja')
    expect(i18n.t('partes.import.tipoFueraUniverso')).not.toBe(
      'partes.import.tipoFueraUniverso',
    )

    await i18n.changeLanguage('en')
    expect(i18n.t('excelImport.sheet.label')).toBe('Sheet')
    expect(i18n.t('partes.import.tipoFueraUniverso')).not.toBe(
      'partes.import.tipoFueraUniverso',
    )
    expect(i18n.t('partes.import.tipoFueraUniverso')).not.toBe(
      i18n.getResource('es', 'common', 'partes.import.tipoFueraUniverso'),
    )
  })

  it('define las claves GEN y de dominio en los cinco locales', () => {
    for (const lng of locales) {
      for (const key of requiredKeys) {
        const value = i18n.getResource(lng, 'common', key)
        expect(value, `${lng}:${key}`).toBeTruthy()
        expect(value, `${lng}:${key}`).not.toBe(key)
      }
      const duration = String(
        i18n.getResource(lng, 'common', 'partes.import.duracionInvalida'),
      )
      expect(duration, `${lng}:duracionInvalida`).toContain('{{tramo}}')
    }
  })
})
