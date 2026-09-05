import { MobileConfigPanel, isNativeApp } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'

type PartesMobileConfigHostProps = {
  getTenant: () => string | null
}

export function PartesMobileConfigHost({ getTenant }: PartesMobileConfigHostProps) {
  const { t } = useTranslation()
  if (!isNativeApp()) {
    return null
  }

  return (
    <MobileConfigPanel
      getTenantForHealth={getTenant}
      projectSlug="partesatencion"
      defaultApiBaseUrl={import.meta.env.VITE_API_BASE_URL}
      t={(key) => t(key)}
    />
  )
}
