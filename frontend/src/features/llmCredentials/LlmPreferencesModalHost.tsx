import { LlmPreferencesPanel } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { partesLlmProviderCatalog } from './partesLlmProviderCatalog'

type LlmPreferencesModalHostProps = {
  visible: boolean
  onClose: () => void
  onCredentialsChanged?: () => void
}

export function LlmPreferencesModalHost({
  visible,
  onClose,
  onCredentialsChanged,
}: LlmPreferencesModalHostProps) {
  const { t } = useTranslation()

  return (
    <LlmPreferencesPanel
      presentation="modal"
      providerCatalog={partesLlmProviderCatalog}
      visible={visible}
      onClose={onClose}
      onCredentialsChanged={onCredentialsChanged}
      entrySource="avatar"
      t={(key) => t(key)}
    />
  )
}
