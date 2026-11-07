import { useEffect, useMemo, useState } from 'react'
import Popup from 'devextreme-react/popup'
import TextBox from 'devextreme-react/text-box'
import CheckBox from 'devextreme-react/check-box'
import Button from 'devextreme-react/button'
import {
  DialogActionButton,
  apiRequest,
  updateUsuario,
  type UsuariosAdminRequestOptions,
  type UsuarioListItem,
} from '@paqsuite/react-core'
import type { ApiClientResult } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import { notifyAdminUsuarioAccess } from './adminSecurityApi'

function resolveUsuariosFormSaveErrorKey(result: ApiClientResult<unknown>): string {
  if (result.kind === 'transportError') {
    return result.i18nKey
  }
  if (result.kind !== 'envelopeError') {
    return 'admin.usuarios.form.saveError'
  }
  const nested =
    result.envelope.resultado &&
    typeof result.envelope.resultado === 'object' &&
    !Array.isArray(result.envelope.resultado)
      ? (result.envelope.resultado as { respuesta?: string }).respuesta
      : null
  if (typeof nested === 'string' && nested.startsWith('auth.password.')) {
    return 'admin.usuarios.validation.passwordPolicy'
  }
  if (result.envelope.respuesta.startsWith('auth.password.')) {
    return 'admin.usuarios.validation.passwordPolicy'
  }
  if (result.envelope.respuesta === 'validation.failed') {
    return 'admin.usuarios.validation.failed'
  }
  if (result.envelope.respuesta.startsWith('admin.usuarios.')) {
    return result.envelope.respuesta
  }
  return result.envelope.respuesta || 'admin.usuarios.form.saveError'
}

export type PartesUsuariosFormPopupProps = {
  visible: boolean
  mode: 'create' | 'edit'
  initial: UsuarioListItem | null
  usuarioCodigoEditable: boolean
  onCancel: () => void
  onSaved: () => void
  requestOptions?: UsuariosAdminRequestOptions
  t?: (key: string) => string
}

export function PartesUsuariosFormPopup({
  visible,
  mode,
  initial,
  usuarioCodigoEditable,
  onCancel,
  onSaved,
  requestOptions,
  t,
}: PartesUsuariosFormPopupProps) {
  const { t: i18nT } = useTranslation()
  const translate = t ?? ((key: string) => i18nT(key, { defaultValue: key }))
  const [usuario, setUsuario] = useState('')
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [activo, setActivo] = useState(true)
  const [inhabilitado, setInhabilitado] = useState(false)
  const [formError, setFormError] = useState<string | null>(null)
  const [notifyError, setNotifyError] = useState<string | null>(null)
  const [saving, setSaving] = useState(false)
  const [notifying, setNotifying] = useState(false)
  const [persistedId, setPersistedId] = useState<number | null>(null)

  const codigoReadOnly = mode === 'edit' && !usuarioCodigoEditable
  const effectiveId = mode === 'edit' ? initial?.id ?? persistedId : persistedId
  const trimmedEmail = email.trim()
  const canNotifyEmail = trimmedEmail !== '' && effectiveId !== null

  useEffect(() => {
    if (!visible) {
      return
    }
    setFormError(null)
    setNotifyError(null)
    setPersistedId(null)
    if (mode === 'edit' && initial) {
      setUsuario(initial.usuario)
      setName(initial.name)
      setEmail(initial.email)
      setActivo(initial.activo)
      setInhabilitado(initial.inhabilitado)
      return
    }
    setUsuario('')
    setName('')
    setEmail('')
    setActivo(true)
    setInhabilitado(false)
  }, [visible, mode, initial])

  const title = useMemo(
    () =>
      mode === 'create'
        ? translate('admin.usuarios.form.createTitle')
        : translate('admin.usuarios.form.editTitle'),
    [mode, translate],
  )

  async function runNotify(userId: number): Promise<boolean> {
    setNotifyError(null)
    if (trimmedEmail === '') {
      setNotifyError(resolveAuthMessage('admin.usuarios.notify.emailRequired'))
      return false
    }
    setNotifying(true)
    try {
      const result = await notifyAdminUsuarioAccess(userId)
      if (result.kind === 'ok') {
        return true
      }
      const message =
        result.kind === 'envelopeError'
          ? resolveAuthMessage(result.envelope.respuesta)
          : resolveAuthMessage(result.i18nKey || 'infra.transport')
      setNotifyError(message)
      return false
    } catch {
      setNotifyError(resolveAuthMessage('infra.unexpected'))
      return false
    } finally {
      setNotifying(false)
    }
  }

  const handleSave = async () => {
    setFormError(null)
    setNotifyError(null)
    const trimmedUsuario = usuario.trim()
    const trimmedName = name.trim()
    if (!trimmedUsuario) {
      setFormError(translate('admin.usuarios.validation.usuarioRequired'))
      return
    }
    if (!trimmedName) {
      setFormError(translate('admin.usuarios.validation.nameRequired'))
      return
    }
    if (!trimmedEmail) {
      setFormError(translate('admin.usuarios.validation.emailRequired'))
      return
    }

    setSaving(true)
    const result =
      mode === 'create'
        ? await apiRequest<{ item: UsuarioListItem }>('/api/v1/admin/usuarios', {
            method: 'POST',
            body: JSON.stringify({
              usuario: trimmedUsuario,
              name: trimmedName,
              email: trimmedEmail,
              activo,
            }),
            ...requestOptions,
          })
        : await updateUsuario(
            initial?.id ?? persistedId ?? 0,
            {
              ...(usuarioCodigoEditable ? { usuario: trimmedUsuario } : {}),
              name: trimmedName,
              email: trimmedEmail,
              activo,
              inhabilitado,
            },
            requestOptions,
          )
    setSaving(false)

    if (result.kind !== 'ok') {
      const errorKey = resolveUsuariosFormSaveErrorKey(result)
      setFormError(translate(errorKey))
      return
    }

    if (mode === 'create' && trimmedEmail) {
      const newId = result.envelope.resultado.item.id
      const notified = await runNotify(newId)
      if (!notified) {
        setPersistedId(newId)
        return
      }
    }

    onSaved()
  }

  const handleNotifyClick = async () => {
    if (!effectiveId || !canNotifyEmail) {
      return
    }
    const ok = await runNotify(effectiveId)
    if (ok) {
      onSaved()
    }
  }

  return (
    <Popup
      visible={visible}
      onHiding={onCancel}
      showTitle
      title={title}
      width={480}
      height="auto"
      dragEnabled={false}
    >
      <div data-testid="admin.usuarios.form">
        {formError ? (
          <div role="alert" data-testid="adminUsuariosFormError">
            {formError}
          </div>
        ) : null}
        {notifyError ? (
          <div role="alert" data-testid="adminUsuariosNotifyFormError">
            {notifyError}
          </div>
        ) : null}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12, marginTop: 8 }}>
          <TextBox
            label={translate('admin.usuarios.form.usuario')}
            value={usuario}
            readOnly={codigoReadOnly}
            onValueChanged={(e) => setUsuario(String(e.value ?? ''))}
            inputAttr={{ 'data-testid': 'admin.usuarios.codigo' }}
          />
          <TextBox
            label={translate('admin.usuarios.form.name')}
            value={name}
            onValueChanged={(e) => setName(String(e.value ?? ''))}
          />
          <div
            style={{
              display: 'grid',
              gridTemplateColumns: '1fr auto',
              gap: 8,
              alignItems: 'end',
            }}
          >
            <TextBox
              label={translate('admin.usuarios.form.email')}
              value={email}
              onValueChanged={(e) => setEmail(String(e.value ?? ''))}
            />
            {mode === 'edit' ? (
              <Button
                icon="email"
                stylingMode="outlined"
                hint={translate('partes.notifyAccess.emailIconHint')}
                disabled={!canNotifyEmail || saving || notifying}
                onClick={() => void handleNotifyClick()}
                elementAttr={{ 'data-testid': 'adminUsuariosNotifyEmail' }}
              />
            ) : null}
          </div>
          <CheckBox
            text={translate('admin.usuarios.form.activo')}
            value={activo}
            onValueChanged={(e) => setActivo(Boolean(e.value))}
          />
          {mode === 'edit' ? (
            <CheckBox
              text={translate('admin.usuarios.form.inhabilitado')}
              value={inhabilitado}
              onValueChanged={(e) => setInhabilitado(Boolean(e.value))}
            />
          ) : null}
          <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end', marginTop: 8 }}>
            <DialogActionButton text={translate('admin.usuarios.form.cancel')} onClick={onCancel} />
            <DialogActionButton
              text={translate('admin.usuarios.form.save')}
              type="default"
              disabled={saving || notifying}
              elementAttr={{ 'data-testid': 'admin.usuarios.save' }}
              onClick={() => void handleSave()}
            />
          </div>
        </div>
      </div>
    </Popup>
  )
}
