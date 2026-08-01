import { useCallback, useEffect, useMemo, useState } from 'react'
import Chart, {
  ArgumentAxis,
  CommonSeriesSettings,
  Legend,
  Series,
  Tooltip,
  ValueAxis,
} from 'devextreme-react/chart'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { formatMinutosAsHhMm } from '../carga/partesTareaDuration'
import { currentMonthValue, monthRange } from './PartesDashboardPage'
import { fetchPaqueteHoras } from './partesInformeApi'
import { LoadingOverlay, ProcessDataGrid } from '@paqsuite/react-core'

function formatDuracionCell(cell: { value?: unknown }) {
  return formatMinutosAsHhMm(Number(cell.value ?? 0))
}

export function PaqueteHorasPage() {
  const [mes, setMes] = useState(currentMonthValue())
  const range = monthRange(mes)
  const [totalMinutos, setTotalMinutos] = useState(0)
  const [cantidadTareas, setCantidadTareas] = useState(0)
  const [porCliente, setPorCliente] = useState<Record<string, unknown>[]>([])
  const [porTipo, setPorTipo] = useState<Record<string, unknown>[]>([])
  const [serie, setSerie] = useState<'cliente' | 'tipo'>('cliente')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await fetchPaqueteHoras(range)
      if (result.kind === 'ok') {
        setTotalMinutos(result.envelope.resultado.totalMinutos)
        setCantidadTareas(result.envelope.resultado.cantidadTareas)
        setPorCliente(result.envelope.resultado.porCliente ?? [])
        setPorTipo(result.envelope.resultado.porTipo ?? [])
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [range.fechaDesde, range.fechaHasta])

  useEffect(() => {
    void load()
  }, [load])

  const chartData = useMemo(() => {
    const source = serie === 'cliente' ? porCliente : porTipo
    return source.map((row) => ({
      arg: String(row.ejeCodigo ?? row.ejeDescripcion ?? ''),
      val: Number(row.totalMinutos ?? 0),
    }))
  }, [serie, porCliente, porTipo])

  return (
    <div data-testid="partesPaqueteHorasPage" style={{ padding: 16 }}>
      <LoadingOverlay visible={loading} />
      <div style={{ display: 'flex', gap: 12, alignItems: 'center', marginBottom: 12 }}>
        <h2 style={{ margin: 0, flex: 1 }}>Paquete de horas</h2>
        <DateBox
          value={mes}
          type="date"
          displayFormat="yyyy-MM"
          calendarOptions={{ zoomLevel: 'year', maxZoomLevel: 'year', minZoomLevel: 'century' }}
          onValueChanged={(e) => {
            if (e.value) {
              setMes(currentMonthValue(new Date(e.value as Date)))
            }
          }}
        />
        <Button text="Actualizar" onClick={() => void load()} />
        <Button
          text={serie === 'cliente' ? 'Serie: Cliente' : 'Serie: Tipo'}
          onClick={() => setSerie((prev) => (prev === 'cliente' ? 'tipo' : 'cliente'))}
          elementAttr={{ 'data-testid': 'partesPaqueteSerieToggle' }}
        />
      </div>
      {error ? <div role="alert">{error}</div> : null}
      <div style={{ display: 'flex', gap: 24, marginBottom: 16 }}>
        <div>
          <strong>Total duración:</strong> {formatMinutosAsHhMm(totalMinutos)}
        </div>
        <div>
          <strong>Cantidad tareas:</strong> {cantidadTareas}
        </div>
      </div>
      <Chart dataSource={chartData} data-testid="partesPaqueteChart">
        <CommonSeriesSettings argumentField="arg" valueField="val" type="bar" />
        <Series name="Duración" />
        <ArgumentAxis />
        <ValueAxis
          label={{
            customizeText: (arg) => formatMinutosAsHhMm(Number(arg.value ?? 0)),
          }}
        />
        <Legend visible={false} />
        <Tooltip
          enabled
          customizeTooltip={(arg) => ({
            text: `${arg.argumentText}: ${formatMinutosAsHhMm(Number(arg.value ?? 0))}`,
          })}
        />
      </Chart>
      <h3>Por cliente</h3>
      <ProcessDataGrid
        dataSource={porCliente}
        keyExpr="ejeKey"
        showBorders
        proceso="partes.informes.paqueteHoras"
        gridId="paqueteHorasCliente"
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
      >
        <Paging defaultPageSize={20} />
        <Pager visible showPageSizeSelector />
        <Column dataField="ejeCodigo" caption="Código" />
        <Column dataField="ejeDescripcion" caption="Cliente" />
        <Column
          dataField="totalMinutos"
          caption="Duración"
          dataType="number"
          customizeText={formatDuracionCell}
        />
        <Column dataField="cantidadTareas" caption="Tareas" dataType="number" />
      </ProcessDataGrid>
      <h3>Por tipo</h3>
      <ProcessDataGrid
        dataSource={porTipo}
        keyExpr="ejeKey"
        showBorders
        proceso="partes.informes.paqueteHoras"
        gridId="paqueteHorasTipo"
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
      >
        <Paging defaultPageSize={20} />
        <Pager visible showPageSizeSelector />
        <Column dataField="ejeCodigo" caption="Código" />
        <Column dataField="ejeDescripcion" caption="Tipo" />
        <Column
          dataField="totalMinutos"
          caption="Duración"
          dataType="number"
          customizeText={formatDuracionCell}
        />
        <Column dataField="cantidadTareas" caption="Tareas" dataType="number" />
      </ProcessDataGrid>
    </div>
  )
}
