import Button from 'devextreme-react/button'
import TextBox from 'devextreme-react/text-box'
import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../app/providers/AuthProvider'

export function LoginPage() {
  const { t } = useTranslation()
  const { login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('dev@example.com')
  const [password, setPassword] = useState('password')
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  const onSubmit = async () => {
    setError(null)
    setLoading(true)
    try {
      await login(email, password)
      navigate('/', { replace: true })
    } catch {
      setError('Credenciales inválidas')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div style={{ maxWidth: 360, margin: '64px auto', display: 'grid', gap: 12 }}>
      <h1 data-testid="login-heading">{t('login')}</h1>
      <TextBox
        value={email}
        placeholder={t('email')}
        mode="email"
        showClearButton
        valueChangeEvent="keyup"
        onValueChanged={(e) => setEmail(String(e.value ?? ''))}
      />
      <TextBox
        value={password}
        placeholder={t('password')}
        mode="password"
        valueChangeEvent="keyup"
        onValueChanged={(e) => setPassword(String(e.value ?? ''))}
      />
      {error ? <p role="alert">{error}</p> : null}
      <Button
        text={t('login')}
        type="default"
        stylingMode="contained"
        disabled={loading}
        onClick={() => void onSubmit()}
      />
    </div>
  )
}
