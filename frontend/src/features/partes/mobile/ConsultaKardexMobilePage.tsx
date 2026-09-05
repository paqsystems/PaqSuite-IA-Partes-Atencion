import { useCallback, useEffect, useMemo, useState } from 'react'
import Button from 'devextreme-react/button'
import { Popup } from 'devextreme-react/popup'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import TextBox from 'devextreme-react/text-box'
import NumberBox from 'devextreme-react/number-box'
import CheckBox from 'devextreme-react/check-box'
import { confirm } from 'devextreme/ui/dialog'
import { ConsultaKardexList } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
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
import { mapPartesTareaToKardexItem } from './mapPartesTareaToKardexItem'

export function ConsultaKardexMobilePage() {
  const { t } = useTranslation()
  const session = getAuthSession()
  const readOnly = session?.partes?.tipoFuncional === 'cliente'
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const asistenteId = session?.partes?.asistenteId ?? null
  const hoy = todayIsoDate()
  const [fecha, setFecha] = useState(hoy)
  const [rows, setRows] = useState<PartesTareaItem[]>([])
  const [error, setError] = useState<string | null>(null)
  const [tramo, setTramo] = useState(15)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState<PartesTareaItem | null>(null)
  const [form, setForm] = useState({
    usuarioId: asistenteId,
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

  const kardexItems = useMemo(
    () => rows.map((row) => mapPartesTareaToKardexItem(row, (key) => t(key))),
    [rows, t],
  )

  async function openCreate() {
    setEditing(null)
    setForm({
      usuarioId: asistenteId,
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
      usuarioId: row.usuarioId,
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
      const ok = await confirm(t('partes.mobile.fechaFuturaConfirm'), t('partes.mobile.fechaFutura'))
      if (!ok) {
        return
      }
      confirmFutura = true
    }
    const body: Record<string, unknown> = {
      usuarioId: form.usuarioId ?? asistenteId,
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
    const ok = await confirm(t('partes.mobile.eliminarConfirm'), t('partes.mobile.eliminar'))
    if (!ok) {
      return
    }
    const result = await deleteTarea(row.id, row.rowVersion)
    if (result.kind === 'ok') {
      setFormOpen(false)
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div className="pqProcessPage partesMobileProcess" data-testid="partesConsultaKardex">
      {error ? <div role="alert">{error}</div> : null}
      <ConsultaKardexList
        items={kardexItems}
        filtersSlot={
          <div className="partesMobileKardexFilters">
            <h2 className="pqProcessTitle">{t('partes.mobile.kardexTitle')}</h2>
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
                text={t('partes.mobile.nuevaTarea')}
                onClick={() => void openCreate()}
                elementAttr={{ 'data-testid': 'partesKardexAdd' }}
              />
            ) : null}
          </div>
        }
        onItemTap={(item) => {
          const row = rows.find((entry) => String(entry.id) === item.id)
          if (row) {
            void openEdit(row)
          }
        }}
        onRefresh={load}
        t={(key) => t(key)}
      />
      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={editing ? t('partes.mobile.editarTarea') : t('partes.mobile.nuevaTarea')}
        width="95%"
        height="auto"
      >
        <div style={{ display: 'grid', gap: 10, padding: 8 }} data-testid="partesKardexForm">
          {esSupervisor ? (
            <SelectBox
              dataSource={asistentes}
              value={form.usuarioId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
              onValueChanged={(e) =>
                setForm((prev) => ({ ...prev, usuarioId: (e.value as number | null) ?? asistenteId }))
              }
            />
          ) : null}
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
            text={t('partes.informe.field.sinCargo')}
            value={form.sinCargo}
            onValueChanged={(e) => setForm((prev) => ({ ...prev, sinCargo: Boolean(e.value) }))}
          />
          <CheckBox
            text={t('partes.informe.field.presencial')}
            value={form.presencial}
            onValueChanged={(e) => setForm((prev) => ({ ...prev, presencial: Boolean(e.value) }))}
          />
          <div style={{ display: 'flex', gap: 8 }}>
            {editing && !editing.cerrado && !readOnly ? (
              <Button
                text={t('partes.mobile.eliminar')}
                stylingMode="text"
                onClick={() => void handleDelete(editing)}
              />
            ) : null}
            <Button text={t('partes.mobile.guardar')} type="default" onClick={() => void persist()} />
          </div>
        </div>
      </Popup>
    </div>
  )
}
