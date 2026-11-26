import { useCallback, useMemo, useState } from 'react'
import {
  SmartCapturePanel,
  useLlmCredentialSelection,
  useSmartCapturePendingChoice,
  type SmartCaptureThreadMessage,
} from '@paqsuite/react-core'
import Button from 'devextreme-react/button'
import { confirm } from 'devextreme/ui/dialog'
import { useTranslation } from 'react-i18next'
import { LlmPreferencesModalHost } from '../../llmCredentials/LlmPreferencesModalHost'
import { resolveAuthMessage } from '../../auth/authMessages'
import { handleEmissionDesignSmartCaptureSend } from './emissionDesignSmartCaptureTurn'

export type EmissionDesignSmartCaptureSlotProps = {
  processCode: string
  reportUrl: string | null
  remountToken: number
  onAppliedRefresh: () => void
  /** L-B-10: apply SC pendiente de Guardar/Descartar. */
  pendingReview?: boolean
  onPendingReview?: (pending: boolean) => void
  onCommitPending?: () => Promise<void>
  onDiscardPending?: () => Promise<void>
}

/**
 * Smart Capture debajo del diseñador (TR-015-update-b / camino B).
 */
export function EmissionDesignSmartCaptureSlot({
  processCode,
  reportUrl,
  remountToken,
  onAppliedRefresh,
  pendingReview = false,
  onPendingReview,
  onCommitPending,
  onDiscardPending,
}: EmissionDesignSmartCaptureSlotProps) {
  const { t, i18n } = useTranslation()
  const llmSelection = useLlmCredentialSelection({ autoSelectFirstEnabled: true })
  const { pendingChoice, setPendingChoice } = useSmartCapturePendingChoice()
  const [thread, setThread] = useState<SmartCaptureThreadMessage[]>([])
  const [prefsOpen, setPrefsOpen] = useState(false)
  const [panelError, setPanelError] = useState<string | null>(null)
  const [reviewBusy, setReviewBusy] = useState(false)

  const resolveMessage = useCallback(
    (key: string) => resolveAuthMessage(key, (k) => t(k)) || t(key),
    [t],
  )

  const confirmDirtyApply = useCallback(async () => {
    const result = await confirm(
      t('partes.emissionDesign.confirmDirtyApply'),
      t('partes.emissionDesign.confirmDirtyTitle'),
    )
    return Boolean(result)
  }, [t])

  const enabled = Boolean(reportUrl) && processCode === 'partes.informes.consultaDetallada'

  const handlers = useMemo(
    () => ({
      processCode,
      reportUrl,
      pendingChoice,
      activeCredentialId: llmSelection.activeCredentialId,
      locale: i18n.language,
      confirmDirtyApply,
      onAssistantReply: (text: string) => {
        setPanelError(null)
        setThread((prev) => [...prev, { role: 'assistant' as const, text }])
      },
      onError: (message: string) => {
        setPanelError(message)
        setThread((prev) => [...prev, { role: 'assistant' as const, text: message }])
      },
      resolveMessage,
      onAppliedRefresh,
      onPendingReview,
      setPendingChoice,
    }),
    [
      processCode,
      reportUrl,
      pendingChoice,
      llmSelection.activeCredentialId,
      i18n.language,
      confirmDirtyApply,
      resolveMessage,
      onAppliedRefresh,
      onPendingReview,
      setPendingChoice,
    ],
  )

  const handleCommit = useCallback(async () => {
    if (!onCommitPending || reviewBusy) {
      return
    }
    setReviewBusy(true)
    setPanelError(null)
    try {
      await onCommitPending()
      setThread((prev) => [
        ...prev,
        { role: 'assistant' as const, text: t('partes.emissionDesign.pendingSaved') },
      ])
    } catch (err) {
      const message =
        err instanceof Error && err.message
          ? err.message
          : t('partes.emissionDesign.pendingSaveFailed')
      setPanelError(message)
    } finally {
      setReviewBusy(false)
    }
  }, [onCommitPending, reviewBusy, t])

  const handleDiscard = useCallback(async () => {
    if (!onDiscardPending || reviewBusy) {
      return
    }
    setReviewBusy(true)
    setPanelError(null)
    try {
      await onDiscardPending()
      setThread((prev) => [
        ...prev,
        { role: 'assistant' as const, text: t('partes.emissionDesign.pendingDiscarded') },
      ])
    } catch (err) {
      const message =
        err instanceof Error && err.message
          ? err.message
          : t('partes.emissionDesign.pendingDiscardFailed')
      setPanelError(message)
    } finally {
      setReviewBusy(false)
    }
  }, [onDiscardPending, reviewBusy, t])

  return (
    <div
      data-testid="emissionDesignSmartCapture"
      style={{ marginTop: 12, borderTop: '1px solid var(--pq-process-border, #ddd)', paddingTop: 12 }}
    >
      {pendingReview ? (
        <div
          role="status"
          data-testid="emissionDesignPendingReview"
          style={{
            marginBottom: 12,
            padding: '10px 12px',
            border: '1px solid var(--pq-process-border, #c9a227)',
            background: 'var(--pq-process-surface-muted, #fff8e6)',
            borderRadius: 4,
            display: 'flex',
            flexWrap: 'wrap',
            gap: 8,
            alignItems: 'center',
            justifyContent: 'space-between',
          }}
        >
          <p style={{ margin: 0, flex: '1 1 220px' }}>{t('partes.emissionDesign.pendingReviewBanner')}</p>
          <div style={{ display: 'flex', gap: 8 }}>
            <Button
              text={t('partes.emissionDesign.pendingSave')}
              type="default"
              stylingMode="contained"
              disabled={reviewBusy || !onCommitPending}
              onClick={() => void handleCommit()}
              elementAttr={{ 'data-testid': 'emissionDesignPendingSave' }}
            />
            <Button
              text={t('partes.emissionDesign.pendingDiscard')}
              type="normal"
              stylingMode="outlined"
              disabled={reviewBusy || !onDiscardPending}
              onClick={() => void handleDiscard()}
              elementAttr={{ 'data-testid': 'emissionDesignPendingDiscard' }}
            />
          </div>
        </div>
      ) : null}
      {panelError ? (
        <div role="alert" data-testid="emissionDesignFormError" style={{ marginBottom: 8 }}>
          {panelError}
        </div>
      ) : null}
      {!reportUrl ? (
        <p data-testid="emissionDesignNeedReport" style={{ opacity: 0.85 }}>
          {t('partes.emissionDesign.reportUrlRequired')}
        </p>
      ) : null}
      <SmartCapturePanel
        key={`sc-${remountToken}-${reportUrl ?? 'none'}`}
        enabled={enabled}
        hintText={t('partes.emissionDesign.hint')}
        credentials={llmSelection.credentials.map((item) => ({
          id: item.id,
          nombre: item.nombre,
          enabled: item.enabled,
          supportsVision: item.supportsVision,
        }))}
        activeCredentialId={llmSelection.activeCredentialId}
        onActiveCredentialChange={(id) => {
          void llmSelection.setActiveCredentialId(id)
        }}
        onOpenPreferences={() => setPrefsOpen(true)}
        threadMessages={thread}
        onThreadChange={setThread}
        t={(key) => t(key)}
        pendingChoice={pendingChoice}
        onSelectPendingOption={(oneBasedIndex) => {
          const choiceText = String(oneBasedIndex)
          setThread((prev) => [...prev, { role: 'user', text: choiceText }])
          void handleEmissionDesignSmartCaptureSend(
            {
              message: choiceText,
              modality: 'texto',
              images: [],
              credentialId: llmSelection.activeCredentialId,
            },
            handlers,
          )
        }}
        onSend={(payload) => {
          setPanelError(null)
          setThread((prev) => [...prev, { role: 'user', text: payload.message }])
          void handleEmissionDesignSmartCaptureSend(payload, handlers)
        }}
      />
      <div data-testid="emissionDesignAssistantTurn" style={{ display: 'none' }} aria-hidden />
      <LlmPreferencesModalHost
        visible={prefsOpen}
        onClose={() => {
          setPrefsOpen(false)
          void llmSelection.refresh()
        }}
        onCredentialsChanged={() => void llmSelection.refresh()}
      />
    </div>
  )
}
