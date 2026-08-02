import type { FormEvent } from 'react'
import { useState } from 'react'
import {
  AuthCardLayout,
  LanguageSelector,
  authClassNames,
  getGuestLocale,
  normalizeLocale,
  type LocaleCode,
} from '@paqsuite/react-core'
import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import { useTranslation } from 'react-i18next'
import { Link } from 'react-router-dom'
import { applyGuestLocale } from '../../i18n/i18n'
import { forgotPasswordRequest } from './authApi'
import { resolveAuthMessage } from './authMessages'
import { resolvePartesAuthHero } from './partesAuthHero'

export function ForgotPasswordPage() {
  const { t } = useTranslation()
  const [email, setEmail] = useState('')
  const [locale, setLocale] = useState<LocaleCode>(
    () => normalizeLocale(getGuestLocale()) ?? 'es',
  )
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [successMessage, setSuccessMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const authHero = resolvePartesAuthHero(t)

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)
    setSuccessMessage(null)
    setIsSubmitting(true)

    try {
      const result = await forgotPasswordRequest({ email, locale })

      if (result.kind === 'ok') {
        setSuccessMessage(t('forgot.success'))
        return
      }

      if (result.kind === 'envelopeError') {
        setErrorMessage(resolveAuthMessage(result.envelope.respuesta))
        return
      }

      setErrorMessage(resolveAuthMessage('infra.transport'))
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <AuthCardLayout
      testId="authForgotPage"
      title={t('forgot.title')}
      hint={t('forgot.hint')}
      companyLogoUrl={authHero.companyLogoUrl}
      toolbar={
        <LanguageSelector
          testId="authLanguageSelect"
          locale={locale}
          onLocaleChange={(next) => {
            void handleLocaleChange(next)
          }}
        />
      }
      footer={
        <Link className="pqAuthCardLink" to="/login">
          {t('forgot.backToLogin')}
        </Link>
      }
    >
      {errorMessage ? (
        <p className={authClassNames.messageError}>{errorMessage}</p>
      ) : null}
      {successMessage ? (
        <p className={authClassNames.messageSuccess}>{successMessage}</p>
      ) : null}

      <form className={authClassNames.form} onSubmit={handleSubmit}>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('forgot.email')}</span>
          <TextBox
            stylingMode="outlined"
            mode="email"
            value={email}
            onValueChanged={(event) => setEmail(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'forgotEmail' }}
          />
        </label>

        <Button
          text={isSubmitting ? t('forgot.loading') : t('forgot.submit')}
          type="default"
          stylingMode="contained"
          useSubmitBehavior
          disabled={isSubmitting}
          className={authClassNames.cta}
          elementAttr={{ 'data-testid': 'forgotSubmit' }}
        />
      </form>
    </AuthCardLayout>
  )
}
