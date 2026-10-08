import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import { useTranslation } from 'react-i18next'
import { isNativeApp, ProcessDataGrid, getEmissionProcess } from '@paqsuite/react-core'
import { PartesEmissionDialog as EmissionDialog } from './PartesEmissionDialog'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import {
  dateDisplayFormat,
  dateSerializationFormat,
  formatMinutosAsHhMm,
  isoDateFromDateBox,
} from '../carga/partesTareaDuration'
import { monthRange, currentMonthValue } from './PartesDashboardPage'
import { usePartesMinutosColumnSummaryItems } from '../partesGridSummary'
import { listCatalogo, listPartesResource } from '../maestros/partesMaestrosApi'
import { fetchInformeAgrupado, fetchInformeTareas } from './partesInformeApi'
import { enrichRowsWithDiaSemana } from './partesInformeDiaSemana'
import { PartesInformeGrillaPivotSection } from './PartesInformeGrillaPivotSection'
import {
  buildPartesConsultaDetalladaPivotCatalog,
  buildPartesConsultasAgrupadasPivotCatalog,
} from './partesInformePivotCatalog'
import {
  buildConsultaDetalladaHostContext,
  shouldDisableConsultaDetalladaEmit,
  shouldMountConsultaDetalladaEmit,
  type ConsultaDetalladaEstadoCerrado,
} from './consultaDetalladaHostContext'
import { setEmissionHostContextSnapshot } from './emissionHostContextBridge'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'

function formatDuracionCell(cell: { value?: unknown }) {
  return formatMinutosAsHhMm(Number(cell.value ?? 0))
}

const CONSULTA_DETALLADA_ID = 'partes.consultaDetallada'
const CONSULTA_AGRUPADA_ID = 'partes.consultasAgrupadas'
const CONSULTA_DETALLADA_PROCESS = 'partes.informes.consultaDetallada'

function catalogDisplay(item: Record<string, unknown> | null, descriptionKey: 'nombre' | 'descripcion'): string {
  if (!item) {
    return ''
  }
  return `${String(item.code ?? '')} — ${String(item[descriptionKey] ?? '')}`
}

export function ConsultaDetalladaPage() {
  const { t, i18n } = useTranslation()
  const pageTitle = useProcessMenuTitle(
    t('partes.informe.page.consultaDetallada'),
    '/partes/informes/consulta-detallada',
  )
  const native = isNativeApp()
  const session = getAuthSession()
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const isCliente = session?.partes?.tipoFuncional === 'cliente'
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [filtroTipoTareaId, setFiltroTipoTareaId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<ConsultaDetalladaEstadoCerrado>('todas')
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [emissionEnabled, setEmissionEnabled] = useState(false)
  const [emitDialogVisible, setEmitDialogVisible] = useState(false)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [catalogosLoading, setCatalogosLoading] = useState(false)

  const rows = useMemo(
    () => enrichRowsWithDiaSemana(rawRows, t, 'fecha'),
    [rawRows, t, i18n.language]
  )

  const pivotCatalog = useMemo(
    () => buildPartesConsultaDetalladaPivotCatalog(t, i18n.language),
    [t, i18n.language],
  )

  const fetchDetalladaRows = useCallback(async () => {
    const query: Record<string, string> = { fechaDesde, fechaHasta, estadoCerrado }
    if (filtroClienteId != null) {
      query.clienteId = String(filtroClienteId)
    }
    if (esSupervisor && filtroUsuarioId != null) {
      query.usuarioId = String(filtroUsuarioId)
    }
    if (filtroTipoTareaId != null) {
      query.tipoTareaId = String(filtroTipoTareaId)
    }
    const result = await fetchInformeTareas(query)
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setRawRows(items)
      setTotal(result.envelope.resultado.total ?? 0)
      if ((result.envelope.resultado.total ?? 0) === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      }
      return enrichRowsWithDiaSemana(items, t, 'fecha')
    }
    if (result.kind === 'envelopeError') {
      const message = resolveAuthMessage(result.envelope.respuesta)
      setError(message)
      setTotal(0)
      throw new Error(message)
    }
    return []
  }, [
    fechaDesde,
    fechaHasta,
    filtroClienteId,
    filtroUsuarioId,
    filtroTipoTareaId,
    estadoCerrado,
    esSupervisor,
    t,
  ])

  const loadPivotDataset = useCallback(async () => fetchDetalladaRows(), [fetchDetalladaRows])

  const syncHostContext = useCallback(() => {
    setEmissionHostContextSnapshot(
      buildConsultaDetalladaHostContext({
        fechaDesde,
        fechaHasta,
        clienteId: filtroClienteId,
        usuarioId: filtroUsuarioId,
        tipoTareaId: filtroTipoTareaId,
        estadoCerrado,
        esSupervisor,
      }),
    )
  }, [
    fechaDesde,
    fechaHasta,
    filtroClienteId,
    filtroUsuarioId,
    filtroTipoTareaId,
    estadoCerrado,
    esSupervisor,
  ])

  useEffect(() => {
    syncHostContext()
  }, [syncHostContext])

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      await fetchDetalladaRows()
    } catch {
      // envelopeError ya setea error en fetchDetalladaRows
    } finally {
      setLoading(false)
    }
  }, [fetchDetalladaRows])

  useEffect(() => {
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount
  }, [])

  useEffect(() => {
    if (native) {
      return
    }
    void getEmissionProcess(CONSULTA_DETALLADA_PROCESS).then((result) => {
      setEmissionEnabled(result.kind === 'ok')
    })
  }, [native])

  useEffect(() => {
    if (isCliente) {
      return
    }
    setCatalogosLoading(true)
    void Promise.all([
      listCatalogo('clientes'),
      listPartesResource('tipos-tarea'),
      esSupervisor ? listCatalogo('asistentes') : Promise.resolve(null),
    ]).then(([clientesResult, tiposResult, asistentesResult]) => {
      if (clientesResult.kind === 'ok') {
        setClientes(clientesResult.envelope.resultado.items ?? [])
      }
      if (tiposResult.kind === 'ok') {
        setTipos(tiposResult.envelope.resultado.items ?? [])
      }
      if (asistentesResult && asistentesResult.kind === 'ok') {
        setAsistentes(asistentesResult.envelope.resultado.items ?? [])
      }
      setCatalogosLoading(false)
    })
  }, [esSupervisor, isCliente])

  const showEmit = shouldMountConsultaDetalladaEmit(native, emissionEnabled)
  const emitDisabled = shouldDisableConsultaDetalladaEmit(loading, total)

  function openEmitDialog() {
    syncHostContext()
    setEmitDialogVisible(true)
  }

  const emitButton = showEmit ? (
    <Button
      text={t('partes.informe.emitir')}
      disabled={emitDisabled}
      onClick={openEmitDialog}
      elementAttr={{ 'data-testid': 'partesConsultaDetalladaEmit' }}
    />
  ) : null

  const catalogLoadingText = catalogosLoading ? t('catalog.loading') : undefined

  const durationSummaryFormatter = useCallback(
    (value: unknown) => formatMinutosAsHhMm(Number(value ?? 0)),
    []
  )

  const duracionSummaryItems = usePartesMinutosColumnSummaryItems(
    'duracionMinutos',
    'pq-duracionMinutos-sum',
  )

  const renderDetalladaGridView = useCallback(
    (toolbarLeading: ReactNode) => (
      <div data-testid="partesConsultaDetalladaGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          showBorders
          loading={loading}
          onRefresh={() => void load()}
          proceso="partes.informes.consultaDetallada"
          gridId="consultaDetallada"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          defaultTotalItems={duracionSummaryItems}
          columnSummaryFormatters={{
            duracionMinutos: durationSummaryFormatter,
          }}
          toolbarLeading={toolbarLeading}
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption={t('partes.informe.field.fecha')} dataType="date" />
          <Column dataField="diaSemana" caption={t('partes.informe.field.diaSemana')} />
          <Column dataField="usuarioCode" caption={t('partes.informe.field.usuarioCode')} />
          <Column dataField="usuarioNombre" caption={t('partes.informe.field.usuarioNombre')} />
          <Column dataField="clienteCode" caption={t('partes.informe.field.clienteCode')} />
          <Column dataField="erpCliente" caption={t('partes.informe.field.erpCliente')} />
          <Column dataField="erpArticulo" caption={t('partes.informe.field.erpArticulo')} />
          <Column dataField="tipoTareaCode" caption={t('partes.informe.field.tipoTareaCode')} />
          <Column
            dataField="tipoTareaDescripcion"
            caption={t('partes.informe.field.tipoTareaDescripcion')}
          />
          <Column
            dataField="duracionMinutos"
            caption={t('partes.informe.field.duracion')}
            dataType="number"
            customizeText={formatDuracionCell}
          />
          <Column dataField="observacion" caption={t('partes.informe.field.observacion')} />
          <Column
            dataField="sinCargo"
            caption={t('partes.informe.field.sinCargo')}
            dataType="boolean"
          />
          <Column
            dataField="presencial"
            caption={t('partes.informe.field.presencial')}
            dataType="boolean"
          />
          <Column
            dataField="cerrado"
            caption={t('partes.informe.field.cerrado')}
            dataType="boolean"
          />
        </ProcessDataGrid>
      </div>
    ),
    [duracionSummaryItems, durationSummaryFormatter, load, loading, rows, t],
  )

  return (
    <div data-testid="partesConsultaDetalladaPage" style={{ padding: 16 }}>
      <h2>{pageTitle}</h2>
      <div
        style={{
          display: 'flex',
          gap: 12,
          marginBottom: 12,
          flexWrap: 'wrap',
          alignItems: 'center',
        }}
      >
        <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <span>{t('partes.informe.field.fecha')}</span>
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
        </label>
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
        {!isCliente ? (
          <>
            <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <span>
                {t('partes.informe.filtro.cliente')}
                {catalogosLoading ? ` (${t('catalog.loading')})` : ''}
              </span>
              <SelectBox
                dataSource={clientes}
                value={filtroClienteId}
                valueExpr="id"
                displayExpr={(item: Record<string, unknown> | null) => catalogDisplay(item, 'nombre')}
                showClearButton
                placeholder={catalogLoadingText}
                noDataText={catalogosLoading ? t('catalog.loading') : undefined}
                onValueChanged={(e) => setFiltroClienteId((e.value as number | null) ?? null)}
                elementAttr={{ 'data-testid': 'partesConsultaDetalladaCliente' }}
                width={220}
              />
            </label>
            {esSupervisor ? (
              <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <span>
                  {t('partes.informe.filtro.asistente')}
                  {catalogosLoading ? ` (${t('catalog.loading')})` : ''}
                </span>
                <SelectBox
                  dataSource={asistentes}
                  value={filtroUsuarioId}
                  valueExpr="id"
                  displayExpr={(item: Record<string, unknown> | null) => catalogDisplay(item, 'nombre')}
                  showClearButton
                  placeholder={catalogLoadingText}
                  noDataText={catalogosLoading ? t('catalog.loading') : undefined}
                  onValueChanged={(e) => setFiltroUsuarioId((e.value as number | null) ?? null)}
                  elementAttr={{ 'data-testid': 'partesConsultaDetalladaAsistente' }}
                  width={220}
                />
              </label>
            ) : null}
            <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <span>
                {t('partes.informe.filtro.tipoTarea')}
                {catalogosLoading ? ` (${t('catalog.loading')})` : ''}
              </span>
              <SelectBox
                dataSource={tipos}
                value={filtroTipoTareaId}
                valueExpr="id"
                displayExpr={(item: Record<string, unknown> | null) =>
                  catalogDisplay(item, 'descripcion')
                }
                showClearButton
                placeholder={catalogLoadingText}
                noDataText={catalogosLoading ? t('catalog.loading') : undefined}
                onValueChanged={(e) => setFiltroTipoTareaId((e.value as number | null) ?? null)}
                elementAttr={{ 'data-testid': 'partesConsultaDetalladaTipo' }}
                width={220}
              />
            </label>
          </>
        ) : null}
        <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <span>{t('partes.informe.filtro.estadoCerrado')}</span>
          <SelectBox
            dataSource={[
              { id: 'todas', text: t('partes.informe.estado.todas') },
              { id: 'abiertas', text: t('partes.informe.estado.abiertas') },
              { id: 'cerradas', text: t('partes.informe.estado.cerradas') },
            ]}
            value={estadoCerrado}
            valueExpr="id"
            displayExpr="text"
            onValueChanged={(e) =>
              setEstadoCerrado((e.value as ConsultaDetalladaEstadoCerrado) ?? 'todas')
            }
            elementAttr={{ 'data-testid': 'partesConsultaDetalladaEstado' }}
            width={160}
          />
        </label>
        <Button text={t('partes.common.buscar')} onClick={() => void load()} disabled={loading} />
      </div>
      {error ? <div role="alert">{error}</div> : null}
      <PartesInformeGrillaPivotSection
        consultaId={CONSULTA_DETALLADA_ID}
        catalog={pivotCatalog}
        locale={i18n.language}
        native={native}
        loadPivotDataset={loadPivotDataset}
        toolbarLeadingExtras={emitButton}
        renderGridView={renderDetalladaGridView}
      />
      {showEmit && emitDialogVisible ? (
        <EmissionDialog
          processCode={CONSULTA_DETALLADA_PROCESS}
          visible={emitDialogVisible}
          onClose={() => setEmitDialogVisible(false)}
          isNative={false}
          permiteConsolidado={false}
          t={(key) => t(key)}
        />
      ) : null}
    </div>
  )
}

export function ConsultasAgrupadasPage() {
  const { t, i18n } = useTranslation()
  const native = isNativeApp()
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [eje, setEje] = useState('cliente')
  const [granularidadFecha, setGranularidadFecha] = useState('mes')
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const rows = useMemo(
    () => enrichRowsWithDiaSemana(rawRows, t, 'ejeCodigo'),
    [rawRows, t, i18n.language]
  )

  const pivotCatalog = useMemo(() => buildPartesConsultasAgrupadasPivotCatalog(t), [t])

  const fetchAgrupadaRows = useCallback(async () => {
    const query: Record<string, string> = { fechaDesde, fechaHasta, eje }
    if (eje === 'fecha') {
      query.granularidadFecha = granularidadFecha
    }
    const result = await fetchInformeAgrupado(query)
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setRawRows(items)
      if ((result.envelope.resultado.total ?? 0) === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      }
      return enrichRowsWithDiaSemana(items, t, 'ejeCodigo')
    }
    if (result.kind === 'envelopeError') {
      const message = resolveAuthMessage(result.envelope.respuesta)
      setError(message)
      throw new Error(message)
    }
    return []
  }, [fechaDesde, fechaHasta, eje, granularidadFecha, t])

  const loadPivotDataset = useCallback(async () => fetchAgrupadaRows(), [fetchAgrupadaRows])

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      await fetchAgrupadaRows()
    } catch {
      // envelopeError ya setea error
    } finally {
      setLoading(false)
    }
  }, [fetchAgrupadaRows])

  useEffect(() => {
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount
  }, [])

  const durationSummaryFormatter = useCallback(
    (value: unknown) => formatMinutosAsHhMm(Number(value ?? 0)),
    []
  )

  const duracionSummaryItems = usePartesMinutosColumnSummaryItems(
    'totalMinutos',
    'pq-totalMinutos-sum',
  )

  const renderAgrupadaGridView = useCallback(
    (toolbarLeading: ReactNode) => (
      <div data-testid="partesConsultaAgrupadaGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="ejeKey"
          showBorders
          loading={loading}
          onRefresh={() => void load()}
          proceso="partes.informes.consultasAgrupadas"
          gridId="consultasAgrupadas"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          defaultTotalItems={duracionSummaryItems}
          columnSummaryFormatters={{
            totalMinutos: durationSummaryFormatter,
          }}
          toolbarLeading={toolbarLeading}
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="ejeCodigo" caption={t('partes.informe.field.ejeCodigo')} />
          <Column dataField="ejeDescripcion" caption={t('partes.informe.field.ejeDescripcion')} />
          <Column dataField="erpCliente" caption={t('partes.informe.field.erpCliente')} />
          <Column dataField="erpArticulo" caption={t('partes.informe.field.erpArticulo')} />
          <Column dataField="diaSemana" caption={t('partes.informe.field.diaSemana')} />
          <Column
            dataField="totalMinutos"
            caption={t('partes.informe.field.duracion')}
            dataType="number"
            customizeText={formatDuracionCell}
          />
          <Column
            dataField="cantidadTareas"
            caption={t('partes.informe.field.cantidadTareas')}
            dataType="number"
          />
          <Column
            dataField="cantidadSinCargo"
            caption={t('partes.informe.field.sinCargo')}
            dataType="number"
          />
          <Column
            dataField="cantidadPresencial"
            caption={t('partes.informe.field.presencial')}
            dataType="number"
          />
        </ProcessDataGrid>
      </div>
    ),
    [duracionSummaryItems, durationSummaryFormatter, load, loading, rows, t],
  )

  return (
    <div data-testid="partesConsultaAgrupadaPage" style={{ padding: 16 }}>
      <h2>{t('partes.informe.page.consultasAgrupadas')}</h2>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, flexWrap: 'wrap', alignItems: 'end' }}>
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
        <SelectBox
          dataSource={[
            { id: 'cliente', text: t('partes.informe.field.clienteNombre') },
            { id: 'asistente', text: t('partes.informe.field.usuarioNombre') },
            { id: 'tipo', text: t('partes.informe.field.tipoTareaDescripcion') },
            { id: 'fecha', text: t('partes.informe.field.fecha') },
          ]}
          value={eje}
          valueExpr="id"
          displayExpr="text"
          onValueChanged={(e) => setEje(String(e.value))}
          elementAttr={{ 'data-testid': 'partesConsultaAgrupadaEje' }}
        />
        {eje === 'fecha' ? (
          <SelectBox
            dataSource={[
              { id: 'dia', text: t('partes.informe.granularidad.dia') },
              { id: 'mes', text: t('partes.informe.granularidad.mes') },
            ]}
            value={granularidadFecha}
            valueExpr="id"
            displayExpr="text"
            onValueChanged={(e) => setGranularidadFecha(String(e.value))}
          />
        ) : null}
        <Button text={t('partes.common.buscar')} onClick={() => void load()} disabled={loading} />
      </div>
      {error ? <div role="alert">{error}</div> : null}
      <PartesInformeGrillaPivotSection
        consultaId={CONSULTA_AGRUPADA_ID}
        catalog={pivotCatalog}
        locale={i18n.language}
        native={native}
        loadPivotDataset={loadPivotDataset}
        renderGridView={renderAgrupadaGridView}
      />
    </div>
  )
}
