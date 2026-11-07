import type { ReactNode } from 'react'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import i18n from '../../../i18n/i18n'
import { PartesUsuariosFormPopup } from './partesUsuariosFormPopup'
import type { UsuarioListItem } from '@paqsuite/react-core'

const notifyMock = vi.fn()
const apiRequestMock = vi.fn()
const updateUsuarioMock = vi.fn()

vi.mock('./adminSecurityApi', () => ({
  notifyAdminUsuarioAccess: (...args: unknown[]) => notifyMock(...args),
}))

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    apiRequest: (...args: unknown[]) => apiRequestMock(...args),
    updateUsuario: (...args: unknown[]) => updateUsuarioMock(...args),
  }
})

vi.mock('devextreme-react/popup', () => ({
  default: ({ children, visible }: { children: ReactNode; visible: boolean }) =>
    visible ? <div data-testid="popup">{children}</div> : null,
}))

vi.mock('devextreme-react/text-box', () => ({
  default: ({
    label,
    value,
    onValueChanged,
    inputAttr,
  }: {
    label?: string
    value?: string
    onValueChanged?: (e: { value?: string }) => void
    inputAttr?: Record<string, string>
  }) => (
    <label>
      {label}
      <input
        aria-label={label}
        value={value ?? ''}
        data-testid={inputAttr?.['data-testid']}
        onChange={(e) => onValueChanged?.({ value: e.target.value })}
      />
    </label>
  ),
}))

vi.mock('devextreme-react/check-box', () => ({
  default: ({ text }: { text?: string }) => <span>{text}</span>,
}))

vi.mock('devextreme-react/button', () => ({
  default: ({
    disabled,
    onClick,
    elementAttr,
    hint,
  }: {
    disabled?: boolean
    onClick?: () => void
    elementAttr?: Record<string, string>
    hint?: string
  }) => (
    <button
      type="button"
      title={hint}
      disabled={disabled}
      data-testid={elementAttr?.['data-testid']}
      onClick={onClick}
    >
      notify
    </button>
  ),
}))

function wrap(ui: ReactNode) {
  return render(<I18nextProvider i18n={i18n}>{ui}</I18nextProvider>)
}

const editUser: UsuarioListItem = {
  id: 9,
  usuario: 'u9',
  name: 'User 9',
  email: 'u9@test.local',
  activo: true,
  inhabilitado: false,
}

describe('PartesUsuariosFormPopup CC #16', () => {
  beforeEach(() => {
    notifyMock.mockReset()
    apiRequestMock.mockReset()
    updateUsuarioMock.mockReset()
  })

  it('en edición muestra ícono notify deshabilitado sin email', () => {
    wrap(
      <PartesUsuariosFormPopup
        visible
        mode="edit"
        initial={{ ...editUser, email: '' }}
        usuarioCodigoEditable={false}
        onCancel={() => undefined}
        onSaved={() => undefined}
      />,
    )
    expect(screen.getByTestId('adminUsuariosNotifyEmail')).toBeDisabled()
  })

  it('en edición habilita ícono con email', () => {
    wrap(
      <PartesUsuariosFormPopup
        visible
        mode="edit"
        initial={editUser}
        usuarioCodigoEditable={false}
        onCancel={() => undefined}
        onSaved={() => undefined}
      />,
    )
    expect(screen.getByTestId('adminUsuariosNotifyEmail')).not.toBeDisabled()
  })

  it('en alta no renderiza campo contraseña', () => {
    wrap(
      <PartesUsuariosFormPopup
        visible
        mode="create"
        initial={null}
        usuarioCodigoEditable
        onCancel={() => undefined}
        onSaved={() => undefined}
      />,
    )
    expect(screen.queryByLabelText(/contraseña|password/i)).toBeNull()
  })

  it('tras alta exitosa encadena notify-access', async () => {
    const onSaved = vi.fn()
    apiRequestMock.mockResolvedValueOnce({
      kind: 'ok',
      envelope: { error: 0, respuesta: 'ok', resultado: { item: { id: 77, usuario: 'n', name: 'N', email: 'n@t.local', activo: true, inhabilitado: false } } },
    })
    notifyMock.mockResolvedValueOnce({ kind: 'ok', envelope: { error: 0, respuesta: 'ok', resultado: { item: { id: 77, notified: true } } } })

    wrap(
      <PartesUsuariosFormPopup
        visible
        mode="create"
        initial={null}
        usuarioCodigoEditable
        onCancel={() => undefined}
        onSaved={onSaved}
      />,
    )

    fireEvent.change(screen.getByTestId('admin.usuarios.codigo'), { target: { value: 'newu' } })
    fireEvent.change(screen.getByLabelText('Nombre'), { target: { value: 'New User' } })
    fireEvent.change(screen.getByLabelText('Email'), { target: { value: 'n@t.local' } })
    fireEvent.click(screen.getByTestId('admin.usuarios.save'))

    await waitFor(() => {
      expect(apiRequestMock).toHaveBeenCalled()
      expect(notifyMock).toHaveBeenCalledWith(77)
      expect(onSaved).toHaveBeenCalled()
    })
  })
})
