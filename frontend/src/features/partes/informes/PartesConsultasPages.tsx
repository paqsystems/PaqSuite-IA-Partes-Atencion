import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import PivotGrid, { FieldChooser, FieldPanel, type PivotGridRef } from 'devextreme-react/pivot-grid'
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source'
import type dxPivotGrid from 'devextreme/ui/pivot_grid'
import { useTranslation } from 'react-i18next'
import {
  getPivotLocalizedUiTexts,
  isNativeApp,
  PivotLayoutsBar,
  ProcessDataGrid,
} from '@paqsuite/react-core'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { formatMinutosAsHhMm, todayIsoDate } from '../carga/partesTareaDuration'
import { monthRange, currentMonthValue } from './PartesDashboardPage'
import { fetchInformeAgrupado, fetchInformeTareas } from './partesInformeApi'
import {
  buildConsultaAgrupadaPivotFields,
  buildConsultaDetalladaPivotFields,
} from './partesInformePivotFields'
import { enrichRowsWithDiaSemana } from './partesInformeDiaSemana'

function formatDuracionCell(cell: { value?: unknown }) {
  return formatMinutosAsHhMm(Number(cell.value ?? 0))
}

/** ISO yyyy-MM-dd desde DateBox; ignora cambios programáticos (evita bucles). */
function isoDateFromDateBox(e: { event?: Event; value?: unknown }): string | null {
  if (!e.event) {
    return null
  }
  if (e.value == null || e.value === '') {
    return ''
  }
  if (typeof e.value === 'string') {
    return e.value.slice(0, 10)
  }
  return todayIsoDate(new Date(e.value as Date))
}

function getPivotInstance(ref: PivotGridRef | null): dxPivotGrid | undefined {
  if (!ref) {
    return undefined
  }
  if (typeof ref.instance === 'function') {
    return ref.instance()
  }
  return undefined
}

const CONSULTA_DETALLADA_ID = 'partes.consultaDetallada'
const CONSULTA_AGRUPADA_ID = 'partes.consultasAgrupadas'

export function ConsultaDetalladaPage() {
  const { t, i18n } = useTranslation()
  const native = isNativeApp()
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [mode, setMode] = useState<'grid' | 'pivot'>('grid')
  const [pivotRemountKey, setPivotRemountKey] = useState(0)
  const pivotRef = useRef<PivotGridRef>(null)

  const rows = useMemo(
    () => enrichRowsWithDiaSemana(rawRows, t, 'fecha'),
    [rawRows, t, i18n.language]
  )

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await fetchInformeTareas({ fechaDesde, fechaHasta })
      if (result.kind === 'ok') {
        setRawRows(result.envelope.resultado.items ?? [])
        if ((result.envelope.resultado.total ?? 0) === 0) {
          setError(resolveAuthMessage('partes.consulta.empty'))
        }
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [fechaDesde, fechaHasta])

  // Carga inicial; filtros se aplican con «Buscar».
  useEffect(() => {
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount
  }, [])

  const pivotSource = useMemo(
    () =>
      new PivotGridDataSource({
        retrieveFields: false,
        fields: buildConsultaDetalladaPivotFields(t, i18n.language),
        store: rows,
      }),
    [rows, t, i18n.language, pivotRemountKey]
  )

  const pivotUiTexts = useMemo(
    () => getPivotLocalizedUiTexts(i18n.language),
    [i18n.language]
  )

  const pivotInstanceKey = `${pivotRemountKey}-${i18n.language}`

  const durationSummaryFormatter = useCallback(
    (value: unknown) => formatMinutosAsHhMm(Number(value ?? 0)),
    []
  )

  const duracionSummaryItems = useMemo(
    () => [
      {
        column: 'duracionMinutos',
        summaryType: 'sum' as const,
        name: 'pq-duracionMinutos-sum',
        displayFormat: 'Suma: {0}',
        customizeText: (info: { value?: string | number | Date }) =>
          `Suma: ${formatMinutosAsHhMm(Number(info.value ?? 0))}`,
      },
    ],
    []
  )

  return (
    <div data-testid="partesConsultaDetalladaPage" style={{ padding: 16 }}>
      <h2>Consulta detallada</h2>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, alignItems: 'end' }}>
        <DateBox
          value={fechaDesde}
          type="date"
          displayFormat="dd/MM/yyyy"
          dateSerializationFormat="yyyy-MM-dd"
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
          displayFormat="dd/MM/yyyy"
          dateSerializationFormat="yyyy-MM-dd"
          onValueChanged={(e) => {
            const next = isoDateFromDateBox(e)
            if (next !== null) {
              setFechaHasta(next)
            }
          }}
        />
        <Button text="Buscar" onClick={() => void load()} disabled={loading} />
      </div>
      {error ? <div role="alert">{error}</div> : null}
      {mode === 'grid' || native ? (
        <div data-testid="partesConsultaDetalladaGrid">
          <ProcessDataGrid
            dataSource={rows}
            keyExpr="id"
            showBorders
            loading={loading}
            proceso="partes.informes.consultaDetallada"
            gridId="consultaDetallada"
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            defaultTotalItems={duracionSummaryItems}
            columnSummaryFormatters={{
              duracionMinutos: durationSummaryFormatter,
            }}
            toolbarLeading={
              !native ? (
                <Button
                  text="Pivot"
                  onClick={() => setMode('pivot')}
                  elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
                />
              ) : undefined
            }
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
      ) : (
        <div data-testid="partesConsultaDetalladaPivot">
          <PivotLayoutsBar
            consultaId={CONSULTA_DETALLADA_ID}
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            leadingSlot={
              <Button
                text="Grilla"
                onClick={() => setMode('grid')}
                elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
              />
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
            canExport={rows.length > 0 && !loading}
          >
            <PivotGrid
              key={pivotInstanceKey}
              ref={pivotRef}
              dataSource={pivotSource}
              allowSortingBySummary
              allowSorting
              allowFiltering
              showBorders
              elementAttr={{ 'data-testid': 'partesConsultaDetalladaPivotGrid' }}
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
  const [mode, setMode] = useState<'grid' | 'pivot'>('grid')
  const [pivotRemountKey, setPivotRemountKey] = useState(0)
  const pivotRef = useRef<PivotGridRef>(null)

  const rows = useMemo(
    () => enrichRowsWithDiaSemana(rawRows, t, 'ejeCodigo'),
    [rawRows, t, i18n.language]
  )

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const query: Record<string, string> = { fechaDesde, fechaHasta, eje }
      if (eje === 'fecha') {
        query.granularidadFecha = granularidadFecha
      }
      const result = await fetchInformeAgrupado(query)
      if (result.kind === 'ok') {
        setRawRows(result.envelope.resultado.items ?? [])
        if ((result.envelope.resultado.total ?? 0) === 0) {
          setError(resolveAuthMessage('partes.consulta.empty'))
        }
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [fechaDesde, fechaHasta, eje, granularidadFecha])

  useEffect(() => {
    void load()
    // eslint-disable-next-line react-hooks/exhaustive-deps -- solo mount
  }, [])

  const pivotSource = useMemo(
    () =>
      new PivotGridDataSource({
        retrieveFields: false,
        fields: buildConsultaAgrupadaPivotFields(t),
        store: rows,
      }),
    [rows, t, i18n.language, pivotRemountKey]
  )

  const pivotUiTexts = useMemo(
    () => getPivotLocalizedUiTexts(i18n.language),
    [i18n.language]
  )

  const pivotInstanceKey = `${pivotRemountKey}-${i18n.language}`

  const durationSummaryFormatter = useCallback(
    (value: unknown) => formatMinutosAsHhMm(Number(value ?? 0)),
    []
  )

  const duracionSummaryItems = useMemo(
    () => [
      {
        column: 'totalMinutos',
        summaryType: 'sum' as const,
        name: 'pq-totalMinutos-sum',
        displayFormat: 'Suma: {0}',
        customizeText: (info: { value?: string | number | Date }) =>
          `Suma: ${formatMinutosAsHhMm(Number(info.value ?? 0))}`,
      },
    ],
    []
  )

  return (
    <div data-testid="partesConsultaAgrupadaPage" style={{ padding: 16 }}>
      <h2>Consultas agrupadas</h2>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, flexWrap: 'wrap', alignItems: 'end' }}>
        <DateBox
          value={fechaDesde}
          type="date"
          displayFormat="dd/MM/yyyy"
          dateSerializationFormat="yyyy-MM-dd"
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
          displayFormat="dd/MM/yyyy"
          dateSerializationFormat="yyyy-MM-dd"
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
              { id: 'dia', text: 'Día' },
              { id: 'mes', text: 'Mes' },
            ]}
            value={granularidadFecha}
            valueExpr="id"
            displayExpr="text"
            onValueChanged={(e) => setGranularidadFecha(String(e.value))}
          />
        ) : null}
        <Button text="Buscar" onClick={() => void load()} disabled={loading} />
      </div>
      {error ? <div role="alert">{error}</div> : null}
      {mode === 'grid' || native ? (
        <div data-testid="partesConsultaAgrupadaGrid">
          <ProcessDataGrid
            dataSource={rows}
            keyExpr="ejeKey"
            showBorders
            loading={loading}
            proceso="partes.informes.consultasAgrupadas"
            gridId="consultasAgrupadas"
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            defaultTotalItems={duracionSummaryItems}
            columnSummaryFormatters={{
              totalMinutos: durationSummaryFormatter,
            }}
            toolbarLeading={
              !native ? (
                <Button
                  text="Pivot"
                  onClick={() => setMode('pivot')}
                  elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
                />
              ) : undefined
            }
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
      ) : (
        <div data-testid="partesConsultaAgrupadaPivot">
          <PivotLayoutsBar
            consultaId={CONSULTA_AGRUPADA_ID}
            accessToken={getAuthToken()}
            platform={buildAuthPlatformHeaders()}
            leadingSlot={
              <Button
                text="Grilla"
                onClick={() => setMode('grid')}
                elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
              />
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
            canExport={rows.length > 0 && !loading}
          >
            <PivotGrid
              key={pivotInstanceKey}
              ref={pivotRef}
              dataSource={pivotSource}
              showBorders
              allowSorting
              allowFiltering
              elementAttr={{ 'data-testid': 'partesConsultaAgrupadaPivotGrid' }}
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
