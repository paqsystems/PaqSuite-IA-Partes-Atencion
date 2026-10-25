import { Navigate, NavLink, Outlet, useNavigate, useParams } from 'react-router-dom'
import {
  TaskAutomationsMonitorPage,
  TaskDefinitionFormPage,
  TaskDefinitionsPage,
  TaskExecutionsPage,
  TaskProcessesPage,
} from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { buildAuthPlatformHeaders } from '../auth/platformContext'
import { getAuthSession, getAuthToken } from '../auth/authSessionStore'

function taskRequestProps() {
  return {
    accessToken: getAuthToken(),
    platform: buildAuthPlatformHeaders(),
  }
}

export function TareasProgramadasLayout() {
  const { t } = useTranslation()

  return (
    <div className="tareas-programadas">
      <h1 className="tareas-programadas__title">{t('tareasProgramadas.title')}</h1>
      <nav
        className="tareas-programadas__nav"
        aria-label={t('tareasProgramadas.title')}
      >
        <NavLink to="/tareas-programadas/procesos" className="tareas-programadas__tab">
          {t('tareasProgramadas.tabs.procesos')}
        </NavLink>
        <NavLink to="/tareas-programadas/definiciones" className="tareas-programadas__tab">
          {t('tareasProgramadas.tabs.definiciones')}
        </NavLink>
        <NavLink to="/tareas-programadas/ejecuciones" className="tareas-programadas__tab">
          {t('tareasProgramadas.tabs.ejecuciones')}
        </NavLink>
        <NavLink to="/tareas-programadas/historial" className="tareas-programadas__tab">
          {t('tareasProgramadas.tabs.historial')}
        </NavLink>
      </nav>
      <div className="tareas-programadas__body">
        <Outlet />
      </div>
    </div>
  )
}

export function TareasProgramadasIndexRedirect() {
  return <Navigate to="definiciones" replace />
}

export function TaskDefinicionesSection() {
  return <Outlet />
}

export function TareasProgramadasSdkProcesosPage() {
  return (
    <div data-testid="tareasProgramadas.procesos">
      <TaskProcessesPage {...taskRequestProps()} />
    </div>
  )
}

export function TareasProgramadasSdkDefinicionesPage() {
  const navigate = useNavigate()

  return (
    <div data-testid="tareasProgramadas.definiciones">
      <TaskDefinitionsPage
        {...taskRequestProps()}
        onNew={() => navigate('/tareas-programadas/definiciones/nueva')}
        onEdit={(id) => navigate(`/tareas-programadas/definiciones/${id}/editar`)}
      />
    </div>
  )
}

export function TareasProgramadasSdkDefinicionFormPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()
  const session = getAuthSession()
  const empresa = session?.empresas[0]
  const isEdit = id !== undefined

  return (
    <div data-testid="tareasProgramadas.definicion.form">
      <TaskDefinitionFormPage
        mode={isEdit ? 'edit' : 'create'}
        definitionId={isEdit ? Number(id) : undefined}
        tenancyMode="single"
        empresas={
          empresa
            ? [{ id: empresa.id, nombreEmpresa: empresa.nombreEmpresa }]
            : []
        }
        {...taskRequestProps()}
        onCancel={() => navigate('/tareas-programadas/definiciones')}
        onSaved={() => navigate('/tareas-programadas/definiciones')}
      />
    </div>
  )
}

export function TareasProgramadasSdkEjecucionesPage() {
  return (
    <div data-testid="tareasProgramadas.ejecuciones">
      <TaskAutomationsMonitorPage {...taskRequestProps()} />
    </div>
  )
}

export function TareasProgramadasSdkHistorialPage() {
  const session = getAuthSession()

  return (
    <div data-testid="tareasProgramadas.historial">
      <TaskExecutionsPage
        {...taskRequestProps()}
      />
    </div>
  )
}
