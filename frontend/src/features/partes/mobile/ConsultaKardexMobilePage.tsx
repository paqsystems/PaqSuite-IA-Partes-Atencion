import { useCallback, useEffect, useState } from 'react'
import List from 'devextreme-react/list'
import Button from 'devextreme-react/button'
import { Popup } from 'devextreme-react/popup'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import TextBox from 'devextreme-react/text-box'
import NumberBox from 'devextreme-react/number-box'
import CheckBox from 'devextreme-react/check-box'
import { confirm } from 'devextreme/ui/dialog'
import { getAuthSession } from '../../auth/authSessionStore'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  deleteTarea,
  fetchDuracionTramo,
  listTareas,
  saveTarea,
  type PartesTareaItem,
} from '../carga/partesTareaApi'
import {
  isFechaFutura,
  isValidDuracionMinutos,
  todayIsoDate,
} from '../carga/partesTareaDuration'

export function ConsultaKardexMobilePage() {
  const session = getAuthSession()
  const readOnly = session?.partes?.tipoFuncional === 'cliente'
  const asistenteId = session?.partes?.asistenteId ?? null
  const hoy = todayIsoDate()
  const [fecha, setFecha] = useState(hoy)
  const [rows, setRows] = useState<PartesTareaItem[]>([])
  const [error, setError] = useState<string | null>(null)
  const [tramo, setTramo] = useState(15)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState<PartesTareaItem | null>(null)
  const [form, setForm] = useState({
    clienteId: null as number | null,
    tipoTareaId: null as number | null,
    fecha: hoy,
    duracionMinutos: 15,
    observacion: '',
    sinCargo: false,
    presencial: false,
    rowVersion: '',
  })

  const load = useCallback(async () => {
    setError(null)
    const result = await listTareas({
      fechaDesde: fecha,
      fechaHasta: fecha,
      estadoCerrado: 'todas',
    })
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [fecha])

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
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  async function openCreate() {
    setEditing(null)
    setForm({
      clienteId: null,
      tipoTareaId: null,
      fecha,
      duracionMinutos: tramo,
      observacion: '',
      sinCargo: false,
      presencial: false,
      rowVersion: '',
    })
    setTipos([])
    setFormOpen(true)
  }

  async function openEdit(row: PartesTareaItem) {
    if (row.cerrado || readOnly) {
      return
    }
    setEditing(row)
    setForm({
      clienteId: row.clienteId,
      tipoTareaId: row.tipoTareaId,
      fecha: String(row.fecha).slice(0, 10),
      duracionMinutos: row.duracionMinutos,
      observacion: row.observacion,
      sinCargo: row.sinCargo,
      presencial: row.presencial,
      rowVersion: row.rowVersion,
    })
    const result = await listCatalogo('tipos-tarea', `?clienteId=${row.clienteId}`)
    if (result.kind === 'ok') {
      setTipos(result.envelope.resultado.items ?? [])
    }
    setFormOpen(true)
  }

  async function handleCliente(clienteId: number | null) {
    setForm((prev) => ({ ...prev, clienteId, tipoTareaId: null }))
    if (!clienteId) {
      setTipos([])
      return
    }
    const result = await listCatalogo('tipos-tarea', `?clienteId=${clienteId}`)
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setTipos(items)
      const def = items.find((item) => item.isDefault) ?? items[0]
      if (def) {
        setForm((prev) => ({ ...prev, tipoTareaId: Number(def.id) }))
      }
    }
  }

  async function persist() {
    if (!isValidDuracionMinutos(form.duracionMinutos, tramo)) {
      setError(resolveAuthMessage('partes.tarea.duracionInvalida'))
      return
    }
    let confirmFutura = false
    if (isFechaFutura(form.fecha)) {
      const ok = await confirm('La fecha es futura. ¿Confirma?', 'Fecha futura')
      if (!ok) {
        return
      }
      confirmFutura = true
    }
    const body: Record<string, unknown> = {
      usuarioId: asistenteId,
      clienteId: form.clienteId,
      tipoTareaId: form.tipoTareaId,
      fecha: form.fecha,
      duracionMinutos: form.duracionMinutos,
      observacion: form.observacion,
      sinCargo: form.sinCargo,
      presencial: form.presencial,
      confirmarFechaFutura: confirmFutura || undefined,
    }
    if (editing) {
      body.rowVersion = form.rowVersion
    }
    const result = await saveTarea(body, editing?.id)
    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: PartesTareaItem) {
    if (row.cerrado || readOnly) {
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

  return (
    <div data-testid="partesConsultaKardex" style={{ padding: 12 }}>
      <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 12 }}>
        <h2 style={{ margin: 0, flex: 1 }}>Partes (kardex)</h2>
        <DateBox
          value={fecha}
          type="date"
          onValueChanged={(e) =>
            setFecha(e.value ? todayIsoDate(new Date(e.value as Date)) : hoy)
          }
        />
        {!readOnly ? (
          <Button
            icon="plus"
            type="default"
            onClick={() => void openCreate()}
            elementAttr={{ 'data-testid': 'partesKardexAdd' }}
          />
        ) : null}
      </div>
      {error ? <div role="alert">{error}</div> : null}
      <List
        dataSource={rows}
        keyExpr="id"
        itemRender={(row: PartesTareaItem) => (
          <div
            data-testid={`partesKardexCard-${row.id}`}
            style={{ padding: 8 }}
            onClick={() => void openEdit(row)}
          >
            <div>
              <strong>{row.clienteCode}</strong> · {row.tipoTareaCode} · {row.duracionMinutos} min
            </div>
            <div style={{ opacity: 0.8 }}>{row.observacion}</div>
            <div>{row.cerrado ? 'Cerrada' : 'Abierta'}</div>
            {!readOnly && !row.cerrado ? (
              <Button text="Eliminar" stylingMode="text" onClick={() => void handleDelete(row)} />
            ) : null}
          </div>
        )}
      />
      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={editing ? 'Editar tarea' : 'Nueva tarea'}
        width="95%"
        height="auto"
      >
        <div style={{ display: 'grid', gap: 10, padding: 8 }}>
          <SelectBox
            dataSource={clientes}
            value={form.clienteId}
            valueExpr="id"
            displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
            onValueChanged={(e) => void handleCliente((e.value as number | null) ?? null)}
          />
          <SelectBox
            dataSource={tipos}
            value={form.tipoTareaId}
            valueExpr="id"
            displayExpr={(item) => (item ? `${item.code} — ${item.descripcion}` : '')}
            onValueChanged={(e) =>
              setForm((prev) => ({ ...prev, tipoTareaId: e.value as number | null }))
            }
          />
          <NumberBox
            value={form.duracionMinutos}
            step={tramo}
            min={tramo}
            max={1440}
            onValueChanged={(e) =>
              setForm((prev) => ({ ...prev, duracionMinutos: Number(e.value) || tramo }))
            }
          />
          <TextBox
            value={form.observacion}
            onValueChanged={(e) =>
              setForm((prev) => ({ ...prev, observacion: String(e.value ?? '') }))
            }
          />
          <CheckBox
            text="Sin cargo"
            value={form.sinCargo}
            onValueChanged={(e) => setForm((prev) => ({ ...prev, sinCargo: Boolean(e.value) }))}
          />
          <CheckBox
            text="Presencial"
            value={form.presencial}
            onValueChanged={(e) => setForm((prev) => ({ ...prev, presencial: Boolean(e.value) }))}
          />
          <Button text="Guardar" type="default" onClick={() => void persist()} />
        </div>
      </Popup>
    </div>
  )
}
