import { useCallback, useEffect, useState } from 'react'
import { ProcessDataGrid } from '@paqsuite/react-core'
import { Column, Paging, Pager } from 'devextreme-react/data-grid'
import Button from 'devextreme-react/button'
import SelectBox from 'devextreme-react/select-box'
import { Popup } from 'devextreme-react/popup'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import {
  deletePartesResource,
  listCatalogo,
  listPartesResource,
  savePartesResource,
} from './partesMaestrosApi'
import { usePartesGridSummaryTypeLabels } from '../partesGridSummary'

export function ClienteTiposTareaPage() {
  const { t } = useTranslation()
  const summaryTypeLabels = usePartesGridSummaryTypeLabels()
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [clientes, setClientes] = useState<Record<string, unknown>[]>([])
  const [tipos, setTipos] = useState<Record<string, unknown>[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [clienteId, setClienteId] = useState<number | null>(null)
  const [tipoTareaId, setTipoTareaId] = useState<number | null>(null)
  const [error, setError] = useState<string | null>(null)

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const result = await listPartesResource('cliente-tipos-tarea')
      if (result.kind === 'ok') {
        setRows(result.envelope.resultado.items ?? [])
      } else if (result.kind === 'envelopeError') {
        setError(resolveAuthMessage(result.envelope.respuesta))
      }
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    void load()
    void listCatalogo('clientes').then((result) => {
      if (result.kind === 'ok') {
        setClientes(result.envelope.resultado.items ?? [])
      }
    })
    void listPartesResource('tipos-tarea').then((result) => {
      if (result.kind === 'ok') {
        const items = (result.envelope.resultado.items ?? []).filter((row) => !row.isGenerico)
        setTipos(items)
      }
    })
  }, [load])

  async function handleSave() {
    const result = await savePartesResource('cliente-tipos-tarea', {
      clienteId,
      tipoTareaId,
    })
    if (result.kind === 'ok') {
      setFormOpen(false)
      setClienteId(null)
      setTipoTareaId(null)
      void load()
      return
    }
    if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  async function handleDelete(row: Record<string, unknown>) {
    const ok = await confirm(
      t('partes.maestros.asignaciones.eliminarConfirm'),
      t('admin.common.delete'),
    )
    if (!ok) {
      return
    }
    const result = await deletePartesResource('cliente-tipos-tarea', Number(row.id))
    if (result.kind === 'ok') {
      void load()
    } else if (result.kind === 'envelopeError') {
      setError(resolveAuthMessage(result.envelope.respuesta))
    }
  }

  return (
    <div data-testid="partesMaestrosAsignacionesPage" style={{ padding: 16 }}>
      <h2 style={{ margin: '0 0 12px' }}>{t('partes.maestros.asignaciones.title')}</h2>
      {error ? <div role="alert">{error}</div> : null}
      <div data-testid="partesMaestrosAsignacionesGrid">
        <ProcessDataGrid
          dataSource={rows}
          keyExpr="id"
          loading={loading}
          proceso="partes.maestros.clienteTiposTarea"
          gridId="asignaciones"
          accessToken={getAuthToken()}
          platform={buildAuthPlatformHeaders()}
          onCreate={() => setFormOpen(true)}
          createHint={t('partes.common.agregar')}
          createTestId="partesMaestrosAsignacionesAdd"
          summaryTypeLabels={summaryTypeLabels}
        >
          <Paging defaultPageSize={20} />
          <Pager visible showPageSizeSelector />
          <Column dataField="clienteCode" caption={t('partes.informe.filtro.cliente')} />
          <Column dataField="clienteNombre" caption={t('partes.maestros.field.nombreCliente')} />
          <Column dataField="tipoTareaCode" caption={t('partes.maestros.field.tipoTareaCorto')} />
          <Column
            dataField="tipoTareaDescripcion"
            caption={t('partes.maestros.field.descripcionTipo')}
          />
          <Column
            type="buttons"
            buttons={[
              {
                hint: t('admin.common.delete'),
                icon: 'trash',
                onClick: (e) => void handleDelete(e.row?.data as Record<string, unknown>),
              },
            ]}
          />
        </ProcessDataGrid>
      </div>
      <Popup
        visible={formOpen}
        onHiding={() => setFormOpen(false)}
        title={t('partes.maestros.asignaciones.nueva')}
        width={480}
        height="auto"
        showCloseButton
      >
        <div
          style={{ display: 'grid', gap: 12, padding: 8 }}
          data-testid="partesMaestrosAsignacionesForm"
        >
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8, alignItems: 'center' }}>
            <label>{t('partes.informe.filtro.cliente')}</label>
            <SelectBox
              dataSource={clientes}
              value={clienteId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.nombre}` : '')}
              searchEnabled
              onValueChanged={(e) => setClienteId(e.value as number | null)}
            />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '140px 1fr', gap: 8, alignItems: 'center' }}>
            <label>{t('partes.informe.filtro.tipoTarea')}</label>
            <SelectBox
              dataSource={tipos}
              value={tipoTareaId}
              valueExpr="id"
              displayExpr={(item) => (item ? `${item.code} — ${item.descripcion}` : '')}
              searchEnabled
              onValueChanged={(e) => setTipoTareaId(e.value as number | null)}
            />
          </div>
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button text={t('parametros.modal.cancel')} onClick={() => setFormOpen(false)} />
            <Button
              text={t('admin.common.save')}
              type="default"
              onClick={() => void handleSave()}
              elementAttr={{ 'data-testid': 'partesMaestrosAsignacionesFormSave' }}
            />
          </div>
        </div>
      </Popup>
    </div>
  )
}
