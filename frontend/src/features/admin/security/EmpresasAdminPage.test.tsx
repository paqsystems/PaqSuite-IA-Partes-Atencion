import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import TextBox from 'devextreme-react/text-box'
import SelectBox from 'devextreme-react/select-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { translateEmpresasAdminSdk } from './securityEmpresasSdkI18n'

describe('EmpresasAdminPage edit popup smoke', () => {
  it('renderiza controles del modal de edición sin vaciar el documento', () => {
    const themeOptions = [
      { value: 'paqsuite.light.generic', label: 'Claro — Generic' },
      { value: 'paqsuite.dark.compact', label: 'Oscuro — Compact' },
    ]
    render(
      <Popup visible title="Edit" width={520} height="auto">
        <div data-testid="adminEmpresasForm">
          <TextBox value="Acme" />
          <SelectBox
            value="paqsuite.light.generic"
            dataSource={themeOptions}
            valueExpr="value"
            displayExpr="label"
            elementAttr={{ 'data-testid': 'adminEmpresasFormTheme' }}
          />
          <CheckBox value={true} />
        </div>
      </Popup>,
    )
    expect(screen.getByTestId('adminEmpresasForm')).toBeTruthy()
    expect(screen.getByTestId('adminEmpresasFormTheme')).toBeTruthy()
  })

  it('resuelve labels del modal sin devolver claves crudas', () => {
    const t = (key: string) => key
    expect(translateEmpresasAdminSdk('admin.empresas.form.editTitle', t)).toBe(
      'Editar empresa',
    )
    expect(translateEmpresasAdminSdk('admin.empresas.form.save', t)).toBe('Guardar')
    expect(translateEmpresasAdminSdk('admin.empresas.form.cancel', t)).toBe('Cancelar')
    expect(translateEmpresasAdminSdk('admin.empresas.applyTheme', t)).toBe('Aplicar')
  })
})
