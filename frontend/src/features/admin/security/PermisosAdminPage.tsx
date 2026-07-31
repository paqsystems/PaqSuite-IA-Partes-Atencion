import { useCallback, useEffect, useMemo, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import DataGrid, { Column, Paging, Pager, Selection } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import SelectBox from 'devextreme-react/select-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import {
  type AdminEmpresa,
  type AdminPermiso,
  type AdminRol,
  type AdminUsuario,
  batchCreateAdminPermisos,
  createAdminPermiso,
  deleteAdminPermiso,
  listAdminEmpresas,
  listAdminPermisos,
  listAdminRoles,
  listAdminUsuariosFull,
} from './adminSecurityApi'
import { buildPermisoBatchItems, type PermisoBulkMode } from './buildPermisoBatchItems'

type CreateForm = {
  userId: number | null
  empresaId: number | null
  rolId: number | null
}

type BulkState = {
  mode: PermisoBulkMode
  anchorId: number | null
  selectedUserIds: number[]
  selectedRolIds: number[]
  selectedEmpresaIds: number[]
}

const emptyCreate: CreateForm = { userId: null, empresaId: null, rolId: null }

export function PermisosAdminPage() {
  const { t } = useTranslation()
  const [usuarios, setUsuarios] = useState<AdminUsuario[]>([])
  const [empresas, setEmpresas] = useState<AdminEmpresa[]>([])
  const [roles, setRoles] = useState<AdminRol[]>([])
  const [rows, setRows] = useState<AdminPermiso[]>([])
  const [error, setError] = useState<string | null>(null)
  const [createOpen, setCreateOpen] = useState(false)
  const [createForm, setCreateForm] = useState<CreateForm>(emptyCreate)
  const [bulkOpen, setBulkOpen] = useState(false)
  const [bulk, setBulk] = useState<BulkState | null>(null)
  const [bulkResult, setBulkResult] = useState<{ creados: number; omitidos: number } | null>(null)

  const empresasActivas = useMemo(() => empresas.filter((e) => e.activo), [empresas])
  const multiEmpresa = empresasActivas.length > 1
  const empresaMonoId = empresasActivas.length === 1 ? empresasActivas[0].id : null

  const load = useCallback(async () => {
    setError(null)
    const result = await listAdminPermisos()
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [])

  useEffect(() => {
    void listAdminUsuariosFull('0').then((result) => {
      if (result.kind === 'ok') {
        setUsuarios(result.envelope.resultado.items ?? [])
      }
    })
    void listAdminEmpresas().then((result) => {
      if (result.kind === 'ok') {
        setEmpresas(result.envelope.resultado.items ?? [])
      }
    })
    void listAdminRoles().then((result) => {
      if (result.kind === 'ok') {
        setRoles(result.envelope.resultado.items ?? [])
      }
    })
    void load()
  }, [load])

  function openCreate() {
    setCreateForm({
      userId: null,
      empresaId: empresaMonoId,
      rolId: null,
    })
    setCreateOpen(true)
  }

  async function handleCreateSave() {
    const empresaId = createForm.empresaId ?? empresaMonoId
    if (createForm.userId === null || createForm.rolId === null || empresaId === null) {
      return
    }
    setError(null)
    const result = await createAdminPermiso({
      userId: createForm.userId,
      empresaId,
      rolId: createForm.rolId,
    })
    if (result.kind === 'ok') {
      setCreateOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: AdminPermiso) {
    const ok = await confirm(t('admin.permisos.confirmDelete'), t('admin.permisos.title'))
    if (!ok) {
      return
    }
    const result = await deleteAdminPermiso(row.id)
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  function openBulk(mode: PermisoBulkMode) {
    setBulkResult(null)
    setBulk({
      mode,
      anchorId: null,
      selectedUserIds: [],
      selectedRolIds: [],
      selectedEmpresaIds: multiEmpresa ? [] : empresaMonoId !== null ? [empresaMonoId] : [],
    })
    setBulkOpen(true)
  }

  const bulkItems = useMemo(() => {
    if (!bulk || bulk.anchorId === null) {
      return []
    }
    return buildPermisoBatchItems(
      bulk.mode,
      bulk.anchorId,
      {
        userIds: bulk.selectedUserIds,
        rolIds: bulk.selectedRolIds,
        empresaIds: bulk.selectedEmpresaIds,
      },
      empresaMonoId ?? undefined
    )
  }, [bulk, empresaMonoId])

  async function handleBulkSave() {
    if (!bulk || bulkItems.length === 0) {
      return
    }
    const ok = await confirm(
      t('admin.permisos.bulk.confirm', { count: bulkItems.length }),
      t('admin.permisos.title')
    )
    if (!ok) {
      return
    }
    setError(null)
    setBulkResult(null)
    const result = await batchCreateAdminPermisos(bulkItems)
    if (result.kind === 'ok') {
      setBulkResult(result.envelope.resultado)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  function bulkTitle(mode: PermisoBulkMode): string {
    if (mode === 'byUser') {
      return t('admin.permisos.bulk.byUser')
    }
    if (mode === 'byRole') {
      return t('admin.permisos.bulk.byRole')
    }
    return t('admin.permisos.bulk.byCompany')
  }

  function formatUsuario(item: AdminUsuario | null): string {
    return item ? `${item.usuario} — ${item.nombre}` : ''
  }

  function formatRol(item: AdminRol | null): string {
    return item ? `${item.codigo} — ${item.nombre}` : ''
  }

  function closeBulk() {
    setBulkOpen(false)
    setBulk(null)
    setBulkResult(null)
  }

  // MUST contentRender (sin children): en DX React 26 los children del Popup quedan en la página.
  const renderCreateContent = useCallback(
    () => (
      <div style={{ display: 'grid', gap: 12, padding: 8 }} data-testid="adminPermisosForm">
        <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
          <label>{t('admin.permisos.field.usuario')}</label>
          <SelectBox
            dataSource={usuarios}
            value={createForm.userId}
            valueExpr="id"
            displayExpr={formatUsuario}
            searchEnabled
            dropDownOptions={{ container: 'body' }}
            elementAttr={{ 'data-testid': 'admin.permisos.create.usuario' }}
            onValueChanged={(e) => setCreateForm((prev) => ({ ...prev, userId: e.value ?? null }))}
          />
        </div>
        {multiEmpresa ? (
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.permisos.field.empresa')}</label>
            <SelectBox
              dataSource={empresasActivas}
              value={createForm.empresaId}
              valueExpr="id"
              displayExpr="nombre"
              searchEnabled
              dropDownOptions={{ container: 'body' }}
              elementAttr={{ 'data-testid': 'admin.permisos.create.empresa' }}
              onValueChanged={(e) => setCreateForm((prev) => ({ ...prev, empresaId: e.value ?? null }))}
            />
          </div>
        ) : null}
        <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
          <label>{t('admin.permisos.field.rol')}</label>
          <SelectBox
            dataSource={roles}
            value={createForm.rolId}
            valueExpr="id"
            displayExpr={formatRol}
            searchEnabled
            dropDownOptions={{ container: 'body' }}
            elementAttr={{ 'data-testid': 'admin.permisos.create.rol' }}
            onValueChanged={(e) => setCreateForm((prev) => ({ ...prev, rolId: e.value ?? null }))}
          />
        </div>
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
          <Button
            text={t('admin.common.cancel')}
            stylingMode="outlined"
            onClick={() => setCreateOpen(false)}
            elementAttr={{ 'data-testid': 'admin.permisos.create.cancel' }}
          />
          <Button
            text={t('admin.common.save')}
            type="default"
            stylingMode="contained"
            onClick={() => void handleCreateSave()}
            elementAttr={{ 'data-testid': 'admin.permisos.create.save' }}
          />
        </div>
      </div>
    ),
    [t, usuarios, roles, empresasActivas, createForm, multiEmpresa]
  )

  const renderBulkContent = useCallback(() => {
    if (!bulk) {
      return null
    }
    return (
      <div
        style={{ display: 'grid', gap: 12, padding: 8, maxHeight: '70vh', overflowY: 'auto' }}
        data-testid="permisos.bulk.modal"
      >
        {bulk.mode === 'byUser' ? (
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.permisos.field.usuario')}</label>
            <SelectBox
              dataSource={usuarios}
              value={bulk.anchorId}
              valueExpr="id"
              displayExpr={formatUsuario}
              searchEnabled
              dropDownOptions={{ container: 'body' }}
              elementAttr={{ 'data-testid': 'permisos.bulk.anchor.usuario' }}
              onValueChanged={(e) =>
                setBulk((prev) => (prev ? { ...prev, anchorId: e.value ?? null } : prev))
              }
            />
          </div>
        ) : null}
        {bulk.mode === 'byRole' ? (
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.permisos.field.rol')}</label>
            <SelectBox
              dataSource={roles}
              value={bulk.anchorId}
              valueExpr="id"
              displayExpr={formatRol}
              searchEnabled
              dropDownOptions={{ container: 'body' }}
              elementAttr={{ 'data-testid': 'permisos.bulk.anchor.rol' }}
              onValueChanged={(e) =>
                setBulk((prev) => (prev ? { ...prev, anchorId: e.value ?? null } : prev))
              }
            />
          </div>
        ) : null}
        {bulk.mode === 'byCompany' ? (
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', alignItems: 'center', gap: 8 }}>
            <label>{t('admin.permisos.field.empresa')}</label>
            <SelectBox
              dataSource={empresasActivas}
              value={bulk.anchorId}
              valueExpr="id"
              displayExpr="nombre"
              searchEnabled
              dropDownOptions={{ container: 'body' }}
              elementAttr={{ 'data-testid': 'permisos.bulk.anchor.empresa' }}
              onValueChanged={(e) =>
                setBulk((prev) => (prev ? { ...prev, anchorId: e.value ?? null } : prev))
              }
            />
          </div>
        ) : null}

        {!multiEmpresa && empresaMonoId !== null ? (
          <div data-testid="permisos.bulk.empresaMono">
            {t('admin.permisos.field.empresa')}: {empresasActivas[0]?.nombre}
          </div>
        ) : null}

        {bulk.mode === 'byUser' || bulk.mode === 'byCompany' ? (
          <div data-testid="permisos.bulk.grid.roles">
            <h4 style={{ margin: '8px 0' }}>{t('admin.permisos.bulk.grid.roles')}</h4>
            <DataGrid
              dataSource={roles}
              keyExpr="id"
              height={220}
              showBorders
              onSelectionChanged={(e) =>
                setBulk((prev) =>
                  prev ? { ...prev, selectedRolIds: (e.selectedRowKeys as number[]) ?? [] } : prev
                )
              }
            >
              <Selection mode="multiple" showCheckBoxesMode="always" />
              <Column dataField="codigo" caption={t('admin.roles.field.codigo')} />
              <Column dataField="nombre" caption={t('admin.roles.field.nombre')} />
            </DataGrid>
          </div>
        ) : null}

        {bulk.mode === 'byRole' || bulk.mode === 'byCompany' ? (
          <div data-testid="permisos.bulk.grid.usuarios">
            <h4 style={{ margin: '8px 0' }}>{t('admin.permisos.bulk.grid.usuarios')}</h4>
            <DataGrid
              dataSource={usuarios}
              keyExpr="id"
              height={220}
              showBorders
              onSelectionChanged={(e) =>
                setBulk((prev) =>
                  prev ? { ...prev, selectedUserIds: (e.selectedRowKeys as number[]) ?? [] } : prev
                )
              }
            >
              <Selection mode="multiple" showCheckBoxesMode="always" />
              <Column dataField="usuario" caption={t('admin.usuarios.field.usuario')} />
              <Column dataField="nombre" caption={t('admin.usuarios.field.nombre')} />
            </DataGrid>
          </div>
        ) : null}

        {multiEmpresa && (bulk.mode === 'byUser' || bulk.mode === 'byRole') ? (
          <div data-testid="permisos.bulk.grid.empresas">
            <h4 style={{ margin: '8px 0' }}>{t('admin.permisos.bulk.grid.empresas')}</h4>
            <DataGrid
              dataSource={empresasActivas}
              keyExpr="id"
              height={180}
              showBorders
              onSelectionChanged={(e) =>
                setBulk((prev) =>
                  prev
                    ? { ...prev, selectedEmpresaIds: (e.selectedRowKeys as number[]) ?? [] }
                    : prev
                )
              }
            >
              <Selection mode="multiple" showCheckBoxesMode="always" />
              <Column dataField="nombre" caption={t('admin.empresas.field.nombre')} />
            </DataGrid>
          </div>
        ) : null}

        {bulkItems.length === 0 ? (
          <div role="status" data-testid="permisos.bulk.empty">
            {t('admin.permisos.bulk.empty')}
          </div>
        ) : (
          <div data-testid="permisos.bulk.count">
            {t('admin.permisos.bulk.count', { count: bulkItems.length })}
          </div>
        )}

        {bulkResult ? (
          <div role="status">
            <span data-testid="permisos.bulk.result.creados">
              {t('admin.permisos.bulk.result.creados', { count: bulkResult.creados })}
            </span>
            {' · '}
            <span data-testid="permisos.bulk.result.omitidos">
              {t('admin.permisos.bulk.result.omitidos', { count: bulkResult.omitidos })}
            </span>
          </div>
        ) : null}

        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
          <Button
            text={t('admin.common.cancel')}
            stylingMode="outlined"
            onClick={closeBulk}
            elementAttr={{ 'data-testid': 'permisos.bulk.cancel' }}
          />
          <Button
            text={t('admin.common.save')}
            type="default"
            stylingMode="contained"
            disabled={bulkItems.length === 0}
            onClick={() => void handleBulkSave()}
            elementAttr={{ 'data-testid': 'permisos.bulk.confirm' }}
          />
        </div>
      </div>
    )
  }, [
    bulk,
    bulkItems.length,
    bulkResult,
    t,
    usuarios,
    roles,
    empresasActivas,
    multiEmpresa,
    empresaMonoId,
  ])

  return (
    <div data-testid="adminPermisosPage" style={{ padding: 16 }}>
      <div style={{ display: 'flex', gap: 8, marginBottom: 12, alignItems: 'center', flexWrap: 'wrap' }}>
        <h2 style={{ margin: 0, flex: 1 }}>{t('admin.permisos.title')}</h2>
        <Button
          text={t('admin.permisos.bulk.byUser')}
          icon="plus"
          onClick={() => openBulk('byUser')}
          elementAttr={{ 'data-testid': 'permisos.bulk.byUser' }}
        />
        <Button
          text={t('admin.permisos.bulk.byRole')}
          icon="plus"
          onClick={() => openBulk('byRole')}
          elementAttr={{ 'data-testid': 'permisos.bulk.byRole' }}
        />
        {multiEmpresa ? (
          <Button
            text={t('admin.permisos.bulk.byCompany')}
            icon="plus"
            onClick={() => openBulk('byCompany')}
            elementAttr={{ 'data-testid': 'permisos.bulk.byCompany' }}
          />
        ) : null}
      </div>

      {error ? <div role="alert">{error}</div> : null}

      <div data-testid="admin.permisos.grid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          onCreate={openCreate}
          createHint={t('admin.common.add')}
          createTestId="admin.permisos.create"
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column
            dataField="usuario"
            caption={t('admin.permisos.field.usuario')}
            calculateCellValue={(row: AdminPermiso) =>
              row.usuario ? `${row.usuario} — ${row.usuarioNombre}` : String(row.userId)
            }
          />
          <Column dataField="empresaNombre" caption={t('admin.permisos.field.empresa')} />
          <Column dataField="rolCodigo" caption={t('admin.permisos.field.rolCodigo')} />
          <Column dataField="rolNombre" caption={t('admin.permisos.field.rolNombre')} />
          <Column
            type="buttons"
            buttons={[
              {
                hint: t('admin.common.delete'),
                icon: 'trash',
                onClick: (e) => void handleDelete(e.row?.data as AdminPermiso),
              },
            ]}
          />
        </ProcessDataGrid>
      </div>

      {createOpen ? (
        <Popup
          visible
          onHiding={() => setCreateOpen(false)}
          title={t('admin.permisos.newTitle')}
          width={480}
          height="auto"
          showCloseButton
          dragEnabled
          hideOnOutsideClick
          shading
          deferRendering={false}
          contentRender={renderCreateContent}
        />
      ) : null}

      {bulkOpen && bulk ? (
        <Popup
          key={`permisos-bulk-${bulk.mode}`}
          visible
          onHiding={closeBulk}
          title={bulkTitle(bulk.mode)}
          width={760}
          height="auto"
          maxHeight="85vh"
          showCloseButton
          dragEnabled
          hideOnOutsideClick
          shading
          deferRendering={false}
          wrapperAttr={{ class: 'permisosBulkModal', 'data-testid': 'permisos.bulk.popup' }}
          contentRender={renderBulkContent}
        />
      ) : null}
    </div>
  )
}
