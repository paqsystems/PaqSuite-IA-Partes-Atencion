import type { FormEvent } from 'react'
import { useMemo, useState } from 'react'
import {
  AuthLoginLayout,
  LanguageSelector,
  authClassNames,
  getGuestLocale,
  isNativeApp,
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
import { resolvePlatformCliente } from './platformContext'
import { PartesMobileConfigHost } from '../partes/mobile/PartesMobileConfigHost'

export function LoginPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const native = isNativeApp()
  const sessionExpired = searchParams.get('expired') === '1'
  const expiredReason = searchParams.get('reason')
  const blocked = searchParams.get('blocked') === '1'

  const [tenant, setTenant] = useState(() =>
    native ? resolvePlatformCliente() : '',
  )
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
      if (expiredReason === 'unauthorized') {
        return resolveAuthMessage('auth.sessionUnauthorized')
      }
      return resolveAuthMessage('auth.sessionExpired')
    }
    if (blocked) {
      return resolveAuthMessage('shell.blockedNoCompany')
    }
    return null
  }, [blocked, expiredReason, sessionExpired, t])

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)
    setIsSubmitting(true)

    try {
      if (native) {
        resolvePlatformCliente(tenant)
      }
      const result = await loginRequest({
        usuario,
        password,
        locale,
        tenant: native ? tenant : undefined,
      })

      if (result.kind === 'ok') {
        const clienteCode = resolvePlatformCliente(native ? tenant : undefined)
        const session = saveLoginSession(result.envelope.resultado, clienteCode)
        bootstrapAuthenticatedSession(session)
        const decision = resolvePostLoginRoute(session)
        const clienteQuery = searchParams.get('cliente')?.trim()
        const search =
          decision.search ??
          (clienteQuery ? `?cliente=${encodeURIComponent(clienteQuery)}` : '')
        navigate(`${decision.route}${search}`)
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
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <LanguageSelector
            testId="authLanguageSelect"
            locale={locale}
            onLocaleChange={(next) => {
              void handleLocaleChange(next)
            }}
          />
          <PartesMobileConfigHost getTenant={() => tenant.trim() || null} />
        </div>
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
        {native ? (
          <label className={authClassNames.field}>
            <span className={authClassNames.fieldLabel}>{t('login.tenant')}</span>
            <TextBox
              stylingMode="outlined"
              value={tenant}
              onValueChanged={(event) => setTenant(String(event.value ?? ''))}
              elementAttr={{ 'data-testid': 'loginTenant' }}
            />
          </label>
        ) : null}

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
