import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import PivotGrid, { FieldChooser, FieldPanel, type PivotGridRef } from 'devextreme-react/pivot-grid'
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source'
import type dxPivotGrid from 'devextreme/ui/pivot_grid'
import { useTranslation } from 'react-i18next'
import Chart, { CommonSeriesSettings, Series, ArgumentAxis, ValueAxis, Legend } from 'devextreme-react/chart'
import {
  ConsultaKardexList,
  getPivotLocalizedUiTexts,
  isNativeApp,
  PivotLayoutsBar,
  ProcessDataGrid,
} from '@paqsuite/react-core'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  dateDisplayFormat,
  dateSerializationFormat,
  formatMinutosAsHhMm,
  isoDateFromDateBox,
} from '../carga/partesTareaDuration'
import { monthRange, currentMonthValue } from './PartesDashboardPage'
import { fetchPaqueteHoras } from './partesInformeApi'
import { buildPaqueteHorasPivotFields } from './partesInformePivotFields'
import { enrichRowsWithDiaSemana } from './partesInformeDiaSemana'
import { aggregatePaqueteHorasDesglose } from '../mobile/aggregatePaqueteHorasDesglose'
import { mapDesgloseToKardexItem } from '../mobile/mapPartesTareaToKardexItem'

function formatDuracionCell(cell: { value?: unknown }) {
  return formatMinutosAsHhMm(Number(cell.value ?? 0))
}

const PAQUETE_HORAS_CONSULTA_ID = 'partes.informes.paqueteHoras'

function getPivotInstance(ref: PivotGridRef | null): dxPivotGrid | undefined {
  if (!ref) {
    return undefined
  }
  if (typeof ref.instance === 'function') {
    return ref.instance()
  }
  return undefined
}

export function PaqueteHorasPage() {
  const { t, i18n } = useTranslation()
  const native = isNativeApp()
  const session = getAuthSession()
  const esCliente = session?.partes?.tipoFuncional === 'cliente'
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [clienteId, setClienteId] = useState<number | null>(null)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [saldoInicial, setSaldoInicial] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [mode, setMode] = useState<'grid' | 'pivot'>('grid')
  const [pivotRemountKey, setPivotRemountKey] = useState(0)
  const pivotRef = useRef<PivotGridRef>(null)

  const [ejeChart, setEjeChart] = useState<'cliente' | 'tipo'>('cliente')

  const rows = useMemo(
    () => enrichRowsWithDiaSemana(rawRows, t, 'fecha'),
    [rawRows, t, i18n.language]
  )

  const pivotRows = useMemo(
    () => rows.filter((row) => !row.esSaldoInicial),
    [rows]
  )

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

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await fetchPaqueteHoras({
        fechaDesde,
        fechaHasta,
        clienteId: esCliente ? undefined : clienteId,
      })
      if (result.kind === 'ok') {
        setRawRows(result.envelope.resultado.items ?? [])
        setSaldoInicial(result.envelope.resultado.saldoInicial ?? 0)
        if ((result.envelope.resultado.total ?? 0) <= 1) {
          setError(resolveAuthMessage('partes.consulta.empty'))
        }
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [fechaDesde, fechaHasta, clienteId, esCliente])

  // Carga inicial únicamente; cambios de filtro se aplican con «Buscar»
  // (evitar overlay a mitad de edición del DateBox).
  useEffect(() => {
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount
  }, [])

  const pivotSource = useMemo(
    () =>
      new PivotGridDataSource({
        retrieveFields: false,
        fields: buildPaqueteHorasPivotFields(t, i18n.language),
        store: pivotRows,
      }),
    [pivotRows, t, i18n.language, pivotRemountKey]
  )

  const pivotUiTexts = useMemo(
    () => getPivotLocalizedUiTexts(i18n.language),
    [i18n.language]
  )

  const pivotInstanceKey = `${pivotRemountKey}-${i18n.language}`

  if (native) {
    return (
      <div className="pqProcessPage partesMobileProcess" data-testid="partesPaqueteHorasPage">
        <div className="pqProcessHeader partesMobileProcessHeader">
          <h2 className="pqProcessTitle">{t('dashboard.linkPaqueteHoras')}</h2>
          <div className="pqProcessToolbar partesMobileProcessToolbar">
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
            <Button
              text={t('dashboard.refresh')}
              onClick={() => void load()}
              disabled={loading}
              elementAttr={{ 'data-testid': 'partesPaqueteBuscar' }}
            />
          </div>
        </div>
        <div className="pqProcessStats partesMobileKpiBlock">
          <strong>{t('partes.mobile.saldoInicial')}:</strong> {formatMinutosAsHhMm(saldoInicial)}
        </div>
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
    )
  }

  return (
    <div data-testid="partesPaqueteHorasPage" style={{ padding: 16 }}>
      <h2>{t('partes.informe.page.paqueteHoras')}</h2>
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
              if (!e.event) {
                return
              }
              setClienteId((e.value as number | null) ?? null)
            }}
            elementAttr={{ 'data-testid': 'partesPaqueteCliente' }}
          />
        ) : null}
        <Button
          text={t('partes.common.buscar')}
          onClick={() => void load()}
          disabled={loading}
          elementAttr={{ 'data-testid': 'partesPaqueteBuscar' }}
        />
      </div>
      <div style={{ marginBottom: 12 }}>
        <strong>{t('partes.informe.saldoInicialLabel')}</strong> {formatMinutosAsHhMm(saldoInicial)}
      </div>
      {error ? <div role="alert">{error}</div> : null}
      {mode === 'grid' ? (
        <div data-testid="partesPaqueteHorasGrid">
          <ProcessDataGrid
            dataSource={rows}
            keyExpr="id"
            showBorders
            loading={loading}
            proceso="partes.informes.paqueteHoras"
            gridId="paqueteHorasDetalle"
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            toolbarLeading={
              !native ? (
                <Button
                  text={t('partes.common.pivot')}
                  onClick={() => setMode('pivot')}
                  elementAttr={{ 'data-testid': 'partesPaquetePivotToggle' }}
                />
              ) : undefined
            }
          >
            <Paging defaultPageSize={50} />
            <Pager visible showPageSizeSelector />
            <Column dataField="fecha" caption={t('partes.informe.field.fecha')} dataType="date" />
            <Column dataField="diaSemana" caption={t('partes.informe.field.diaSemana')} />
            <Column dataField="usuarioCode" caption={t('partes.informe.field.usuarioCode')} />
            <Column dataField="usuarioNombre" caption={t('partes.informe.field.usuarioNombre')} />
            <Column dataField="clienteCode" caption={t('partes.informe.field.clienteCode')} />
            <Column dataField="clienteNombre" caption={t('partes.informe.field.clienteNombre')} />
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
              customizeText={formatDuracionCell}
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
      ) : (
        <div data-testid="partesPaqueteHorasPivot">
          <PivotLayoutsBar
            consultaId={PAQUETE_HORAS_CONSULTA_ID}
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            leadingSlot={
              <Button text={t('partes.common.grilla')} onClick={() => setMode('grid')} />
            }
            getPivotState={() => {
              const state = getPivotInstance(pivotRef.current)?.getDataSource()?.state()
              return (state ?? null) as Record<string, unknown> | null
            }}
            applyPivotStateJson={(state) => {
              if (state === null) {
                setPivotRemountKey((key) => key + 1)
                return
              }
              const dataSource = getPivotInstance(pivotRef.current)?.getDataSource()
              dataSource?.state(state)
            }}
            getPivotComponent={() => getPivotInstance(pivotRef.current)}
            canExport={pivotRows.length > 0 && !loading}
          >
            <PivotGrid
              key={pivotInstanceKey}
              ref={pivotRef}
              dataSource={pivotSource}
              allowSorting
              allowSortingBySummary
              allowFiltering
              showBorders
              showColumnGrandTotals
              showRowGrandTotals
            >
              <FieldPanel
                visible
                showColumnFields
                showDataFields
                showFilterFields
                showRowFields
                allowFieldDragging
                texts={pivotUiTexts.fieldPanel}
              />
              <FieldChooser
                enabled
                title={pivotUiTexts.fieldChooserTitle}
                texts={pivotUiTexts.fieldChooser}
              />
            </PivotGrid>
          </PivotLayoutsBar>
        </div>
      )}
    </div>
  )
}
