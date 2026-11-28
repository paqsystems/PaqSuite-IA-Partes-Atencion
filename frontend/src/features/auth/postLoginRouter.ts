import {
  isNativeApp,
  postLoginSelectorPath,
  resolveEmpresaGate,
  type EmpresaGateResult,
} from '@paqsuite/react-core'
import { resolveNativeMobileHomePath } from '../partes/mobile/resolveNativeMobileHomePath'
import type { AuthSession } from './authTypes'

export type PostLoginRoute =
  | '/change-password'
  | '/partes'
  | '/partes/carga'
  | '/partes/informes/consulta-detallada'
  | '/partes/informes/paquete-horas'
  | typeof postLoginSelectorPath
  | '/login'

export type PostLoginDecision = {
  route: PostLoginRoute
  gate: EmpresaGateResult | 'firstLogin'
  search?: string
}

export function resolvePostLoginRoute(session: AuthSession): PostLoginDecision {
  if (session.firstLogin) {
    return { route: '/change-password', gate: 'firstLogin' }
  }

  const gate = resolveEmpresaGate({
    tenancy: session.tenancy,
    empresasConPermiso: session.empresas.map((empresa) => ({ id: empresa.id })),
  })

  if (gate === 'blockedNoCompany') {
    return {
      route: '/login',
      gate,
      search: '?blocked=1',
    }
  }

  if (gate === 'selector') {
    return { route: postLoginSelectorPath, gate }
  }

  if (isNativeApp()) {
    return {
      route: resolveNativeMobileHomePath(session) as PostLoginRoute,
      gate,
    }
  }

  return { route: '/partes', gate }
}
