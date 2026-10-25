import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import {
  ProcessDataGrid,
  ProcessPageLayout,
  SmartCapturePanel,
  useLlmCredentialSelection,
  useSmartCapturePendingChoice,
} from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import TextBox from 'devextreme-react/text-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { Navigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { LlmPreferencesModalHost } from '../../llmCredentials/LlmPreferencesModalHost'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import type { PaqueteFormState } from './cargaPaquetesFormTypes'
import { defaultPaqueteFiltroMes } from './cargaPaquetesDefaultFilters'
import { cargaPaquetesPersistErrorKey } from './cargaPaquetesPersistValidation'
import {
  deletePaquete,
  listPaquetes,
  savePaquete,
  type PartesPaqueteItem,
} from './partesPaqueteApi'
import { fetchDuracionTramo } from './partesTareaApi'
import {
  dateDisplayFormat,
  dateSerializationFormat,
  DURACION_HORAS_NUMBER_FORMAT,
  formatMinutosAsHhMm,
  isFechaFutura,
  isoDateFromDateBox,
  minutosToHorasDecimal,
  todayIsoDate,
} from './partesTareaDuration'
import { buildPaqueteDuracionOptions } from './partesPaqueteDuration'
import { normalizeCatalogItems } from './catalogItems'
import { usePartesTareaGridCaptions } from '../partesTareaGridI18n'
import { usePartesDuracionHorasSummaryItems } from '../partesGridSummary'
import { handlePartesPaqueteSmartCaptureSend } from './partesPaqueteSmartCaptureTurn'

type PaqueteGridRow = PartesPaqueteItem & {
  duracionHoras: number
  duracionHhMm: string
}

const emptyForm = (): PaqueteFormState => ({
  clienteId: null,
  fecha: todayIsoDate(),
  duracionMinutos: 8 * 60,
  observacion: '',
})

export function CargaPaquetesPage() {
  const { t } = useTranslation()
  const pageTitle = useProcessMenuTitle(t('partes.paquete.title'), '/partes/carga-paquetes')
  const gridCaptions = usePartesTareaGridCaptions()
  const session = getAuthSession()
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const llmSelection = useLlmCredentialSelection({ autoSelectFirstEnabled: true })
  const { pendingChoice, setPendingChoice, clearPendingChoice } = useSmartCapturePendingChoice()

  const mesDefault = defaultPaqueteFiltroMes()
  const [fechaDesde, setFechaDesde] = useState(mesDefault.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(mesDefault.fechaHasta)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [rows, setRows] = useState<PaqueteGridRow[]>([])
  const [loading, setLoading] = useState(true)
  const [total, setTotal] = useState(0)
  const [listError, setListError] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [tramo, setTramo] = useState(15)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<PaqueteFormState>(emptyForm())
  const [isSaving, setIsSaving] = useState(false)
  const [prefsOpen, setPrefsOpen] = useState(false)
  const formRef = useRef(form)
  const editingIdRef = useRef(editingId)
  formRef.current = form
  editingIdRef.current = editingId

  const duracionSummaryItems = usePartesDuracionHorasSummaryItems()
  const duracionOptions = useMemo(
    () => buildPaqueteDuracionOptions((horas) => t('partes.paquete.horasLabel', { horas })),
    [t],
  )

  const load = useCallback(async () => {
    setLoading(true)
    setListError(null)
    const result = await listPaquetes({
      fechaDesde,
      fechaHasta,
      clienteId: filtroClienteId,
    })
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setRows(
        items.map((row) => ({
          ...row,
          duracionHoras: minutosToHorasDecimal(row.duracionMinutos),
          duracionHhMm: formatMinutosAsHhMm(row.duracionMinutos),
        }))
      )
      setTotal(result.envelope.resultado.total ?? items.length)
    } else if (result.kind === 'envelopeError') {
      setListError(resolveAuthMessage(result.envelope.respuesta))
    }
    setLoading(false)
  }, [fechaDesde, fechaHasta, filtroClienteId])

  useEffect(() => {
    void (async () => {
      const tramoRes = await fetchDuracionTramo()
      if (tramoRes.kind === 'ok') {
        setTramo(tramoRes.envelope.resultado.tramoMinutos ?? 15)
      }
      const cat = await listCatalogo('clientes')
      if (cat.kind === 'ok') {
        setClientes(normalizeCatalogItems(cat.envelope.resultado.items ?? []))
      }
    })()
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  function closeForm() {
    setFormOpen(false)
    setFormError(null)
    clearPendingChoice()
  }

  function openCreate() {
    setEditingId(null)
    setFormError(null)
    setIsSaving(false)
    const initial = emptyForm()
    initial.duracionMinutos = tramo
    formRef.current = initial
    setForm(initial)
    clearPendingChoice()
    setFormOpen(true)
  }

  function openEdit(row: PartesPaqueteItem) {
    setEditingId(row.id)
    setFormError(null)
    setIsSaving(false)
    const next: PaqueteFormState = {
      clienteId: row.clienteId,
      fecha: String(row.fecha).slice(0, 10),
      duracionMinutos: row.duracionMinutos,
      observacion: row.observacion,
      rowVersion: row.rowVersion,
    }
    formRef.current = next
    setForm(next)
    clearPendingChoice()
    setFormOpen(true)
  }

  async function persist(confirmFutura = false) {
    const current = formRef.current
    const currentEditingId = editingIdRef.current
    const validationKey = cargaPaquetesPersistErrorKey(current)
    if (validationKey) {
      setFormError(resolveAuthMessage(validationKey))
      return
    }
    let confirmedFutura = confirmFutura
    if (isFechaFutura(current.fecha) && !confirmedFutura) {
      const ok = await confirm(
        t('partes.paquete.fechaFuturaConfirm'),
        t('partes.paquete.fechaFuturaTitle')
      )
      if (!ok) {
        return
      }
      confirmedFutura = true
    }

    setFormError(null)
    setIsSaving(true)
    try {
      const body: Record<string, unknown> = {
        clienteId: current.clienteId,
        fecha: current.fecha,
        duracionMinutos: Number(current.duracionMinutos),
        observacion: current.observacion.trim(),
        confirmarFechaFutura: confirmedFutura || undefined,
      }
      if (currentEditingId !== null) {
        body.rowVersion = current.rowVersion
      }

      let result = await savePaquete(body, currentEditingId ?? undefined)
      if (
        result.kind === 'envelopeError' &&
        result.envelope.respuesta === 'partes.paquete.fechaFuturaConfirmacion'
      ) {
        body.confirmarFechaFutura = true
        result = await savePaquete(body, currentEditingId ?? undefined)
      }
      if (result.kind === 'ok') {
        closeForm()
        void load()
        return
      }
      if (result.kind === 'envelopeError') {
        setFormError(resolveAuthMessage(result.envelope.respuesta))
        return
      }
      setFormError(resolveAuthMessage(result.i18nKey || 'infra.transport'))
    } catch {
      setFormError(resolveAuthMessage('infra.unexpected'))
    } finally {
      setIsSaving(false)
    }
  }

  async function handleDelete(row: PartesPaqueteItem) {
    const ok = await confirm(t('partes.paquete.eliminarConfirm'), t('partes.mobile.eliminar'))
    if (!ok) {
      return
    }
    const result = await deletePaquete(row.id, row.rowVersion)
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setListError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  if (!esSupervisor) {
    return <Navigate to="/partes/carga-diaria" replace />
  }

  const scHandlers = {
    form,
    editingId,
    esSupervisor: true,
    clientes: clientes as Array<{ id: number; code?: string; nombre?: string }>,
    tramoMinutos: tramo,
    pendingChoice,
    activeCredentialId: llmSelection.activeCredentialId,
    supportsVision: Boolean(llmSelection.activeResolved?.supportsVision),
    setForm,
    setPendingChoice,
    onSave: () => persist(),
    onAssistantReply: () => {},
    onError: (msg: string) => setFormError(msg),
    resolveMessage: (key: string) => resolveAuthMessage(key) || t(key, { defaultValue: key }),
  }

  return (
    <ProcessPageLayout title={pageTitle}>
      <div data-testid="partesCargaPaquetesPage">
        <div
          data-testid="partesCargaPaquetesFiltros"
          style={{ display: 'flex', flexWrap: 'wrap', gap: 12, marginBottom: 12, alignItems: 'end' }}
        >
          <div>
            <label>{t('partes.common.desde')}</label>
            <DateBox
              value={fechaDesde}
              type="date"
              useMaskBehavior
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
              useMaskBehavior
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
          <Button
            text={t('partes.common.buscar')}
            onClick={() => void load()}
            elementAttr={{ 'data-testid': 'partesCargaPaquetesSearch' }}
          />
        </div>

        {!formOpen && listError ? (
          <div role="alert" data-testid="partesCargaPaquetesListError">
            {listError}
          </div>
        ) : null}

        <div data-testid="partesCargaPaquetesGrid">
          <ProcessDataGrid
            dataSource={rows}
            keyExpr="id"
            loading={loading}
            onRefresh={() => void load()}
            proceso="partes.carga.paquetes"
            gridId="cargaPaquetes"
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            onCreate={openCreate}
            allowCreate
            createHint={t('partes.paquete.nuevo')}
            createTestId="partesCargaPaquetesAdd"
            defaultTotalItems={duracionSummaryItems}
          >
            <Paging defaultPageSize={20} />
            <Pager visible showPageSizeSelector />
            <Column dataField="fecha" caption={gridCaptions.fecha} dataType="date" />
            <Column dataField="usuarioNombre" caption={gridCaptions.asistente} />
            <Column dataField="clienteNombre" caption={gridCaptions.cliente} />
            <Column
              dataField="duracionHoras"
              caption={gridCaptions.duracion}
              dataType="number"
              format={DURACION_HORAS_NUMBER_FORMAT}
            />
            <Column dataField="observacion" caption={gridCaptions.observacion} />
            <Column
              type="buttons"
              buttons={[
                {
                  hint: gridCaptions.hintEditar,
                  icon: 'edit',
                  onClick: (e) => openEdit(e.row?.data as PartesPaqueteItem),
                },
                {
                  hint: gridCaptions.hintEliminar,
                  icon: 'trash',
                  onClick: (e) => void handleDelete(e.row?.data as PartesPaqueteItem),
                },
              ]}
            />
          </ProcessDataGrid>
          <div style={{ marginTop: 8, opacity: 0.7 }}>{t('partes.common.total', { count: total })}</div>
        </div>

        <Popup
          visible={formOpen}
          onHiding={() => closeForm()}
          title={editingId ? t('partes.paquete.editar') : t('partes.paquete.nuevo')}
          width={640}
          height="auto"
          maxHeight="90vh"
          showCloseButton
        >
          <div style={{ display: 'grid', gap: 10, padding: 8 }} data-testid="partesCargaPaquetesForm">
            {formError ? (
              <div role="alert" data-testid="partesCargaPaquetesFormError">
                {formError}
              </div>
            ) : null}
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>{t('partes.informe.filtro.cliente')}</label>
              <SelectBox
                dataSource={clientes}
                value={form.clienteId}
                valueExpr="id"
                displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
                searchEnabled
                elementAttr={{ 'data-testid': 'partesCargaPaquetesCliente' }}
                onValueChanged={(e) =>
                  setForm((prev) => ({ ...prev, clienteId: (e.value as number | null) ?? null }))
                }
              />
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>{gridCaptions.fecha}</label>
              <DateBox
                value={form.fecha}
                type="date"
                displayFormat={dateDisplayFormat}
                dateSerializationFormat={dateSerializationFormat}
                useMaskBehavior
                elementAttr={{ 'data-testid': 'partesCargaPaquetesFecha' }}
                onValueChanged={(e) => {
                  const next = isoDateFromDateBox(e)
                  if (next !== null) {
                    setForm((prev) => ({ ...prev, fecha: next }))
                  }
                }}
              />
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>{gridCaptions.duracion}</label>
              <SelectBox
                dataSource={duracionOptions}
                value={form.duracionMinutos}
                valueExpr="minutos"
                displayExpr="label"
                elementAttr={{ 'data-testid': 'partesCargaPaquetesDuracion' }}
                onValueChanged={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    duracionMinutos: Number(e.value ?? 8 * 60),
                  }))
                }
              />
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8 }}>
              <label>{gridCaptions.observacion}</label>
              <TextBox
                value={form.observacion}
                elementAttr={{ 'data-testid': 'partesCargaPaquetesObservacion' }}
                onValueChanged={(e) => setForm((prev) => ({ ...prev, observacion: String(e.value ?? '') }))}
              />
            </div>
            <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end' }}>
              <Button text={t('partes.common.cerrar')} onClick={() => closeForm()} />
              <Button
                text={isSaving ? t('partes.carga.guardando') : t('partes.mobile.guardar')}
                type="default"
                disabled={isSaving}
                elementAttr={{ 'data-testid': 'partesCargaPaquetesSave' }}
                onClick={() => void persist()}
              />
            </div>
            <div data-testid="partesCargaPaquetesSmartCapture">
              <SmartCapturePanel
                hintText={t('partes.paquete.smartCapture.hint')}
                enabled={formOpen && llmSelection.activeCredentialId !== null}
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
                onOpenPreferences={() => setPrefsOpen(true)}
                onSend={(payload) => handlePartesPaqueteSmartCaptureSend(payload, scHandlers)}
                pendingChoice={pendingChoice}
              />
            </div>
          </div>
        </Popup>

        <LlmPreferencesModalHost visible={prefsOpen} onClose={() => setPrefsOpen(false)} />
      </div>
    </ProcessPageLayout>
  )
}
