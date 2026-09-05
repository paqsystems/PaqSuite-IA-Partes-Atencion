import { useCallback, useEffect, useMemo, useState } from 'react'
import type { ReactNode } from 'react'
import Button from 'devextreme-react/button'
import LoadPanel from 'devextreme-react/load-panel'
import SelectBox from 'devextreme-react/select-box'
import { apiRequest, ReportDesignerHost } from '@paqsuite/react-core'
import { readDxReportingConfig } from './dxReportingConfig'
import type { PartesReportDesignerContext } from './DxReportDesignerPanel'
import {
  catalogCodeFromDxUrl,
  catalogEmissionReportCode,
  displayNameFromReportUrl,
  isUnknownEmissionReportCode,
} from './emissionReportCatalog'

const LAYOUT_MIME_REPX = 'application/xml'

export type EmissionProcessListItem = {
  processCode: string
  label: string
}

export type EmissionReportListItem = {
  id: number
  code: string
  name: string
  isPrincipal: boolean
}

export type EmissionReportDesignerPageProps = {
  initialProcessCode?: string
  hasDesignPermission?: boolean
  isNative?: boolean
  t?: (key: string) => string
  renderDesigner?: (context: PartesReportDesignerContext) => ReactNode
}

type ProcessesEnvelope = {
  items?: Array<{
    processCode?: string
    name?: string
    menuProcessCode?: string
    reports?: Array<{ name?: string }>
  }>
}

type DesignReportsEnvelope = {
  designer?: string
  items?: Array<{
    id?: number
    code?: string
    name?: string
    isPrincipal?: boolean
  }>
}

type DesignCreateEnvelope = {
  item?: {
    id?: number
    code?: string
    name?: string
    isPrincipal?: boolean
  }
}

function mapProcessItems(raw: ProcessesEnvelope['items']): EmissionProcessListItem[] {
  return (raw ?? [])
    .map((item) => {
      const processCode = String(item.processCode ?? '').trim()
      if (!processCode) {
        return null
      }
      const label = String(item.name ?? item.reports?.[0]?.name ?? item.menuProcessCode ?? processCode)
      return { processCode, label }
    })
    .filter((item): item is EmissionProcessListItem => item !== null)
}

function mapReportItems(raw: DesignReportsEnvelope['items']): EmissionReportListItem[] {
  return (raw ?? [])
    .map((item) => {
      const id = item.id != null ? Number(item.id) : NaN
      if (!Number.isFinite(id)) {
        return null
      }
      return {
        id,
        code: String(item.code ?? '').trim(),
        name: String(item.name ?? item.code ?? id),
        isPrincipal: Boolean(item.isPrincipal),
      }
    })
    .filter((item): item is EmissionReportListItem => item !== null)
}

/**
 * Chrome GEN-15 del diseñador (lista de diseños + Save/Save As vía DX).
 * El SDK 2.3.x no exporta `EmissionReportDesignerPage` 2.4.x; el host cubre el contrato.
 */
export function EmissionReportDesignerPage({
  initialProcessCode,
  hasDesignPermission = true,
  isNative = false,
  t,
  renderDesigner,
}: EmissionReportDesignerPageProps) {
  const translate = t ?? ((key: string) => key)
  const [loading, setLoading] = useState(false)
  const [errorKey, setErrorKey] = useState<string | null>(null)
  const [processes, setProcesses] = useState<EmissionProcessListItem[]>([])
  const [selectedProcessCode, setSelectedProcessCode] = useState<string | null>(
    initialProcessCode?.trim() || null,
  )
  const [confirmedProcessCode, setConfirmedProcessCode] = useState<string | null>(null)
  const [reports, setReports] = useState<EmissionReportListItem[]>([])
  const [selectedReportId, setSelectedReportId] = useState<number | null>(null)
  const [reportsReady, setReportsReady] = useState(false)

  useEffect(() => {
    if (isNative || !hasDesignPermission) {
      return
    }
    let active = true
    setLoading(true)
    setErrorKey(null)
    void apiRequest<ProcessesEnvelope>('/api/v1/emissions/processes').then((result) => {
      if (!active) {
        return
      }
      setLoading(false)
      if (result.kind === 'ok') {
        const items = mapProcessItems(result.envelope.resultado?.items)
        setProcesses(items)
        if (items.length === 0) {
          setErrorKey('emission.design.noProcesses')
        }
        return
      }
      if (result.kind === 'envelopeError') {
        setErrorKey(result.envelope.respuesta || 'emission.design.forbidden')
        return
      }
      setErrorKey(result.i18nKey || 'emission.design.forbidden')
    })
    return () => {
      active = false
    }
  }, [hasDesignPermission, isNative])

  useEffect(() => {
    if (!confirmedProcessCode) {
      setReportsReady(false)
      setReports([])
      setSelectedReportId(null)
      return
    }
    let active = true
    setReportsReady(false)
    setLoading(true)
    void apiRequest<DesignReportsEnvelope>(
      `/api/v1/emissions/design/processes/${encodeURIComponent(confirmedProcessCode)}/reports`,
    ).then((result) => {
      if (!active) {
        return
      }
      setLoading(false)
      if (result.kind !== 'ok') {
        setReports([])
        setSelectedReportId(null)
        setReportsReady(true)
        return
      }
      const items = mapReportItems(result.envelope.resultado?.items)
      setReports(items)
      const principal = items.find((item) => item.isPrincipal) ?? items[0]
      setSelectedReportId(principal?.id ?? null)
      setReportsReady(true)
    })
    return () => {
      active = false
    }
  }, [confirmedProcessCode])

  const selectedReport = reports.find((item) => item.id === selectedReportId) ?? null

  const handleDxReportSaved = useCallback(
    async (event: { url: string; layoutDefinition?: string }) => {
      if (!confirmedProcessCode) {
        return
      }
      const code = catalogCodeFromDxUrl(event.url)
      if (!code) {
        setErrorKey('emission.design.saveAsFailed')
        return
      }
      const isNew = isUnknownEmissionReportCode(
        code,
        reports.map((item) => catalogEmissionReportCode(item.code)),
      )
      if (isNew) {
        setLoading(true)
        const createResult = await apiRequest<DesignCreateEnvelope>(
          `/api/v1/emissions/design/processes/${encodeURIComponent(confirmedProcessCode)}/reports`,
          {
            method: 'POST',
            body: JSON.stringify({
              code,
              name: displayNameFromReportUrl(event.url.trim() || code),
              layoutDefinition: event.layoutDefinition,
              layoutMime: event.layoutDefinition ? LAYOUT_MIME_REPX : undefined,
            }),
          },
        )
        setLoading(false)
        if (createResult.kind !== 'ok') {
          setErrorKey(createResult.i18nKey || 'emission.design.saveAsFailed')
          return
        }
        const createdRaw = createResult.envelope.resultado?.item
        const created = mapReportItems(createdRaw ? [createdRaw] : [])[0]
        if (created) {
          setReports((prev) => {
            const without = prev.filter((item) => item.code !== created.code && item.id !== created.id)
            return [...without, created]
          })
          setSelectedReportId(created.id)
        }
        setErrorKey(null)
        return
      }
      if (selectedReportId === null || !event.layoutDefinition) {
        return
      }
      const updateResult = await apiRequest(`/api/v1/emissions/design/reports/${selectedReportId}/layout`, {
        method: 'PUT',
        body: JSON.stringify({
          layoutDefinition: event.layoutDefinition,
          layoutMime: LAYOUT_MIME_REPX,
        }),
      })
      if (updateResult.kind !== 'ok') {
        setErrorKey(updateResult.i18nKey || 'emissions.error.transport')
      }
    },
    [confirmedProcessCode, reports, selectedReportId],
  )

  const markPrincipal = () => {
    if (selectedReportId === null) {
      return
    }
    void apiRequest(`/api/v1/emissions/design/reports/${selectedReportId}/set-principal`, {
      method: 'POST',
    }).then((result) => {
      if (result.kind !== 'ok') {
        setErrorKey(result.i18nKey || 'emissions.error.transport')
        return
      }
      setReports((prev) =>
        prev.map((item) => ({
          ...item,
          isPrincipal: item.id === selectedReportId,
        })),
      )
    })
  }

  const closeDesigner = () => {
    setConfirmedProcessCode(null)
    setReports([])
    setSelectedReportId(null)
    setReportsReady(false)
    setErrorKey(null)
  }

  const designerContext = useMemo((): PartesReportDesignerContext | null => {
    if (!confirmedProcessCode || !reportsReady) {
      return null
    }
    const config = readDxReportingConfig()
    return {
      processCode: confirmedProcessCode,
      reportId: selectedReportId,
      reportCode: catalogEmissionReportCode(selectedReport?.code) || null,
      designerEndpoint: config.host,
      knownReportCodes: reports.map((item) => catalogEmissionReportCode(item.code)),
      onDxReportSaved: handleDxReportSaved,
    }
  }, [
    confirmedProcessCode,
    reportsReady,
    selectedReportId,
    selectedReport?.code,
    reports,
    handleDxReportSaved,
  ])

  if (isNative) {
    return (
      <div data-testid="emissions.designer.excludedNative" data-i18n-key="emissions.designer.mobileExcluded">
        {translate('emissions.designer.mobileExcluded')}
      </div>
    )
  }

  if (!hasDesignPermission) {
    return (
      <div data-testid="emission.design.forbidden" role="alert">
        {translate('emission.design.forbidden')}
      </div>
    )
  }

  const processConfirmed = Boolean(confirmedProcessCode)
  const designerReady = Boolean(designerContext)

  return (
    <div data-testid="emission.design.page">
      <LoadPanel visible={loading} />
      <div
        data-testid="emission.design.toolbar"
        style={{
          display: 'flex',
          flexWrap: 'wrap',
          gap: 8,
          alignItems: 'center',
          marginBottom: 16,
        }}
      >
        <SelectBox
          dataSource={processes}
          value={selectedProcessCode}
          valueExpr="processCode"
          displayExpr="label"
          placeholder={translate('emission.design.selectProcess')}
          onValueChanged={(event) => {
            const next = String(event.value ?? '').trim() || null
            setSelectedProcessCode(next)
            if (confirmedProcessCode && next !== confirmedProcessCode) {
              closeDesigner()
            }
          }}
          width={280}
          elementAttr={{ 'data-testid': 'emission.design.process' }}
        />
        <Button
          text={translate('emission.design.confirmProcess')}
          type="default"
          stylingMode="contained"
          disabled={!selectedProcessCode}
          onClick={() => setConfirmedProcessCode(selectedProcessCode)}
          elementAttr={{ 'data-testid': 'emission.design.confirmProcess' }}
        />
        {processConfirmed ? (
          <Button
            text={translate('emission.design.closeDesigner')}
            onClick={closeDesigner}
            elementAttr={{ 'data-testid': 'emission.design.closeDesigner' }}
          />
        ) : null}
        {designerReady ? (
          <>
            <SelectBox
              dataSource={reports}
              value={selectedReportId}
              valueExpr="id"
              displayExpr="name"
              placeholder={translate('emission.design.selectReport')}
              onValueChanged={(event) => {
                const next = event.value != null ? Number(event.value) : null
                setSelectedReportId(Number.isFinite(next) ? next : null)
              }}
              width={280}
              elementAttr={{
                'data-testid': 'emission.design.report',
                'data-report-codes': reports.map((item) => catalogEmissionReportCode(item.code)).join(','),
              }}
            />
            <Button
              text={translate('emission.design.setPrincipal')}
              disabled={selectedReportId === null}
              onClick={markPrincipal}
              elementAttr={{ 'data-testid': 'emission.design.setPrincipal' }}
            />
          </>
        ) : null}
      </div>
      {errorKey ? (
        <p data-i18n-key={errorKey} role="status">
          {translate(errorKey)}
        </p>
      ) : null}
      {designerReady ? (
        <p data-testid="emission.design.engineHint" style={{ opacity: 0.8, fontSize: 13 }}>
          {translate('emission.design.dxHint')} {translate('emission.design.dxSaveHint')}
        </p>
      ) : null}
      {designerContext ? (
        <div data-testid="emission.design.host">
          <ReportDesignerHost
            context={designerContext}
            isNative={false}
            renderDesigner={renderDesigner}
            t={translate}
          />
        </div>
      ) : null}
    </div>
  )
}
