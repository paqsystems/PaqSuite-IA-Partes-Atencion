import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { cleanup, render, screen } from '@testing-library/react'
import { I18nextProvider } from 'react-i18next'
import i18n from '../../../i18n/i18n'
import { MasivoInformarConfirmDialog } from './MasivoInformarConfirmDialog'
import type { MasivoInformarDestinatario } from './partesMasivoApi'

vi.mock('devextreme-react/popup', () => ({
  default: ({
    children,
    visible,
    wrapperAttr,
  }: {
    children: ReactNode
    visible: boolean
    wrapperAttr?: Record<string, string>
  }) => (visible ? <div data-testid={wrapperAttr?.['data-testid']}>{children}</div> : null),
}))

vi.mock('devextreme-react/load-indicator', () => ({
  default: () => <div data-testid="load-indicator" />,
}))

vi.mock('@paqsuite/react-core', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@paqsuite/react-core')>()
  return {
    ...actual,
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

afterEach(() => {
  cleanup()
})

function recipient(index: number): MasivoInformarDestinatario {
  return {
    clienteId: index,
    clienteCode: `C${index}`,
    clienteNombre: `Cliente ${index}`,
    email: `cliente${index}@paqsystems.com.ar`,
    nombre: `Usuario ${index}`,
    codigo: `U${index}`,
  }
}

function renderDialog(destinatarios: MasivoInformarDestinatario[]) {
  return render(
    <I18nextProvider i18n={i18n}>
      <MasivoInformarConfirmDialog
        visible
        loading={false}
        submitting={false}
        error={null}
        destinatarioCount={destinatarios.length}
        destinatarios={destinatarios}
        fechaDesde="2026-01-01"
        fechaHasta="2026-11-06"
        onCancel={() => undefined}
        onSend={() => undefined}
      />
    </I18nextProvider>,
  )
}

describe('MasivoInformarConfirmDialog', () => {
  it('mantiene todas las tarjetas de destinatarios sin recortar la lista', () => {
    const destinatarios = Array.from({ length: 7 }, (_, index) => recipient(index + 1))
    renderDialog(destinatarios)

    const list = screen.getByTestId('partesMasivoInformarRecipientList')
    expect(list.querySelectorAll('.pqMasivoInformarRecipient')).toHaveLength(7)
    expect(screen.getByText('Cliente 1')).toBeInTheDocument()
    expect(screen.getByText('Cliente 7')).toBeInTheDocument()
    expect(screen.queryByText(/más$/i)).not.toBeInTheDocument()
  })
})
