import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import DataGrid, {
  Column,
  Paging,
  Pager,
  Selection,
  type DataGridRef,
} from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import { confirm } from 'devextreme/ui/dialog'
import { Navigate } from 'react-router-dom'
import { getAuthSession } from '../../auth/authSessionStore'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  listTareas,
  type PartesTareaItem,
  type TareaListQuery,
} from '../carga/partesTareaApi'
import { todayIsoDate } from '../carga/partesTareaDuration'
import { listTareaIds, masivoSetCerrado, type TareaIdItem } from './partesMasivoApi'

const PAGE_SIZE = 20

export function ProcesoMasivoPage() {
  const session = getAuthSession()
  if (!session?.partes?.esSupervisor) {
    return <Navigate to="/partes/carga-diaria" replace />
  }

  return <ProcesoMasivoView />
}

function ProcesoMasivoView() {
  const hoy = todayIsoDate()
  const gridRef = useRef<DataGridRef>(null)
  const [fechaDesde, setFechaDesde] = useState(hoy)
  const [fechaHasta, setFechaHasta] = useState(hoy)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<'todas' | 'abiertas' | 'cerradas'>('todas')
  const [rows, setRows] = useState<PartesTareaItem[]>([])
  const [total, setTotal] = useState(0)
  const [error, setError] = useState<string | null>(null)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [selectedKeys, setSelectedKeys] = useState<number[]>([])
  const [selectedMap, setSelectedMap] = useState<Record<number, TareaIdItem>>({})

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

  const load = useCallback(async () => {
    if (!fechaDesde || !fechaHasta) {
      setError(resolveAuthMessage('partes.tarea.fechasRequeridas'))
      return
    }
    setError(null)
    const result = await listTareas(filters)
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
      setTotal(result.envelope.resultado.total ?? 0)
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
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
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  function clearSelection() {
    setSelectedKeys([])
    setSelectedMap({})
    gridRef.current?.instance()?.deselectAll()
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

  function onSelectionChanged(e: { selectedRowKeys?: Array<string | number>; selectedRowsData?: PartesTareaItem[] }) {
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
    const items = selectedKeys
      .map((id) => selectedMap[id])
      .filter((item): item is TareaIdItem => Boolean(item))
    if (items.length === 0) {
      setError(resolveAuthMessage('partes.masivo.emptySelection'))
      return
    }
    const sample = items.slice(0, 5)
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

  return (
    <div data-testid="partesMasivoPage" style={{ padding: 16 }}>
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

      <div style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
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

      {error ? (
        <div role="alert" data-testid="partesMasivoError">
          {error}
        </div>
      ) : null}

      <div data-testid="partesMasivoGrid">
        <DataGrid
          ref={gridRef}
          dataSource={rows}
          keyExpr="id"
          showBorders
          hoverStateEnabled
          selectedRowKeys={selectedKeys}
          onSelectionChanged={onSelectionChanged}
        >
          <Selection mode="multiple" showCheckBoxesMode="always" />
          <Paging defaultPageSize={PAGE_SIZE} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption="Fecha" dataType="date" />
          <Column dataField="usuarioCode" caption="Asistente" />
          <Column dataField="clienteCode" caption="Cliente" />
          <Column dataField="tipoTareaCode" caption="Tipo" />
          <Column dataField="duracionMinutos" caption="Minutos" />
          <Column dataField="observacion" caption="Observación" />
          <Column dataField="cerrado" caption="Cerrado" />
        </DataGrid>
        <div style={{ marginTop: 8, opacity: 0.7 }}>Total filtro: {total}</div>
      </div>
    </div>
  )
}
