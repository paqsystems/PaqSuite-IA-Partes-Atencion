import type { FormEvent } from 'react'
import { useMemo, useState } from 'react'
import {
  AuthLoginLayout,
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
import { loginRequest } from './authApi'
import { resolveAuthMessage } from './authMessages'
import { saveLoginSession } from './authSessionStore'
import { bootstrapAuthenticatedSession } from './authBootstrap'
import { resolvePostLoginRoute } from './postLoginRouter'
import { resolvePartesAuthHero } from './partesAuthHero'

export function LoginPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const sessionExpired = searchParams.get('expired') === '1'
  const blocked = searchParams.get('blocked') === '1'

  const [usuario, setUsuario] = useState('')
  const [password, setPassword] = useState('')
  const [locale, setLocale] = useState<LocaleCode>(
    () => normalizeLocale(getGuestLocale()) ?? 'es',
  )
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const authHero = useMemo(() => resolvePartesAuthHero(t), [t])

  const bannerMessage = useMemo(() => {
    if (sessionExpired) {
      return resolveAuthMessage('auth.sessionExpired')
    }
    if (blocked) {
      return resolveAuthMessage('shell.blockedNoCompany')
    }
    return null
  }, [blocked, sessionExpired, t])

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)
    setIsSubmitting(true)

    try {
      const result = await loginRequest({ usuario, password, locale })

      if (result.kind === 'ok') {
        const session = saveLoginSession(result.envelope.resultado)
        bootstrapAuthenticatedSession(session)
        const decision = resolvePostLoginRoute(session)
        navigate(`${decision.route}${decision.search ?? ''}`)
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
    <AuthLoginLayout
      hero={authHero}
      badge={t('shell.footer.brand')}
      cardTitle={t('login.welcome')}
      cardHint={t('login.hint')}
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
        <Link data-testid="loginForgotLink" to="/forgot-password">
          {t('login.forgotPasswordLink')}
        </Link>
      }
    >
      {bannerMessage ? (
        <p className={authClassNames.messageError}>{bannerMessage}</p>
      ) : null}
      {errorMessage ? (
        <p className={authClassNames.messageError}>{errorMessage}</p>
      ) : null}

      <form className={authClassNames.form} onSubmit={handleSubmit}>
        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('login.username')}</span>
          <TextBox
            stylingMode="outlined"
            value={usuario}
            onValueChanged={(event) => setUsuario(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'loginUsuario' }}
          />
        </label>

        <label className={authClassNames.field}>
          <span className={authClassNames.fieldLabel}>{t('login.password')}</span>
          <TextBox
            stylingMode="outlined"
            mode="password"
            value={password}
            onValueChanged={(event) => setPassword(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'loginPassword' }}
          />
        </label>

        <Button
          text={isSubmitting ? t('login.loading') : t('login.submit')}
          type="default"
          stylingMode="contained"
          useSubmitBehavior
          disabled={isSubmitting}
          className={authClassNames.cta}
          elementAttr={{ 'data-testid': 'loginSubmit' }}
        />
      </form>
    </AuthLoginLayout>
  )
}
