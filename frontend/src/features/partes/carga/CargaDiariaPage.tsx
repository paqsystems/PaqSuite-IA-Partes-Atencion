import { useCallback, useEffect, useMemo, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import TextBox from 'devextreme-react/text-box'
import NumberBox from 'devextreme-react/number-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { Link } from 'react-router-dom'
import { getAuthSession } from '../../auth/authSessionStore'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  deleteTarea,
  fetchDuracionTramo,
  listTareas,
  saveTarea,
  setTareaCerrado,
  type PartesTareaItem,
} from './partesTareaApi'
import {
  buildTramoOptions,
  isFechaFutura,
  isValidDuracionMinutos,
  todayIsoDate,
} from './partesTareaDuration'

type FormState = {
  usuarioId: number | null
  clienteId: number | null
  tipoTareaId: number | null
  fecha: string
  duracionMinutos: number
  sinCargo: boolean
  presencial: boolean
  observacion: string
  rowVersion?: string
}

const emptyForm = (asistenteId: number | null): FormState => ({
  usuarioId: asistenteId,
  clienteId: null,
  tipoTareaId: null,
  fecha: todayIsoDate(),
  duracionMinutos: 15,
  sinCargo: false,
  presencial: false,
  observacion: '',
})

export function CargaDiariaPage() {
  const session = getAuthSession()
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const asistenteId = session?.partes?.asistenteId ?? null

  const hoy = todayIsoDate()
  const [fechaDesde, setFechaDesde] = useState(hoy)
  const [fechaHasta, setFechaHasta] = useState(hoy)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<'todas' | 'abiertas' | 'cerradas'>('todas')
  const [rows, setRows] = useState<PartesTareaItem[]>([])
  const [total, setTotal] = useState(0)
  const [error, setError] = useState<string | null>(null)
  const [tramo, setTramo] = useState(15)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<FormState>(() => emptyForm(asistenteId))

  const tramoOptions = useMemo(() => buildTramoOptions(tramo), [tramo])

  const load = useCallback(async () => {
    if (!fechaDesde || !fechaHasta) {
      setError(resolveAuthMessage('partes.tarea.fechasRequeridas'))
      setRows([])
      return
    }
    setError(null)
    const result = await listTareas({
      fechaDesde,
      fechaHasta,
      clienteId: filtroClienteId,
      usuarioId: esSupervisor ? filtroUsuarioId : null,
      estadoCerrado,
    })
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
      setTotal(result.envelope.resultado.total ?? 0)
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [fechaDesde, fechaHasta, filtroClienteId, filtroUsuarioId, estadoCerrado, esSupervisor])

  useEffect(() => {
    void fetchDuracionTramo().then((result) => {
      if (result.kind === 'ok') {
        setTramo(result.envelope.resultado.tramoMinutos || 15)
      }
    })
    void listCatalogo('clientes').then((result) => {
      if (result.kind === 'ok') {
        setClientes(result.envelope.resultado.items ?? [])
      }
    })
    if (esSupervisor) {
      void listCatalogo('asistentes').then((result) => {
        if (result.kind === 'ok') {
          setAsistentes(result.envelope.resultado.items ?? [])
        }
      })
    }
  }, [esSupervisor])

  useEffect(() => {
    void load()
  }, [load])

  async function loadUniverso(clienteId: number | null) {
    if (!clienteId) {
      setTipos([])
      return
    }
    const result = await listCatalogo('tipos-tarea', `?clienteId=${clienteId}`)
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setTipos(items)
      return items
    }
    setTipos([])
    return []
  }

  function openCreate() {
    setEditingId(null)
    const initial = emptyForm(asistenteId)
    initial.duracionMinutos = tramo
    setForm(initial)
    setTipos([])
    setFormOpen(true)
  }

  async function openEdit(row: PartesTareaItem) {
    if (row.cerrado) {
      return
    }
    setEditingId(row.id)
    setForm({
      usuarioId: row.usuarioId,
      clienteId: row.clienteId,
      tipoTareaId: row.tipoTareaId,
      fecha: String(row.fecha).slice(0, 10),
      duracionMinutos: row.duracionMinutos,
      sinCargo: row.sinCargo,
      presencial: row.presencial,
      observacion: row.observacion,
      rowVersion: row.rowVersion,
    })
    await loadUniverso(row.clienteId)
    setFormOpen(true)
  }

  async function handleClienteChange(clienteId: number | null) {
    const items = await loadUniverso(clienteId)
    setForm((prev) => {
      const stillValid = items?.some((item) => Number(item.id) === prev.tipoTareaId)
      const defaultTipo = items?.find((item) => item.isDefault) ?? items?.[0]
      return {
        ...prev,
        clienteId,
        tipoTareaId: stillValid
          ? prev.tipoTareaId
          : defaultTipo
            ? Number(defaultTipo.id)
            : null,
      }
    })
  }

  async function persist(confirmFutura = false) {
    if (!isValidDuracionMinutos(form.duracionMinutos, tramo)) {
      setError(resolveAuthMessage('partes.tarea.duracionInvalida'))
      return
    }
    if (isFechaFutura(form.fecha) && !confirmFutura) {
      const ok = await confirm(
        'La fecha es futura. ¿Confirma el registro?',
        'Fecha futura'
      )
      if (!ok) {
        return
      }
      return persist(true)
    }

    const body: Record<string, unknown> = {
      usuarioId: form.usuarioId,
      clienteId: form.clienteId,
      tipoTareaId: form.tipoTareaId,
      fecha: form.fecha,
      duracionMinutos: form.duracionMinutos,
      sinCargo: form.sinCargo,
      presencial: form.presencial,
      observacion: form.observacion,
      confirmarFechaFutura: confirmFutura || undefined,
    }
    if (editingId !== null) {
      body.rowVersion = form.rowVersion
    }

    const result = await saveTarea(body, editingId ?? undefined)
    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      if (result.envelope.respuesta === 'partes.tarea.fechaFuturaConfirmacion') {
        return persist(true)
      }
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: PartesTareaItem) {
    if (row.cerrado) {
      return
    }
    const ok = await confirm('¿Eliminar la tarea?', 'Eliminar')
    if (!ok) {
      return
    }
    const result = await deleteTarea(row.id, row.rowVersion)
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleCerrarReabrir(row: PartesTareaItem) {
    const result = await setTareaCerrado(row.id, row.rowVersion, !row.cerrado)
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="partesCargaPage" style={{ padding: 16 }}>
      <div style={{ display: 'flex', gap: 12, alignItems: 'center', marginBottom: 12 }}>
        <h2 style={{ margin: 0, flex: 1 }}>Carga diaria</h2>
        <Link to="/partes/proceso-masivo" data-testid="partesCargaLinkMasivo">
          Ir a proceso masivo
        </Link>
      </div>

      <div
        data-testid="partesCargaFiltros"
        style={{ display: 'flex', flexWrap: 'wrap', gap: 12, marginBottom: 12, alignItems: 'end' }}
      >
        <div>
          <label>Desde</label>
          <DateBox
            value={fechaDesde}
            type="date"
            displayFormat="yyyy-MM-dd"
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
            displayFormat="yyyy-MM-dd"
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
        {esSupervisor ? (
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
        ) : null}
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
        <Button text="Buscar" onClick={() => void load()} elementAttr={{ 'data-testid': 'partesCargaSearch' }} />
      </div>

      {error ? (
        <div role="alert" data-testid="partesCargaError">
          {error}
        </div>
      ) : null}
      <div data-testid="partesCargaGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          onCreate={openCreate}
          createHint="Nueva tarea"
          createTestId="partesCargaAdd"
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption="Fecha" dataType="date" />
          {esSupervisor ? <Column dataField="usuarioCode" caption="Asistente" /> : null}
          <Column dataField="clienteCode" caption="Cliente" />
          <Column dataField="tipoTareaCode" caption="Tipo" />
          <Column dataField="duracionMinutos" caption="Minutos" />
          <Column dataField="observacion" caption="Observación" />
          <Column dataField="cerrado" caption="Cerrado" />
          <Column
            type="buttons"
            buttons={[
              {
                hint: 'Editar',
                icon: 'edit',
                visible: (e) => !(e.row?.data as PartesTareaItem | undefined)?.cerrado,
                onClick: (e) => void openEdit(e.row?.data as PartesTareaItem),
              },
              {
                hint: 'Eliminar',
                icon: 'trash',
                visible: (e) => !(e.row?.data as PartesTareaItem | undefined)?.cerrado,
                onClick: (e) => void handleDelete(e.row?.data as PartesTareaItem),
              },
              {
                hint: 'Cerrar/Reabrir',
                icon: 'isblank',
                visible: () => esSupervisor,
                onClick: (e) => void handleCerrarReabrir(e.row?.data as PartesTareaItem),
              },
            ]}
          />
        </ProcessDataGrid>
        <div style={{ marginTop: 8, opacity: 0.7 }}>Total: {total}</div>
      </div>

      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={editingId ? 'Editar tarea' : 'Nueva tarea'}
        width={560}
        height="auto"
        showCloseButton
      >
        <div style={{ display: 'grid', gap: 10, padding: 8 }} data-testid="partesCargaForm">
          {esSupervisor ? (
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>Asistente</label>
              <SelectBox
                dataSource={asistentes}
                value={form.usuarioId}
                valueExpr="id"
                displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
                searchEnabled
                onValueChanged={(e) => setForm((prev) => ({ ...prev, usuarioId: e.value as number }))}
              />
            </div>
          ) : null}
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Fecha</label>
            <DateBox
              value={form.fecha}
              type="date"
              onValueChanged={(e) =>
                setForm((prev) => ({
                  ...prev,
                  fecha: e.value ? todayIsoDate(new Date(e.value as Date)) : prev.fecha,
                }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Cliente</label>
            <SelectBox
              dataSource={clientes}
              value={form.clienteId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
              searchEnabled
              onValueChanged={(e) => void handleClienteChange((e.value as number | null) ?? null)}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Tipo tarea</label>
            <SelectBox
              dataSource={tipos}
              value={form.tipoTareaId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.descripcion}` : '')}
              searchEnabled
              onValueChanged={(e) =>
                setForm((prev) => ({ ...prev, tipoTareaId: e.value as number | null }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Duración (tramo)</label>
            <SelectBox
              dataSource={tramoOptions}
              value={form.duracionMinutos}
              onValueChanged={(e) =>
                setForm((prev) => ({ ...prev, duracionMinutos: Number(e.value) }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Duración (min)</label>
            <NumberBox
              value={form.duracionMinutos}
              min={tramo}
              max={1440}
              step={tramo}
              onValueChanged={(e) =>
                setForm((prev) => ({ ...prev, duracionMinutos: Number(e.value) || tramo }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Observación</label>
            <TextBox
              value={form.observacion}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, observacion: String(e.value ?? '') }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Sin cargo</label>
            <CheckBox
              value={form.sinCargo}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, sinCargo: Boolean(e.value) }))}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>Presencial</label>
            <CheckBox
              value={form.presencial}
              onValueChanged={(e) => setForm((prev) => ({ ...prev, presencial: Boolean(e.value) }))}
            />
          </div>
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text="Cancelar" onClick={() => setFormOpen(false)} />
            <Button
              text="Guardar"
              type="default"
              onClick={() => void persist(false)}
              elementAttr={{ 'data-testid': 'partesCargaSave' }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
