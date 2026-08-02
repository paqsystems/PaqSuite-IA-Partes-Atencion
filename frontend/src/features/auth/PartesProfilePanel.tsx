import { Popup } from 'devextreme-react/popup'
import type { PartesSessionContext } from './authTypes'

type PartesProfilePanelProps = {
  visible: boolean
  onHiding: () => void
  partes: PartesSessionContext | undefined
  loginUsuario: string
}

export function PartesProfilePanel({
  visible,
  onHiding,
  partes,
  loginUsuario,
}: PartesProfilePanelProps) {
  const email = partes?.email?.trim() ? partes.email : '—'
  const tipoLabel =
    partes?.tipoFuncional === 'cliente'
      ? 'Cliente'
      : partes?.tipoFuncional === 'asistente'
        ? 'Asistente'
        : '—'

  return (
    <Popup
      visible={visible}
      onHiding={onHiding}
      dragEnabled={false}
      showCloseButton
      title="Perfil Partes"
      width={420}
      height="auto"
    >
      <div data-testid="partesProfilePanel" style={{ padding: '8px 4px', display: 'grid', gap: 10 }}>
        <div>
          <strong>Tipo funcional:</strong> {tipoLabel}
        </div>
        <div>
          <strong>Código:</strong> {partes?.code ?? '—'}
        </div>
        <div>
          <strong>Nombre:</strong> {partes?.nombre ?? '—'}
        </div>
        {partes?.tipoFuncional === 'asistente' ? (
          <div>
            <strong>Supervisor:</strong> {partes.esSupervisor ? 'Sí' : 'No'}
          </div>
        ) : null}
        <div>
          <strong>Email:</strong> {email}
        </div>
        <div>
          <strong>Usuario login:</strong> {loginUsuario || '—'}
        </div>
      </div>
    </Popup>
  )
}
