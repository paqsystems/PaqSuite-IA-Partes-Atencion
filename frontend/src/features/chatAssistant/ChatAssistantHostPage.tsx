import { ChatAssistantPage } from '@paqsuite/react-core'
import { useCallback, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { LlmPreferencesModalHost } from '../llmCredentials/LlmPreferencesModalHost'

/**
 * Host Partes GEN-21: monta ChatAssistantPage en `/chat-assistant`.
 */
export function ChatAssistantHostPage() {
  const { t } = useTranslation()
  const [preferencesVisible, setPreferencesVisible] = useState(false)
  const [credentialsRevision, setCredentialsRevision] = useState(0)

  const openPreferences = useCallback(() => {
    setPreferencesVisible(true)
  }, [])

  const bumpCredentialsRevision = useCallback(() => {
    setCredentialsRevision((current) => current + 1)
  }, [])

  const closePreferences = useCallback(() => {
    setPreferencesVisible(false)
    bumpCredentialsRevision()
  }, [bumpCredentialsRevision])

  return (
    <div data-testid="partesChatAssistantHost">
      <ChatAssistantPage
        turnUrl="/api/v1/chat-assistant/turns"
        welcomeText={t('partes.chatAssistant.welcome', { project: t('shell.productName') })}
        onOpenPreferences={openPreferences}
        credentialsRevision={credentialsRevision}
        t={(key) => t(key)}
      />
      <LlmPreferencesModalHost
        visible={preferencesVisible}
        onClose={closePreferences}
        onCredentialsChanged={bumpCredentialsRevision}
      />
    </div>
  )
}
