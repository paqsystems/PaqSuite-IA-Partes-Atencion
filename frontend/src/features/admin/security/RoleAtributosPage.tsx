import { useCallback, useEffect, useMemo, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import TreeView from 'devextreme-react/tree-view'
import Button from 'devextreme-react/button'
import CheckBox from 'devextreme-react/check-box'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import {
  type MenuArbolNode,
  type RolAtributoItem,
  getRolAtributos,
  updateRolAtributos,
} from './adminSecurityApi'

type AttrFlags = {
  permisoAlta: boolean
  permisoBaja: boolean
  permisoModi: boolean
  permisoRepo: boolean
}

type TreeNode = {
  id: number
  parentId: number
  text: string
  esProceso: boolean
}

const emptyFlags: AttrFlags = {
  permisoAlta: false,
  permisoBaja: false,
  permisoModi: false,
  permisoRepo: false,
}

export function RoleAtributosPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { id, rolId: rolIdParam } = useParams<{ id?: string; rolId?: string }>()
  const rolId = Number(id ?? rolIdParam)

  const [arbol, setArbol] = useState<MenuArbolNode[]>([])
  const [accesoTotal, setAccesoTotal] = useState(false)
  const [rolCodigo, setRolCodigo] = useState('')
  const [rolNombre, setRolNombre] = useState('')
  const [attrMap, setAttrMap] = useState<Record<number, AttrFlags>>({})
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [saved, setSaved] = useState(false)

  const load = useCallback(async () => {
    setError(null)
    setLoading(true)
    const result = await getRolAtributos(rolId)
    if (result.kind === 'ok') {
      const resultado = result.envelope.resultado
      setArbol(resultado.arbol ?? [])
      setAccesoTotal(resultado.accesoTotal)
      setRolCodigo(resultado.codigo ?? '')
      setRolNombre(resultado.nombre ?? '')
      const map: Record<number, AttrFlags> = {}
      for (const item of resultado.items ?? []) {
        map[item.menuId] = {
          permisoAlta: item.permisoAlta,
          permisoBaja: item.permisoBaja,
          permisoModi: item.permisoModi,
          permisoRepo: item.permisoRepo,
        }
      }
      setAttrMap(map)
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
    setLoading(false)
  }, [rolId])

  useEffect(() => {
    void load()
  }, [load])

  const treeData: TreeNode[] = useMemo(
    () =>
      arbol.map((node) => ({
        id: node.menuId,
        parentId: node.padreId ?? 0,
        text: node.titulo,
        esProceso: node.esProceso,
      })),
    [arbol]
  )

  function setFlag(menuId: number, flag: keyof AttrFlags, value: boolean) {
    setAttrMap((prev) => ({
      ...prev,
      [menuId]: { ...(prev[menuId] ?? emptyFlags), [flag]: value },
    }))
  }

  function setAllFlags(menuId: number, value: boolean) {
    setAttrMap((prev) => ({
      ...prev,
      [menuId]: {
        permisoAlta: value,
        permisoBaja: value,
        permisoModi: value,
        permisoRepo: value,
      },
    }))
  }

  function allFlagsChecked(flags: AttrFlags): boolean {
    return flags.permisoAlta && flags.permisoBaja && flags.permisoModi && flags.permisoRepo
  }

  async function handleSave() {
    setError(null)
    setSaved(false)
    const items: RolAtributoItem[] = Object.entries(attrMap)
      .map(([menuId, flags]) => ({ menuId: Number(menuId), ...flags }))
      .filter((item) => item.permisoAlta || item.permisoBaja || item.permisoModi || item.permisoRepo)

    const result = await updateRolAtributos(rolId, items)
    if (result.kind === 'ok') {
      setSaved(true)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  function renderNode(node: TreeNode) {
    if (!node.esProceso) {
      return <span>{node.text}</span>
    }

    const flags = attrMap[node.id] ?? emptyFlags
    const todosChecked = allFlagsChecked(flags)

    return (
      <div style={{ display: 'flex', alignItems: 'center', gap: 16, flexWrap: 'wrap' }}>
        <span style={{ minWidth: 220 }}>{node.text}</span>
        <label style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <CheckBox
            value={todosChecked}
            disabled={accesoTotal}
            onValueChanged={(e) => setAllFlags(node.id, Boolean(e.value))}
            elementAttr={{ 'data-testid': `admin.roles.attr.todos.${node.id}` }}
          />
          {t('admin.roles.atributos.column.todos')}
        </label>
        <label style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <CheckBox
            value={flags.permisoAlta}
            disabled={accesoTotal}
            onValueChanged={(e) => setFlag(node.id, 'permisoAlta', Boolean(e.value))}
            elementAttr={{ 'data-testid': `admin.roles.attr.alta.${node.id}` }}
          />
          {t('admin.roles.atributos.column.alta')}
        </label>
        <label style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <CheckBox
            value={flags.permisoBaja}
            disabled={accesoTotal}
            onValueChanged={(e) => setFlag(node.id, 'permisoBaja', Boolean(e.value))}
            elementAttr={{ 'data-testid': `admin.roles.attr.baja.${node.id}` }}
          />
          {t('admin.roles.atributos.column.baja')}
        </label>
        <label style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <CheckBox
            value={flags.permisoModi}
            disabled={accesoTotal}
            onValueChanged={(e) => setFlag(node.id, 'permisoModi', Boolean(e.value))}
            elementAttr={{ 'data-testid': `admin.roles.attr.modi.${node.id}` }}
          />
          {t('admin.roles.atributos.column.modi')}
        </label>
        <label style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <CheckBox
            value={flags.permisoRepo}
            disabled={accesoTotal}
            onValueChanged={(e) => setFlag(node.id, 'permisoRepo', Boolean(e.value))}
            elementAttr={{ 'data-testid': `admin.roles.attr.repo.${node.id}` }}
          />
          {t('admin.roles.atributos.column.repo')}
        </label>
      </div>
    )
  }

  return (
    <div data-testid="admin.roles.atributos" style={{ padding: 16 }}>
      <div style={{ display: 'flex', gap: 8, marginBottom: 12, alignItems: 'center' }}>
        <Button icon="back" onClick={() => navigate('/admin/roles')} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <h2 style={{ margin: 0 }}>{t('admin.roles.atributos.title')}</h2>
          {rolCodigo || rolNombre ? (
            <p
              style={{ margin: '4px 0 0', color: 'var(--pq-shell-muted, #6b7280)' }}
              data-testid="admin.roles.atributos.rol"
            >
              {rolCodigo && rolNombre
                ? `${rolCodigo} — ${rolNombre}`
                : rolCodigo || rolNombre}
            </p>
          ) : null}
        </div>
        <Button
          text={t('admin.roles.atributos.save')}
          type="default"
          disabled={accesoTotal}
          onClick={() => void handleSave()}
          elementAttr={{ 'data-testid': 'admin.roles.atributos.save' }}
        />
      </div>

      {error ? <div role="alert">{error}</div> : null}
      {saved ? <div role="status">{t('admin.roles.atributos.saved')}</div> : null}

      {accesoTotal ? (
        <div role="status" style={{ marginBottom: 12, padding: 8, background: '#fff3cd' }}>
          {t('admin.roles.atributos.accesoTotalBanner')}
        </div>
      ) : null}

      {!loading && treeData.length === 0 ? <p>{t('admin.roles.atributos.empty')}</p> : null}

      {!loading && treeData.length > 0 ? (
        <TreeView
          dataSource={treeData}
          dataStructure="plain"
          keyExpr="id"
          parentIdExpr="parentId"
          rootValue={0}
          displayExpr="text"
          itemRender={renderNode}
          width="100%"
        />
      ) : null}
    </div>
  )
}
