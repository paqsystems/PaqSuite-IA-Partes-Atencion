import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { httpClient } from '../../services/http/httpClient'

type StatusPayload =
  | {
      installationMode: string
      appName: string
      environment: string
    }
  | null

export function HomePage() {
  const { t } = useTranslation()
  const [status, setStatus] = useState<StatusPayload>(null)

  useEffect(() => {
    void (async () => {
      const { data } = await httpClient.get<{ resultado: StatusPayload }>('/v1/system/status')
      setStatus(data.resultado)
    })()
  }, [])

  return (
    <div>
      <h2>{t('homeWelcome')}</h2>
      <p>
        {t('versionLabel')}: {import.meta.env.VITE_APP_VERSION}
      </p>
      {status ? (
        <p>
          {t('installationMode')}: {status.installationMode} — {status.appName} ({status.environment})
        </p>
      ) : null}
    </div>
  )
}
