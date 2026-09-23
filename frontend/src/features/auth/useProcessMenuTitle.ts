import {
  findMenuProcessByRoute,
  resolveMenuCaption,
  useMenuAuth,
} from '@paqsuite/react-core'
import { useMemo } from 'react'
import { useLocation, useOutletContext } from 'react-router-dom'
import type { AppTranslateFn, ShellOutletContext } from './shellI18n'

/**
 * Título de proceso alineado al menú localizado (GEN-02 / GEN-07).
 */
export function useProcessMenuTitle(
  fallbackTitle: string,
  routeName?: string,
  appTranslate?: AppTranslateFn,
): string {
  const location = useLocation()
  const auth = useMenuAuth()
  const outlet = useOutletContext<ShellOutletContext | undefined>()
  const t = appTranslate ?? outlet?.t
  const route = routeName ?? location.pathname

  return useMemo(() => {
    if (!t || !auth || auth.items.length === 0) {
      return fallbackTitle
    }
    const processNode = findMenuProcessByRoute(auth.items, route)
    if (!processNode) {
      return fallbackTitle
    }
    return resolveMenuCaption(processNode, (key) => t(key, processNode.text))
  }, [auth, fallbackTitle, route, t])
}
