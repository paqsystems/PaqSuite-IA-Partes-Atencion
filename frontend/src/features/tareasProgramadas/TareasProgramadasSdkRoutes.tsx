import { Navigate, NavLink, Outlet, useNavigate, useParams } from 'react-router-dom'
import {
  TaskAutomationsMonitorPage,
  TaskDefinitionFormPage,
  TaskDefinitionsPage,
  TaskExecutionsPage,
  TaskProcessesPage,
  normalizeLocale,
} from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useMemo } from 'react'
import { buildAuthPlatformHeaders } from '../auth/platformContext'
import { getAuthSession, getAuthToken } from '../auth/authSessionStore'
import { createTaskTranslator } from './tareasProgramadasI18n'

function taskRequestProps() {
  return {
    accessToken: getAuthToken(),
    platform: buildAuthPlatformHeaders(),
  }
}

function useTaskTranslator() {
  const { i18n } = useTranslation()
  const locale = normalizeLocale(i18n.language) ?? 'es'

  return useMemo(() => createTaskTranslator(locale), [locale])
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
  const translate = useTaskTranslator()

  return (
    <div data-testid="tareasProgramadas.procesos">
      <TaskProcessesPage {...taskRequestProps()} t={translate} />
    </div>
  )
}

export function TareasProgramadasSdkDefinicionesPage() {
  const navigate = useNavigate()
  const translate = useTaskTranslator()

  return (
    <div data-testid="tareasProgramadas.definiciones">
      <TaskDefinitionsPage
        {...taskRequestProps()}
        t={translate}
        onNew={() => navigate('/tareas-programadas/definiciones/nueva')}
        onEdit={(id) => navigate(`/tareas-programadas/definiciones/${id}/editar`)}
      />
    </div>
  )
}

export function TareasProgramadasSdkDefinicionFormPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()
  const translate = useTaskTranslator()
  const session = getAuthSession()
  const empresa = session?.empresas[0]
  const isEdit = id !== undefined

  return (
    <div data-testid="tareasProgramadas.definicion.form">
      <TaskDefinitionFormPage
        mode={isEdit ? 'edit' : 'create'}
        definitionId={isEdit ? Number(id) : undefined}
        tenancyMode="single"
        t={translate}
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
  const translate = useTaskTranslator()

  return (
    <div data-testid="tareasProgramadas.ejecuciones">
      <TaskAutomationsMonitorPage {...taskRequestProps()} t={translate} />
    </div>
  )
}

export function TareasProgramadasSdkHistorialPage() {
  const translate = useTaskTranslator()

  return (
    <div data-testid="tareasProgramadas.historial">
      <TaskExecutionsPage
        {...taskRequestProps()}
        t={translate}
      />
    </div>
  )
}
