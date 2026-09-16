import { normalizeClienteCode } from '@paqsuite/react-core'
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

export function saveLoginSession(
  resultado: LoginSessionResultado,
  cliente?: string,
): AuthSession {
  const activeCompanyId =
    resultado.empresas.length === 1 ? resultado.empresas[0]?.id : undefined

  const session: AuthSession = {
    ...resultado,
    minutosWeb:
      typeof resultado.minutosWeb === 'number' && resultado.minutosWeb > 0
        ? resultado.minutosWeb
        : defaultMinutosWeb,
    activeCompanyId,
    cliente: normalizeClienteCode(cliente) || undefined,
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

/**
 * El token Sanctum vive en la BD del tenant. Si el X-Paq-Cliente actual
 * no coincide con el de la sesión, hay que forzar re-login (mismo
 * vercel.app sirve PAQ/DEMO/ESTUDIOGB).
 *
 * @returns true si la sesión se invalidó
 */
export function invalidateSessionIfClienteMismatch(currentCliente: string): boolean {
  const session = readRaw()
  if (!session?.token) {
    return false
  }

  const expected = normalizeClienteCode(session.cliente)
  const actual = normalizeClienteCode(currentCliente)

  // Sesiones previas sin `cliente`: un solo re-login al entrar con tenant.
  if (!expected) {
    if (actual) {
      clearAuthSession()
      return true
    }
    return false
  }

  if (actual && expected !== actual) {
    clearAuthSession()
    return true
  }

  return false
}
