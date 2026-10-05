import { afterEach, describe, expect, it, vi } from 'vitest'
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import i18n from '../../../i18n/i18n'
import { UsuariosAdminPage } from './UsuariosAdminPage'
import * as adminSecurityApi from './adminSecurityApi'

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    ProcessDataGrid: ({ children, onCreate }: { children: React.ReactNode; onCreate?: () => void }) => (
      <div>
        <button type="button" data-testid="adminUsuariosAdd" onClick={() => onCreate?.()}>
          add
        </button>
        {children}
      </div>
    ),
  }
})

vi.mock('devextreme/ui/dialog', () => ({
  confirm: vi.fn(() => Promise.resolve(true)),
}))

vi.mock('../../auth/authSessionStore', () => ({
  getAuthToken: () => 'token',
}))

vi.mock('../../auth/platformContext', () => ({
  buildAuthPlatformHeaders: () => ({}),
}))

afterEach(() => {
  cleanup()
  vi.restoreAllMocks()
})

function renderPage() {
  return render(
    <I18nextProvider i18n={i18n}>
      <UsuariosAdminPage />
    </I18nextProvider>,
  )
}

describe('UsuariosAdminPage errores en modal', () => {
  it('muestra error de contraseña dentro del formulario y no en el listado', async () => {
    vi.spyOn(adminSecurityApi, 'listAdminUsuariosFull').mockResolvedValue({
      kind: 'ok',
      envelope: { error: false, respuesta: '', resultado: { items: [] } },
    })
    vi.spyOn(adminSecurityApi, 'createAdminUsuario').mockResolvedValue({
      kind: 'envelopeError',
      envelope: { error: true, respuesta: 'auth.password.policyUnsafe', resultado: {} },
    })

    renderPage()

    await waitFor(() => {
      expect(screen.getByTestId('adminUsuariosAdd')).toBeInTheDocument()
    })

    fireEvent.click(screen.getByTestId('adminUsuariosAdd'))

    await waitFor(() => {
      expect(screen.getByTestId('adminUsuariosForm')).toBeInTheDocument()
    })

    fireEvent.click(screen.getByTestId('adminUsuariosFormSave'))

    await waitFor(() => {
      expect(screen.getByTestId('adminUsuariosFormError')).toBeInTheDocument()
    })

    expect(screen.queryByTestId('adminUsuariosListError')).not.toBeInTheDocument()
  })
})
