import { useCallback, useEffect, useMemo, useState } from 'react'
import {
  DashboardContainer,
  EmpresaSelectorPage,
  LanguageSelector,
  MenuAuthProvider,
  ShellLayout,
  UserAvatarMenu,
  apiRequest,
  changeActiveEmpresa,
  getGuestLocale,
  isNativeApp,
  normalizeLocale,
  useMenuPresentation,
  type LocaleCode,
  type MenuNode,
  type PaqSuiteEnvelope,
} from '@paqsuite/react-core'
import Button from 'devextreme-react/button'
import { useTranslation } from 'react-i18next'
import { Outlet, useNavigate } from 'react-router-dom'
import { applyGuestLocale } from '../../i18n/i18n'
import { logoutAndRedirect } from './authBootstrap'
import { getAuthSession, patchAuthSession } from './authSessionStore'
import { PartesProfilePanel } from './PartesProfilePanel'
import { buildAuthPlatformHeaders } from './platformContext'
import { PartesMenuSidebar } from '../partes/PartesMenuSidebar'
import { partesMobilePolicy } from '../partes/mobile/partesMobilePolicy'
import { LlmPreferencesModalHost } from '../llmCredentials/LlmPreferencesModalHost'
import { resolveAuthMessage } from './authMessages'
import {
  applyDevExtremeTheme,
  getActiveEmpresaThemeFromSession,
} from '../../theme/devExtremeThemeSwitcher'

type HealthResultado = {
  serviceName: string
  status: string
  tenancy: string
  db: string
}

function resolveSessionLocale(): LocaleCode {
  const sessionLocale = normalizeLocale(getAuthSession()?.user.locale)
  if (sessionLocale) {
    return sessionLocale
  }
  return normalizeLocale(getGuestLocale()) ?? 'es'
}

const appVersion = import.meta.env.VITE_APP_VERSION ?? '0.1.0'

export function AuthenticatedShell() {
  const { t } = useTranslation()
  const session = getAuthSession()
  const navigate = useNavigate()
  const platform = useMemo(() => buildAuthPlatformHeaders(), [
    session?.activeCompanyId,
    session?.empresas[0]?.id,
    session?.tenancy,
  ])
  const [locale, setLocale] = useState<LocaleCode>(resolveSessionLocale)
  const [openInNewTab, setOpenInNewTab] = useState(false)
  const [profileVisible, setProfileVisible] = useState(false)
  const [llmPreferencesVisible, setLlmPreferencesVisible] = useState(false)
  const [menuItems, setMenuItems] = useState<MenuNode[]>([])
  const menuPresentation = useMenuPresentation(session?.user.id ?? null, 'partes')
  const menuAuthValue = useMemo(
    () => ({ items: menuItems, setItems: setMenuItems }),
    [menuItems]
  )
  const handleMenuItemsLoaded = useCallback((items: MenuNode[]) => {
    setMenuItems(items)
  }, [])

  const empresaNombre = useMemo(() => {
    const activeId = session?.activeCompanyId
    const match = session?.empresas.find((empresa) => empresa.id === activeId)
    return match?.nombreEmpresa ?? session?.empresas[0]?.nombreEmpresa ?? '—'
  }, [session])

  const showChangeEmpresa =
    session?.tenancy === 'multi' && (session?.empresas?.length ?? 0) > 1

  useEffect(() => {
    if (!session) {
      return
    }
    void applyDevExtremeTheme(
      getActiveEmpresaThemeFromSession({
        activeCompanyId: session.activeCompanyId,
        empresas: session.empresas,
      }),
      { reloadOnGroupChange: false }
    )
  }, [session?.activeCompanyId, session?.empresas])

  async function handleLocaleChange(next: LocaleCode) {
    setLocale(next)
    await applyGuestLocale(next)
    const token = session?.token
    if (!token) {
      return
    }
    await apiRequest('/api/v1/user/preferences', {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify({ locale: next }),
      platform,
    })
    patchAuthSession({
      user: { ...(session?.user ?? { id: 0, usuario: '' }), locale: next },
    })
  }

  async function handleOpenInNewTabChange(value: boolean) {
    setOpenInNewTab(value)
    const token = session?.token
    if (!token) {
      return
    }
    await apiRequest('/api/v1/user/preferences', {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify({ openInNewTab: value }),
      platform,
    })
  }

  return (
    <MenuAuthProvider value={menuAuthValue}>
      <ShellLayout
        brand={<strong>{t('shell.productName')}</strong>}
        sidebarVisible={menuPresentation.sidebarVisible}
        onToggleSidebarVisible={menuPresentation.toggleSidebarVisible}
        languageSlot={
          <LanguageSelector
            locale={locale}
            onLocaleChange={(next) => void handleLocaleChange(next)}
          />
        }
        avatarSlot={
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <Button
              text={t('shell.profile')}
              stylingMode="text"
              elementAttr={{ 'data-testid': 'partesProfileOpen' }}
              onClick={() => setProfileVisible(true)}
            />
            <UserAvatarMenu
              userName={session?.user.usuario ?? '—'}
              isNativeApp={isNativeApp()}
              showPreferences
              showChat
              showHelp={false}
              showOpenInNewTab={!isNativeApp()}
              showChangeEmpresa={showChangeEmpresa}
              openInNewTab={openInNewTab}
              onOpenInNewTabChange={(value) => void handleOpenInNewTabChange(value)}
              onPreferences={() => setLlmPreferencesVisible(true)}
              onChat={() => navigate('/chat-assistant')}
              onChangePassword={() => navigate('/change-password')}
              onLogout={() => {
                void logoutAndRedirect()
              }}
              onChangeEmpresa={
                showChangeEmpresa ? () => navigate('/select-empresa') : undefined
              }
              labels={{
                changePassword: t('avatar.changePassword'),
                changeEmpresa: t('avatar.changeEmpresa'),
                logout: t('avatar.logout'),
                openInNewTab: t('avatar.openInNewTab'),
                preferences: t('avatar.preferences'),
                chat: t('avatar.chat'),
              }}
            />
          </div>
        }
        menuSlot={
          <PartesMenuSidebar
            platform={platform}
            presentation={menuPresentation}
            onItemsLoaded={handleMenuItemsLoaded}
            onNavigate={(routeName) => {
              if (isNativeApp() && !partesMobilePolicy.isAllowed(routeName)) {
                navigate('/partes')
                return
              }
              navigate(routeName)
            }}
          />
        }
        footer={{
          brandLabel: t('shell.footer.brand'),
          user: `${t('shell.session')}: ${session?.user.usuario ?? '—'}`,
          empresa: empresaNombre,
          version: t('shell.footer.version', { version: appVersion }),
        }}
      >
        <Outlet />
      </ShellLayout>
      <PartesProfilePanel
        visible={profileVisible}
        onHiding={() => setProfileVisible(false)}
        partes={session?.partes}
        loginUsuario={session?.user.usuario ?? ''}
      />
      <LlmPreferencesModalHost
        visible={llmPreferencesVisible}
        onClose={() => setLlmPreferencesVisible(false)}
      />
    </MenuAuthProvider>
  )
}

export function DashboardPage() {
  const { t } = useTranslation()
  const [health, setHealth] = useState<PaqSuiteEnvelope<HealthResultado> | null>(null)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)

  async function loadHealth() {
    setLoading(true)
    setErrorMessage(null)
    try {
      const payload = await apiRequest<HealthResultado>('/api/v1/health', {
        platform: buildAuthPlatformHeaders(),
      })
      if (payload.kind === 'ok') {
        setHealth(payload.envelope)
      } else if (payload.kind === 'envelopeError') {
        setErrorMessage(resolveAuthMessage(payload.envelope.respuesta))
      } else {
        setErrorMessage(resolveAuthMessage(payload.i18nKey))
      }
    } catch (error: unknown) {
      setErrorMessage(
        error instanceof Error ? error.message : resolveAuthMessage('infra.transport'),
      )
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void loadHealth()
  }, [])

  return (
    <div data-testid="shellDashboardPage">
      <DashboardContainer
        loading={loading}
        error={errorMessage}
        emptyTitle={t('homeWelcome')}
        emptyDescription={t('dashboard.title')}
        onRefresh={() => void loadHealth()}
        widgets={
          health
            ? [
                {
                  id: 'health',
                  title: 'Health',
                  render: () => (
                    <pre data-testid="shellHealthPayload">{JSON.stringify(health, null, 2)}</pre>
                  ),
                },
              ]
            : []
        }
      />
    </div>
  )
}

export function SelectEmpresaPage() {
  const { t } = useTranslation()
  const session = getAuthSession()
  const navigate = useNavigate()

  return (
    <main className="authPage" data-testid="shellSelectorPage">
      <EmpresaSelectorPage
        empresas={session?.empresas ?? []}
        onSelect={(id) => {
          changeActiveEmpresa({
            companyId: id,
            onNavigate: (path) => {
              const companyId = Number(id)
              patchAuthSession({ activeCompanyId: companyId })
              const nextSession = getAuthSession()
              void applyDevExtremeTheme(
                getActiveEmpresaThemeFromSession({
                  activeCompanyId: companyId,
                  empresas: nextSession?.empresas ?? session?.empresas ?? [],
                }),
                { reloadOnGroupChange: true }
              )
              navigate(path)
            },
          })
        }}
        blockedMessage={t('shell.blockedNoCompany')}
      />
    </main>
  )
}
