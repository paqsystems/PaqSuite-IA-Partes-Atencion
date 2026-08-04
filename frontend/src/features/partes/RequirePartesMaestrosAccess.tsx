import { isNativeApp } from '@paqsuite/react-core'
import { Navigate, Outlet } from 'react-router-dom'
import { getAuthSession } from '../auth/authSessionStore'

/** AC-10: deniega maestros a cliente funcional y a native. */
export function RequirePartesMaestrosAccess() {
  const session = getAuthSession()
  if (isNativeApp() || session?.partes?.tipoFuncional === 'cliente') {
    return <Navigate to="/dashboard" replace />
  }
  return <Outlet />
}
