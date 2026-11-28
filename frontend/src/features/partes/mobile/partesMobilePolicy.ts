import {
  createMobilePolicy,
  genMobileExclusions,
  type GenMobileExclusion,
} from '@paqsuite/react-core'

/**
 * Allowlist native Partes v1 (TR-007-update RN-MV1-TR-01).
 * Sin dashboard, chat, raíz `/partes` ni consulta legacy `/partes/consulta`.
 */
export const partesMobileAllowlist = [
  '/partes/carga',
  '/partes/informes/consulta-detallada',
  '/partes/informes/paquete-horas',
  '/change-password',
  '/select-empresa',
] as const

/** Exclusiones de producto (hijos de `/partes`, maestros y emisiones). El engine sigue siendo GEN. */
const partesWebOnlyExclusions: GenMobileExclusion[] = [
  {
    family: 'partesWebOnly',
    patterns: [
      '/archivos/partes',
      '/archivos/partes/*',
      '/partes/proceso-masivo',
      '/partes/proceso-masivo/*',
      '/partes/carga-diaria',
      '/partes/carga-diaria/*',
      '/partes/carga-paquetes',
      '/partes/carga-paquetes/*',
      '/partes/informes/consultas-agrupadas',
      '/partes/informes/consultas-agrupadas/*',
      '/emisiones',
      '/emisiones/*',
      '/admin',
      '/admin/*',
      '/parametros',
      '/parametros/*',
      '/tareas-programadas',
      '/tareas-programadas/*',
    ],
  },
]

export const partesMobilePolicy = createMobilePolicy({
  allowlistRouteNames: [...partesMobileAllowlist],
  exclusions: [...genMobileExclusions, ...partesWebOnlyExclusions],
})

export function isPartesMobileRouteAllowed(routeName: string): boolean {
  return partesMobilePolicy.isAllowed(routeName)
}
