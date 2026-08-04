import type { FormEvent } from 'react'
import { useState } from 'react'
import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import { useNavigate } from 'react-router-dom'
import { changePasswordRequest } from './authApi'
import { passwordPolicyHint, resolveAuthMessage } from './authMessages'
import { getAuthSession, patchAuthSession } from './authSessionStore'
import { resolvePostLoginRoute } from './postLoginRouter'

export function ChangePasswordPage() {
  const navigate = useNavigate()
  const session = getAuthSession()

  const [passwordActual, setPasswordActual] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

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
    <main className="authPage" data-testid="authChangePage">
      <h1>Cambiar contraseña</h1>
      {session?.firstLogin ? (
        <p className="authHint">Debe cambiar la contraseña antes de continuar.</p>
      ) : null}
      <p className="authHint">{passwordPolicyHint('simple')}</p>

      {errorMessage ? <p className="error">{errorMessage}</p> : null}

      <form className="authForm" onSubmit={handleSubmit}>
        <div className="authField">
          <span>Contraseña actual</span>
          <TextBox
            mode="password"
            value={passwordActual}
            onValueChanged={(event) => setPasswordActual(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePasswordActual' }}
          />
        </div>

        <div className="authField">
          <span>Nueva contraseña</span>
          <TextBox
            mode="password"
            value={password}
            onValueChanged={(event) => setPassword(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePassword' }}
          />
        </div>

        <div className="authField">
          <span>Confirmar contraseña</span>
          <TextBox
            mode="password"
            value={passwordConfirmation}
            onValueChanged={(event) => setPasswordConfirmation(String(event.value ?? ''))}
            elementAttr={{ 'data-testid': 'changePasswordConfirmation' }}
          />
        </div>

        <Button
          text={isSubmitting ? 'Guardando…' : 'Cambiar contraseña'}
          type="default"
          useSubmitBehavior
          disabled={isSubmitting}
          elementAttr={{ 'data-testid': 'changeSubmit' }}
        />
      </form>
    </main>
  )
}
