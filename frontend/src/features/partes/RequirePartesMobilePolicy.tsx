import { isNativeApp } from '@paqsuite/react-core'
import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { isPartesMobileRouteAllowed } from './mobile/partesMobilePolicy'

/** En native, deniega deep-links fuera de allowlist. */
export function RequirePartesMobilePolicy() {
  const location = useLocation()
  if (isNativeApp() && !isPartesMobileRouteAllowed(location.pathname)) {
    return <Navigate to="/partes" replace state={{ mobileRouteExcluded: true }} />
  }
  return <Outlet />
}
