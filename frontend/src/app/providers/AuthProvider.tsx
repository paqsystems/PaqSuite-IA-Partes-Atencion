import type { ReactNode } from 'react'
import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { httpClient } from '../../services/http/httpClient'
import { getStoredToken, setStoredToken } from '../../services/auth/authStorage'

type AuthUser = { id: number; name: string; email: string }

type AuthContextValue = {
  token: string | null
  user: AuthUser | null
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  refreshMe: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [token, setToken] = useState<string | null>(() => getStoredToken())
  const [user, setUser] = useState<AuthUser | null>(null)

  const login = useCallback(async (email: string, password: string) => {
    setStoredToken(null)
    setToken(null)
    const { data } = await httpClient.post<{
      resultado: { token: string; user: AuthUser }
    }>('/v1/auth/login', { email, password })
    const newToken = data.resultado.token
    setStoredToken(newToken)
    setToken(newToken)
    setUser(data.resultado.user)
  }, [])

  const logout = useCallback(async () => {
    try {
      await httpClient.post('/v1/auth/logout')
    } finally {
      setStoredToken(null)
      setToken(null)
      setUser(null)
    }
  }, [])

  const refreshMe = useCallback(async () => {
    if (!getStoredToken()) {
      setUser(null)
      return
    }
    const { data } = await httpClient.get<{ resultado: AuthUser }>('/v1/auth/me')
    setUser(data.resultado)
  }, [])

  const value = useMemo(
    () => ({ token, user, login, logout, refreshMe }),
    [token, user, login, logout, refreshMe]
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) {
    throw new Error('useAuth debe usarse dentro de AuthProvider')
  }
  return ctx
}
