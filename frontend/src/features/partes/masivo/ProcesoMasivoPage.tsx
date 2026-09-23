import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Column, Paging, Pager, Selection } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import CheckBox from 'devextreme-react/check-box'
import DateBox from 'devextreme-react/date-box'
import Popup from 'devextreme-react/popup'
import SelectBox from 'devextreme-react/select-box'
import { confirm } from 'devextreme/ui/dialog'
import { Navigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { LoadingOverlay, ProcessDataGrid } from '@paqsuite/react-core'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo, listPartesResource } from '../maestros/partesMaestrosApi'
import {
  listTareas,
  type PartesTareaItem,
  type TareaListQuery,
} from '../carga/partesTareaApi'
import {
  dateDisplayFormat,
  dateSerializationFormat,
  formatMinutosAsHhMm,
  isoDateFromDateBox,
  minutosToHorasDecimal,
  todayIsoDate,
} from '../carga/partesTareaDuration'
import {
  listTareaIds,
  masivoActualizar,
  masivoSetCerrado,
  type MasivoCamposUpdate,
  type TareaIdItem,
} from './partesMasivoApi'
import { buildMasivoApplyPreview, type MasivoApplyPreview } from './masivoApplyPreview'
import { masivoApplyFechaErrorKey } from './masivoApplyValidation'
import { buildMasivoCamposUpdate } from './partesMasivoCampos'
import {
  isSpuriousMasivoClear,
  reduceMasivoSelection,
  type MasivoSelectionEvent,
} from './masivoSelection'
import { usePartesEstadoCerradoOptions } from '../partesFiltroEstado'
import { usePartesTareaGridCaptions } from '../partesTareaGridI18n'
import {
  usePartesDuracionHorasSummaryItems,
  usePartesGridSummaryTypeLabels,
} from '../partesGridSummary'

const PAGE_SIZE = 20

type MasivoGridRow = PartesTareaItem & { duracionHoras: number }

export function ProcesoMasivoPage() {
  const session = getAuthSession()
  if (!session?.partes?.esSupervisor) {
    return <Navigate to="/partes/carga-diaria" replace />
  }

  return <ProcesoMasivoView />
}

function ProcesoMasivoView() {
  const { t } = useTranslation()
  const estadoOpciones = usePartesEstadoCerradoOptions()
  const gridCaptions = usePartesTareaGridCaptions()
  const summaryTypeLabels = usePartesGridSummaryTypeLabels()
  const hoy = todayIsoDate()
  const session = getAuthSession()
  const platform = useMemo(
    () => buildAuthPlatformHeaders(),
    [session?.activeCompanyId, session?.empresas[0]?.id, session?.tenancy]
  )
  const [fechaDesde, setFechaDesde] = useState(hoy)
  const [fechaHasta, setFechaHasta] = useState(hoy)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<'todas' | 'abiertas' | 'cerradas'>('todas')
  const [rows, setRows] = useState<MasivoGridRow[]>([])
  const [loading, setLoading] = useState(true)
  const [total, setTotal] = useState(0)
  const [error, setError] = useState<string | null>(null)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tiposTarea, setTiposTarea] = useState<Record<string, unknown>[]>([])
  const [selectedKeys, setSelectedKeys] = useState<number[]>([])
  const [selectedMap, setSelectedMap] = useState<Record<number, TareaIdItem>>({})
  const selectionRef = useRef({ keys: selectedKeys, map: selectedMap })
  selectionRef.current = { keys: selectedKeys, map: selectedMap }
  const restoringSelectionRef = useRef(false)
  const userSelectionIntentRef = useRef(false)
  const gridHostRef = useRef<HTMLDivElement>(null)
  const [applyTipoTareaId, setApplyTipoTareaId] = useState<number | null>(null)
  const [applySinCargo, setApplySinCargo] = useState(false)
  const [touchSinCargo, setTouchSinCargo] = useState(false)
  const [applyPresencial, setApplyPresencial] = useState(false)
  const [touchPresencial, setTouchPresencial] = useState(false)
  const [applyUsuarioId, setApplyUsuarioId] = useState<number | null>(null)
  const [applyFecha, setApplyFecha] = useState<string>('')
  const [touchFecha, setTouchFecha] = useState(false)
  const [applyConfirmOpen, setApplyConfirmOpen] = useState(false)
  const [applyConfirmPreview, setApplyConfirmPreview] = useState<MasivoApplyPreview | null>(null)
  const [applyConfirmError, setApplyConfirmError] = useState<string | null>(null)
  const [applyErrorOpen, setApplyErrorOpen] = useState(false)
  const [applyErrorMessage, setApplyErrorMessage] = useState('')
  const [applySubmitting, setApplySubmitting] = useState(false)
  const pendingApplyRef = useRef<{
    campos: MasivoCamposUpdate
    items: TareaIdItem[]
  } | null>(null)

  const filters: TareaListQuery = useMemo(
    () => ({
      fechaDesde,
      fechaHasta,
      clienteId: filtroClienteId,
      usuarioId: filtroUsuarioId,
      estadoCerrado,
      page: 1,
      pageSize: PAGE_SIZE,
    }),
    [fechaDesde, fechaHasta, filtroClienteId, filtroUsuarioId, estadoCerrado]
  )

  const duracionSummaryItems = usePartesDuracionHorasSummaryItems()

  const load = useCallback(async () => {
    if (!fechaDesde || !fechaHasta) {
      setError(resolveAuthMessage('partes.tarea.fechasRequeridas'))
      setLoading(false)
      return
    }
    setSelectedKeys([])
    setSelectedMap({})
    selectionRef.current = { keys: [], map: {} }
    setLoading(true)
    setError(null)
    try {
      const result = await listTareas(filters)
      if (result.kind === 'ok') {
        const items = result.envelope.resultado.items ?? []
        setRows(
          items.map((item) => ({
            ...item,
            duracionHoras: minutosToHorasDecimal(item.duracionMinutos),
          }))
        )
        setTotal(result.envelope.resultado.total ?? 0)
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [fechaDesde, fechaHasta, filters])

  useEffect(() => {
    void listCatalogo('clientes').then((result) => {
      if (result.kind === 'ok') {
        setClientes(result.envelope.resultado.items ?? [])
      }
    })
    void listCatalogo('asistentes').then((result) => {
      if (result.kind === 'ok') {
        setAsistentes(result.envelope.resultado.items ?? [])
      }
    })
    void listPartesResource('tipos-tarea').then((result) => {
      if (result.kind === 'ok') {
        setTiposTarea(result.envelope.resultado.items ?? [])
      }
    })
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  useEffect(() => {
    const root = gridHostRef.current
    if (!root) {
      return
    }
    const onPointerDown = (ev: Event) => {
      const el = ev.target as HTMLElement | null
      if (el?.closest('.dx-command-select, .dx-select-checkbox')) {
        userSelectionIntentRef.current = true
      }
    }
    root.addEventListener('pointerdown', onPointerDown, true)
    return () => root.removeEventListener('pointerdown', onPointerDown, true)
  }, [])

  function clearSelection() {
    const empty = { keys: [] as number[], map: {} as Record<number, TareaIdItem> }
    selectionRef.current = empty
    setSelectedKeys([])
    setSelectedMap({})
  }

  function selectedItems(): TareaIdItem[] {
    return selectedKeys
      .map((id) => selectedMap[id])
      .filter((item): item is TareaIdItem => Boolean(item))
  }

  async function handleSelectAllFiltered() {
    const result = await listTareaIds({
      fechaDesde,
      fechaHasta,
      clienteId: filtroClienteId,
      usuarioId: filtroUsuarioId,
      estadoCerrado,
    })
    if (result.kind !== 'ok') {
      if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
      return
    }
    const items = result.envelope.resultado.items ?? []
    const totalIds = result.envelope.resultado.total ?? items.length
    const pages = Math.ceil(totalIds / PAGE_SIZE)
    if (pages > 1) {
      const ok = await confirm(
        t('partes.masivo.selectAllConfirm', { count: totalIds }),
        t('partes.masivo.selectAllTitle'),
      )
      if (!ok) {
        return
      }
    }
    const map: Record<number, TareaIdItem> = {}
    const keys: number[] = []
    items.forEach((item) => {
      map[item.id] = item
      keys.push(item.id)
    })
    setSelectedMap(map)
    setSelectedKeys(keys)
    selectionRef.current = { keys, map }
  }

  const pageIds = useMemo(() => rows.map((row) => row.id), [rows])

  const onSelectionChanged = useCallback(
    (e: MasivoSelectionEvent) => {
      const userIntent = userSelectionIntentRef.current
      userSelectionIntentRef.current = false
      const prev = selectionRef.current
      if (isSpuriousMasivoClear(prev.keys, e, userIntent)) {
        if (restoringSelectionRef.current) {
          return
        }
        const restoreKeys = prev.keys
        const selectRows = e.component?.selectRows
        if (selectRows && restoreKeys.length > 0) {
          restoringSelectionRef.current = true
          queueMicrotask(() => {
            try {
              selectRows(restoreKeys, false)
            } finally {
              restoringSelectionRef.current = false
            }
          })
        }
        return
      }
      const next = reduceMasivoSelection(prev, e, pageIds, userIntent)
      selectionRef.current = next
      setSelectedKeys(next.keys)
      setSelectedMap(next.map)
    },
    [pageIds]
  )

  async function runAccion(accion: 'cerrar' | 'reabrir') {
    const items = selectedItems()
    if (items.length === 0) {
      setError(resolveAuthMessage('partes.masivo.emptySelection'))
      return
    }
    const sample = items
      .slice(0, 5)
      .map((item) => `#${item.id} ${item.fecha ?? ''} ${item.usuarioCode ?? ''}`.trim())
      .join('\n')
    const ok = await confirm(
      t('partes.masivo.confirmProcesoBody', {
        accion:
          accion === 'cerrar' ? t('partes.masivo.accion.cerrar') : t('partes.masivo.accion.reabrir'),
        count: items.length,
        desde: fechaDesde,
        hasta: fechaHasta,
        muestra: sample,
      }),
      t('partes.masivo.confirmProcesoTitle'),
    )
    if (!ok) {
      return
    }
    const result = await masivoSetCerrado(
      accion,
      items.map((item) => ({ id: item.id, rowVersion: item.rowVersion }))
    )
    if (result.kind === 'ok') {
      clearSelection()
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  function showApplyError(message: string) {
    setApplyErrorMessage(message)
    setApplyErrorOpen(true)
  }

  function closeApplyConfirm() {
    setApplyConfirmOpen(false)
    setApplyConfirmPreview(null)
    setApplyConfirmError(null)
    pendingApplyRef.current = null
  }

  function resetApplyCampos() {
    setApplyTipoTareaId(null)
    setTouchSinCargo(false)
    setApplySinCargo(false)
    setTouchPresencial(false)
    setApplyPresencial(false)
    setApplyUsuarioId(null)
    setTouchFecha(false)
    setApplyFecha('')
  }

  async function runActualizarCampos() {
    const items = selectedItems()
    if (items.length === 0) {
      showApplyError(resolveAuthMessage('partes.masivo.emptySelection'))
      return
    }
    const campos = buildMasivoCamposUpdate({
      tipoTareaId: applyTipoTareaId,
      touchSinCargo,
      sinCargo: applySinCargo,
      touchPresencial,
      presencial: applyPresencial,
      usuarioId: applyUsuarioId,
      fecha: touchFecha ? applyFecha : null,
    })
    if (!campos) {
      showApplyError(resolveAuthMessage('partes.masivo.atributoInvalido'))
      return
    }
    const fechaErrorKey = masivoApplyFechaErrorKey(touchFecha, applyFecha)
    if (fechaErrorKey) {
      showApplyError(resolveAuthMessage(fechaErrorKey))
      return
    }
    pendingApplyRef.current = { campos, items }
    setApplyConfirmPreview(
      buildMasivoApplyPreview({
        itemCount: items.length,
        applyTipoTareaId,
        tiposTarea,
        touchSinCargo,
        applySinCargo,
        touchPresencial,
        applyPresencial,
        applyUsuarioId,
        asistentes,
        touchFecha,
        applyFecha,
        fechaDesde,
        fechaHasta,
        items,
      }, t)
    )
    setApplyConfirmError(null)
    setApplyConfirmOpen(true)
  }

  async function confirmApplyCampos() {
    const pending = pendingApplyRef.current
    if (!pending) {
      closeApplyConfirm()
      return
    }
    const fechaErrorKey = masivoApplyFechaErrorKey(touchFecha, applyFecha)
    if (fechaErrorKey) {
      setApplyConfirmError(resolveAuthMessage(fechaErrorKey))
      return
    }
    setApplyConfirmError(null)
    setApplySubmitting(true)
    try {
      const result = await masivoActualizar(
        pending.campos,
        pending.items.map((item) => ({ id: item.id, rowVersion: item.rowVersion }))
      )
      if (result.kind === 'ok') {
        closeApplyConfirm()
        clearSelection()
        resetApplyCampos()
        void load()
        return
      }
      if (result.kind === 'envelopeError') {
        setApplyConfirmError(resolveAuthMessage(result.envelope.respuesta))
        return
      }
      setApplyConfirmError(resolveAuthMessage(result.i18nKey || 'infra.transport'))
    } catch {
      setApplyConfirmError(resolveAuthMessage('infra.unexpected'))
    } finally {
      setApplySubmitting(false)
    }
  }

  return (
    <div data-testid="partesMasivoPage" style={{ padding: 16 }}>
      <LoadingOverlay visible={loading} />
      <h2 style={{ marginTop: 0 }}>{t('partes.masivo.title')}</h2>
      <div
        data-testid="partesMasivoFiltros"
        style={{ display: 'flex', flexWrap: 'wrap', gap: 12, marginBottom: 12, alignItems: 'end' }}
      >
        <div>
          <label>{t('partes.common.desde')}</label>
          <DateBox
            value={fechaDesde}
            type="date"
            displayFormat={dateDisplayFormat}
            dateSerializationFormat={dateSerializationFormat}
            onValueChanged={(e) => {
              const next = isoDateFromDateBox(e)
              if (next !== null) {
                setFechaDesde(next)
              }
            }}
          />
        </div>
        <div>
          <label>{t('partes.common.hasta')}</label>
          <DateBox
            value={fechaHasta}
            type="date"
            displayFormat={dateDisplayFormat}
            dateSerializationFormat={dateSerializationFormat}
            onValueChanged={(e) => {
              const next = isoDateFromDateBox(e)
              if (next !== null) {
                setFechaHasta(next)
              }
            }}
          />
        </div>
        <div style={{ minWidth: 200 }}>
          <label>{t('partes.informe.filtro.cliente')}</label>
          <SelectBox
            dataSource={clientes}
            value={filtroClienteId}
            valueExpr="id"
            displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
            showClearButton
            searchEnabled
            onValueChanged={(e) => setFiltroClienteId((e.value as number | null) ?? null)}
          />
        </div>
        <div style={{ minWidth: 200 }}>
          <label>{t('partes.informe.filtro.asistente')}</label>
          <SelectBox
            dataSource={asistentes}
            value={filtroUsuarioId}
            valueExpr="id"
            displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
            showClearButton
            searchEnabled
            onValueChanged={(e) => setFiltroUsuarioId((e.value as number | null) ?? null)}
          />
        </div>
        <div style={{ minWidth: 160 }}>
          <label>{t('partes.informe.filtro.estadoCerrado')}</label>
          <SelectBox
            dataSource={estadoOpciones}
            value={estadoCerrado}
            valueExpr="id"
            displayExpr="text"
            onValueChanged={(e) =>
              setEstadoCerrado((e.value as 'todas' | 'abiertas' | 'cerradas') ?? 'todas')
            }
          />
        </div>
        <Button
          text={t('partes.common.buscar')}
          onClick={() => void load()}
          elementAttr={{ 'data-testid': 'partesMasivoSearch' }}
        />
      </div>

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 12, alignItems: 'center' }}>
        <Button
          text={t('partes.masivo.selectAllFiltered')}
          onClick={() => void handleSelectAllFiltered()}
          elementAttr={{ 'data-testid': 'partesMasivoSelectAll' }}
        />
        <Button
          text={t('partes.masivo.cerrarSeleccion')}
          type="default"
          onClick={() => void runAccion('cerrar')}
          elementAttr={{ 'data-testid': 'partesMasivoConfirmAction' }}
        />
        <Button
          text={t('partes.masivo.reabrirSeleccion')}
          onClick={() => void runAccion('reabrir')}
          elementAttr={{ 'data-testid': 'partesMasivoReabrir' }}
        />
        <span data-testid="partesMasivoSelectionCount">
          {t('partes.masivo.seleccionados', { count: selectedKeys.length })}
        </span>
      </div>

      <div
        data-testid="partesMasivoApplyCampos"
        style={{
          display: 'flex',
          flexWrap: 'wrap',
          gap: 12,
          marginBottom: 12,
          alignItems: 'end',
          padding: 12,
          border: '1px solid var(--dx-color-border, #ddd)',
          borderRadius: 4,
        }}
      >
        <div style={{ minWidth: 260 }}>
          <label>{t('partes.masivo.loteTipoTarea')}</label>
          <SelectBox
            dataSource={tiposTarea}
            value={applyTipoTareaId}
            valueExpr="id"
            displayExpr={(item) =>
              item ? `${String(item.code ?? '')} — ${String(item.descripcion ?? '')}` : ''
            }
            showClearButton
            searchEnabled
            elementAttr={{ 'data-testid': 'partesMasivoTipoTarea' }}
            onValueChanged={(e) => setApplyTipoTareaId((e.value as number | null) ?? null)}
          />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          <CheckBox
            text={t('partes.masivo.cambiarSinCargo')}
            value={touchSinCargo}
            elementAttr={{ 'data-testid': 'partesMasivoTouchSinCargo' }}
            onValueChanged={(e) => setTouchSinCargo(Boolean(e.value))}
          />
          <CheckBox
            text={t('partes.informe.field.sinCargo')}
            value={applySinCargo}
            disabled={!touchSinCargo}
            elementAttr={{ 'data-testid': 'partesMasivoSinCargo' }}
            onValueChanged={(e) => setApplySinCargo(Boolean(e.value))}
          />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          <CheckBox
            text={t('partes.masivo.cambiarPresencial')}
            value={touchPresencial}
            elementAttr={{ 'data-testid': 'partesMasivoTouchPresencial' }}
            onValueChanged={(e) => setTouchPresencial(Boolean(e.value))}
          />
          <CheckBox
            text={t('partes.informe.field.presencial')}
            value={applyPresencial}
            disabled={!touchPresencial}
            elementAttr={{ 'data-testid': 'partesMasivoPresencial' }}
            onValueChanged={(e) => setApplyPresencial(Boolean(e.value))}
          />
        </div>
        <div style={{ minWidth: 220 }}>
          <label>{t('partes.masivo.loteAsistente')}</label>
          <SelectBox
            dataSource={asistentes}
            value={applyUsuarioId}
            valueExpr="id"
            displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
            showClearButton
            searchEnabled
            elementAttr={{ 'data-testid': 'partesMasivoAsistente' }}
            onValueChanged={(e) => setApplyUsuarioId((e.value as number | null) ?? null)}
          />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6, minWidth: 180 }}>
          <CheckBox
            text={t('partes.masivo.cambiarFecha')}
            value={touchFecha}
            elementAttr={{ 'data-testid': 'partesMasivoTouchFecha' }}
            onValueChanged={(e) => {
              const on = Boolean(e.value)
              setTouchFecha(on)
              if (on && !applyFecha) {
                setApplyFecha(hoy)
              }
            }}
          />
          <DateBox
            value={applyFecha || undefined}
            type="date"
            displayFormat={dateDisplayFormat}
            dateSerializationFormat={dateSerializationFormat}
            disabled={!touchFecha}
            elementAttr={{ 'data-testid': 'partesMasivoFecha' }}
            onValueChanged={(e) => {
              const next = isoDateFromDateBox(e)
              if (next !== null) {
                setApplyFecha(next)
              }
            }}
          />
        </div>
        <Button
          text={t('partes.masivo.aplicarCambios')}
          type="success"
          onClick={() => void runActualizarCampos()}
          elementAttr={{ 'data-testid': 'partesMasivoApplyCamposBtn' }}
        />
      </div>

      {error ? (
        <div role="alert" data-testid="partesMasivoError">
          {error}
        </div>
      ) : null}

      <Popup
        visible={applyConfirmOpen}
        onHiding={() => {
          if (!applySubmitting) {
            closeApplyConfirm()
          }
        }}
        title={t('partes.masivo.confirmApplyTitle')}
        width={480}
        height="auto"
        showCloseButton={!applySubmitting}
        wrapperAttr={{ 'data-testid': 'partesMasivoApplyConfirmDialog' }}
      >
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12, padding: 8 }}>
          {applyConfirmError ? (
            <div
              role="alert"
              data-testid="partesMasivoApplyConfirmError"
              style={{
                padding: '8px 10px',
                borderRadius: 4,
                background: 'var(--dx-color-danger, #fde7e9)',
                color: 'var(--dx-color-danger, #d13438)',
              }}
            >
              {applyConfirmError}
            </div>
          ) : null}
          {applyConfirmPreview ? (
            <div
              data-testid="partesMasivoApplyConfirmSummary"
              style={{ display: 'flex', flexDirection: 'column', gap: 6 }}
            >
              {applyConfirmPreview.rows.map((row) => (
                <div
                  key={row.label}
                  style={{ display: 'flex', gap: 8, alignItems: 'baseline', flexWrap: 'wrap' }}
                >
                  <strong style={{ minWidth: 108, flexShrink: 0 }}>{row.label}:</strong>
                  <span>{row.value}</span>
                </div>
              ))}
              {applyConfirmPreview.muestra.length > 0 ? (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                  <strong>{t('partes.common.muestra')}</strong>
                  {applyConfirmPreview.muestra.map((line) => (
                    <span key={line}>{line}</span>
                  ))}
                </div>
              ) : null}
            </div>
          ) : null}
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button
              text={t('parametros.modal.cancel')}
              disabled={applySubmitting}
              onClick={closeApplyConfirm}
              elementAttr={{ 'data-testid': 'partesMasivoApplyConfirmCancel' }}
            />
            <Button
              text={t('partes.common.confirmar')}
              type="default"
              disabled={applySubmitting}
              onClick={() => void confirmApplyCampos()}
              elementAttr={{ 'data-testid': 'partesMasivoApplyConfirmOk' }}
            />
          </div>
        </div>
      </Popup>

      <Popup
        visible={applyErrorOpen}
        onHiding={() => {
          setApplyErrorOpen(false)
          setApplyErrorMessage('')
        }}
        title={t('partes.masivo.applyErrorTitle')}
        width={480}
        height="auto"
        showCloseButton
        wrapperAttr={{ 'data-testid': 'partesMasivoApplyErrorDialog' }}
      >
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12, padding: 8 }}>
          <div role="alert" data-testid="partesMasivoApplyError">
            {applyErrorMessage}
          </div>
          <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
            <Button
              text={t('partes.common.cerrar')}
              type="default"
              onClick={() => {
                setApplyErrorOpen(false)
                setApplyErrorMessage('')
              }}
              elementAttr={{ 'data-testid': 'partesMasivoApplyErrorClose' }}
            />
          </div>
        </div>
      </Popup>

      <div data-testid="partesMasivoGrid" ref={gridHostRef}>
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.masivo"
          gridId="procesoMasivo"
          accessToken={getAuthToken()}
          platform={platform}
          selectedRowKeys={selectedKeys}
          onSelectionChanged={onSelectionChanged}
          defaultTotalItems={duracionSummaryItems}
          summaryTypeLabels={summaryTypeLabels}
        >
          <Selection mode="multiple" showCheckBoxesMode="always" />
          <Paging defaultPageSize={PAGE_SIZE} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption={gridCaptions.fecha} dataType="date" />
          <Column dataField="usuarioCode" caption={gridCaptions.asistente} />
          <Column dataField="clienteNombre" caption={gridCaptions.cliente} />
          <Column dataField="tipoTareaDescripcion" caption={gridCaptions.tipoTarea} />
          <Column
            dataField="duracionHoras"
            caption={gridCaptions.duracion}
            dataType="number"
            customizeText={(cell) =>
              formatMinutosAsHhMm(Math.round(Number(cell.value ?? 0) * 60))
            }
          />
          <Column dataField="sinCargo" caption={gridCaptions.sinCargo} dataType="boolean" />
          <Column dataField="presencial" caption={gridCaptions.presencial} dataType="boolean" />
          <Column dataField="observacion" caption={gridCaptions.observacion} />
          <Column dataField="cerrado" caption={gridCaptions.cerrado} dataType="boolean" />
          <Column dataField="clienteCode" caption={gridCaptions.clienteCode} visible={false} />
          <Column dataField="tipoTareaCode" caption={gridCaptions.tipoTareaCode} visible={false} />
          <Column dataField="duracionMinutos" caption={gridCaptions.minutos} dataType="number" visible={false} />
        </ProcessDataGrid>
        <div style={{ marginTop: 8, opacity: 0.7 }}>{t('partes.common.totalFiltro', { count: total })}</div>
      </div>
    </div>
  )
}
