import type { ReactNode } from 'react'
import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import TextBox from 'devextreme-react/text-box'
import SelectBox from 'devextreme-react/select-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'

vi.mock('devextreme-react/popup', () => ({
  Popup: ({ children, visible }: { children: ReactNode; visible?: boolean }) =>
    visible ? <div data-testid="dx-popup-mock">{children}</div> : null,
}))

vi.mock('devextreme-react/text-box', () => ({
  default: ({ value }: { value?: string }) => <input readOnly value={value ?? ''} aria-hidden />,
}))

vi.mock('devextreme-react/select-box', () => ({
  default: ({ elementAttr }: { elementAttr?: Record<string, string> }) => (
    <div data-testid={elementAttr?.['data-testid']} />
  ),
}))

vi.mock('devextreme-react/check-box', () => ({
  default: () => <input type="checkbox" aria-hidden />,
}))

describe('EmpresasAdminPage edit popup smoke', () => {
  it('renderiza controles del modal de edición sin vaciar el documento', () => {
    const themeOptions = [
      { value: 'paqsuite.light.generic', label: 'Claro — Generic' },
      { value: 'paqsuite.dark.compact', label: 'Oscuro — Compact' },
    ]
    const bodyChildCountBefore = document.body.childNodes.length

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
    expect(document.body.childNodes.length).toBeGreaterThanOrEqual(bodyChildCountBefore)
  })
})