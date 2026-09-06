import type { PaqSuiteDb, PaqSuiteTenancy } from '@paqsuite/react-core'

export type AuthUser = {
  id: number
  usuario: string
  email?: string
  locale?: string
}

export type AuthEmpresa = {
  id: number
  nombreEmpresa: string
  theme?: string | null
}

export type PartesSessionContext = {
  tipoFuncional: 'asistente' | 'cliente'
  asistenteId: number | null
  clienteId: number | null
  esSupervisor: boolean
  code: string
  nombre: string
  email: string | null
}

export type AuthSession = {
  token: string
  user: AuthUser
  firstLogin: boolean
  minutosWeb: number
  tenancy: PaqSuiteTenancy
  db: PaqSuiteDb
  empresas: AuthEmpresa[]
  activeCompanyId?: number
  /** Código X-Paq-Cliente con el que se emitió el token (Sanctum por BD). */
  cliente?: string
  partes?: PartesSessionContext
}

export type LoginSessionResultado = Omit<AuthSession, 'activeCompanyId'>
