import { useCallback, useEffect, useState } from 'react'
import DataGrid, { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import Form, { SimpleItem, RequiredRule } from 'devextreme-react/form'
import { Popup } from 'devextreme-react/popup'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthSession, patchAuthSession } from '../../auth/authSessionStore'
import {
  applyDevExtremeTheme,
  getActiveEmpresaThemeFromSession,
} from '../../../theme/devExtremeThemeSwitcher'
import { type AdminEmpresa, listAdminEmpresas, updateAdminEmpresa } from './adminSecurityApi'
import { EMPRESA_THEME_DEFAULT, formatThemeLabel, getEmpresaThemeOptions } from './empresaThemeCatalog'

type FormState = {
  nombre: string
  activo: boolean
  theme: string
}

const themeOptions = getEmpresaThemeOptions()
const previewDraftKey = 'adminEmpresasThemePreview'

type PreviewDraft = {
  editingId: number
  form: FormState
  themeBeforeEdit: string
}

/**
 * ABM Empresas (GEN-06) — MONO: instalación de una sola empresa lógica.
 * Sin alta ni baja de registros: solo edición de nombre/activo/theme.
 */
export function EmpresasAdminPage() {
  const { t } = useTranslation()
  const [rows, setRows] = useState<AdminEmpresa[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<FormState>({ nombre: '', activo: true, theme: EMPRESA_THEME_DEFAULT })
  const [themeBeforeEdit, setThemeBeforeEdit] = useState(EMPRESA_THEME_DEFAULT)
  const [error, setError] = useState<string | null>(null)
  const [previewing, setPreviewing] = useState(false)

  const load = useCallback(async () => {
    setError(null)
    const result = await listAdminEmpresas()
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  useEffect(() => {
    const raw = sessionStorage.getItem(previewDraftKey)
    if (!raw) {
      return
    }
    sessionStorage.removeItem(previewDraftKey)
    try {
      const draft = JSON.parse(raw) as PreviewDraft
      setEditingId(draft.editingId)
      setForm(draft.form)
      setThemeBeforeEdit(draft.themeBeforeEdit)
      setFormOpen(true)
      setPreviewing(true)
      // El tema preview ya lo aplicó ThemeProvider vía PENDING_EMPRESA_THEME_KEY.
    } catch {
      // ignore
    }
  }, [])

  function openEdit(row: AdminEmpresa) {
    const theme = row.theme || EMPRESA_THEME_DEFAULT
    setEditingId(row.id)
    setForm({ nombre: row.nombre, activo: row.activo, theme })
    setThemeBeforeEdit(theme)
    setPreviewing(false)
    setFormOpen(true)
  }

  async function restoreThemeBeforeEdit() {
    await applyDevExtremeTheme(themeBeforeEdit, { reloadOnGroupChange: true })
  }

  async function handleClose() {
    if (previewing) {
      await restoreThemeBeforeEdit()
    }
    setFormOpen(false)
    setPreviewing(false)
  }

  async function handleApply() {
    setError(null)
    if (editingId !== null) {
      sessionStorage.setItem(
        previewDraftKey,
        JSON.stringify({ editingId, form, themeBeforeEdit } satisfies PreviewDraft)
      )
    }
    setPreviewing(true)
    const result = await applyDevExtremeTheme(form.theme, { reloadOnGroupChange: true })
    if (!result.reloaded) {
      sessionStorage.removeItem(previewDraftKey)
    }
  }

  async function handleSave() {
    if (editingId === null) {
      return
    }
    setError(null)
    const result = await updateAdminEmpresa(editingId, form)
    if (result.kind === 'ok') {
      const saved = result.envelope.resultado.item
      const session = getAuthSession()
      if (session) {
        const empresas = session.empresas.map((empresa) =>
          empresa.id === saved.id
            ? { ...empresa, nombreEmpresa: saved.nombre, theme: saved.theme }
            : empresa
        )
        patchAuthSession({ empresas })
        const activeId = session.activeCompanyId ?? empresas[0]?.id
        if (activeId === saved.id) {
          void applyDevExtremeTheme(saved.theme, { reloadOnGroupChange: true })
        } else if (previewing) {
          void applyDevExtremeTheme(
            getActiveEmpresaThemeFromSession({
              activeCompanyId: session.activeCompanyId,
              empresas,
            }),
            { reloadOnGroupChange: true }
          )
        }
      }
      sessionStorage.removeItem(previewDraftKey)
      setPreviewing(false)
      setFormOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="adminEmpresasPage" className="dx-viewport" style={{ padding: 16 }}>
      <div style={{ display: 'flex', gap: 8, marginBottom: 12, alignItems: 'center' }}>
        <h2 style={{ margin: 0, flex: 1 }}>{t('admin.empresas.title')}</h2>
      </div>
      <p style={{ marginTop: 0 }}>{t('admin.empresas.monoNote')}</p>
      {error ? <div role="alert">{error}</div> : null}
      <div data-testid="adminEmpresasGrid">
        <DataGrid dataSource={rows} keyExpr="id" showBorders hoverStateEnabled>
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="nombre" caption={t('admin.empresas.field.nombre')} />
          <Column
            dataField="theme"
            caption={t('admin.empresas.field.theme')}
            calculateCellValue={(row: AdminEmpresa) => formatThemeLabel(row.theme || EMPRESA_THEME_DEFAULT)}
          />
          <Column dataField="activo" caption={t('admin.common.activo')} dataType="boolean" />
          <Column
            type="buttons"
            buttons={[
              {
                hint: t('admin.common.edit'),
                icon: 'edit',
                onClick: (e) => openEdit(e.row?.data as AdminEmpresa),
              },
            ]}
          />
        </DataGrid>
      </div>

      <Popup
        visible={formOpen}
        onHiding={() => void handleClose()}
        title={t('admin.empresas.editTitle')}
        width={520}
        height="auto"
        showCloseButton
        dragEnabled
      >
        <div data-testid="adminEmpresasForm" style={{ padding: '8px 12px 4px' }}>
          <Form
            formData={form}
            labelLocation="left"
            colCount={1}
            labelMode="outside"
            showValidationSummary={false}
            onFieldDataChanged={(e) => {
              const dataField = e.dataField as keyof FormState | undefined
              if (!dataField) {
                return
              }
              setForm((prev) => ({ ...prev, [dataField]: e.value as FormState[typeof dataField] }))
            }}
          >
            <SimpleItem
              dataField="nombre"
              editorType="dxTextBox"
              isRequired
              label={{ text: t('admin.empresas.field.nombre') }}
              editorOptions={{ stylingMode: 'outlined' }}
            >
              <RequiredRule />
            </SimpleItem>
            <SimpleItem
              dataField="theme"
              editorType="dxSelectBox"
              label={{ text: t('admin.empresas.field.theme') }}
              editorOptions={{
                dataSource: themeOptions,
                valueExpr: 'value',
                displayExpr: 'label',
                searchEnabled: true,
                stylingMode: 'outlined',
                elementAttr: { 'data-testid': 'adminEmpresasFormTheme' },
              }}
            />
            <SimpleItem
              dataField="activo"
              editorType="dxCheckBox"
              label={{ text: t('admin.common.activo') }}
            />
          </Form>

          {previewing ? (
            <div role="status" style={{ margin: '8px 0' }} data-testid="adminEmpresasThemePreviewHint">
              {t('admin.empresas.applyThemeHint')}
            </div>
          ) : null}

          <div
            style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 12, flexWrap: 'wrap' }}
          >
            <Button
              text={t('admin.common.cancel')}
              stylingMode="outlined"
              onClick={() => void handleClose()}
              elementAttr={{ 'data-testid': 'adminEmpresasFormCancel' }}
            />
            <Button
              text={t('admin.empresas.applyTheme')}
              stylingMode="outlined"
              type="normal"
              icon="refresh"
              onClick={() => void handleApply()}
              elementAttr={{ 'data-testid': 'adminEmpresasFormApply' }}
            />
            <Button
              text={t('admin.common.save')}
              type="default"
              stylingMode="contained"
              icon="save"
              onClick={() => void handleSave()}
              elementAttr={{ 'data-testid': 'adminEmpresasFormSave' }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
