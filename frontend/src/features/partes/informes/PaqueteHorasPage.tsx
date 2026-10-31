import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import { useTranslation } from 'react-i18next'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import Chart, { CommonSeriesSettings, Series, ArgumentAxis, ValueAxis, Legend } from 'devextreme-react/chart'
import { ConsultaKardexList, isNativeApp, ProcessDataGrid, ProcessPageLayout } from '@paqsuite/react-core'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  dateDisplayFormat,
  dateSerializationFormat,
  enrichRowsWithDuracionHoras,
  formatMinutosAsHhMm,
  formatMinutosAsHhMmSigned,
  isoDateFromDateBox,
} from '../carga/partesTareaDuration'
import { monthRange, currentMonthValue } from './PartesDashboardPage'
import { fetchPaqueteHoras } from './partesInformeApi'
import { enrichRowsWithDiaSemana } from './partesInformeDiaSemana'
import { PartesInformeGrillaPivotSection } from './PartesInformeGrillaPivotSection'
import { buildPartesPaqueteHorasPivotCatalog } from './partesInformePivotCatalog'
import { aggregatePaqueteHorasDesglose } from '../mobile/aggregatePaqueteHorasDesglose'
import { mapDesgloseToKardexItem } from '../mobile/mapPartesTareaToKardexItem'

function formatDuracionCell(cell: { value?: unknown; data?: Record<string, unknown> }) {
  const row = cell.data ?? {}
  const mins = Number(cell.value ?? 0)
  const base = formatMinutosAsHhMm(mins)
  if (row.esSaldoInicial) {
    return base
  }
  if (row.esTarea === false) {
    return `-${base}`
  }
  return base
}

function formatSaldoCell(cell: { value?: unknown }) {
  return formatMinutosAsHhMmSigned(Number(cell.value ?? 0))
}

function onPaqueteHorasCellPrepared(e: {
  rowType?: string
  column?: { dataField?: string }
  cellElement?: HTMLElement
  data?: Record<string, unknown>
}) {
  if (e.rowType !== 'data' || !e.column || !e.cellElement || !e.data) {
    return
  }
  if (e.column.dataField === 'duracionMinutos') {
    const text = formatDuracionCell({ value: e.data.duracionMinutos, data: e.data })
    e.cellElement.textContent = text
    if (!e.data.esSaldoInicial && e.data.esTarea === false) {
      e.cellElement.classList.add('partes-paquete-horas-duration-credit')
    }
  }
  if (e.column.dataField === 'saldo') {
    e.cellElement.textContent = formatSaldoCell({ value: e.data.saldo })
  }
}

const PAQUETE_HORAS_CONSULTA_ID = 'partes.informes.paqueteHoras'

export function PaqueteHorasPage() {
  const { t, i18n } = useTranslation()
  const pageTitle = useProcessMenuTitle(
    t('partes.informe.page.paqueteHoras'),
    '/partes/informes/paquete-horas',
  )
  const native = isNativeApp()
  const session = getAuthSession()
  const esCliente = session?.partes?.tipoFuncional === 'cliente'
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [clienteId, setClienteId] = useState<number | null>(null)
  const [tipoClienteId, setTipoClienteId] = useState<number | null>(null)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [tiposCliente, setTiposCliente] = useState<Record<string, unknown>[]>([])
  const [tiposClienteLoading, setTiposClienteLoading] = useState(true)
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [saldoInicial, setSaldoInicial] = useState(0)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  /** Remonta la grilla tras búsqueda OK (plantilla DX / filter row no debe dejar filas ocultas). */
  const [gridResetKey, setGridResetKey] = useState(0)

  const [ejeChart, setEjeChart] = useState<'cliente' | 'tipo'>('cliente')

  const rows = useMemo(
    () => enrichRowsWithDuracionHoras(enrichRowsWithDiaSemana(rawRows, t, 'fecha')),
    [rawRows, t, i18n.language]
  )

  const pivotCatalog = useMemo(
    () => buildPartesPaqueteHorasPivotCatalog(t, i18n.language),
    // eslint-disable-next-line react-hooks/exhaustive-deps -- locale
    [i18n.language],
  )

  const fetchPaqueteHorasRows = useCallback(async () => {
    const result = await fetchPaqueteHoras({
      fechaDesde,
      fechaHasta,
      clienteId: esCliente ? undefined : clienteId,
      tipoClienteId,
    })
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setRawRows(items)
      setSaldoInicial(result.envelope.resultado.saldoInicial ?? 0)
      const movimientos = items.filter((row) => !row.esSaldoInicial)
      if (movimientos.length === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      } else {
        setError(null)
      }
      const enriched = enrichRowsWithDuracionHoras(enrichRowsWithDiaSemana(items, t, 'fecha'))
      return enriched.filter((row) => !row.esSaldoInicial)
    }
    if (result.kind === 'envelopeError') {
      const message = resolveAuthMessage(result.envelope.respuesta)
      setError(message)
      throw new Error(message)
    }
    return []
  }, [clienteId, esCliente, fechaDesde, fechaHasta, t, tipoClienteId])

  const loadPivotDataset = useCallback(async () => {
    if (!esCliente && (clienteId === null || clienteId <= 0)) {
      return []
    }
    return fetchPaqueteHorasRows()
  }, [clienteId, esCliente, fetchPaqueteHorasRows])

  const desgloseCliente = useMemo(
    () => aggregatePaqueteHorasDesglose(rawRows, 'cliente'),
    [rawRows],
  )
  const desgloseTipo = useMemo(
    () => aggregatePaqueteHorasDesglose(rawRows, 'tipo'),
    [rawRows],
  )
  const chartSource = ejeChart === 'cliente' ? desgloseCliente : desgloseTipo

  useEffect(() => {
    if (esCliente) {
      return
    }
    void listCatalogo('clientes').then((result) => {
      if (result.kind === 'ok') {
        setClientes(result.envelope.resultado.items ?? [])
      }
    })
  }, [esCliente])

  useEffect(() => {
    setTiposClienteLoading(true)
    void listCatalogo('tipos-cliente')
      .then((result) => {
        if (result.kind === 'ok') {
          setTiposCliente(result.envelope.resultado.items ?? [])
        }
      })
      .finally(() => setTiposClienteLoading(false))
  }, [])

  const load = useCallback(async () => {
    if (!esCliente && (clienteId === null || clienteId <= 0)) {
      setError(resolveAuthMessage('partes.informe.clienteRequerido'))
      setRawRows([])
      setSaldoInicial(0)
      return
    }
    setLoading(true)
    setError(null)
    try {
      await fetchPaqueteHorasRows()
      setGridResetKey((revision) => revision + 1)
    } catch {
      // envelopeError ya setea error
    } finally {
      setLoading(false)
    }
  }, [clienteId, esCliente, fetchPaqueteHorasRows])

  useEffect(() => {
    if (esCliente) {
      void load()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount cliente funcional
  }, [esCliente])

  useEffect(() => {
    if (esCliente) {
      return
    }
    if (clienteId == null || clienteId <= 0) {
      setRawRows([])
      setSaldoInicial(0)
      setError(null)
      return
    }
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- recarga al elegir cliente, no al cambiar fechas
  }, [clienteId, esCliente])

  const needsClienteSelection = !esCliente && (clienteId == null || clienteId <= 0)

  const renderPaqueteGridView = useCallback(
    (toolbarLeading: ReactNode) => (
      <div data-testid="partesPaqueteHorasGrid">
        <ProcessDataGrid
          key={`paqueteHorasGrid-${clienteId ?? 'none'}-${fechaDesde}-${fechaHasta}-${tipoClienteId ?? 'all'}-${gridResetKey}`}
          dataSource={rows}
          keyExpr="id"
          showBorders
          loading={loading}
          onRefresh={() => void load()}
          onCellPrepared={onPaqueteHorasCellPrepared}
          proceso="partes.informes.paqueteHoras"
          gridId="paqueteHorasDetalle"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          toolbarLeading={toolbarLeading}
        >
          <Paging defaultPageSize={50} />
          <Pager visible showPageSizeSelector />
          <Column dataField="fecha" caption={t('partes.informe.field.fecha')} dataType="date" />
          <Column dataField="diaSemana" caption={t('partes.informe.field.diaSemana')} />
          <Column dataField="usuarioCode" caption={t('partes.informe.field.usuarioCode')} />
          <Column dataField="usuarioNombre" caption={t('partes.informe.field.usuarioNombre')} />
          <Column dataField="clienteCode" caption={t('partes.informe.field.clienteCode')} />
          <Column dataField="clienteNombre" caption={t('partes.informe.field.clienteNombre')} />
          <Column dataField="tipoClienteCode" caption={t('partes.informe.field.tipoClienteCode')} />
          <Column
            dataField="tipoClienteDescripcion"
            caption={t('partes.informe.field.tipoClienteDescripcion')}
          />
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
          <Column
            dataField="saldo"
            caption={t('partes.informe.field.saldo')}
            dataType="number"
            customizeText={formatSaldoCell}
          />
          <Column dataField="esTarea" caption={t('partes.informe.field.esTarea')} dataType="boolean" />
          <Column
            dataField="esSaldoInicial"
            caption={t('partes.mobile.saldoInicial')}
            dataType="boolean"
            visible={false}
          />
          <Column dataField="observacion" caption={t('partes.informe.field.observacion')} />
          <Column dataField="sinCargo" caption={t('partes.informe.field.sinCargo')} dataType="boolean" />
          <Column dataField="presencial" caption={t('partes.informe.field.presencial')} dataType="boolean" />
          <Column dataField="cerrado" caption={t('partes.informe.field.cerrado')} dataType="boolean" />
        </ProcessDataGrid>
      </div>
    ),
    [clienteId, fechaDesde, fechaHasta, gridResetKey, load, loading, rows, t, tipoClienteId],
  )

  if (native) {
    return (
      <ProcessPageLayout
        title={pageTitle}
        toolbar={
          <div className="partesMobileProcessToolbar">
            <DateBox
              value={fechaDesde}
              type="date"
              displayFormat={dateDisplayFormat}
              dateSerializationFormat={dateSerializationFormat}
              elementAttr={{ 'data-testid': 'partesPaqueteFechaDesde' }}
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
              elementAttr={{ 'data-testid': 'partesPaqueteFechaHasta' }}
              onValueChanged={(e) => {
                const next = isoDateFromDateBox(e)
                if (next !== null) {
                  setFechaHasta(next)
                }
              }}
            />
            <SelectBox
              dataSource={tiposCliente}
              value={tipoClienteId}
              displayExpr={(item: Record<string, unknown> | null) =>
                item ? `${String(item.code ?? '')} — ${String(item.descripcion ?? '')}` : ''
              }
              valueExpr="id"
              searchEnabled
              showClearButton
              placeholder={
                tiposClienteLoading ? t('catalog.loading') : t('partes.informe.filtro.tipoCliente')
              }
              noDataText={tiposClienteLoading ? t('catalog.loading') : undefined}
              disabled={tiposClienteLoading}
              width={240}
              onValueChanged={(e) => {
                setTipoClienteId((e.value as number | null) ?? null)
              }}
              elementAttr={{ 'data-testid': 'partesPaqueteTipoCliente' }}
            />
            <Button
              text={t('dashboard.refresh')}
              onClick={() => void load()}
              disabled={loading}
              elementAttr={{ 'data-testid': 'partesPaqueteBuscar' }}
            />
          </div>
        }
      >
        <div className="partesMobileProcess" data-testid="partesPaqueteHorasPage">
        <div className="pqProcessStats partesMobileKpiBlock">
          <strong>{t('partes.mobile.saldoInicial')}:</strong> {formatMinutosAsHhMm(saldoInicial)}
        </div>
        {needsClienteSelection ? (
          <div role="status" data-testid="partesPaqueteClienteHint">
            {t('partes.informe.clienteRequerido')}
          </div>
        ) : null}
        {error ? <div role="alert">{error}</div> : null}
        <div className="partesMobileChartToggle">
          <Button
            text={t('dashboard.colCliente')}
            type={ejeChart === 'cliente' ? 'default' : 'normal'}
            onClick={() => setEjeChart('cliente')}
          />
          <Button
            text={t('partes.informe.field.tipoTareaDescripcion')}
            type={ejeChart === 'tipo' ? 'default' : 'normal'}
            onClick={() => setEjeChart('tipo')}
          />
        </div>
        <div className="partesMobileChartWrap" data-testid="partesPaqueteChart">
          <Chart dataSource={chartSource}>
            <CommonSeriesSettings argumentField="title" type="bar" />
            <Series valueField="totalMinutos" name={t('dashboard.colMinutos')} />
            <ArgumentAxis />
            <ValueAxis />
            <Legend visible={false} />
          </Chart>
        </div>
        <section className="partesMobileSection">
          <h3 className="partesMobileSectionTitle">{t('dashboard.colCliente')}</h3>
          <ConsultaKardexList
            items={desgloseCliente.map((row) =>
              mapDesgloseToKardexItem(row, (key) => t(key), formatMinutosAsHhMm),
            )}
            onItemTap={() => undefined}
            t={(key) => t(key)}
          />
        </section>
        <section className="partesMobileSection">
          <h3 className="partesMobileSectionTitle">
            {t('partes.informe.field.tipoTareaDescripcion')}
          </h3>
          <ConsultaKardexList
            items={desgloseTipo.map((row) =>
              mapDesgloseToKardexItem(row, (key) => t(key), formatMinutosAsHhMm),
            )}
            onItemTap={() => undefined}
            t={(key) => t(key)}
          />
        </section>
        </div>
      </ProcessPageLayout>
    )
  }

  return (
    <ProcessPageLayout title={pageTitle}>
    <div data-testid="partesPaqueteHorasPage" style={{ padding: 16 }}>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, alignItems: 'end', flexWrap: 'wrap' }}>
        <DateBox
          value={fechaDesde}
          type="date"
          displayFormat={dateDisplayFormat}
          dateSerializationFormat={dateSerializationFormat}
          elementAttr={{ 'data-testid': 'partesPaqueteFechaDesde' }}
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
          elementAttr={{ 'data-testid': 'partesPaqueteFechaHasta' }}
          onValueChanged={(e) => {
            const next = isoDateFromDateBox(e)
            if (next !== null) {
              setFechaHasta(next)
            }
          }}
        />
        {!esCliente ? (
          <SelectBox
            dataSource={clientes}
            value={clienteId}
            displayExpr={(item: Record<string, unknown> | null) =>
              item ? `${String(item.code ?? '')} — ${String(item.nombre ?? '')}` : ''
            }
            valueExpr="id"
            searchEnabled
            showClearButton
            placeholder={t('partes.informe.filtro.cliente')}
            width={280}
            onValueChanged={(e) => {
              setClienteId((e.value as number | null) ?? null)
            }}
            elementAttr={{ 'data-testid': 'partesPaqueteCliente' }}
          />
        ) : null}
        <SelectBox
          dataSource={tiposCliente}
          value={tipoClienteId}
          displayExpr={(item: Record<string, unknown> | null) =>
            item ? `${String(item.code ?? '')} — ${String(item.descripcion ?? '')}` : ''
          }
          valueExpr="id"
          searchEnabled
          showClearButton
          placeholder={
            tiposClienteLoading ? t('catalog.loading') : t('partes.informe.filtro.tipoCliente')
          }
          noDataText={tiposClienteLoading ? t('catalog.loading') : undefined}
          disabled={tiposClienteLoading}
          width={240}
          onValueChanged={(e) => {
            setTipoClienteId((e.value as number | null) ?? null)
          }}
          elementAttr={{ 'data-testid': 'partesPaqueteTipoCliente' }}
        />
        <Button
          text={t('partes.common.buscar')}
          onClick={() => void load()}
          disabled={loading || (!esCliente && clienteId == null)}
          elementAttr={{ 'data-testid': 'partesPaqueteBuscar' }}
        />
      </div>
      <div style={{ marginBottom: 12 }}>
        <strong>{t('partes.informe.saldoInicialLabel')}</strong> {formatMinutosAsHhMm(saldoInicial)}
      </div>
      {needsClienteSelection ? (
        <div role="status" data-testid="partesPaqueteClienteHint" style={{ marginBottom: 12 }}>
          {t('partes.informe.clienteRequerido')}
        </div>
      ) : null}
      {error ? <div role="alert">{error}</div> : null}
      <PartesInformeGrillaPivotSection
        consultaId={PAQUETE_HORAS_CONSULTA_ID}
        catalog={pivotCatalog}
        locale={i18n.language}
        native={native}
        loadPivotDataset={loadPivotDataset}
        renderGridView={renderPaqueteGridView}
      />
    </div>
    </ProcessPageLayout>
  )
}
