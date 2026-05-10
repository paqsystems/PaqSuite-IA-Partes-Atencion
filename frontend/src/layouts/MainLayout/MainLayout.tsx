import Button from 'devextreme-react/button'
import { useTranslation } from 'react-i18next'
import { Outlet } from 'react-router-dom'
import { useAuth } from '../../app/providers/AuthProvider'

export function MainLayout() {
  const { t } = useTranslation()
  const { logout } = useAuth()

  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <header
        style={{
          padding: '8px 16px',
          borderBottom: '1px solid #e0e0e0',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
        }}
      >
        <strong>{t('appTitle')}</strong>
        <Button text={t('logout')} type="normal" stylingMode="outlined" onClick={() => void logout()} />
      </header>
      <main style={{ flex: 1, padding: 16 }}>
        <Outlet />
      </main>
    </div>
  )
}
