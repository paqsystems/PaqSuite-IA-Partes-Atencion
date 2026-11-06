import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { cleanup, render, screen } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import i18n from '../../../i18n/i18n'
import { NotifyUsuarioAccessDialog } from './NotifyUsuarioAccessDialog'
import type { AdminUsuario } from './adminSecurityApi'

vi.mock('devextreme-react/popup', () => ({
  Popup: ({
    children,
    visible,
    wrapperAttr,
  }: {
    children: ReactNode
    visible: boolean
    wrapperAttr?: Record<string, string>
  }) => (visible ? <div data-testid={wrapperAttr?.['data-testid']}>{children}</div> : null),
}))

vi.mock('devextreme-react/select-box', () => ({
  default: ({
    elementAttr,
    disabled,
  }: {
    elementAttr?: Record<string, string>
    disabled?: boolean
  }) => <input data-testid={elementAttr?.['data-testid']} disabled={disabled} />,
}))

vi.mock('devextreme-react/button', () => ({
  default: ({
    text,
    onClick,
    elementAttr,
    disabled,
  }: {
    text: string
    onClick?: () => void
    elementAttr?: Record<string, string>
    disabled?: boolean
  }) => (
    <button type="button" data-testid={elementAttr?.['data-testid']} onClick={onClick} disabled={disabled}>
      {text}
    </button>
  ),
}))

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    isNativeApp: () => false,
    DialogActionButton: ({
      text,
      onClick,
      disabled,
      elementAttr,
    }: {
      text: string
      onClick?: () => void
      disabled?: boolean
      elementAttr?: Record<string, string>
    }) => (
      <button
        type="button"
        data-testid={elementAttr?.['data-testid']}
        onClick={onClick}
        disabled={disabled}
      >
        {text}
      </button>
    ),
  }
})

vi.mock('./adminSecurityApi', () => ({
  listAdminUsuariosFull: vi.fn(async () => ({
    kind: 'ok' as const,
    data: {
      items: [
        {
          id: 1,
          usuario: 'conmail',
          nombre: 'Con Mail',
          email: 'conmail@partes.local',
          activo: true,
          inhabilitado: false,
        } satisfies AdminUsuario,
        {
          id: 2,
          usuario: 'sinmail',
          nombre: 'Sin Mail',
          email: '',
          activo: true,
          inhabilitado: false,
        } satisfies AdminUsuario,
      ],
    },
  })),
  notifyAdminUsuarioAccess: vi.fn(),
}))

afterEach(() => {
  cleanup()
})

describe('NotifyUsuarioAccessDialog', () => {
  it('muestra el error en el popup y el envío deshabilitado sin usuario', async () => {
    render(
      <I18nextProvider i18n={i18n}>
        <NotifyUsuarioAccessDialog
          visible
          onClose={() => undefined}
        />
      </I18nextProvider>,
    )

    expect(await screen.findByTestId('adminUsuariosNotifyDialog')).toBeTruthy()
    expect(screen.getByTestId('adminUsuariosNotifyUser')).toBeTruthy()
    expect(screen.getByTestId('adminUsuariosNotifySend')).toBeDisabled()
    expect(screen.queryByTestId('adminUsuariosNotifyFormError')).toBeNull()
  })
})
