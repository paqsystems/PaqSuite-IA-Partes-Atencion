import { Popup } from 'devextreme-react/popup'
import { useTranslation } from 'react-i18next'
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
  const { t } = useTranslation()
  const email = partes?.email?.trim() ? partes.email : '—'
  const tipoLabel =
    partes?.tipoFuncional === 'cliente'
      ? t('partes.profile.tipo.cliente')
      : partes?.tipoFuncional === 'asistente'
        ? t('partes.profile.tipo.asistente')
        : '—'

  return (
    <Popup
      visible={visible}
      onHiding={onHiding}
      dragEnabled={false}
      showCloseButton
      title={t('partes.profile.title')}
      width={420}
      height="auto"
    >
      <div data-testid="partesProfilePanel" style={{ padding: '8px 4px', display: 'grid', gap: 10 }}>
        <div>
          <strong>{t('partes.profile.tipoFuncional')}</strong> {tipoLabel}
        </div>
        <div>
          <strong>{t('partes.profile.codigo')}</strong> {partes?.code ?? '—'}
        </div>
        <div>
          <strong>{t('partes.profile.nombre')}</strong> {partes?.nombre ?? '—'}
        </div>
        {partes?.tipoFuncional === 'asistente' ? (
          <div>
            <strong>{t('partes.profile.supervisor')}</strong>{' '}
            {partes.esSupervisor ? t('partes.common.si') : t('partes.common.no')}
          </div>
        ) : null}
        <div>
          <strong>{t('partes.profile.email')}</strong> {email}
        </div>
        <div>
          <strong>{t('partes.profile.loginUsuario')}</strong> {loginUsuario || '—'}
        </div>
      </div>
    </Popup>
  )
}
