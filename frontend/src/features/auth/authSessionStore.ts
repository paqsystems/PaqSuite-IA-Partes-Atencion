import type { AuthSession, LoginSessionResultado } from './authTypes'

const storageKey = 'paqAuthSession'
const defaultMinutosWeb = 60

type StorageLike = Pick<Storage, 'getItem' | 'setItem' | 'removeItem'>

function getStorage(): StorageLike | null {
  if (typeof localStorage === 'undefined') {
    return null
  }
  return localStorage
}

function readRaw(): AuthSession | null {
  const storage = getStorage()
  if (!storage) {
    return null
  }

  const raw = storage.getItem(storageKey)
  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as AuthSession
  } catch {
    return null
  }
}

function writeRaw(session: AuthSession | null): void {
  const storage = getStorage()
  if (!storage) {
    return
  }

  if (!session) {
    storage.removeItem(storageKey)
    return
  }

  storage.setItem(storageKey, JSON.stringify(session))
}

export function getAuthSession(): AuthSession | null {
  return readRaw()
}

export function getAuthToken(): string | null {
  return readRaw()?.token ?? null
}

export function getMinutosWeb(): number {
  const value = readRaw()?.minutosWeb
  if (typeof value === 'number' && value > 0) {
    return value
  }
  return defaultMinutosWeb
}

export function saveLoginSession(resultado: LoginSessionResultado): AuthSession {
  const activeCompanyId =
    resultado.empresas.length === 1 ? resultado.empresas[0]?.id : undefined

  const session: AuthSession = {
    ...resultado,
    minutosWeb:
      typeof resultado.minutosWeb === 'number' && resultado.minutosWeb > 0
        ? resultado.minutosWeb
        : defaultMinutosWeb,
    activeCompanyId,
  }

  writeRaw(session)
  return session
}

export function patchAuthSession(partial: Partial<AuthSession>): AuthSession | null {
  const current = readRaw()
  if (!current) {
    return null
  }

  const next: AuthSession = { ...current, ...partial }
  writeRaw(next)
  return next
}

export function clearAuthSession(): void {
  writeRaw(null)
}

export function isAuthenticated(): boolean {
  return Boolean(getAuthToken())
}
