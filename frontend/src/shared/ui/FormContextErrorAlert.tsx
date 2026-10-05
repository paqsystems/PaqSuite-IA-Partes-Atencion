type FormContextErrorAlertProps = {
  message: string | null
  testId: string
}

/**
 * Alerta de error en formularios modal (regla 36). Debe renderizarse dentro del Popup.
 */
export function FormContextErrorAlert({ message, testId }: FormContextErrorAlertProps) {
  if (!message) {
    return null
  }
  return (
    <div
      role="alert"
      data-testid={testId}
      style={{ color: 'var(--dx-color-danger, #d9534f)', fontWeight: 600 }}
    >
      {message}
    </div>
  )
}

/** Errores de listado: solo cuando no hay modal de carga abierto. */
export function shouldShowPageListError(formOpen: boolean, listError: string | null): boolean {
  return !formOpen && Boolean(listError)
}
