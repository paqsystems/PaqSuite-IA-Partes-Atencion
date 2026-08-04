import { useCallback, useEffect, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import {
  type AdminRol,
  createAdminRol,
  deleteAdminRol,
  listAdminRoles,
  updateAdminRol,
} from './adminSecurityApi'

type FormState = {
  codigo: string
  nombre: string
  accesoTotal: boolean
  activo: boolean
}

const emptyForm: FormState = { codigo: '', nombre: '', accesoTotal: false, activo: true }

export function RolesAdminPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [rows, setRows] = useState<AdminRol[]>([])
  const [loading, setLoading] = useState(true)
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<FormState>(emptyForm)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await listAdminRoles()
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

  function openEdit(row: AdminRol) {
    setEditingId(row.id)
    setForm({ codigo: row.codigo, nombre: row.nombre, accesoTotal: row.accesoTotal, activo: row.activo })
    setFormOpen(true)
  }

  async function handleSave() {
    setError(null)
    const isCreate = editingId === null
    const result = isCreate ? await createAdminRol(form) : await updateAdminRol(editingId, form)

    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
      if (isCreate && !form.accesoTotal) {
        const newId = result.envelope.resultado.item?.id
        if (newId) {
          navigate(`/admin/roles/${newId}/atributos`)
        }
      }
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: AdminRol) {
    const ok = await confirm(t('admin.roles.confirmDelete'), t('admin.roles.title'))
    if (!ok) {
      return
    }
    setError(null)
    const result = await deleteAdminRol(row.id)
    if (result.kind === 'ok') {
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="adminRolesPage" style={{ padding: 16 }}>
      <h2 style={{ margin: '0 0 12px' }}>{t('admin.roles.title')}</h2>
      {error ? <div role="alert">{error}</div> : null}
      <div data-testid="adminRolesGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.admin.roles"
          gridId="roles"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          onCreate={openCreate}
          createHint={t('admin.common.add')}
          createTestId="adminRolesAdd"
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="codigo" caption={t('admin.roles.field.codigo')} />
          <Column dataField="nombre" caption={t('admin.roles.field.nombre')} />
          <Column dataField="accesoTotal" caption={t('admin.roles.field.accesoTotal')} dataType="boolean" />
          <Column dataField="activo" caption={t('admin.common.activo')} dataType="boolean" />
          <Column
            type="buttons"
            width={200}
            buttons={[
              {
                hint: t('admin.common.edit'),
                icon: 'edit',
                onClick: (e) => openEdit(e.row?.data as AdminRol),
              },
              {
                hint: t('admin.roles.atributos.button'),
                icon: 'hierarchy',
                visible: (e) => !(e.row?.data as AdminRol | undefined)?.accesoTotal,
                onClick: (e) => navigate(`/admin/roles/${(e.row?.data as AdminRol).id}/atributos`),
              },
              {
                hint: t('admin.common.delete'),
                icon: 'trash',
                onClick: (e) => void handleDelete(e.row?.data as AdminRol),
              },
            ]}
          />
        </ProcessDataGrid>
      </div>

      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={editingId ? t('admin.roles.editTitle') : t('admin.roles.newTitle')}
        width={420}
        height="auto"
        showCloseButton
      >
        <div style={{ display: 'grid', gap: 12, padding: 8 }} data-testid="adminRolesForm">
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.roles.field.codigo')}</label>
            <TextBox
              value={form.codigo}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, codigo: e.value ?? '' }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.roles.field.nombre')}</label>
            <TextBox
              value={form.nombre}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, nombre: e.value ?? '' }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.roles.field.accesoTotal')}</label>
            <CheckBox
              value={form.accesoTotal}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, accesoTotal: Boolean(e.value) }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.common.activo')}</label>
            <CheckBox
              value={form.activo}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, activo: Boolean(e.value) }))}
            />
          </div>
          {editingId !== null && !form.accesoTotal ? (
            <Button
              text={t('admin.roles.atributos.button')}
              icon="hierarchy"
              onClick={() => {
                setFormOpen(false)
                navigate(`/admin/roles/${editingId}/atributos`)
              }}
              elementAttr={{ 'data-testid': 'adminRolesFormAtributos' }}
            />
          ) : null}
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text={t('admin.common.cancel')} onClick={() => setFormOpen(false)} />
            <Button
              text={t('admin.common.save')}
              type="default"
              onClick={() => void handleSave()}
              elementAttr={{ 'data-testid': 'adminRolesFormSave' }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
