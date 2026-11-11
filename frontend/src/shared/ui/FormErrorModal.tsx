import { DialogActionButton } from '@paqsuite/react-core'
import { Popup } from 'devextreme-react/popup'

type FormErrorModalProps = {
  visible: boolean
  title: string
  message: string | null
  closeLabel: string
  testId: string
  onClose: () => void
}

/**
 * Diálogo modal de error de formulario (SPEC-001 / regla 36, vía Popup DX).
 * No debe renderizarse el mismo mensaje en la pantalla de listado.
 */
export function FormErrorModal({
  visible,
  title,
  message,
  closeLabel,
  testId,
  onClose,
}: FormErrorModalProps) {
  if (!message) {
    return null
  }

  return (
    <Popup
      visible={visible}
      title={title}
      showCloseButton
      dragEnabled={false}
      hideOnOutsideClick
      shading
      zIndex={10000}
      width={440}
      height="auto"
      maxWidth="90vw"
      wrapperAttr={{ class: 'pqFormErrorModal', 'data-testid': `${testId}Dialog` }}
      onHiding={onClose}
    >
      <div style={{ display: 'grid', gap: 12, padding: 8 }}>
        <div
          role="alert"
          data-testid={testId}
          style={{ color: 'var(--dx-color-danger, #d9534f)', fontWeight: 600, whiteSpace: 'pre-line' }}
        >
          {message}
        </div>
        <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
          <DialogActionButton
            text={closeLabel}
            type="default"
            onClick={onClose}
            elementAttr={{ 'data-testid': `${testId}Close` }}
          />
        </div>
      </div>
    </Popup>
  )
}
