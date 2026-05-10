import type { ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { getStoredToken } from '../../services/auth/authStorage'

export function RequireAuth({ children }: { children: ReactNode }) {
  const location = useLocation()
  const token = getStoredToken()

  if (!token) {
    return <Navigate to="/login" replace state={{ from: location }} />
  }

  return <>{children}</>
}
