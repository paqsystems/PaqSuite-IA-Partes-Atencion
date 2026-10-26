import type { Plugin } from 'vite'

/**
 * Vite/Rolldown convierte `import * as ko from 'knockout'` en named imports.
 * El UMD de knockout no los declara → `ko.observable` es undefined →
 * `(void 0) is not a function` al abrir el diseñador DX.
 */
export const KNOCKOUT_NAMED_EXPORTS = [
  'applyBindings',
  'applyBindingsToDescendants',
  'applyBindingsToNode',
  'bindingHandlers',
  'bindingProvider',
  'cleanNode',
  'components',
  'computed',
  'computedContext',
  'contextFor',
  'dataFor',
  'expressionRewriting',
  'extenders',
  'getBindingHandler',
  'ignoreDependencies',
  'isComputed',
  'isObservable',
  'isSubscribable',
  'isWritableObservable',
  'memoization',
  'nativeTemplateEngine',
  'observable',
  'observableArray',
  'onError',
  'options',
  'pureComputed',
  'removeNode',
  'renderTemplate',
  'setTemplateEngine',
  'subscribable',
  'tasks',
  'templateEngine',
  'templateSources',
  'toJS',
  'toJSON',
  'unwrap',
  'utils',
  'version',
  'virtualElements',
  'when',
] as const

function resolveCjsNamespace(globalName: 'ko' | 'ace'): string {
  return `const __cjs = (typeof module !== "undefined" && module.exports) ? module.exports : undefined;
const __ns = (__cjs && (typeof __cjs === "object" || typeof __cjs === "function")
  ? (__cjs.default && typeof __cjs.default === "object" ? __cjs.default : __cjs)
  : (typeof globalThis !== "undefined" ? globalThis.${globalName} : undefined));
if (typeof globalThis !== "undefined" && __ns) globalThis.${globalName} = __ns;
export default __ns;`
}

export function knockoutEsmExportSuffix(): string {
  const named = KNOCKOUT_NAMED_EXPORTS.map(
    (name) => `export const ${name} = __ns && __ns.${name};`,
  ).join('\n')
  return `${resolveCjsNamespace('ko')}\n${named}\n`
}

export function aceEsmExportSuffix(): string {
  return `${resolveCjsNamespace('ace')}\n`
}

export function transformReportingCjsModule(
  code: string,
  id: string,
): { code: string; map: null } | null {
  const file = id.split('?')[0].replaceAll('\\', '/')
  const isAce = file.includes('/ace-builds/') && file.endsWith('.js')
  const isKnockout = file.includes('/knockout/') && file.endsWith('.js')
  if (!isAce && !isKnockout) {
    return null
  }
  if (isAce) {
    if (/\bexport\s+default\b/.test(code)) {
      return null
    }
    return { code: `${code}\n${aceEsmExportSuffix()}`, map: null }
  }
  if (/\bexport\s+const\s+observable\b/.test(code)) {
    return null
  }
  return { code: `${code}\n${knockoutEsmExportSuffix()}`, map: null }
}

export function reportingCjsDefaultExportPlugin(): Plugin {
  return {
    name: 'reporting-cjs-default-export',
    enforce: 'pre',
    transform(code, id) {
      return transformReportingCjsModule(code, id)
    },
  }
}
