import {
  createMobilePolicy,
  genMobileExclusions,
  type GenMobileExclusion,
} from '@paqsuite/react-core'

/**
 * Allowlist native Partes (TR-007 RN-TR-02).
 * `/partes` es prefijo GEN: los hijos web-only se cortan con `partesWebOnlyExclusions`.
 */
export const partesMobileAllowlist = [
  '/partes',
  '/partes/consulta',
  '/partes/carga',
  '/partes/informes/paquete-horas',
  '/chat-assistant',
  '/dashboard',
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
      '/partes/informes/consulta-detallada',
      '/partes/informes/consulta-detallada/*',
      '/partes/informes/consultas-agrupadas',
      '/partes/informes/consultas-agrupadas/*',
      '/emisiones',
      '/emisiones/*',
      '/admin',
      '/admin/*',
      '/parametros',
      '/parametros/*',
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
