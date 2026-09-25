import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import {
  ProcessDataGrid,
  ExcelImportToolbar,
  isNativeApp,
  SmartCapturePanel,
  useLlmCredentialSelection,
  useSmartCapturePendingChoice,
  type SmartCaptureThreadMessage,
} from '@paqsuite/react-core'
import type { ExcelImportCompletePayload } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import TextBox from 'devextreme-react/text-box'
import CheckBox from 'devextreme-react/check-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { LlmPreferencesModalHost } from '../../llmCredentials/LlmPreferencesModalHost'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import type { FormState } from './cargaDiariaFormTypes'
import {
  deleteTarea,
  fetchDuracionTramo,
  listTareas,
  saveTarea,
  setTareaCerrado,
  type PartesTareaItem,
} from './partesTareaApi'
import {
  buildTramoHhMmOptions,
  dateDisplayFormat,
  dateSerializationFormat,
  formatMinutosAsHhMm,
  isFechaFutura,
  isoDateFromDateBox,
  minutosToHorasDecimal,
  todayIsoDate,
} from './partesTareaDuration'
import { shouldRefreshCargaAfterImport } from './excelImportCargaHelpers'
import { handlePartesSmartCaptureSend } from './partesSmartCaptureTurn'
import { resolveDefaultTipoId } from './cargaDiariaTipoDefault'
import { cargaDiariaPersistErrorKey } from './cargaDiariaPersistValidation'
import { isDxUserEvent, normalizeCatalogItems } from './catalogItems'
import { usePartesEstadoCerradoOptions } from '../partesFiltroEstado'
import { usePartesTareaGridCaptions } from '../partesTareaGridI18n'
import {
  usePartesDuracionHorasSummaryItems,
} from '../partesGridSummary'

type CargaDiariaGridRow = PartesTareaItem & {
  /** Horas decimales para sumatoria DevExtreme (persistencia = minutos). */
  duracionHoras: number
  duracionHhMm: string
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
  const { t } = useTranslation()
  const pageTitle = useProcessMenuTitle(t('partes.carga.title'), '/partes/carga-diaria')
  const estadoOpciones = usePartesEstadoCerradoOptions()
  const gridCaptions = usePartesTareaGridCaptions()
  const session = getAuthSession()
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const asistenteId = session?.partes?.asistenteId ?? null
  const isCliente = session?.partes?.tipoFuncional === 'cliente'
  const llmSelection = useLlmCredentialSelection({ autoSelectFirstEnabled: true })
  const { pendingChoice, setPendingChoice, clearPendingChoice } = useSmartCapturePendingChoice()

  const hoy = todayIsoDate()
  const [fechaDesde, setFechaDesde] = useState(hoy)
  const [fechaHasta, setFechaHasta] = useState(hoy)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<'todas' | 'abiertas' | 'cerradas'>('todas')
  const [rows, setRows] = useState<CargaDiariaGridRow[]>([])
  const [loading, setLoading] = useState(true)
  const [total, setTotal] = useState(0)
  const [error, setError] = useState<string | null>(null)
  const [tramo, setTramo] = useState(15)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [formCerrado, setFormCerrado] = useState(false)
  const [form, setForm] = useState<FormState>(() => emptyForm(asistenteId))
  const [isSaving, setIsSaving] = useState(false)
  const [scThread, setScThread] = useState<SmartCaptureThreadMessage[]>([])
  const [llmPreferencesVisible, setLlmPreferencesVisible] = useState(false)
  const [credentialsRevision, setCredentialsRevision] = useState(0)
  const formRef = useRef(form)
  const editingIdRef = useRef(editingId)
  editingIdRef.current = editingId

  /** Aplica cambios al form y sincroniza formRef en el mismo tick (persist/SC no pueden esperar el re-render). */
  const patchForm = useCallback((updater: (prev: FormState) => FormState) => {
    const next = updater(formRef.current)
    formRef.current = next
    setForm(next)
  }, [])

  useEffect(() => {
    if (credentialsRevision === 0) {
      return
    }
    void llmSelection.refresh()
  }, [credentialsRevision, llmSelection.refresh])

  function resetSmartCaptureState() {
    setScThread([])
    clearPendingChoice()
  }

  function closeForm() {
    setFormOpen(false)
    setIsSaving(false)
    resetSmartCaptureState()
  }

  function renderFormErrorAlert() {
    if (!error) {
      return null
    }
    return (
      <div
        role="alert"
        data-testid="partesCargaError"
        style={{ color: 'var(--dx-color-danger, #d9534f)', fontWeight: 600 }}
      >
        {error}
      </div>
    )
  }

  function resolveSmartCaptureMessage(key: string): string {
    const viaI18n = t(key)
    if (viaI18n !== key) {
      return viaI18n
    }
    return resolveAuthMessage(key)
  }

  function buildSmartCaptureHandlers() {
    return {
      form: formRef.current,
      editingId: editingIdRef.current,
      cerrado: formCerrado,
      esSupervisor,
      clientes: clientes as Array<{ id: number; code?: string; nombre?: string }>,
      asistentes: asistentes as Array<{ id: number; code?: string; nombre?: string }>,
      tipos: tipos as Array<{ id: number; code?: string; descripcion?: string }>,
      tramoMinutos: tramo,
      pendingChoice,
      activeCredentialId: llmSelection.activeCredentialId,
      supportsVision: llmSelection.canAttachImages,
      setForm: (updater: (prev: FormState) => FormState) => {
        patchForm(updater)
      },
      setPendingChoice,
      onClienteIdChange: handleClienteChange,
      onSave: () => persist(false),
      onAssistantReply: (text: string) => {
        setScThread((prev) => [...prev, { role: 'assistant', text }])
      },
      onError: (message: string) => {
        setError(message)
        setScThread((prev) => [...prev, { role: 'assistant', text: message }])
      },
      resolveMessage: resolveSmartCaptureMessage,
    }
  }

  const tramoOptions = useMemo(() => buildTramoHhMmOptions(tramo), [tramo])

  const duracionSummaryItems = usePartesDuracionHorasSummaryItems()

  function mapGridRows(items: PartesTareaItem[]): CargaDiariaGridRow[] {
    return items.map((item) => ({
      ...item,
      duracionHoras: minutosToHorasDecimal(item.duracionMinutos),
      duracionHhMm: formatMinutosAsHhMm(item.duracionMinutos),
    }))
  }

  const load = useCallback(async () => {
    if (!fechaDesde || !fechaHasta) {
      setError(resolveAuthMessage('partes.tarea.fechasRequeridas'))
      setRows([])
      setLoading(false)
      return
    }
    setLoading(true)
    setError(null)
    try {
      const result = await listTareas({
        fechaDesde,
        fechaHasta,
        clienteId: filtroClienteId,
        usuarioId: esSupervisor ? filtroUsuarioId : null,
        estadoCerrado,
      })
      if (result.kind === 'ok') {
        setRows(mapGridRows(result.envelope.resultado.items ?? []))
        setTotal(result.envelope.resultado.total ?? 0)
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
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
        setClientes(normalizeCatalogItems(result.envelope.resultado.items ?? []))
      }
    })
    if (esSupervisor) {
      void listCatalogo('asistentes').then((result) => {
        if (result.kind === 'ok') {
          setAsistentes(normalizeCatalogItems(result.envelope.resultado.items ?? []))
        }
      })
    }
  }, [esSupervisor])

  useEffect(() => {
    void load()
  }, [load])

  async function loadUniverso(clienteId: number | null) {
    const query = clienteId ? `?clienteId=${clienteId}` : ''
    const result = await listCatalogo('tipos-tarea', query)
    if (result.kind === 'ok') {
      const items = normalizeCatalogItems(result.envelope.resultado.items ?? [])
      setTipos(items)
      return items
    }
    setTipos([])
    return []
  }

  async function openCreate() {
    setEditingId(null)
    setFormCerrado(false)
    setError(null)
    setIsSaving(false)
    const initial = emptyForm(asistenteId)
    initial.duracionMinutos = tramo
    const generics = await loadUniverso(null)
    initial.tipoTareaId = resolveDefaultTipoId(generics)
    formRef.current = initial
    setForm(initial)
    resetSmartCaptureState()
    setFormOpen(true)
  }

  async function openEdit(row: PartesTareaItem) {
    if (row.cerrado) {
      return
    }
    setEditingId(row.id)
    setFormCerrado(false)
    setError(null)
    setIsSaving(false)
    const next: FormState = {
      usuarioId: row.usuarioId,
      clienteId: row.clienteId,
      tipoTareaId: row.tipoTareaId,
      fecha: String(row.fecha).slice(0, 10),
      duracionMinutos: row.duracionMinutos,
      sinCargo: row.sinCargo,
      presencial: row.presencial,
      observacion: row.observacion,
      rowVersion: row.rowVersion,
    }
    formRef.current = next
    setForm(next)
    await loadUniverso(row.clienteId)
    resetSmartCaptureState()
    setFormOpen(true)
  }

  async function handleClienteChange(clienteId: number | null) {
    const items = await loadUniverso(clienteId)
    patchForm((prev) => {
      const stillValid = items.some((item) => Number(item.id) === Number(prev.tipoTareaId))
      return {
        ...prev,
        clienteId,
        tipoTareaId: stillValid ? prev.tipoTareaId : resolveDefaultTipoId(items),
      }
    })
  }

  async function persist(confirmFutura = false) {
    const current = formRef.current
    const currentEditingId = editingIdRef.current
    const validationKey = cargaDiariaPersistErrorKey(current, tramo)
    if (validationKey) {
      setError(resolveAuthMessage(validationKey))
      return
    }
    let confirmedFutura = confirmFutura
    if (isFechaFutura(current.fecha) && !confirmedFutura) {
      const ok = await confirm(
        'La fecha es futura. ¿Confirma el registro?',
        'Fecha futura'
      )
      if (!ok) {
        return
      }
      confirmedFutura = true
    }

    setError(null)
    setIsSaving(true)
    try {
      const body: Record<string, unknown> = {
        usuarioId: current.usuarioId,
        clienteId: current.clienteId,
        tipoTareaId: current.tipoTareaId,
        fecha: current.fecha,
        duracionMinutos: Number(current.duracionMinutos),
        sinCargo: current.sinCargo,
        presencial: current.presencial,
        observacion: current.observacion.trim(),
        confirmarFechaFutura: confirmedFutura || undefined,
      }
      if (currentEditingId !== null) {
        body.rowVersion = current.rowVersion
      }

      let result = await saveTarea(body, currentEditingId ?? undefined)
      if (
        result.kind === 'envelopeError' &&
        result.envelope.respuesta === 'partes.tarea.fechaFuturaConfirmacion'
      ) {
        body.confirmarFechaFutura = true
        result = await saveTarea(body, currentEditingId ?? undefined)
      }
      if (result.kind === 'ok') {
        closeForm()
        void load()
        return
      }
      if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
        return
      }
      setError(resolveAuthMessage(result.i18nKey || 'infra.transport'))
    } catch {
      setError(resolveAuthMessage('infra.unexpected'))
    } finally {
      setIsSaving(false)
    }
  }

  async function handleDelete(row: PartesTareaItem) {
    if (row.cerrado) {
      return
    }
    const ok = await confirm(t('partes.mobile.eliminarConfirm'), t('partes.mobile.eliminar'))
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
        <h2 style={{ margin: 0, flex: 1 }}>{pageTitle}</h2>
        <Link to="/partes/proceso-masivo" data-testid="partesCargaLinkMasivo">
          {t('partes.carga.linkMasivo')}
        </Link>
      </div>

      <div
        data-testid="partesCargaFiltros"
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
        {esSupervisor ? (
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
        ) : null}
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
          elementAttr={{ 'data-testid': 'partesCargaSearch' }}
        />
      </div>

      {!isNativeApp() && session?.partes?.tipoFuncional !== 'cliente' ? (
        <ExcelImportToolbar
          processCode="partes.tareas.import"
          onComplete={(payload: ExcelImportCompletePayload) => {
            if (shouldRefreshCargaAfterImport(payload)) {
              void load()
            }
          }}
          t={(key) => resolveAuthMessage(key) || key}
        />
      ) : null}

      {!formOpen ? renderFormErrorAlert() : null}
      <div data-testid="partesCargaGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.carga.diaria"
          gridId="cargaDiaria"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          onCreate={openCreate}
          allowCreate
          createHint={gridCaptions.nuevaTarea}
          createTestId="partesCargaAdd"
          defaultTotalItems={duracionSummaryItems}
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption={gridCaptions.fecha} dataType="date" />
          {esSupervisor ? <Column dataField="usuarioCode" caption={gridCaptions.asistente} /> : null}
          <Column dataField="clienteNombre" caption={gridCaptions.cliente} />
          <Column dataField="tipoTareaDescripcion" caption={gridCaptions.tipoTarea} />
          <Column dataField="duracionHhMm" caption={gridCaptions.duracion} />
          <Column
            dataField="duracionHoras"
            caption={gridCaptions.duracionDecimal}
            dataType="number"
            format="#0.##"
          />
          <Column dataField="sinCargo" caption={gridCaptions.sinCargo} dataType="boolean" />
          <Column dataField="presencial" caption={gridCaptions.presencial} dataType="boolean" />
          <Column dataField="observacion" caption={gridCaptions.observacion} />
          <Column dataField="cerrado" caption={gridCaptions.cerrado} dataType="boolean" />
          <Column dataField="clienteCode" caption={gridCaptions.clienteCode} visible={false} />
          <Column dataField="tipoTareaCode" caption={gridCaptions.tipoTareaCode} visible={false} />
          <Column dataField="duracionMinutos" caption={gridCaptions.minutos} dataType="number" visible={false} />
          <Column
            type="buttons"
            buttons={[
              {
                hint: gridCaptions.hintEditar,
                icon: 'edit',
                visible: (e) => !(e.row?.data as PartesTareaItem | undefined)?.cerrado,
                onClick: (e) => void openEdit(e.row?.data as PartesTareaItem),
              },
              {
                hint: gridCaptions.hintEliminar,
                icon: 'trash',
                visible: (e) => !(e.row?.data as PartesTareaItem | undefined)?.cerrado,
                onClick: (e) => void handleDelete(e.row?.data as PartesTareaItem),
              },
              {
                hint: gridCaptions.hintCerrarReabrir,
                icon: 'isblank',
                visible: () => esSupervisor,
                onClick: (e) => void handleCerrarReabrir(e.row?.data as PartesTareaItem),
              },
            ]}
          />
        </ProcessDataGrid>
        <div style={{ marginTop: 8, opacity: 0.7 }}>{t('partes.common.total', { count: total })}</div>
      </div>

      <Popup
        visible={formOpen}
        onHiding={() => closeForm()}
        title={editingId ? t('partes.mobile.editarTarea') : t('partes.mobile.nuevaTarea')}
        width={640}
        height="auto"
        maxHeight="90vh"
        showCloseButton
      >
        <div style={{ display: 'grid', gap: 10, padding: 8 }} data-testid="partesCargaForm">
          {formOpen ? renderFormErrorAlert() : null}
          {esSupervisor ? (
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>{t('partes.informe.filtro.asistente')}</label>
              <SelectBox
                dataSource={asistentes}
                value={form.usuarioId}
                valueExpr="id"
                displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
                searchEnabled
                elementAttr={{ 'data-testid': 'partesCargaAsistente' }}
                onValueChanged={(e) => {
                  if (!isDxUserEvent(e)) {
                    return
                  }
                  patchForm((prev) => ({ ...prev, usuarioId: (e.value as number | null) ?? null }))
                }}
              />
            </div>
          ) : null}
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.field.fecha')}</label>
            <DateBox
              value={form.fecha}
              type="date"
              displayFormat={dateDisplayFormat}
              dateSerializationFormat={dateSerializationFormat}
              elementAttr={{ 'data-testid': 'partesCargaFecha' }}
              onValueChanged={(e) => {
                const next = isoDateFromDateBox(e)
                if (next !== null) {
                  patchForm((prev) => ({ ...prev, fecha: next || prev.fecha }))
                }
              }}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.filtro.cliente')}</label>
            <SelectBox
              dataSource={clientes}
              value={form.clienteId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
              searchEnabled
              elementAttr={{ 'data-testid': 'partesCargaCliente' }}
              onValueChanged={(e) => {
                if (!isDxUserEvent(e)) {
                  return
                }
                void handleClienteChange((e.value as number | null) ?? null)
              }}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.filtro.tipoTarea')}</label>
            <SelectBox
              dataSource={tipos}
              value={form.tipoTareaId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.descripcion}` : '')}
              searchEnabled
              elementAttr={{ 'data-testid': 'partesCargaTipoTarea' }}
              onValueChanged={(e) => {
                if (!isDxUserEvent(e)) {
                  return
                }
                patchForm((prev) => ({ ...prev, tipoTareaId: (e.value as number | null) ?? null }))
              }}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.tarea.duracion')}</label>
            <SelectBox
              dataSource={tramoOptions}
              value={form.duracionMinutos}
              valueExpr="minutos"
              displayExpr="label"
              searchEnabled
              elementAttr={{ 'data-testid': 'partesCargaDuracion' }}
              onValueChanged={(e) => {
                if (!isDxUserEvent(e)) {
                  return
                }
                patchForm((prev) => ({
                  ...prev,
                  duracionMinutos: Number(e.value) || tramo,
                }))
              }}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.field.observacion')}</label>
            <TextBox
              value={form.observacion}
              elementAttr={{ 'data-testid': 'partesCargaObservacion' }}
              onValueChanged={(e) =>
                patchForm((prev) => ({ ...prev, observacion: String(e.value ?? '') }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.field.sinCargo')}</label>
            <CheckBox
              value={form.sinCargo}
              onValueChanged={(e) =>
                patchForm((prev) => ({ ...prev, sinCargo: Boolean(e.value) }))
              }
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
            <label>{t('partes.informe.field.presencial')}</label>
            <CheckBox
              value={form.presencial}
              onValueChanged={(e) =>
                patchForm((prev) => ({ ...prev, presencial: Boolean(e.value) }))
              }
            />
          </div>
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text={t('parametros.modal.cancel')} onClick={() => closeForm()} />
            <Button
              text={isSaving ? t('partes.carga.guardando') : t('partes.mobile.guardar')}
              type="default"
              disabled={isSaving}
              onClick={() => void persist(false)}
              elementAttr={{ 'data-testid': 'partesCargaSave' }}
            />
          </div>

          {!isNativeApp() && !isCliente ? (
            <div data-testid="partesCargaSmartCapture" style={{ marginTop: 8 }}>
              <SmartCapturePanel
                enabled={!formCerrado}
                hintText={t('partes.smartCapture.hint')}
                credentials={llmSelection.credentials.map((item) => ({
                  id: item.id,
                  nombre: item.nombre,
                  enabled: item.enabled,
                  supportsVision: item.supportsVision,
                }))}
                activeCredentialId={llmSelection.activeCredentialId}
                onActiveCredentialChange={(id) => {
                  void llmSelection.setActiveCredentialId(id)
                }}
                onOpenPreferences={() => setLlmPreferencesVisible(true)}
                threadMessages={scThread}
                onThreadChange={setScThread}
                t={(key) => t(key)}
                pendingChoice={pendingChoice}
                onSelectPendingOption={(oneBasedIndex) => {
                  const choiceText = String(oneBasedIndex)
                  setScThread((prev) => [...prev, { role: 'user', text: choiceText }])
                  void handlePartesSmartCaptureSend(
                    {
                      message: choiceText,
                      modality: 'texto',
                      images: [],
                      credentialId: llmSelection.activeCredentialId,
                    },
                    buildSmartCaptureHandlers()
                  )
                }}
                onDiscardThread={() => {
                  clearPendingChoice()
                }}
                onSend={async (payload) => {
                  await handlePartesSmartCaptureSend(payload, buildSmartCaptureHandlers())
                }}
              />
            </div>
          ) : null}
        </div>
      </Popup>

      <LlmPreferencesModalHost
        visible={llmPreferencesVisible}
        onClose={() => {
          setLlmPreferencesVisible(false)
          setCredentialsRevision((current) => current + 1)
        }}
        onCredentialsChanged={() => setCredentialsRevision((current) => current + 1)}
      />
    </div>
  )
}
