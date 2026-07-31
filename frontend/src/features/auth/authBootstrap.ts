import { setApiUnauthorizedHandler } from '@paqsuite/react-core'
import type { AuthSession, PartesSessionContext } from './authTypes'
import { meRequest } from './authApi'
import {
  clearAuthSession,
  getAuthToken,
  isAuthenticated,
  patchAuthSession,
} from './authSessionStore'
import { startIdleSession, stopIdleSession } from './idleSessionService'
import { applyDevExtremeTheme } from '../../theme/devExtremeThemeSwitcher'
import { EMPRESA_THEME_DEFAULT } from '../admin/security/empresaThemeCatalog'

type NavigateFn = (path: string) => void

let navigateRef: NavigateFn | undefined

export function registerAuthNavigator(navigate: NavigateFn): void {
  navigateRef = navigate
}

export function expireSession(reason: 'idle' | 'unauthorized' = 'idle'): void {
  if (!isAuthenticated()) {
    return
  }

  stopIdleSession()
  clearAuthSession()
  void applyDevExtremeTheme(EMPRESA_THEME_DEFAULT)
  navigateRef?.(`/login?expired=1&reason=${reason}`)
}

export function bootstrapAuthenticatedSession(session: AuthSession): void {
  startIdleSession({
    minutosWeb: session.minutosWeb,
    onExpire: () => expireSession('idle'),
  })
}

export function restoreSessionOnBoot(): void {
  if (!getAuthToken()) {
    return
  }

  startIdleSession({
    onExpire: () => expireSession('idle'),
  })

  void refreshPartesProfileFromMe()
}

async function refreshPartesProfileFromMe(): Promise<void> {
  const result = await meRequest()
  if (!result) {
    return
  }

  if (result.kind === 'envelopeError') {
    const respuesta = result.envelope.respuesta
    if (
      result.httpStatus === 403 &&
      (respuesta === 'partes.auth.noFunctionalProfile' ||
        respuesta === 'partes.auth.inconsistentProfile')
    ) {
      expireSession('unauthorized')
    }
    return
  }

  if (result.kind === 'ok') {
    const partes = result.envelope.resultado.partes as PartesSessionContext | undefined
    if (partes) {
      patchAuthSession({ partes })
    }
  }
}

export function setupUnauthorizedHandler(): () => void {
  setApiUnauthorizedHandler(() => {
    expireSession('unauthorized')
  })

  return () => {
    setApiUnauthorizedHandler(undefined)
    stopIdleSession()
  }
}

export async function logoutAndRedirect(): Promise<void> {
  stopIdleSession()
  clearAuthSession()
  void applyDevExtremeTheme(EMPRESA_THEME_DEFAULT)
  navigateRef?.('/login')
}
