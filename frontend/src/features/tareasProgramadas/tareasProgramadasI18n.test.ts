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

  it('traduce proceso, parámetros y error de guardado', () => {
    const translate = createTaskTranslator('it')

    expect(translate('tareasProgramadas.procesos.item.BACKUP_DATABASE')).toBe(
      'Backup del database'
    )
    expect(
      translate('tareasProgramadas.definiciones.parameter.windowsBackupFolder')
    ).toBe('Cartella di backup Windows')
    expect(translate('tareasProgramadas.definiciones.saveError')).toBe(
      'Impossibile salvare la definizione.'
    )
  })

  it('traduce monitoreo e historial según locale (no inglés por defecto)', () => {
    const translateIt = createTaskTranslator('it')
    expect(translateIt('tareasProgramadas.monitoreo.title')).toBe(
      'Monitoraggio automatizzazioni'
    )
    expect(translateIt('tareasProgramadas.ejecuciones.message')).toBe('Motivo / messaggio')

    const translateFr = createTaskTranslator('fr')
    expect(translateFr('tareasProgramadas.monitoreo.emptyList')).toBe(
      'Aucune automatisation à afficher.'
    )
  })
})
