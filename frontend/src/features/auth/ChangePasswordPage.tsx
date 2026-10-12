import type { FormEvent } from 'react'
import { useEffect, useMemo, useState } from 'react'
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
import { useNavigate } from 'react-router-dom'
import { applyGuestLocale } from '../../i18n/i18n'
import { changePasswordRequest, passwordPolicyRequest } from './authApi'
import {
  normalizePasswordComplejidad,
  passwordPolicyHint,
  resolveAuthMessage,
} from './authMessages'
import { getAuthSession, patchAuthSession } from './authSessionStore'
import { resolveAppAuthHero } from './appProductBranding'
import { resolvePostLoginRoute } from './postLoginRouter'

export function ChangePasswordPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const session = getAuthSession()
  const [locale, setLocale] = useState<LocaleCode>(
    () => normalizeLocale(session?.user.locale ?? getGuestLocale()) ?? 'es',
  )
  const [policyMode, setPolicyMode] = useState<'simple' | 'segura'>(() =>
    normalizePasswordComplejidad(session?.passwordComplejidad),
  )
  const [policyMin, setPolicyMin] = useState(session?.passwordLongitudMin ?? 8)

  useEffect(() => {
    if (session?.passwordComplejidad) {
      return
    }
    void passwordPolicyRequest().then((result) => {
      if (result.kind === 'ok') {
        setPolicyMode(normalizePasswordComplejidad(result.envelope.resultado.passwordComplejidad))
        setPolicyMin(Number(result.envelope.resultado.passwordLongitudMin) || 8)
      }
    })
  }, [session?.passwordComplejidad])

  const [passwordActual, setPasswordActual] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const authHero = useMemo(() => resolveAppAuthHero(locale), [locale])
  const hint = session?.firstLogin
    ? t('change.firstLoginHint')
    : passwordPolicyHint(policyMode, policyMin)

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)
    setIsSubmitting(true)

    try {
      const result = await changePasswordRequest({
        passwordActual,
        password,
        passwordConfirmation,
      })

      if (!result) {
        setErrorMessage(resolveAuthMessage('auth.sessionExpired'))
        navigate('/login?expired=1')
        return
      }

      if (result.kind === 'ok') {
        const nextSession = patchAuthSession({ firstLogin: false })
        if (nextSession) {
          const decision = resolvePostLoginRoute(nextSession)
          navigate(`${decision.route}${decision.search ?? ''}`)
        } else {
          navigate('/login')
        }
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
      testId="authChangePage"
      title={t('change.title')}
      hint={hint}
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
    >
      {errorMessage ? (
        <p className={authClassNames.messageError}>{errorMessage}</p>
      ) : null}
      <form className={authClassNames.form} onSubmit={handleSubmit}>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('change.currentPassword')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={passwordActual}
            valueChangeEvent="input"
            onValueChanged={(event) => setPasswordActual(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePasswordActual' }}
          />
        </label>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('change.newPassword')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={password}
            valueChangeEvent="input"
            onValueChanged={(event) => setPassword(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePassword' }}
          />
        </label>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('change.confirmPassword')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={passwordConfirmation}
            valueChangeEvent="input"
            onValueChanged={(event) => setPasswordConfirmation(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePasswordConfirmation' }}
          />
        </label>
        <Button
          text={isSubmitting ? t('change.loading') : t('change.submit')}
          type="default"
          stylingMode="contained"
          useSubmitBehavior
          disabled={isSubmitting}
          className={authClassNames.cta}
          elementAttr={{ 'data-testid': 'changeSubmit' }}
        />
      </form>
    </AuthCardLayout>
  )
}
