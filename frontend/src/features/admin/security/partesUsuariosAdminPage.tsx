import { useCallback, useEffect, useMemo, useState } from 'react'
import { Column } from 'devextreme-react/data-grid'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import type { BuildPlatformHeadersInput } from '@paqsuite/react-core'
import {
  ProcessDataGrid,
  deleteUsuario,
  listUsuarios,
  useMenuProcessPermissions,
  type UsuariosAdminRequestOptions,
  type UsuarioListItem,
} from '@paqsuite/react-core'
import { PartesUsuariosFormPopup } from './partesUsuariosFormPopup'

/** Misma superficie que GEN-06 `ADMIN_ABM_GRID_DEFAULTS` (no exportado del SDK). */
const ADMIN_ABM_GRID_DEFAULTS = {
  filterRowVisible: true,
  groupPanelVisible: false,
  headerFilterVisible: false,
  columnChooserEnabled: false,
  summaryEnabled: false,
  exportEnabled: false,
  layoutsEnabled: false,
} as const

export type PartesUsuariosAdminPageProps = {
  accessToken?: string | null
  platform?: BuildPlatformHeadersInput
  t?: (key: string) => string
  i18nNamespace?: string
  menuRouteName?: string
}

function resolveAction(allowed: boolean | undefined, menuLoaded: boolean): boolean {
  if (!menuLoaded) {
    return true
  }
  return Boolean(allowed)
}

export function PartesUsuariosAdminPage({
  accessToken,
  platform,
  t,
  i18nNamespace = 'common',
  menuRouteName = '/admin/usuarios',
}: PartesUsuariosAdminPageProps) {
  const { t: i18nT } = useTranslation(i18nNamespace)
  const translate = useCallback(
    (key: string) => {
      if (t) {
        const fromHost = t(key)
        if (fromHost && fromHost !== key) {
          return fromHost
        }
      }
      return i18nT(key, { defaultValue: key })
    },
    [i18nT, t],
  )
  const menuPermissions = useMenuProcessPermissions(menuRouteName)
  const menuLoaded = menuPermissions !== null

  const canCreate = resolveAction(menuPermissions?.create, menuLoaded)
  const canUpdate = resolveAction(menuPermissions?.update, menuLoaded)
  const canDelete = resolveAction(menuPermissions?.delete, menuLoaded)

  const requestOptions = useMemo<UsuariosAdminRequestOptions>(
    () => ({
      platform,
      ...(accessToken ? { headers: { Authorization: `Bearer ${accessToken}` } } : {}),
    }),
    [accessToken, platform],
  )

  const [rows, setRows] = useState<UsuarioListItem[]>([])
  const [usuarioCodigoEditable, setUsuarioCodigoEditable] = useState(false)
  const [loadError, setLoadError] = useState<string | null>(null)
  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null)
  const [editing, setEditing] = useState<UsuarioListItem | null>(null)

  const load = useCallback(async () => {
    setLoadError(null)
    const result = await listUsuarios(requestOptions)
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
      setUsuarioCodigoEditable(Boolean(result.envelope.resultado.usuarioCodigoEditable))
      return
    }
    setLoadError(
      result.kind === 'envelopeError'
        ? result.envelope.respuesta || 'admin.usuarios.loadError'
        : result.i18nKey,
    )
  }, [requestOptions])

  useEffect(() => {
    void load()
  }, [load])

  const handleDelete = useCallback(
    async (row: UsuarioListItem) => {
      const confirmed = await confirm(
        translate('admin.usuarios.delete.confirm').replace('{usuario}', row.usuario),
        translate('admin.usuarios.action.delete'),
      )
      if (!confirmed) {
        return
      }
      const result = await deleteUsuario(row.id, requestOptions)
      if (result.kind === 'ok') {
        void load()
        return
      }
      const message =
        result.kind === 'envelopeError'
          ? result.envelope.respuesta || 'admin.usuarios.delete.error'
          : result.i18nKey
      await confirm(translate(message), translate('admin.usuarios.action.delete'))
    },
    [load, requestOptions, translate],
  )

  const gridButtons = useMemo(() => {
    const buttons: Array<{
      hint: string
      icon: string
      onClick: (e: { row?: { data: UsuarioListItem } }) => void
    }> = []

    if (canUpdate) {
      buttons.push({
        hint: translate('admin.usuarios.action.edit'),
        icon: 'edit',
        onClick: (e) => {
          const row = e.row?.data as UsuarioListItem
          setEditing(row)
          setFormMode('edit')
        },
      })
    }
    if (canDelete) {
      buttons.push({
        hint: translate('admin.usuarios.action.delete'),
        icon: 'trash',
        onClick: (e) => {
          const row = e.row?.data as UsuarioListItem
          void handleDelete(row)
        },
      })
    }
    return buttons
  }, [canDelete, canUpdate, handleDelete, translate])

  return (
    <div data-testid="admin.usuarios.grid">
      <h2 style={{ margin: '0 0 12px' }}>{translate('admin.usuarios.title')}</h2>

      {loadError && formMode === null ? <div role="alert">{translate(loadError)}</div> : null}
      {rows.length === 0 && !loadError ? <div>{translate('admin.usuarios.empty')}</div> : null}

      <ProcessDataGrid
        {...ADMIN_ABM_GRID_DEFAULTS}
        dataSource={rows}
        keyExpr="id"
        menuRouteName={menuRouteName}
        t={translate}
        allowCreate={canCreate}
        onCreate={() => {
          setEditing(null)
          setFormMode('create')
        }}
        createHint={translate('admin.usuarios.action.add')}
        createTestId="admin.usuarios.create"
        onRefresh={load}
        accessToken={accessToken}
        platform={platform}
      >
        <Column dataField="usuario" caption={translate('admin.usuarios.list.usuario')} />
        <Column dataField="name" caption={translate('admin.usuarios.list.name')} />
        <Column dataField="email" caption={translate('admin.usuarios.list.email')} />
        <Column
          dataField="activo"
          caption={translate('admin.usuarios.list.activo')}
          dataType="boolean"
        />
        <Column
          dataField="inhabilitado"
          caption={translate('admin.usuarios.list.inhabilitado')}
          dataType="boolean"
        />
        {gridButtons.length > 0 ? (
          <Column type="buttons" width={100} buttons={gridButtons} />
        ) : null}
      </ProcessDataGrid>

      {formMode ? (
        <PartesUsuariosFormPopup
          visible
          mode={formMode}
          initial={editing}
          usuarioCodigoEditable={usuarioCodigoEditable}
          onCancel={() => {
            setFormMode(null)
            setEditing(null)
          }}
          onSaved={() => {
            setFormMode(null)
            setEditing(null)
            void load()
          }}
          requestOptions={requestOptions}
          t={translate}
        />
      ) : null}
    </div>
  )
}
