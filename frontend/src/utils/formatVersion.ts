export function formatVersionLabel(version: string): string {
  return `v${version.replace(/^v/i, '')}`
}
