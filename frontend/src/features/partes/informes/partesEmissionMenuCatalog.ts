/**
 * Procesos Partes con botón Emitir en pantalla (GEN-15 host).
 * SoT menú emisión incorporada; diseñador = ruta aparte.
 */
export type PartesEmissionMenuEntry = {
  processCode: string
  routePath: string
  pageTestId: string
  emitTestId: string
  menuLabelKey: string
}

export const partesEmissionMenuEntries: PartesEmissionMenuEntry[] = [
  {
    processCode: 'partes.informes.consultaDetallada',
    routePath: '/partes/informes/consulta-detallada',
    pageTestId: 'partesConsultaDetalladaPage',
    emitTestId: 'partesConsultaDetalladaEmit',
    menuLabelKey: 'menu.partes.consultaDetallada',
  },
  {
    processCode: 'partes.informes.paqueteHoras',
    routePath: '/partes/informes/paquete-horas',
    pageTestId: 'partesPaqueteHorasPage',
    emitTestId: 'partesPaqueteHorasEmit',
    menuLabelKey: 'menu.partes.informePaqueteHoras',
  },
]
