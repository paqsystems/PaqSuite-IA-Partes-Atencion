import { useCallback, useEffect, useMemo, useState } from 'react'
import Button from 'devextreme-react/button'
import DateBox from 'devextreme-react/date-box'
import SelectBox from 'devextreme-react/select-box'
import { Popup } from 'devextreme-react/popup'
import { ConsultaKardexList, ProcessPageLayout } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { getAuthSession } from '../../auth/authSessionStore'
import { resolveAuthMessage } from '../../auth/authMessages'
import { listCatalogo } from '../maestros/partesMaestrosApi'
import {
  enrichRowsWithDuracionHoras,
  resolvePartesDateDisplayFormat,
  dateSerializationFormat,
  todayIsoDate,
} from '../carga/partesTareaDuration'
import { enrichRowsWithDiaSemana } from '../informes/partesInformeDiaSemana'
import { fetchInformeTareas } from '../informes/partesInformeApi'
import type { ConsultaDetalladaEstadoCerrado } from '../informes/consultaDetalladaHostContext'
import { mapConsultaDetalladaToKardexItem } from './mapConsultaDetalladaToKardexItem'

const estadoOptions = (t: (key: string) => string) => [
  { value: 'todas' as const, label: t('partes.informe.estado.todas') },
  { value: 'abiertas' as const, label: t('partes.informe.estado.abiertas') },
  { value: 'cerradas' as const, label: t('partes.informe.estado.cerradas') },
]

export function ConsultaDetalladaMobileView() {
  const { t, i18n } = useTranslation()
  const dateFormat = resolvePartesDateDisplayFormat(i18n.language)
  const pageTitle = useProcessMenuTitle(
    t('partes.informe.page.consultaDetallada'),
    '/partes/informes/consulta-detallada',
  )
  const session = getAuthSession()
  const esSupervisor = Boolean(session?.partes?.esSupervisor)
  const hoy = todayIsoDate()
  const [fechaDesde, setFechaDesde] = useState(hoy)
  const [fechaHasta, setFechaHasta] = useState(hoy)
  const [filtroClienteId, setFiltroClienteId] = useState<number | null>(null)
  const [filtroUsuarioId, setFiltroUsuarioId] = useState<number | null>(null)
  const [filtroTipoTareaId, setFiltroTipoTareaId] = useState<number | null>(null)
  const [estadoCerrado, setEstadoCerrado] = useState<ConsultaDetalladaEstadoCerrado>('todas')
  const [rawRows, setRawRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [detalleRow, setDetalleRow] = useState<Record<string, unknown> | null>(null)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [asistentes, setAsistentes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])

  const rows = useMemo(
    () => enrichRowsWithDuracionHoras(enrichRowsWithDiaSemana(rawRows, t, 'fecha')),
    [rawRows, t, i18n.language],
  )

  const kardexItems = useMemo(
    () => rows.map((row) => mapConsultaDetalladaToKardexItem(row, (key) => t(key))),
    [rows, t],
  )

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    const query: Record<string, string> = {
      fechaDesde,
      fechaHasta,
      estadoCerrado,
      pageSize: '0',
    }
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
    setLoading(false)
    if (result.kind === 'ok') {
      const items = result.envelope.resultado.items ?? []
      setRawRows(items)
      if ((result.envelope.resultado.total ?? items.length) === 0) {
        setError(resolveAuthMessage('partes.consulta.empty'))
      }
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
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
    void listCatalogo('clientes').then((result) => {
      if (result.kind === 'ok') {
        setClientes(result.envelope.resultado.items ?? [])
      }
    })
    if (esSupervisor) {
      void listCatalogo('asistentes').then((result) => {
        if (result.kind === 'ok') {
          setAsistentes(result.envelope.resultado.items ?? [])
        }
      })
    }
    void listCatalogo('tipos-tarea').then((result) => {
      if (result.kind === 'ok') {
        setTipos(result.envelope.resultado.items ?? [])
      }
    })
  }, [esSupervisor])

  useEffect(() => {
    void load()
  }, [load])

  const formRowStyle = { display: 'grid', gridTemplateColumns: '120px 1fr', gap: 8, alignItems: 'center' }

  return (
    <ProcessPageLayout title={pageTitle}>
      <div className="partesMobileProcess" data-testid="partesConsultaDetalladaKardex">
        {error ? <div role="alert">{error}</div> : null}
        <ConsultaKardexList
          items={kardexItems}
          filtersSlot={
            <div className="partesMobileKardexFilters" style={{ display: 'grid', gap: 8 }}>
              <div style={formRowStyle}>
                <label>{t('partes.informe.field.fecha')}</label>
                <DateBox
                  value={fechaDesde}
                  type="date"
                  displayFormat={dateFormat}
                  dateSerializationFormat={dateSerializationFormat}
                  onValueChanged={(e) =>
                    setFechaDesde(e.value ? todayIsoDate(new Date(e.value as Date)) : hoy)
                  }
                />
              </div>
              <div style={formRowStyle}>
                <label>{t('partes.informe.field.fecha')}</label>
                <DateBox
                  value={fechaHasta}
                  type="date"
                  displayFormat={dateFormat}
                  dateSerializationFormat={dateSerializationFormat}
                  onValueChanged={(e) =>
                    setFechaHasta(e.value ? todayIsoDate(new Date(e.value as Date)) : hoy)
                  }
                />
              </div>
              <div style={formRowStyle}>
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
                <div style={formRowStyle}>
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
              <div style={formRowStyle}>
                <label>{t('partes.informe.filtro.tipoTarea')}</label>
                <SelectBox
                  dataSource={tipos}
                  value={filtroTipoTareaId}
                  valueExpr="id"
                  displayExpr={(item) => (item ? `${item.code} — ${item.descripcion}` : '')}
                  showClearButton
                  searchEnabled
                  onValueChanged={(e) => setFiltroTipoTareaId((e.value as number | null) ?? null)}
                />
              </div>
              <div style={formRowStyle}>
                <label>{t('partes.informe.filtro.estadoCerrado')}</label>
                <SelectBox
                  dataSource={estadoOptions(t)}
                  value={estadoCerrado}
                  valueExpr="value"
                  displayExpr="label"
                  onValueChanged={(e) =>
                    setEstadoCerrado((e.value as ConsultaDetalladaEstadoCerrado) ?? 'todas')
                  }
                />
              </div>
              <Button
                text={t('dashboard.refresh')}
                type="default"
                disabled={loading}
                onClick={() => void load()}
                elementAttr={{ 'data-testid': 'partesConsultaDetalladaRefresh' }}
              />
            </div>
          }
          onItemTap={(item) => {
            const row = rows.find((entry) => String(entry.id) === item.id)
            if (row) {
              setDetalleRow(row)
            }
          }}
          onRefresh={load}
          t={(key) => t(key)}
        />
        <Popup
          visible={detalleRow !== null}
          onHiding={() => setDetalleRow(null)}
          title={t('partes.informe.page.consultaDetallada')}
          width="95%"
          height="auto"
        >
          {detalleRow ? (
            <motionlessDetalleContent row={detalleRow} t={t} dateFormat={dateFormat} />
          ) : null}
        </Popup>
      </div>
    </ProcessPageLayout>
  )
}

function motionlessDetalleContent({
  row,
  t,
  dateFormat: _dateFormat,
}: {
  row: Record<string, unknown>
  t: (key: string) => string
  dateFormat: string
}) {
  const item = mapConsultaDetalladaToKardexItem(row, t)
  return (
    <div style={{ display: 'grid', gap: 8, padding: 8 }} data-testid="partesConsultaDetalladaDetalle">
      <strong>{item.title}</strong>
      {item.fields.map((field) => (
        <div key={field.label}>
          <small>{field.label}</small>
          <div>{field.value}</div>
        </div>
      ))}
    </div>
  )
}