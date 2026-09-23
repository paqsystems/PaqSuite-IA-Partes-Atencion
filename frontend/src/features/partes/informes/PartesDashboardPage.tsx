import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import { useTranslation } from 'react-i18next'
import { Link } from 'react-router-dom'
import {
  ConsultaKardexList,
  DashboardContainer,
  isNativeApp,
  LoadingOverlay,
  ProcessDataGrid,
} from '@paqsuite/react-core'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { resolveAuthMessage } from '../../auth/authMessages'
import { formatMinutosAsHhMm, todayIsoDate } from '../carga/partesTareaDuration'
import { fetchDashboard, fetchDashboardParametros } from './partesInformeApi'
import { mapDashboardTopToKardexItem } from '../mobile/mapPartesTareaToKardexItem'

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
  const [loading, setLoading] = useState(true)
  const seqRef = useRef(0)

  const load = useCallback(async (showOverlay = true) => {
    const seq = ++seqRef.current
    if (showOverlay) {
      setLoading(true)
    }
    setError(null)
    try {
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
    } finally {
      if (seq === seqRef.current && showOverlay) {
        setLoading(false)
      }
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
      void load(false)
    }, refreshSeg * 1000)
    return () => window.clearInterval(id)
  }, [load, refreshSeg, native])

  const range = monthRange(mes)
  const topKardex = useMemo(
    () =>
      top.map((row) =>
        mapDashboardTopToKardexItem(row, (key) => t(key), formatMinutosAsHhMm),
      ),
    [top, t],
  )

  const mesPicker = (
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
  )

  if (native) {
    return (
      <div className="pqProcessPage partesMobileProcess" data-testid="partesDashboardRoot">
        <div className="pqProcessHeader partesMobileProcessHeader">
          <h2 className="pqProcessTitle">{t('dashboard.title')}</h2>
          <div className="pqProcessToolbar partesMobileProcessToolbar">
            {mesPicker}
            <Button
              text={t('dashboard.refresh')}
              onClick={() => void load()}
              elementAttr={{ 'data-testid': 'partesDashboardRefresh' }}
            />
            <Link
              className="partesMobileTextLink"
              to="/partes/informes/paquete-horas"
              data-testid="partesDashboardLinkPaquete"
            >
              {t('dashboard.linkPaqueteHoras')}
            </Link>
          </div>
        </div>
        <DashboardContainer
          loading={loading}
          error={error}
          emptyTitle={t('dashboard.title')}
          onRefresh={() => void load()}
          widgets={[
            {
              id: 'partes-native-dashboard',
              title: t('dashboard.periodo'),
              render: () => (
                <>
                  <div className="pqProcessStats partesMobileKpiBlock">
                    <div data-testid="partesDashboardTotalMinutos">
                      <strong>{t('dashboard.totalMinutos')}:</strong>{' '}
                      {formatMinutosAsHhMm(totalMinutos)}
                    </div>
                    <div data-testid="partesDashboardCantidad">
                      <strong>{t('dashboard.cantidadTareas')}:</strong> {cantidadTareas}
                    </div>
                    <div className="pqProcessMuted">
                      {range.fechaDesde} → {range.fechaHasta}
                    </div>
                  </div>
                  <section className="partesMobileSection">
                    <h3 className="partesMobileSectionTitle">{t('dashboard.topClientes')}</h3>
                    <ConsultaKardexList
                      items={topKardex}
                      onItemTap={() => undefined}
                      t={(key) => t(key)}
                    />
                  </section>
                </>
              ),
            },
          ]}
        />
      </div>
    )
  }

  return (
    <div className="pqProcessPage" data-testid="partesDashboardRoot">
      <LoadingOverlay visible={loading} />
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
          <strong>{t('dashboard.totalMinutos')}:</strong> {formatMinutosAsHhMm(totalMinutos)}
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
      <ProcessDataGrid
        dataSource={top}
        keyExpr="codigo"
        showBorders
        proceso="partes.dashboard"
        gridId="dashboardTop"
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
      >
        <Paging defaultPageSize={20} />
        <Pager visible showPageSizeSelector />
        <Column dataField="codigo" caption={t('dashboard.colCodigo')} />
        <Column dataField="descripcion" caption={t('dashboard.colCliente')} />
        <Column
          dataField="totalMinutos"
          caption={t('dashboard.colMinutos')}
          dataType="number"
          customizeText={(cell) => formatMinutosAsHhMm(Number(cell.value ?? 0))}
        />
        <Column dataField="cantidadTareas" caption={t('dashboard.colTareas')} dataType="number" />
      </ProcessDataGrid>
    </div>
  )
}

export { currentMonthValue, monthRange, todayIsoDate }
