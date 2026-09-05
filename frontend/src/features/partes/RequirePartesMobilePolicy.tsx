import { isNativeApp, MobileRouteGuard } from '@paqsuite/react-core'
import { useCallback } from 'react'
import { Outlet, useLocation, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { partesMobilePolicy } from './mobile/partesMobilePolicy'

/** En native, deniega deep-links fuera de allowlist GEN-22. */
export function RequirePartesMobilePolicy() {
  const location = useLocation()
  const navigate = useNavigate()
  const { t } = useTranslation()
  const onRedirect = useCallback(
    (homePath: string) => {
      navigate(homePath, { replace: true })
    },
    [navigate],
  )

  if (!isNativeApp()) {
    return <Outlet />
  }

  return (
    <MobileRouteGuard
      homePath="/partes"
      pathname={location.pathname}
      isAllowed={(pathname) => partesMobilePolicy.isAllowed(pathname)}
      onRedirect={onRedirect}
      bypassPaths={['/dashboard']}
      t={(key) => t(key)}
    >
      <Outlet />
    </MobileRouteGuard>
  )
}
