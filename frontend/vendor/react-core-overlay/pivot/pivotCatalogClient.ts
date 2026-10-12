import { apiRequest, type ApiClientResult, type ApiRequestOptions } from '../../http/apiClient';

export type PivotSummaryType = 'sum' | 'avg' | 'min' | 'max' | 'count';

export type PivotCampoDataType = 'number' | 'string' | 'date' | 'datetime' | 'boolean';

export type PivotCampoCatalogDto = {
  dataField: string;
  dataType: PivotCampoDataType;
  nombreVisible: string;
  summaryType?: PivotSummaryType;
  allowedSummaryTypes?: PivotSummaryType[];
  /** Área inicial DX. Si falta, el mapper solo pone `data` en numéricos. */
  area?: 'row' | 'column' | 'data' | 'filter';
  /** Fecha: default host/SDK `day`. El Field Chooser DX permite cambiar el intervalo. */
  groupInterval?: 'year' | 'quarter' | 'month' | 'day' | 'dayOfWeek' | 'hour';
};

export type PivotConsultaCatalogDto = {
  consultaId: string;
  proceso?: string;
  procedimientoHost?: string;
  mostrarGrillaYPivot: boolean;
  soloPivot: boolean;
  campos: PivotCampoCatalogDto[];
};

export const pivotsCatalogBasePath = '/api/v1/pivots/consultas';

type RequestOverrides = Pick<ApiRequestOptions, 'platform' | 'signal' | 'headers'>;

export function fetchPivotCatalog(
  consultaId: string,
  options: RequestOverrides = {}
): Promise<ApiClientResult<PivotConsultaCatalogDto>> {
  const encoded = encodeURIComponent(consultaId);

  return apiRequest<PivotConsultaCatalogDto>(`${pivotsCatalogBasePath}/${encoded}`, {
    method: 'GET',
    ...options,
  });
}
