import LoadIndicator from 'devextreme-react/load-indicator'
import Popup from 'devextreme-react/popup'
import { DialogActionButton } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { formatMasivoIsoDate } from './formatMasivoIsoDate'
import { masivoRecipientInitials } from './masivoRecipientInitials'
import type { MasivoInformarDestinatario } from './partesMasivoApi'
import './MasivoInformarConfirmDialog.css'

const MAX_VISIBLE_RECIPIENTS = 20

type Props = {
  visible: boolean
  loading: boolean
  submitting: boolean
  error: string | null
  destinatarioCount: number
  destinatarios: MasivoInformarDestinatario[]
  fechaDesde: string
  fechaHasta: string
  onCancel: () => void
  onSend: () => void
}

export function MasivoInformarConfirmDialog({
  visible,
  loading,
  submitting,
  error,
  destinatarioCount,
  destinatarios,
  fechaDesde,
  fechaHasta,
  onCancel,
  onSend,
}: Props) {
  const { t } = useTranslation()
  const hiddenCount = Math.max(0, destinatarios.length - MAX_VISIBLE_RECIPIENTS)
  const visibleRecipients = destinatarios.slice(0, MAX_VISIBLE_RECIPIENTS)
  const busy = loading || submitting
  const canSend = !busy && destinatarioCount > 0

  return (
    <Popup
      visible={visible}
      onHiding={onCancel}
      title={t('partes.masivo.mailInformarClientes.confirmTitle')}
      showCloseButton={!submitting}
      width={560}
      height="auto"
      maxHeight="80vh"
      wrapperAttr={{ 'data-testid': 'partesMasivoInformarConfirmDialog' }}
    >
      <div className="pqMasivoInformar">
        {error ? (
          <div role="alert" data-testid="partesMasivoInformarError" className="pqMasivoInformarError">
            {error}
          </div>
        ) : null}

        {loading ? (
          <div className="pqMasivoInformarLoading" data-testid="partesMasivoInformarLoading">
            <LoadIndicator height={28} width={28} />
            <span>{t('partes.masivo.mailInformarClientes.loading')}</span>
          </div>
        ) : destinatarioCount > 0 ? (
          <>
            <div className="pqMasivoInformarHero" data-testid="partesMasivoInformarConfirmBody">
              <i className="dx-icon-email pqMasivoInformarHeroIcon" aria-hidden />
              <div className="pqMasivoInformarCountRow">
                <span className="pqMasivoInformarCount">{destinatarioCount}</span>
                <span className="pqMasivoInformarCountLabel">
                  {' '}
                  {t('partes.masivo.mailInformarClientes.countLabel', { count: destinatarioCount })}
                </span>
              </div>
            </div>

            <div className="pqMasivoInformarPeriod" data-testid="partesMasivoInformarPeriod">
              <strong>{t('partes.masivo.mailInformarClientes.periodLabel')}</strong>
              <span>
                {formatMasivoIsoDate(fechaDesde)} → {formatMasivoIsoDate(fechaHasta)}
              </span>
            </div>

            {visibleRecipients.length > 0 ? (
              <div className="pqMasivoInformarRecipients">
                <strong className="pqMasivoInformarRecipientsTitle">
                  {t('partes.masivo.mailInformarClientes.recipientsTitle')}
                </strong>
                <div
                  className="pqMasivoInformarRecipientList"
                  data-testid="partesMasivoInformarRecipientList"
                >
                  {visibleRecipients.map((item) => {
                    const displayName = item.clienteNombre || item.nombre
                    return (
                      <div
                        key={`${item.clienteId}-${item.email}`}
                        className="pqMasivoInformarRecipient"
                        data-testid={`partesMasivoInformarRecipient-${item.clienteId}`}
                      >
                        <div className="pqMasivoInformarAvatar" aria-hidden>
                          {masivoRecipientInitials(displayName)}
                        </div>
                        <div className="pqMasivoInformarRecipientMeta">
                          <div className="pqMasivoInformarRecipientName">{displayName}</div>
                          <div className="pqMasivoInformarRecipientEmail">{item.email}</div>
                        </div>
                      </div>
                    )
                  })}
                </div>
                {hiddenCount > 0 ? (
                  <span className="pqMasivoInformarMore">
                    {t('partes.masivo.mailInformarClientes.moreRecipients', { count: hiddenCount })}
                  </span>
                ) : null}
              </div>
            ) : null}
          </>
        ) : (
          <p className="pqMasivoInformarEmpty" data-testid="partesMasivoInformarConfirmBody">
            {t('partes.masivo.mailInformarClientes.empty')}
          </p>
        )}

        <div className="pqMasivoInformarActions">
          <DialogActionButton
            text={t('parametros.modal.cancel')}
            stylingMode="outlined"
            useSubmitBehavior={false}
            disabled={submitting}
            onClick={onCancel}
            elementAttr={{ 'data-testid': 'partesMasivoInformarCancel' }}
          />
          <DialogActionButton
            text={t('partes.masivo.mailInformarClientes.confirmSend')}
            type="default"
            stylingMode="contained"
            useSubmitBehavior={false}
            disabled={!canSend}
            onClick={onSend}
            elementAttr={{ 'data-testid': 'partesMasivoInformarOk' }}
          />
        </div>
      </div>
    </Popup>
  )
}
