import { isNativeApp } from '@paqsuite/react-core'
import { getAuthSession } from './authSessionStore'
import { readStoredClienteCode } from './platformContext'

export const openInNewTabStorageKey = 'paqOpenInNewTab'

export function readOpenInNewTabPreference(): boolean {
  if (typeof localStorage === 'undefined') {
    return false
  }
  try {
    return localStorage.getItem(openInNewTabStorageKey) === '1'
  } catch {
    return false
  }
}

export function writeOpenInNewTabPreference(value: boolean): void {
  if (typeof localStorage === 'undefined') {
    return
  }
  try {
    localStorage.setItem(openInNewTabStorageKey, value ? '1' : '0')
  } catch {
    // ignore
  }
}

export function resolveProcessHref(
  routeName: string,
  location: { origin: string; search: string },
  clienteHint?: string | null,
): string {
  const url = new URL(routeName, location.origin)
  const currentCliente = new URLSearchParams(location.search).get('cliente')
  const cliente = (currentCliente || clienteHint || '').trim()
  if (cliente !== '' && !url.searchParams.get('cliente')) {
    url.searchParams.set('cliente', cliente)
  }
  return `${url.pathname}${url.search}`
}

export function openShellProcess(
  routeName: string,
  options: {
    openInNewTab: boolean
    navigate: (routeName: string) => void
  },
): void {
  if (!options.openInNewTab || isNativeApp()) {
    options.navigate(routeName)
    return
  }

  const cliente = getAuthSession()?.cliente || readStoredClienteCode()
  const href = resolveProcessHref(routeName, window.location, cliente)
  window.open(href, '_blank', 'noopener,noreferrer')
}
