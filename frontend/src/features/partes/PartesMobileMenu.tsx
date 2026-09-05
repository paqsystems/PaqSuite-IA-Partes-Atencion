import { useCallback, useEffect, useState } from 'react'
import {
  MobileMenuShell,
  apiRequest,
  isNativeApp,
  type BuildPlatformHeadersInput,
  type MenuNode,
  type MenuResultado,
  type MobileMenuShellItem,
} from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken } from '../auth/authSessionStore'
import { transformPartesMenuItems } from './PartesMenuSidebar'

type PartesMobileMenuProps = {
  platform: BuildPlatformHeadersInput
  onNavigate: (routeName: string) => void
  onItemsLoaded?: (items: MenuNode[]) => void
}

export function PartesMobileMenu({
  platform,
  onNavigate,
  onItemsLoaded,
}: PartesMobileMenuProps) {
  const { t } = useTranslation()
  const session = getAuthSession()
  const hideMaestros = session?.partes?.tipoFuncional === 'cliente'
  const hideMasivo = !session?.partes?.esSupervisor
  const [items, setItems] = useState<MenuNode[]>([])
  const token = getAuthToken()

  const transform = useCallback(
    (raw: MenuNode[]) =>
      transformPartesMenuItems(raw, {
        hideMaestros,
        hideMasivo,
        native: isNativeApp(),
      }),
    [hideMaestros, hideMasivo],
  )

  useEffect(() => {
    if (!token) {
      return
    }
    let cancelled = false
    void apiRequest<MenuResultado>('/api/v1/user/menu', {
      platform,
      headers: { Authorization: `Bearer ${token}` },
    }).then((payload) => {
      if (cancelled || payload.kind !== 'ok') {
        return
      }
      const next = transform(payload.envelope.resultado.items ?? [])
      setItems(next)
      onItemsLoaded?.(next)
    })
    return () => {
      cancelled = true
    }
  }, [token, platform, transform, onItemsLoaded])

  return (
    <MobileMenuShell
      items={items as MobileMenuShellItem[]}
      onNavigate={onNavigate}
      variant="list"
      t={(key) => t(key)}
    />
  )
}
