import { resolveRequestUrl } from '@paqsuite/react-core'

/**
 * Reescribe rutas `/api/v1/*` con la base cacheada por `bootstrapApiBaseUrl`.
 * GEN-14 (`downloadTemplate`, `exportBatchErrors`) y emisiones usan `fetch` directo,
 * no `apiRequest` — sin esto en Vercel la descarga es `index.html` renombrado a `.xlsx`.
 */
export function rewriteApiFetchInput(
  input: RequestInfo | URL,
  url: string,
): { input: RequestInfo | URL; url: string } {
  const resolvedUrl = resolveRequestUrl(url)
  if (resolvedUrl === url) {
    return { input, url }
  }

  if (typeof input === 'string') {
    return { input: resolvedUrl, url: resolvedUrl }
  }

  if (input instanceof URL) {
    return { input: new URL(resolvedUrl), url: resolvedUrl }
  }

  return { input: new Request(resolvedUrl, input), url: resolvedUrl }
}
