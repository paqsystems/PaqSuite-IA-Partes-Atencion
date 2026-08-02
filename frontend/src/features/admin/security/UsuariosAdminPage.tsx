import { useCallback, useEffect, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import {
  type AdminUsuario,
  createAdminUsuario,
  deleteAdminUsuario,
  listAdminUsuariosFull,
  updateAdminUsuario,
} from './adminSecurityApi'

type FormState = {
  usuario: string
  nombre: string
  email: string
  password: string
  activo: boolean
  inhabilitado: boolean
}

const emptyForm: FormState = {
  usuario: '',
  nombre: '',
  email: '',
  password: '',
  activo: true,
  inhabilitado: false,
}

export function UsuariosAdminPage() {
  const { t } = useTranslation()
  const [rows, setRows] = useState<AdminUsuario[]>([])
  const [loading, setLoading] = useState(true)
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<FormState>(emptyForm)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await listAdminUsuariosFull('0')
      if (result.kind === 'ok') {
        setRows(result.envelope.resultado.items ?? [])
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  function openCreate() {
    setEditingId(null)
    setForm(emptyForm)
    setFormOpen(true)
  }

  function openEdit(row: AdminUsuario) {
    setEditingId(row.id)
    setForm({
      usuario: row.usuario,
      nombre: row.nombre,
      email: row.email,
      password: '',
      activo: row.activo,
      inhabilitado: row.inhabilitado,
    })
    setFormOpen(true)
  }

  async function handleSave() {
    setError(null)
    if (editingId === null) {
      const result = await createAdminUsuario({
        usuario: form.usuario,
        nombre: form.nombre,
        email: form.email,
        password: form.password,
        activo: form.activo,
      })
      if (result.kind === 'ok') {
        setFormOpen(false)
        void load()
        return
      }
      if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
      return
    }

    const result = await updateAdminUsuario(editingId, {
      usuario: form.usuario,
      nombre: form.nombre,
      email: form.email,
      activo: form.activo,
      inhabilitado: form.inhabilitado,
    })
    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDisable(row: AdminUsuario) {
    const ok = await confirm(t('admin.usuarios.confirmDisable'), t('admin.usuarios.title'))
    if (!ok) {
      return
    }
    const result = await deleteAdminUsuario(row.id)
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="adminUsuariosPage" style={{ padding: 16 }}>
      <h2 style={{ margin: '0 0 12px' }}>{t('admin.usuarios.title')}</h2>
      {error ? <div role="alert">{error}</div> : null}
      <div data-testid="adminUsuariosGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.admin.usuarios"
          gridId="usuarios"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          onCreate={openCreate}
          createHint={t('admin.common.add')}
          createTestId="adminUsuariosAdd"
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="usuario" caption={t('admin.usuarios.field.usuario')} />
          <Column dataField="nombre" caption={t('admin.usuarios.field.nombre')} />
          <Column dataField="email" caption={t('admin.usuarios.field.email')} />
          <Column dataField="activo" caption={t('admin.common.activo')} dataType="boolean" />
          <Column dataField="inhabilitado" caption={t('admin.usuarios.field.inhabilitado')} dataType="boolean" />
          <Column
            type="buttons"
            buttons={[
              {
                hint: t('admin.common.edit'),
                icon: 'edit',
                onClick: (e) => openEdit(e.row?.data as AdminUsuario),
              },
              {
                hint: t('admin.usuarios.disable'),
                icon: 'trash',
                onClick: (e) => void handleDisable(e.row?.data as AdminUsuario),
              },
            ]}
          />
        </ProcessDataGrid>
      </div>

      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={editingId ? t('admin.usuarios.editTitle') : t('admin.usuarios.newTitle')}
        width={420}
        height="auto"
        showCloseButton
      >
        <div style={{ display: 'grid', gap: 12, padding: 8 }} data-testid="adminUsuariosForm">
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.usuarios.field.usuario')}</label>
            <TextBox
              value={form.usuario}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, usuario: e.value ?? '' }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.usuarios.field.nombre')}</label>
            <TextBox
              value={form.nombre}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, nombre: e.value ?? '' }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.usuarios.field.email')}</label>
            <TextBox
              value={form.email}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, email: e.value ?? '' }))}
            />
          </div>
          {editingId === null ? (
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
              <label>{t('admin.usuarios.field.password')}</label>
              <TextBox
                mode="password"
                value={form.password}
                onValueChanged={(e) => setForm((prev) => ({ ...prev, password: e.value ?? '' }))}
              />
            </div>
          ) : null}
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.common.activo')}</label>
            <CheckBox
              value={form.activo}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, activo: Boolean(e.value) }))}
            />
          </div>
          {editingId !== null ? (
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
              <label>{t('admin.usuarios.field.inhabilitado')}</label>
              <CheckBox
                value={form.inhabilitado}
                onValueChanged={(e) => setForm((prev) => ({ ...prev, inhabilitado: Boolean(e.value) }))}
                elementAttr={{ 'data-testid': 'adminUsuariosInhabilitado' }}
              />
            </div>
          ) : null}
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text={t('admin.common.cancel')} onClick={() => setFormOpen(false)} />
            <Button
              text={t('admin.common.save')}
              type="default"
              onClick={() => void handleSave()}
              elementAttr={{ 'data-testid': 'adminUsuariosFormSave' }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
