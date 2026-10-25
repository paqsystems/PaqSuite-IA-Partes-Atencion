import { describe, expect, it } from 'vitest'
import { createTaskTranslator } from './tareasProgramadasI18n'

describe('tareasProgramadasI18n', () => {
  it('traduce los captions de GEN-13 según el locale activo', () => {
    const translate = createTaskTranslator('it')

    expect(translate('tareasProgramadas.procesos.title')).toBe('Processi programmabili')
    expect(translate('tareasProgramadas.definiciones.baseDate')).toBe('Data di riferimento')
  })

  it('conserva el fallback del SDK para claves aún no catalogadas', () => {
    const translate = createTaskTranslator('es')

    expect(translate('tareasProgramadas.definiciones.save')).toBe('Guardar')
  })
})
