import LoadPanel from 'devextreme-react/load-panel'
import { useEffect, useRef, useState, type ReactNode } from 'react'
import { useTranslation } from 'react-i18next'
import { postEmissionDesignHydrateSidecar } from './emissionDesignHydrateApi'

type EmissionDesignHydrateGateProps = {
  processCode: string
  reportCode: string | null
  onHydrateDone?: () => void
  children: ReactNode
}

/**
 * CC-ST-4: batch hydrate al montar proceso + puntual al cambiar variante antes del lienzo DX.
 */
export function EmissionDesignHydrateGate({
  processCode,
  reportCode,
  onHydrateDone,
  children,
}: EmissionDesignHydrateGateProps) {
  const { t } = useTranslation()
  const [blocking, setBlocking] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const batchDoneFor = useRef<string | null>(null)
  const lastPunctual = useRef<string | null>(null)

  useEffect(() => {
    let cancelled = false
    const run = async () => {
      setBlocking(true)
      setError(null)

      const runBatch = batchDoneFor.current !== processCode
      const punctualKey = reportCode ? `${processCode}:${reportCode}` : null
      const runPunctual =
        punctualKey !== null && lastPunctual.current !== punctualKey

      if (!runBatch && !runPunctual) {
        setBlocking(false)
        return
      }

      if (runBatch) {
        const batch = await postEmissionDesignHydrateSidecar(processCode)
        if (cancelled) {
          return
        }
        if (batch.kind !== 'ok') {
          setError(t('partes.emissionDesign.hydrate.batchFailed'))
          setBlocking(false)
          return
        }
        if (batch.envelope.resultado.failed?.length) {
          setError(t('partes.emissionDesign.hydrate.partialFailed'))
        }
        batchDoneFor.current = processCode
      }

      if (runPunctual && reportCode) {
        const punctual = await postEmissionDesignHydrateSidecar(processCode, [reportCode])
        if (cancelled) {
          return
        }
        if (punctual.kind !== 'ok') {
          setError(t('partes.emissionDesign.hydrate.variantFailed'))
          setBlocking(false)
          return
        }
        lastPunctual.current = punctualKey
      }

      if (!cancelled) {
        setBlocking(false)
        onHydrateDone?.()
      }
    }

    void run()

    return () => {
      cancelled = true
    }
  }, [processCode, reportCode, onHydrateDone, t])

  if (blocking) {
    return (
      <>
        <LoadPanel
          visible
          shading
          showIndicator
          showPane
          message={t('partes.emissionDesign.hydrate.loading')}
        />
        <div data-testid="partesEmissionDesignHydrateLoading" aria-busy="true" />
      </>
    )
  }

  return (
    <>
      {error ? (
        <div role="alert" data-testid="partesEmissionDesignHydrateError">
          {error}
        </div>
      ) : null}
      {children}
    </>
  )
}