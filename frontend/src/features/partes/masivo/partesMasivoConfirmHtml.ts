export type MasivoConfirmLine = {
  label: string
  value: string
}

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

/** Diálogo confirm DX: un ítem por línea (messageHtml). Sin listado de IDs: el total va en «Registros». */
export function buildMasivoConfirmHtml(lines: MasivoConfirmLine[]): string {
  const rows = lines
    .map(
      (line) =>
        `<div><strong>${escapeHtml(line.label)}:</strong> ${escapeHtml(line.value)}</div>`
    )
    .join('')

  return `<div style="display:flex;flex-direction:column;gap:6px;text-align:left;line-height:1.45">${rows}</div>`
}

export function buildMasivoSelectAllConfirmHtml(totalIds: number): string {
  return buildMasivoConfirmHtml([
    { label: 'Registros a seleccionar', value: String(totalIds) },
    { label: 'Confirmación', value: '¿Desea continuar?' },
  ])
}
