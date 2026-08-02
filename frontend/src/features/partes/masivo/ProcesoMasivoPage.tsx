import { useCallback, useEffect, useMemo, useState } from 'react'
import { Column, Paging, Pager, Selection } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import CheckBox from 'devextreme-react/check-box'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import { confirm } from 'devextreme/ui/dialog'
import { Navigate } from 'react-router-dom'
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
  formatMinutosAsHhMm,
  minutosToHorasDecimal,
  todayIsoDate,
} from '../carga/partesTareaDuration'
import {
  listTareaIds,
  masivoActualizar,
  masivoSetCerrado,
  type TareaIdItem,
} from './partesMasivoApi'
import { buildMasivoCamposUpdate } from './partesMasivoCampos'

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
  const hoy = todayIsoDate()
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
  const [applyTipoTareaId, setApplyTipoTareaId] = useState<number | null>(null)
  const [applySinCargo, setApplySinCargo] = useState(false)
  const [touchSinCargo, setTouchSinCargo] = useState(false)
  const [applyPresencial, setApplyPresencial] = useState(false)
  const [touchPresencial, setTouchPresencial] = useState(false)
  const [applyUsuarioId, setApplyUsuarioId] = useState<number | null>(null)
  const [applyFecha, setApplyFecha] = useState<string>('')
  const [touchFecha, setTouchFecha] = useState(false)

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

  const duracionSummaryItems = useMemo(
    () => [
      {
        column: 'duracionHoras',
        summaryType: 'sum' as const,
        name: 'pq-duracionHoras-sum',
        displayFormat: 'Suma: {0} h',
        valueFormat: '#0.##',
      },
    ],
    []
  )

  const load = useCallback(async () => {
    if (!fechaDesde || !fechaHasta) {
      setError(resolveAuthMessage('partes.tarea.fechasRequeridas'))
      setLoading(false)
      return
    }
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

  function clearSelection() {
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
        `Afectará a ${totalIds} partes. ¿Confirma?`,
        'Seleccionar todos'
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
  }

  function onSelectionChanged(e: {
    selectedRowKeys?: Array<string | number>
    selectedRowsData?: PartesTareaItem[]
  }) {
    const keys = (e.selectedRowKeys ?? []).map((key) => Number(key))
    setSelectedKeys(keys)
    setSelectedMap((prev) => {
      const next = { ...prev }
      Object.keys(next).forEach((key) => {
        if (!keys.includes(Number(key))) {
          delete next[Number(key)]
        }
      })
      ;(e.selectedRowsData ?? []).forEach((row) => {
        next[row.id] = {
          id: row.id,
          rowVersion: row.rowVersion,
          fecha: String(row.fecha).slice(0, 10),
          usuarioCode: row.usuarioCode,
        }
      })
      return next
    })
  }

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
      `${accion === 'cerrar' ? 'Cerrar' : 'Reabrir'} ${items.length} parte(s).\n` +
        `Rango filtro: ${fechaDesde} → ${fechaHasta}\n` +
        `Muestra:\n${sample}`,
      'Confirmar proceso masivo'
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

  async function runActualizarCampos() {
    const items = selectedItems()
    if (items.length === 0) {
      setError(resolveAuthMessage('partes.masivo.emptySelection'))
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
      setError(resolveAuthMessage('partes.masivo.atributoInvalido'))
      return
    }
    const tipoLabel =
      applyTipoTareaId == null
        ? '—'
        : (() => {
            const tipo = tiposTarea.find((row) => Number(row.id) === applyTipoTareaId)
            return tipo
              ? `${String(tipo.code ?? '')} — ${String(tipo.descripcion ?? '')}`
              : String(applyTipoTareaId)
          })()
    const asistenteLabel =
      applyUsuarioId == null
        ? '(sin cambio)'
        : (() => {
            const asistente = asistentes.find((row) => Number(row.id) === applyUsuarioId)
            return asistente
              ? `${String(asistente.code ?? '')} — ${String(asistente.nombre ?? '')}`
              : String(applyUsuarioId)
          })()
    const sample = items
      .slice(0, 5)
      .map((item) => `#${item.id} ${item.fecha ?? ''} ${item.usuarioCode ?? ''}`.trim())
      .join('\n')
    const ok = await confirm(
      `Aplicar cambios a ${items.length} parte(s).\n` +
        `Tipo de tarea: ${applyTipoTareaId == null ? '(sin cambio)' : tipoLabel}\n` +
        `Sin cargo: ${touchSinCargo ? (applySinCargo ? 'Sí' : 'No') : '(sin cambio)'}\n` +
        `Presencial: ${touchPresencial ? (applyPresencial ? 'Sí' : 'No') : '(sin cambio)'}\n` +
        `Asistente: ${asistenteLabel}\n` +
        `Fecha: ${touchFecha && applyFecha ? applyFecha : '(sin cambio)'}\n` +
        `Rango filtro: ${fechaDesde} → ${fechaHasta}\n` +
        `Muestra:\n${sample}`,
      'Confirmar actualización masiva'
    )
    if (!ok) {
      return
    }
    const result = await masivoActualizar(
      campos,
      items.map((item) => ({ id: item.id, rowVersion: item.rowVersion }))
    )
    if (result.kind === 'ok') {
      clearSelection()
      setApplyTipoTareaId(null)
      setTouchSinCargo(false)
      setApplySinCargo(false)
      setTouchPresencial(false)
      setApplyPresencial(false)
      setApplyUsuarioId(null)
      setTouchFecha(false)
      setApplyFecha('')
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="partesMasivoPage" style={{ padding: 16 }}>
      <LoadingOverlay visible={loading} />
      <h2 style={{ marginTop: 0 }}>Proceso masivo</h2>
      <div
        data-testid="partesMasivoFiltros"
        style={{ display: 'flex', flexWrap: 'wrap', gap: 12, marginBottom: 12, alignItems: 'end' }}
      >
        <div>
          <label>Desde</label>
          <DateBox
            value={fechaDesde}
            type="date"
            onValueChanged={(e) =>
              setFechaDesde(e.value ? todayIsoDate(new Date(e.value as Date)) : '')
            }
          />
        </div>
        <div>
          <label>Hasta</label>
          <DateBox
            value={fechaHasta}
            type="date"
            onValueChanged={(e) =>
              setFechaHasta(e.value ? todayIsoDate(new Date(e.value as Date)) : '')
            }
          />
        </div>
        <div style={{ minWidth: 200 }}>
          <label>Cliente</label>
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
          <label>Asistente</label>
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
          <label>Estado</label>
          <SelectBox
            dataSource={[
              { id: 'todas', text: 'Todas' },
              { id: 'abiertas', text: 'Abiertas' },
              { id: 'cerradas', text: 'Cerradas' },
            ]}
            value={estadoCerrado}
            valueExpr="id"
            displayExpr="text"
            onValueChanged={(e) =>
              setEstadoCerrado((e.value as 'todas' | 'abiertas' | 'cerradas') ?? 'todas')
            }
          />
        </div>
        <Button text="Buscar" onClick={() => void load()} elementAttr={{ 'data-testid': 'partesMasivoSearch' }} />
      </div>

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 12, alignItems: 'center' }}>
        <Button
          text="Seleccionar todos del filtro"
          onClick={() => void handleSelectAllFiltered()}
          elementAttr={{ 'data-testid': 'partesMasivoSelectAll' }}
        />
        <Button
          text="Cerrar selección"
          type="default"
          onClick={() => void runAccion('cerrar')}
          elementAttr={{ 'data-testid': 'partesMasivoConfirmAction' }}
        />
        <Button
          text="Reabrir selección"
          onClick={() => void runAccion('reabrir')}
          elementAttr={{ 'data-testid': 'partesMasivoReabrir' }}
        />
        <span data-testid="partesMasivoSelectionCount">Seleccionados: {selectedKeys.length}</span>
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
          <label>Tipo de tarea (lote)</label>
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
            text="Cambiar sin cargo"
            value={touchSinCargo}
            elementAttr={{ 'data-testid': 'partesMasivoTouchSinCargo' }}
            onValueChanged={(e) => setTouchSinCargo(Boolean(e.value))}
          />
          <CheckBox
            text="Sin cargo"
            value={applySinCargo}
            disabled={!touchSinCargo}
            elementAttr={{ 'data-testid': 'partesMasivoSinCargo' }}
            onValueChanged={(e) => setApplySinCargo(Boolean(e.value))}
          />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          <CheckBox
            text="Cambiar presencial"
            value={touchPresencial}
            elementAttr={{ 'data-testid': 'partesMasivoTouchPresencial' }}
            onValueChanged={(e) => setTouchPresencial(Boolean(e.value))}
          />
          <CheckBox
            text="Presencial"
            value={applyPresencial}
            disabled={!touchPresencial}
            elementAttr={{ 'data-testid': 'partesMasivoPresencial' }}
            onValueChanged={(e) => setApplyPresencial(Boolean(e.value))}
          />
        </div>
        <div style={{ minWidth: 220 }}>
          <label>Asistente (lote)</label>
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
            text="Cambiar fecha"
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
            disabled={!touchFecha}
            elementAttr={{ 'data-testid': 'partesMasivoFecha' }}
            onValueChanged={(e) =>
              setApplyFecha(e.value ? todayIsoDate(new Date(e.value as Date)) : '')
            }
          />
        </div>
        <Button
          text="Aplicar cambios a selección"
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

      <div data-testid="partesMasivoGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.masivo"
          gridId="procesoMasivo"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          selectedRowKeys={selectedKeys}
          onSelectionChanged={onSelectionChanged}
          defaultTotalItems={duracionSummaryItems}
        >
          <Selection mode="multiple" showCheckBoxesMode="always" />
          <Paging defaultPageSize={PAGE_SIZE} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption="Fecha" dataType="date" />
          <Column dataField="usuarioCode" caption="Asistente" />
          <Column dataField="clienteNombre" caption="Cliente" />
          <Column dataField="tipoTareaDescripcion" caption="Tipo de Tarea" />
          <Column
            dataField="duracionHoras"
            caption="Duración"
            dataType="number"
            customizeText={(cell) =>
              formatMinutosAsHhMm(Math.round(Number(cell.value ?? 0) * 60))
            }
          />
          <Column dataField="sinCargo" caption="Sin cargo" dataType="boolean" />
          <Column dataField="presencial" caption="Presencial" dataType="boolean" />
          <Column dataField="observacion" caption="Observación" />
          <Column dataField="cerrado" caption="Cerrado" dataType="boolean" />
          <Column dataField="clienteCode" caption="Cliente (código)" visible={false} />
          <Column dataField="tipoTareaCode" caption="Tipo (código)" visible={false} />
          <Column dataField="duracionMinutos" caption="Minutos" dataType="number" visible={false} />
        </ProcessDataGrid>
        <div style={{ marginTop: 8, opacity: 0.7 }}>Total filtro: {total}</div>
      </div>
    </div>
  )
}
