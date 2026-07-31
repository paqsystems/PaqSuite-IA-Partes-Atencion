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
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { applyGuestLocale } from '../../i18n/i18n'
import { resetPasswordRequest } from './authApi'
import { passwordPolicyHint, resolveAuthMessage } from './authMessages'
import { resolvePartesAuthHero } from './partesAuthHero'

export function ResetPasswordPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const resetToken = searchParams.get('token') ?? ''

  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [locale, setLocale] = useState<LocaleCode>(
    () => normalizeLocale(getGuestLocale()) ?? 'es',
  )
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const authHero = resolvePartesAuthHero(t)

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)

    if (!resetToken) {
      setErrorMessage(resolveAuthMessage('auth.resetTokenInvalid'))
      return
    }

    setIsSubmitting(true)

    try {
      const result = await resetPasswordRequest({
        token: resetToken,
        password,
        passwordConfirmation,
        locale: searchParams.get('locale') ?? locale,
      })

      if (result.kind === 'ok') {
        navigate('/login')
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
      testId="authResetPage"
      title={t('reset.title')}
      hint={passwordPolicyHint('simple')}
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
          {t('reset.backToLogin')}
        </Link>
      }
    >
      {errorMessage ? (
        <p className={authClassNames.messageError}>{errorMessage}</p>
      ) : null}

      <form className={authClassNames.form} onSubmit={handleSubmit}>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('reset.newPassword')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={password}
            onValueChanged={(event) => setPassword(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'resetPassword' }}
          />
        </label>

        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('reset.confirmPassword')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={passwordConfirmation}
            onValueChanged={(event) =>
              setPasswordConfirmation(String(event.value ?? ''))
            }
            elementAttr={{ 'data-testid': 'resetPasswordConfirmation' }}
          />
        </label>

        <Button
          text={isSubmitting ? t('reset.loading') : t('reset.submit')}
          type="default"
          stylingMode="contained"
          useSubmitBehavior
          disabled={isSubmitting}
          className={authClassNames.cta}
          elementAttr={{ 'data-testid': 'resetSubmit' }}
        />
      </form>
    </AuthCardLayout>
  )
}
