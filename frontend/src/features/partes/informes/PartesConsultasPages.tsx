import { useCallback, useEffect, useMemo, useState } from 'react'
import DataGrid, { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import PivotGrid, { FieldChooser } from 'devextreme-react/pivot-grid'
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source'
import { isNativeApp } from '@paqsuite/react-core'
import { resolveAuthMessage } from '../../auth/authMessages'
import { monthRange, currentMonthValue } from './PartesDashboardPage'
import { fetchInformeAgrupado, fetchInformeTareas } from './partesInformeApi'

export function ConsultaDetalladaPage() {
  const native = isNativeApp()
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [error, setError] = useState<string | null>(null)
  const [mode, setMode] = useState<'grid' | 'pivot'>('grid')

  const load = useCallback(async () => {
    setError(null)
    const result = await fetchInformeTareas({ fechaDesde, fechaHasta })
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
      if ((result.envelope.resultado.total ?? 0) === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      }
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [fechaDesde, fechaHasta])

  useEffect(() => {
    void load()
  }, [load])

  const pivotSource = useMemo(
    () =>
      new PivotGridDataSource({
        fields: [
          { dataField: 'clienteCode', area: 'row' },
          { dataField: 'tipoTareaCode', area: 'column' },
          { dataField: 'duracionMinutos', area: 'data', summaryType: 'sum' },
        ],
        store: rows,
      }),
    [rows]
  )

  return (
    <div data-testid="partesConsultaDetalladaPage" style={{ padding: 16 }}>
      <h2>Consulta detallada</h2>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, alignItems: 'end' }}>
        <DateBox
          value={fechaDesde}
          type="date"
          onValueChanged={(e) =>
            setFechaDesde(e.value ? (e.value as Date).toISOString().slice(0, 10) : '')
          }
        />
        <DateBox
          value={fechaHasta}
          type="date"
          onValueChanged={(e) =>
            setFechaHasta(e.value ? (e.value as Date).toISOString().slice(0, 10) : '')
          }
        />
        <Button text="Buscar" onClick={() => void load()} />
        {!native ? (
          <Button
            text={mode === 'grid' ? 'Ver Pivot' : 'Ver Grilla'}
            onClick={() => setMode((prev) => (prev === 'grid' ? 'pivot' : 'grid'))}
            elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
          />
        ) : null}
      </div>
      {error ? <div role="alert">{error}</div> : null}
      {mode === 'grid' || native ? (
        <div data-testid="partesConsultaDetalladaGrid">
          <DataGrid dataSource={rows} keyExpr="id" showBorders>
            <Paging defaultPageSize={20} />
            <Pager visible />
            <Column dataField="fecha" caption="Fecha" />
            <Column dataField="usuarioCode" caption="Asistente" />
            <Column dataField="usuarioNombre" caption="Nombre asistente" />
            <Column dataField="clienteCode" caption="Cliente" />
            <Column dataField="tipoTareaCode" caption="Tipo" />
            <Column dataField="duracionMinutos" caption="Minutos" />
            <Column dataField="observacion" caption="Observación" />
            <Column dataField="cerrado" caption="Cerrado" />
          </DataGrid>
        </div>
      ) : (
        <PivotGrid dataSource={pivotSource} allowSortingBySummary showBorders>
          <FieldChooser enabled />
        </PivotGrid>
      )}
    </div>
  )
}

export function ConsultasAgrupadasPage() {
  const native = isNativeApp()
  const defaultRange = monthRange(currentMonthValue())
  const [fechaDesde, setFechaDesde] = useState(defaultRange.fechaDesde)
  const [fechaHasta, setFechaHasta] = useState(defaultRange.fechaHasta)
  const [eje, setEje] = useState('cliente')
  const [granularidadFecha, setGranularidadFecha] = useState('mes')
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [error, setError] = useState<string | null>(null)
  const [mode, setMode] = useState<'grid' | 'pivot'>('grid')

  const load = useCallback(async () => {
    setError(null)
    const query: Record<string, string> = { fechaDesde, fechaHasta, eje }
    if (eje === 'fecha') {
      query.granularidadFecha = granularidadFecha
    }
    const result = await fetchInformeAgrupado(query)
    if (result.kind === 'ok') {
      setRows(result.envelope.resultado.items ?? [])
      if ((result.envelope.resultado.total ?? 0) === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      }
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }, [fechaDesde, fechaHasta, eje, granularidadFecha])

  useEffect(() => {
    void load()
  }, [load])

  const pivotSource = useMemo(
    () =>
      new PivotGridDataSource({
        fields: [
          { dataField: 'ejeCodigo', area: 'row' },
          { dataField: 'totalMinutos', area: 'data', summaryType: 'sum' },
          { dataField: 'cantidadTareas', area: 'data', summaryType: 'sum' },
        ],
        store: rows,
      }),
    [rows]
  )

  return (
    <div data-testid="partesConsultaAgrupadaPage" style={{ padding: 16 }}>
      <h2>Consultas agrupadas</h2>
      <div style={{ display: 'flex', gap: 12, marginBottom: 12, flexWrap: 'wrap', alignItems: 'end' }}>
        <DateBox
          value={fechaDesde}
          type="date"
          onValueChanged={(e) =>
            setFechaDesde(e.value ? (e.value as Date).toISOString().slice(0, 10) : '')
          }
        />
        <DateBox
          value={fechaHasta}
          type="date"
          onValueChanged={(e) =>
            setFechaHasta(e.value ? (e.value as Date).toISOString().slice(0, 10) : '')
          }
        />
        <SelectBox
          dataSource={[
            { id: 'cliente', text: 'Cliente' },
            { id: 'asistente', text: 'Asistente' },
            { id: 'tipo', text: 'Tipo tarea' },
            { id: 'fecha', text: 'Fecha' },
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
        <Button text="Buscar" onClick={() => void load()} />
        {!native ? (
          <Button
            text={mode === 'grid' ? 'Ver Pivot' : 'Ver Grilla'}
            onClick={() => setMode((prev) => (prev === 'grid' ? 'pivot' : 'grid'))}
            elementAttr={{ 'data-testid': 'partesInformePivotToggle' }}
          />
        ) : null}
      </div>
      {error ? <div role="alert">{error}</div> : null}
      {mode === 'grid' || native ? (
        <DataGrid dataSource={rows} keyExpr="ejeKey" showBorders>
          <Column dataField="ejeCodigo" caption="Código" />
          <Column dataField="ejeDescripcion" caption="Descripción" />
          <Column dataField="totalMinutos" caption="Minutos" />
          <Column dataField="cantidadTareas" caption="Tareas" />
        </DataGrid>
      ) : (
        <PivotGrid dataSource={pivotSource} showBorders>
          <FieldChooser enabled />
        </PivotGrid>
      )}
    </div>
  )
}
