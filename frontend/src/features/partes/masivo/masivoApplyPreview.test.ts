import { describe, expect, it } from 'vitest'
import { buildMasivoApplyPreview } from './masivoApplyPreview'

describe('buildMasivoApplyPreview', () => {
  it('arma filas de resumen con valores legibles', () => {
    const preview = buildMasivoApplyPreview({
      itemCount: 2,
      applyTipoTareaId: 7,
      tiposTarea: [{ id: 7, code: 'DEV', descripcion: 'Desarrollo' }],
      touchSinCargo: true,
      applySinCargo: true,
      touchPresencial: false,
      applyPresencial: false,
      applyUsuarioId: 3,
      asistentes: [{ id: 3, code: 'usr', nombre: 'Usuario' }],
      touchFecha: true,
      applyFecha: '2026-09-16',
      fechaDesde: '2026-09-01',
      fechaHasta: '2026-09-30',
      items: [
        { id: 11, rowVersion: '1', fecha: '2026-09-15', usuarioCode: 'admin' },
        { id: 12, rowVersion: '1', fecha: '2026-09-16', usuarioCode: 'admin' },
      ],
    })

    expect(preview.rows).toEqual([
      { label: 'Partes', value: '2' },
      { label: 'Tipo de tarea', value: 'DEV — Desarrollo' },
      { label: 'Sin cargo', value: 'Sí' },
      { label: 'Presencial', value: '(sin cambio)' },
      { label: 'Asistente', value: 'usr — Usuario' },
      { label: 'Fecha', value: '2026-09-16' },
      { label: 'Rango filtro', value: '2026-09-01 → 2026-09-30' },
    ])
    expect(preview.muestra).toEqual(['#11 2026-09-15 admin', '#12 2026-09-16 admin'])
  })
})
