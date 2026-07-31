import { useCallback, useEffect, useRef, useState } from 'react'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import DataGrid, { Column } from 'devextreme-react/data-grid'
import { useTranslation } from 'react-i18next'
import { Link } from 'react-router-dom'
import { isNativeApp } from '@paqsuite/react-core'
import { resolveAuthMessage } from '../../auth/authMessages'
import { todayIsoDate } from '../carga/partesTareaDuration'
import { fetchDashboard, fetchDashboardParametros } from './partesInformeApi'

function currentMonthValue(date = new Date()): string {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

function monthRange(mes: string): { fechaDesde: string; fechaHasta: string } {
  const [y, m] = mes.split('-').map(Number)
  const desde = `${mes}-01`
  const last = new Date(y, m, 0).getDate()
  return { fechaDesde: desde, fechaHasta: `${mes}-${String(last).padStart(2, '0')}` }
}

export function PartesDashboardPage() {
  const { t } = useTranslation()
  const native = isNativeApp()
  const [mes, setMes] = useState(currentMonthValue())
  const [refreshSeg, setRefreshSeg] = useState(60)
  const [totalMinutos, setTotalMinutos] = useState(0)
  const [cantidadTareas, setCantidadTareas] = useState(0)
  const [top, setTop] = useState<
    Array<{ codigo: string; descripcion: string; totalMinutos: number; cantidadTareas: number }>
  >([])
  const [error, setError] = useState<string | null>(null)
  const seqRef = useRef(0)

  const load = useCallback(async () => {
    const seq = ++seqRef.current
    setError(null)
    const result = await fetchDashboard({ mes })
    if (seq !== seqRef.current) {
      return
    }
    if (result.kind === 'ok') {
      setTotalMinutos(result.envelope.resultado.totalMinutos)
      setCantidadTareas(result.envelope.resultado.cantidadTareas)
      setTop(result.envelope.resultado.top ?? [])
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    } else {
      setError(resolveAuthMessage('infra.transport'))
    }
  }, [mes])

  useEffect(() => {
    void fetchDashboardParametros().then((result) => {
      if (result.kind === 'ok') {
        setRefreshSeg(result.envelope.resultado.refreshSeg)
      }
    })
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  useEffect(() => {
    if (native || refreshSeg <= 0) {
      return
    }
    const id = window.setInterval(() => {
      void load()
    }, refreshSeg * 1000)
    return () => window.clearInterval(id)
  }, [load, refreshSeg, native])

  const range = monthRange(mes)

  return (
    <div className="pqProcessPage" data-testid="partesDashboardRoot">
      <div className="pqProcessHeader">
        <h2 className="pqProcessTitle">{t('dashboard.title')}</h2>
        <div className="pqProcessToolbar">
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
          <Button
            text={t('dashboard.refresh')}
            onClick={() => void load()}
            elementAttr={{ 'data-testid': 'partesDashboardRefresh' }}
          />
          <Link to="/partes/informes/paquete-horas" data-testid="partesDashboardLinkPaquete">
            {t('dashboard.linkPaqueteHoras')}
          </Link>
        </div>
      </div>

      {error ? (
        <div role="alert" className="error">
          {error}
        </div>
      ) : null}

      <div className="pqProcessStats">
        <div data-testid="partesDashboardTotalMinutos">
          <strong>{t('dashboard.totalMinutos')}:</strong> {totalMinutos}
        </div>
        <div data-testid="partesDashboardCantidad">
          <strong>{t('dashboard.cantidadTareas')}:</strong> {cantidadTareas}
        </div>
        <div className="pqProcessMuted">
          {t('dashboard.periodo')}: {range.fechaDesde} → {range.fechaHasta}
          {!native && refreshSeg > 0
            ? ` · ${t('dashboard.autoRefresh', { seconds: refreshSeg })}`
            : ''}
        </div>
      </div>

      <h3 className="pqProcessTitle" style={{ fontSize: '1.15rem' }}>
        {t('dashboard.topClientes')}
      </h3>
      <DataGrid dataSource={top} keyExpr="codigo" showBorders>
        <Column dataField="codigo" caption={t('dashboard.colCodigo')} />
        <Column dataField="descripcion" caption={t('dashboard.colCliente')} />
        <Column dataField="totalMinutos" caption={t('dashboard.colMinutos')} />
        <Column dataField="cantidadTareas" caption={t('dashboard.colTareas')} />
      </DataGrid>
    </div>
  )
}

export { currentMonthValue, monthRange, todayIsoDate }
