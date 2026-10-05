import { useCallback, useEffect, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import CheckBox from 'devextreme-react/check-box'
import SelectBox from 'devextreme-react/select-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import {
  FormContextErrorAlert,
  shouldShowPageListError,
} from '../../../shared/ui/FormContextErrorAlert'
import {
  deletePartesResource,
  listUsuariosVinculables,
  listCatalogo,
  listPartesResource,
  savePartesResource,
} from './partesMaestrosApi'

export type MaestroField =
  | { key: string; label: string; type: 'text'; maxLength?: number }
  | { key: string; label: string; type: 'check' }
  | { key: string; label: string; type: 'user' }
  | {
      key: string
      label: string
      type: 'catalog'
      catalog: string
      valueExpr?: string
      displayExpr?: string | ((item: Record<string, unknown> | null) => string)
    }

type MaestroCrudPageProps = {
  title: string
  resourcePath: string
  testIdPrefix: string
  columns: Array<{ dataField: string; caption: string }>
  fields: MaestroField[]
  initialForm: Record<string, unknown>
}

export function MaestroCrudPage({
  title,
  resourcePath,
  testIdPrefix,
  columns,
  fields,
  initialForm,
}: MaestroCrudPageProps) {
  const { t } = useTranslation()
  const pageTitle = useProcessMenuTitle(title)
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<Record<string, unknown>>(initialForm)
  const [listError, setListError] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [users, setUsers] = useState<Array<{ id: number; codigo: string; nombre: string }>>([])
  const [usersLoading, setUsersLoading] = useState(false)
  const [catalogs, setCatalogs] = useState<Record<string, Record<string, unknown>[]>>({})

  const load = useCallback(async () => {
    setLoading(true)
    setListError(null)
    try {
      const result = await listPartesResource(resourcePath)
      if (result.kind === 'ok') {
        setRows(result.envelope.resultado.items ?? [])
      } else if (result.kind === 'envelopeError') {
        setListError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [resourcePath])

  useEffect(() => {
    void load()
  }, [load])

  const loadVinculables = useCallback((exceptoUserId?: number | null) => {
    if (!fields.some((field) => field.type === 'user')) {
      return
    }
    setUsersLoading(true)
    void listUsuariosVinculables(exceptoUserId)
      .then((result) => {
        if (result.kind === 'ok') {
          setUsers(result.envelope.resultado.items ?? [])
        }
      })
      .finally(() => setUsersLoading(false))
  }, [fields])

  useEffect(() => {
    const catalogFields = fields.filter(
      (field): field is Extract<MaestroField, { type: 'catalog' }> => field.type === 'catalog'
    )
    catalogFields.forEach((field) => {
      void listCatalogo(field.catalog).then((result) => {
        if (result.kind === 'ok') {
          setCatalogs((prev) => ({ ...prev, [field.catalog]: result.envelope.resultado.items ?? [] }))
        }
      })
    })
  }, [fields])

  function closeForm() {
    setFormOpen(false)
    setFormError(null)
  }

  function openCreate() {
    setEditingId(null)
    setForm({ ...initialForm })
    loadVinculables(null)
    setFormError(null)
    setFormOpen(true)
  }

  function openEdit(row: Record<string, unknown>) {
    setEditingId(Number(row.id))
    setForm({ ...initialForm, ...row })
    const currentUserId = Number(row.userId ?? 0)
    loadVinculables(currentUserId > 0 ? currentUserId : null)
    setFormError(null)
    setFormOpen(true)
  }

  async function handleSave() {
    setFormError(null)
    if (editingId !== null && fields.some((f) => f.type === 'user')) {
      const previous = rows.find((row) => Number(row.id) === editingId)
      if (previous && previous.userId && form.userId && previous.userId !== form.userId) {
        const ok = await confirm(
          t('partes.maestros.cambioUsuarioConfirm'),
          t('partes.maestros.cambioUsuarioTitle'),
        )
        if (!ok) {
          return
        }
      }
    }
    const result = await savePartesResource(resourcePath, form, editingId ?? undefined)
    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setFormError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: Record<string, unknown>) {
    const ok = await confirm(t('partes.maestros.eliminarRegistroConfirm'), t('admin.common.delete'))
    if (!ok) {
      return
    }
    const result = await deletePartesResource(resourcePath, Number(row.id))
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setListError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid={`${testIdPrefix}Page`} style={{ padding: 16 }}>
      <h2 style={{ margin: '0 0 12px' }}>{pageTitle}</h2>
      {shouldShowPageListError(formOpen, listError) ? (
        <FormContextErrorAlert message={listError} testId={`${testIdPrefix}ListError`} />
      ) : null}
      <div data-testid={`${testIdPrefix}Grid`}>
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso={`partes.maestros.${resourcePath}`}
          gridId={testIdPrefix}
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          onCreate={openCreate}
          createHint={t('partes.common.agregar')}
          createTestId={`${testIdPrefix}Add`}
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          {columns.map((column) => (
            <Column key={column.dataField} dataField={column.dataField} caption={column.caption} />
          ))}
          <Column
            type="buttons"
            buttons={[
              {
                hint: t('admin.common.edit'),
                icon: 'edit',
                onClick: (e) => openEdit(e.row?.data as Record<string, unknown>),
              },
              {
                hint: t('admin.common.delete'),
                icon: 'trash',
                onClick: (e) => void handleDelete(e.row?.data as Record<string, unknown>),
              },
            ]}
          />
        </ProcessDataGrid>
      </div>

      <Popup
        visible={formOpen}
        onHiding={closeForm}
        title={
          editingId
            ? t('partes.maestros.editTitle', { title })
            : t('partes.maestros.newTitle', { title })
        }
        width={480}
        height="auto"
        showCloseButton
      >
        <div style={{ display: 'grid', gap: 12, padding: 8 }} data-testid={`${testIdPrefix}Form`}>
          <FormContextErrorAlert message={formError} testId={`${testIdPrefix}FormError`} />
          {fields.map((field) => (
            <div
              key={field.key}
              style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}
            >
              <label>{field.label}</label>
              {field.type === 'text' ? (
                <TextBox
                  value={String(form[field.key] ?? '')}
                  maxLength={field.maxLength}
                  onValueChanged={(e) => setForm((prev) => ({ ...prev, [field.key]: e.value }))}
                />
              ) : null}
              {field.type === 'check' ? (
                <CheckBox
                  value={Boolean(form[field.key])}
                  onValueChanged={(e) => setForm((prev) => ({ ...prev, [field.key]: e.value }))}
                />
              ) : null}
              {field.type === 'user' ? (
                <SelectBox
                  dataSource={users}
                  value={form[field.key] ?? null}
                  valueExpr="id"
                  displayExpr={(item) =>
                    item ? `${item.codigo} — ${item.nombre}` : ''
                  }
                  searchEnabled
                  placeholder={usersLoading ? t('catalog.loading') : ''}
                  noDataText={usersLoading ? t('catalog.loading') : undefined}
                  disabled={usersLoading}
                  onValueChanged={(e) => setForm((prev) => ({ ...prev, [field.key]: e.value }))}
                  elementAttr={{ 'data-testid': `${testIdPrefix}Usuario` }}
                />
              ) : null}
              {field.type === 'catalog' ? (
                <SelectBox
                  dataSource={catalogs[field.catalog] ?? []}
                  value={form[field.key] ?? null}
                  valueExpr={field.valueExpr ?? 'id'}
                  displayExpr={field.displayExpr ?? 'code'}
                  searchEnabled
                  onValueChanged={(e) => setForm((prev) => ({ ...prev, [field.key]: e.value }))}
                />
              ) : null}
            </div>
          ))}
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text={t('parametros.modal.cancel')} onClick={closeForm} />
            <Button
              text={t('admin.common.save')}
              type="default"
              onClick={() => void handleSave()}
              elementAttr={{ 'data-testid': `${testIdPrefix}FormSave` }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
