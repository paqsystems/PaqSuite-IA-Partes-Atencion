/**
 * Lista nombres de hojas de un .xlsx (OpenXML) sin parsear filas.
 * Usa ExcelJS (peer) solo para el workbook; el motor de staging sigue en BE.
 */
export async function listXlsxSheetNames(file: File): Promise<string[]> {
  const { Workbook } = await import('exceljs');
  const workbook = new Workbook();
  await workbook.xlsx.load(await file.arrayBuffer());
  return workbook.worksheets
    .map((sheet) => String(sheet.name ?? '').trim())
    .filter((name) => name !== '');
}
