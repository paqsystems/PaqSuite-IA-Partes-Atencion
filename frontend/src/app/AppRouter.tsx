import { useEffect } from 'react'
import { BrowserRouter, Navigate, Route, Routes, useNavigate } from 'react-router-dom'
import {
  registerAuthNavigator,
  restoreSessionOnBoot,
  setupUnauthorizedHandler,
} from '../features/auth/authBootstrap'
import { GuestOnly, RequireAuth } from '../features/auth/AuthGuards'
import { ChangePasswordPage } from '../features/auth/ChangePasswordPage'
import { ForgotPasswordPage } from '../features/auth/ForgotPasswordPage'
import { LoginPage } from '../features/auth/LoginPage'
import { ResetPasswordPage } from '../features/auth/ResetPasswordPage'
import {
  AuthenticatedShell,
  SelectEmpresaPage,
} from '../features/auth/ShellPage'
import { RequirePartesMaestrosAccess } from '../features/partes/RequirePartesMaestrosAccess'
import { RequirePartesMobilePolicy } from '../features/partes/RequirePartesMobilePolicy'
import {
  AsistentesPage,
  ClientesPage,
  TiposClientePage,
  TiposTareaPage,
} from '../features/partes/maestros/PartesMaestrosPages'
import { ClienteTiposTareaPage } from '../features/partes/maestros/ClienteTiposTareaPage'
import { CargaDiariaPage } from '../features/partes/carga/CargaDiariaPage'
import { ProcesoMasivoPage } from '../features/partes/masivo/ProcesoMasivoPage'
import { PartesDashboardPage } from '../features/partes/informes/PartesDashboardPage'
import {
  ConsultaDetalladaPage,
  ConsultasAgrupadasPage,
} from '../features/partes/informes/PartesConsultasPages'
import { ReportDesignerHostPage } from '../features/partes/informes/ReportDesignerHostPage'
import { PaqueteHorasPage } from '../features/partes/informes/PaqueteHorasPage'
import { ConsultaKardexMobilePage } from '../features/partes/mobile/ConsultaKardexMobilePage'
import { ParametrosGeneralesPage } from '../features/admin/ParametrosGeneralesPage'
import { UsuariosAdminPage } from '../features/admin/security/UsuariosAdminPage'
import { RolesAdminPage } from '../features/admin/security/RolesAdminPage'
import { RoleAtributosPage } from '../features/admin/security/RoleAtributosPage'
import { PermisosAdminPage } from '../features/admin/security/PermisosAdminPage'
import { EmpresasAdminPage } from '../features/admin/security/EmpresasAdminPage'
import { ChatAssistantHostPage } from '../features/chatAssistant/ChatAssistantHostPage'
import { isNativeApp } from '@paqsuite/react-core'

function AuthBootstrap() {
  const navigate = useNavigate()

  useEffect(() => {
    registerAuthNavigator(navigate)
    restoreSessionOnBoot()
    return setupUnauthorizedHandler()
  }, [navigate])

  return null
}

function ConsultaRoute() {
  if (isNativeApp()) {
    return <ConsultaKardexMobilePage />
  }
  return <Navigate to="/partes/informes/consulta-detallada" replace />
}

export function AppRouter() {
  return (
    <BrowserRouter>
      <AuthBootstrap />
      <Routes>
        <Route element={<GuestOnly />}>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/reset-password" element={<ResetPasswordPage />} />
        </Route>

        <Route element={<RequireAuth />}>
          <Route path="/change-password" element={<ChangePasswordPage />} />
          <Route path="/select-empresa" element={<SelectEmpresaPage />} />
          <Route element={<AuthenticatedShell />}>
            <Route element={<RequirePartesMobilePolicy />}>
              <Route path="/dashboard" element={<Navigate to="/partes" replace />} />
              <Route path="/shell" element={<Navigate to="/partes" replace />} />
              <Route path="/partes" element={<PartesDashboardPage />} />
              <Route path="/chat-assistant" element={<ChatAssistantHostPage />} />
              <Route path="/partes/consulta" element={<ConsultaRoute />} />
              <Route path="/partes/carga" element={<ConsultaRoute />} />
              <Route path="/partes/informes/paquete-horas" element={<PaqueteHorasPage />} />
              <Route path="/partes/informes/consulta-detallada" element={<ConsultaDetalladaPage />} />
              <Route path="/partes/informes/consultas-agrupadas" element={<ConsultasAgrupadasPage />} />
              <Route path="/emisiones/disenador" element={<ReportDesignerHostPage />} />
              <Route
                path="/parametros/:programa"
                element={<ParametrosGeneralesPage />}
              />
              <Route path="/admin/usuarios" element={<UsuariosAdminPage />} />
              <Route path="/admin/roles" element={<RolesAdminPage />} />
              <Route path="/admin/roles/:rolId/atributos" element={<RoleAtributosPage />} />
              <Route path="/admin/permisos" element={<PermisosAdminPage />} />
              <Route path="/admin/empresas" element={<EmpresasAdminPage />} />
              <Route element={<RequirePartesMaestrosAccess />}>
                <Route path="/archivos/partes/asistentes" element={<AsistentesPage />} />
                <Route path="/archivos/partes/clientes" element={<ClientesPage />} />
                <Route path="/archivos/partes/tipos-cliente" element={<TiposClientePage />} />
                <Route path="/archivos/partes/tipos-tarea" element={<TiposTareaPage />} />
                <Route path="/archivos/partes/cliente-tipos-tarea" element={<ClienteTiposTareaPage />} />
                <Route path="/partes/carga-diaria" element={<CargaDiariaPage />} />
                <Route path="/partes/proceso-masivo" element={<ProcesoMasivoPage />} />
              </Route>
            </Route>
          </Route>
        </Route>

        <Route path="/" element={<Navigate to="/login" replace />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
