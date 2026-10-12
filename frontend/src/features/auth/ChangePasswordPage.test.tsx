import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { cleanup, render, screen } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import { MemoryRouter } from 'react-router-dom'
import i18n from '../../i18n/i18n'
import { ChangePasswordPage } from './ChangePasswordPage'

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    AuthCardLayout: ({
      children,
      title,
      testId,
    }: {
      children: ReactNode
      title: string
      testId?: string
    }) => (
      <div data-testid={testId}>
        <h1>{title}</h1>
        {children}
      </div>
    ),
    LanguageSelector: () => null,
  }
})

vi.mock('./authSessionStore', () => ({
  getAuthSession: () => ({
    user: { locale: 'es' },
    passwordComplejidad: 'simple',
    passwordLongitudMin: 8,
    firstLogin: false,
  }),
  patchAuthSession: vi.fn(),
}))

afterEach(() => {
  cleanup()
})

describe('ChangePasswordPage PA-12', () => {
  it('usa AuthCardLayout y claves i18n change.*', () => {
    render(
      <I18nextProvider i18n={i18n}>
        <MemoryRouter>
          <ChangePasswordPage />
        </MemoryRouter>
      </I18nextProvider>,
    )

    expect(screen.getByTestId('authChangePage')).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Cambiar contraseña' })).toBeInTheDocument()
    expect(screen.getByTestId('changePasswordActual')).toBeInTheDocument()
    expect(screen.getByTestId('changePassword')).toBeInTheDocument()
    expect(screen.getByTestId('changePasswordConfirmation')).toBeInTheDocument()
    expect(screen.getByTestId('changeSubmit')).toBeInTheDocument()
  })
})
