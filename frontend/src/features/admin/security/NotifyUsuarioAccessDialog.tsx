import { useCallback, useEffect, useMemo, useState } from 'react'
import Button from 'devextreme-react/button'
import { Popup } from 'devextreme-react/popup'
import SelectBox from 'devextreme-react/select-box'
import { DialogActionButton, isNativeApp } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import {
  listAdminUsuariosFull,
  notifyAdminUsuarioAccess,
  type AdminUsuario,
} from './adminSecurityApi'

const formRowStyle = {
  display: 'grid',
  gridTemplateColumns: '140px 1fr',
  gap: 8,
  alignItems: 'center',
  marginBottom: 12,
} as const

function userHasEmail(user: AdminUsuario | null): boolean {
  return Boolean(user?.email && user.email.trim() !== '')
}

function userDisplay(user: AdminUsuario | null | undefined): string {
  if (!user) {
    return ''
  }
  const code = user.usuario || user.codigo || ''
  return user.nombre ? `${code} — ${user.nombre}` : code
}

export function NotifyUsuarioAccessToolbar() {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)

  if (isNativeApp()) {
    return null
  }

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 8 }}>
        <Button
          text={t('partes.notifyAccess.open')}
          stylingMode="outlined"
          onClick={() => setOpen(true)}
          elementAttr={{ 'data-testid': 'adminUsuariosNotify' }}
        />
      </div>
      <NotifyUsuarioAccessDialog visible={open} onClose={() => setOpen(false)} />
    </>
  )
}

type DialogProps = {
  visible: boolean
  onClose: () => void
}

export function NotifyUsuarioAccessDialog({ visible, onClose }: DialogProps) {
  const { t } = useTranslation()
  const [users, setUsers] = useState<AdminUsuario[]>([])
  const [selectedId, setSelectedId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [formError, setFormError] = useState<string | null>(null)

  const selected = useMemo(
    () => users.find((item) => item.id === selectedId) ?? null,
    [users, selectedId],
  )
  const canSend = userHasEmail(selected) && !submitting && !loading

  const loadUsers = useCallback(async () => {
    setLoading(true)
    setFormError(null)
    try {
      const result = await listAdminUsuariosFull('0')
      if (result.kind === 'ok') {
        setUsers(result.data.items ?? [])
        return
      }
      if (result.kind === 'envelopeError') {
        setFormError(resolveAuthMessage(result.envelope.respuesta))
        return
      }
      setFormError(resolveAuthMessage(result.i18nKey || 'infra.transport'))
    } catch {
      setFormError(resolveAuthMessage('infra.unexpected'))
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    if (!visible) {
      return
    }
    setSelectedId(null)
    setFormError(null)
    void loadUsers()
  }, [visible, loadUsers])

  function handleClose() {
    if (submitting) {
      return
    }
    setFormError(null)
    setSelectedId(null)
    onClose()
  }

  async function handleSend() {
    if (!selected) {
      setFormError(t('partes.notifyAccess.userRequired'))
      return
    }
    if (!userHasEmail(selected)) {
      setFormError(resolveAuthMessage('admin.usuarios.notify.emailRequired'))
      return
    }
    setSubmitting(true)
    setFormError(null)
    try {
      const result = await notifyAdminUsuarioAccess(selected.id)
      if (result.kind === 'ok') {
        setFormError(null)
        setSelectedId(null)
        onClose()
        return
      }
      if (result.kind === 'envelopeError') {
        setFormError(resolveAuthMessage(result.envelope.respuesta))
        return
      }
      setFormError(resolveAuthMessage(result.i18nKey || 'infra.transport'))
    } catch {
      setFormError(resolveAuthMessage('infra.unexpected'))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Popup
      visible={visible}
      onHiding={handleClose}
      title={t('partes.notifyAccess.title')}
      showCloseButton={!submitting}
      width={520}
      height="auto"
      wrapperAttr={{ 'data-testid': 'adminUsuariosNotifyDialog' }}
    >
      <div>
        {formError ? (
          <div role="alert" data-testid="adminUsuariosNotifyFormError" style={{ marginBottom: 12 }}>
            {formError}
          </div>
        ) : null}

        <p data-testid="adminUsuariosNotifyHint">{t('partes.notifyAccess.hint')}</p>

        <div style={formRowStyle}>
          <label htmlFor="adminUsuariosNotifyUser">{t('partes.notifyAccess.userLabel')}</label>
          <SelectBox
            inputAttr={{ id: 'adminUsuariosNotifyUser' }}
            dataSource={users}
            value={selectedId}
            valueExpr="id"
            displayExpr={userDisplay}
            searchEnabled
            disabled={loading || submitting}
            placeholder={t('partes.notifyAccess.userPlaceholder')}
            onValueChanged={(e) => {
              setSelectedId(typeof e.value === 'number' ? e.value : null)
              setFormError(null)
            }}
            elementAttr={{ 'data-testid': 'adminUsuariosNotifyUser' }}
          />
        </div>

        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
          <DialogActionButton
            text={t('partes.notifyAccess.cancel')}
            stylingMode="outlined"
            useSubmitBehavior={false}
            disabled={submitting}
            onClick={handleClose}
            elementAttr={{ 'data-testid': 'adminUsuariosNotifyCancel' }}
          />
          <DialogActionButton
            text={t('partes.notifyAccess.confirm')}
            type="default"
            stylingMode="contained"
            useSubmitBehavior={false}
            disabled={!canSend}
            onClick={() => void handleSend()}
            elementAttr={{ 'data-testid': 'adminUsuariosNotifySend' }}
          />
        </div>
      </div>
    </Popup>
  )
}
