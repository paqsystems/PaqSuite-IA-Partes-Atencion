import type { FormEvent } from 'react'
import { useMemo, useRef, useState } from 'react'
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
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import { applyGuestLocale } from '../../i18n/i18n'
import { loginRequest } from './authApi'
import { resolveAuthMessage } from './authMessages'
import { saveLoginSession } from './authSessionStore'
import { bootstrapAuthenticatedSession } from './authBootstrap'
import { searchHasResetToken } from './guestLanding'
import { resolvePostLoginRoute } from './postLoginRouter'
import { resolveAppAuthHero } from './appProductBranding'
import { resolvePlatformCliente } from './platformContext'
import { PartesMobileConfigHost } from '../partes/mobile/PartesMobileConfigHost'

export function LoginPage() {
  const { t, i18n } = useTranslation()
  const formRef = useRef<HTMLFormElement>(null)
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
  const [isPasswordVisible, setIsPasswordVisible] = useState(false)
  const [locale, setLocale] = useState<LocaleCode>(
    () => normalizeLocale(getGuestLocale()) ?? 'es',
  )
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const authHero = useMemo(() => resolveAppAuthHero(locale), [locale])

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

  if (searchHasResetToken(searchParams.toString())) {
    return (
      <Navigate
        to={{ pathname: '/reset-password', search: `?${searchParams.toString()}` }}
        replace
      />
    )
  }

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
  }

  function resolveCredentialsFromDom(form: HTMLFormElement): {
    usuario: string
    password: string
    tenant: string
  } {
    function queryTextBoxValue(...dataTestIds: string[]): string {
      for (const dataTestId of dataTestIds) {
        const host = form.querySelector(`[data-testid="${dataTestId}"]`)
        const input = host?.querySelector('input')
        const value = (input?.value ?? '').trim()
        if (value !== '') {
          return value
        }
      }
      return ''
    }

    const textInputs = Array.from(
      form.querySelectorAll<HTMLInputElement>('input'),
    ).filter((input) => {
      const type = (input.type || 'text').toLowerCase()
      return type === 'text' || type === 'email' || type === 'tel'
    })
    const passwordInputs = Array.from(
      form.querySelectorAll<HTMLInputElement>('input[type="password"]'),
    )

    const usuarioFromDom =
      queryTextBoxValue('loginUsuario', 'login.codigo', 'login.username') ||
      (textInputs[0]?.value ?? '').trim()
    const passwordFromDom =
      queryTextBoxValue('loginPassword', 'login.password') ||
      (passwordInputs[0]?.value ?? '').trim()
    const tenantFromDom = native ? queryTextBoxValue('loginTenant') : ''

    return {
      usuario: usuario.trim() || usuarioFromDom,
      password: password || passwordFromDom,
      tenant: tenant.trim() || tenantFromDom,
    }
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrorMessage(null)
    setIsSubmitting(true)

    const form = event.currentTarget
    const creds = resolveCredentialsFromDom(form)

    try {
      if (native) {
        resolvePlatformCliente(creds.tenant)
      }
      const result = await loginRequest({
        usuario: creds.usuario,
        password: creds.password,
        locale,
        tenant: native ? creds.tenant : undefined,
      })

      if (result.kind === 'ok') {
        const clienteCode = resolvePlatformCliente(
          native ? creds.tenant : undefined,
        )
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
      key={i18n.language}
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

      <form ref={formRef} className={authClassNames.form} onSubmit={handleSubmit}>
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
            key={`login-password-${isPasswordVisible ? 'visible' : 'hidden'}`}
            stylingMode="outlined"
            mode={isPasswordVisible ? 'text' : 'password'}
            value={password}
            onValueChanged={(event) => setPassword(String(event.value ?? ''))}
            buttons={[
              {
                name: 'togglePasswordVisibility',
                location: 'after',
                options: {
                  text: isPasswordVisible ? '◉' : '👁',
                  stylingMode: 'text',
                  hint: t(
                    isPasswordVisible
                      ? 'login.hidePassword'
                      : 'login.showPassword',
                  ),
                  elementAttr: { 'data-testid': 'loginPasswordToggle' },
                  onClick: () => setIsPasswordVisible((visible) => !visible),
                },
              },
            ]}
            elementAttr={{
              'data-testid': 'loginPassword',
              'aria-label': t('login.password'),
            }}
          />
        </label>

        <Button
          text={isSubmitting ? t('login.loading') : t('login.submit')}
          type="default"
          stylingMode="contained"
          useSubmitBehavior={false}
          disabled={isSubmitting}
          className={authClassNames.cta}
          elementAttr={{ 'data-testid': 'loginSubmit' }}
          onClick={() => formRef.current?.requestSubmit()}
        />
      </form>
    </AuthLoginLayout>
  )
}
