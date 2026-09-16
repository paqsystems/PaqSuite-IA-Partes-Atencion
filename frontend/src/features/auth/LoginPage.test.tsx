import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { cleanup, render, screen } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import { MemoryRouter } from 'react-router-dom'
import { isNativeApp } from '@paqsuite/react-core'
import i18n from '../../i18n/i18n'
import { LoginPage } from './LoginPage'

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
    isNativeApp: vi.fn(() => false),
    AuthLoginLayout: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    LanguageSelector: () => null,
  }
})

vi.mock('../partes/mobile/PartesMobileConfigHost', () => ({
  PartesMobileConfigHost: () => null,
}))

afterEach(() => {
  cleanup()
  vi.mocked(isNativeApp).mockReturnValue(false)
})

function renderLogin() {
  return render(
    <I18nextProvider i18n={i18n}>
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>
    </I18nextProvider>,
  )
}

describe('LoginPage tenant field', () => {
  it('no muestra empresa en web', () => {
    vi.mocked(isNativeApp).mockReturnValue(false)
    renderLogin()
    expect(screen.queryByTestId('loginTenant')).not.toBeInTheDocument()
    expect(screen.getByTestId('loginUsuario')).toBeInTheDocument()
  })

  it('muestra empresa en native', () => {
    vi.mocked(isNativeApp).mockReturnValue(true)
    renderLogin()
    expect(screen.getByTestId('loginTenant')).toBeInTheDocument()
  })
})
