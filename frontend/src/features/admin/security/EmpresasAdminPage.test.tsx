import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import TextBox from 'devextreme-react/text-box'
import SelectBox from 'devextreme-react/select-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { getEmpresaThemeOptions } from './empresaThemeCatalog'

describe('EmpresasAdminPage edit popup smoke', () => {
  it('renderiza controles del modal de edición sin vaciar el documento', () => {
    const themeOptions = getEmpresaThemeOptions()
    render(
      <Popup visible title="Edit" width={520} height="auto">
        <div data-testid="adminEmpresasForm">
          <TextBox value="Acme" />
          <SelectBox
            value="generic.light"
            dataSource={themeOptions}
            valueExpr="value"
            displayExpr="label"
            elementAttr={{ 'data-testid': 'adminEmpresasFormTheme' }}
          />
          <CheckBox value={true} />
        </div>
      </Popup>
    )
    expect(screen.getByTestId('adminEmpresasForm')).toBeTruthy()
    expect(screen.getByTestId('adminEmpresasFormTheme')).toBeTruthy()
  })
})
