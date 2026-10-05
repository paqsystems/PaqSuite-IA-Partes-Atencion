import { describe, it, expect } from 'vitest'
import { adminEmpresaToFormState } from './adminEmpresaForm'

describe('adminEmpresaToFormState', () => {
  it('usa nombreEmpresa y habilitada del contrato API', () => {
    expect(
      adminEmpresaToFormState({
        id: 1,
        nombreEmpresa: 'Partes Demo',
        habilitada: true,
        theme: 'generic.light',
      })
    ).toEqual({
      nombreEmpresa: 'Partes Demo',
      habilitada: true,
      theme: 'generic.light',
    })
  })
})
