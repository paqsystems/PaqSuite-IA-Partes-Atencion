import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { getAuthSession, isAuthenticated } from './authSessionStore'

export function RequireAuth() {
  const location = useLocation()

  if (!isAuthenticated()) {
    return <Navigate to={{ pathname: '/login', search: location.search }} replace />
  }

  const session = getAuthSession()
  if (session?.firstLogin && location.pathname !== '/change-password') {
    return <Navigate to="/change-password" replace />
  }

  return <Outlet />
}

export function GuestOnly() {
  if (isAuthenticated()) {
    const session = getAuthSession()
    if (session?.firstLogin) {
      return <Navigate to="/change-password" replace />
    }
    return <Navigate to="/dashboard" replace />
  }

  return <Outlet />
}
