import {
  forwardRef,
  useCallback,
  useEffect,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from 'react';
import Button from 'devextreme-react/button';
import PivotGrid, {
  Export,
  FieldChooser,
  FieldPanel,
  type PivotGridRef,
} from 'devextreme-react/pivot-grid';
import type dxPivotGrid from 'devextreme/ui/pivot_grid';
import type { ContextMenuPreparingEvent } from 'devextreme/ui/pivot_grid';
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import { isNativeApp } from '../../mobile/isNativeApp';
import type { PivotConsultaCatalogDto } from './pivotCatalogClient';
import { mapCatalogToPivotFields } from './mapCatalogToPivotFields';
import { applyPivotState, capturePivotState } from './pivotState';
import { PivotRefreshButton } from './PivotRefreshButton';
import { PivotExportButton } from './PivotExportButton';
import {
  exportPivotGridExcel,
  hasPivotExportableData,
  type PivotExportMode,
} from './exportPivotGridExcel';
import { getPivotLocalizedUiTexts } from './pivotLocalizedUiTexts';

export type PivotGridBlockProps = {
  consultaId: string;
  catalog: PivotConsultaCatalogDto;
  rows: Record<string, unknown>[];
  locale?: string;
  loading?: boolean;
  /** Default `true`. `false` oculta el botón (C1-12-F06). */
  exportEnabled?: boolean;
  onRefresh?: () => void;
  toolbarLayoutsSlot?: ReactNode;
  toolbarPlantillaSlot?: ReactNode;
  /** Si se pasa, reemplaza el auto-append de `PivotExportButton`. */
  toolbarExportSlot?: ReactNode;
  toolbarExtras?: ReactNode;
  expandAllLabel?: string;
  collapseAllLabel?: string;
  fieldChooserTitle?: string;
  exportTitle?: string;
  exportBasicLabel?: string;
  exportFormattedLabel?: string;
  exportNoDataHint?: string;
  /** Controles a la izquierda (p. ej. toggle Grilla|Pivot). */
  leadingSlot?: ReactNode;
  /** Botón textual «Selector de campos» en toolbar. Default `false` (queda el ícono DX). */
  fieldChooserButtonEnabled?: boolean;
  fieldChooserButtonLabel?: string;
};

export type PivotGridBlockHandle = {
  refresh: () => void;
  applyState: (stateJson: string | null) => void;
  captureState: () => string;
};

function getPivotInstance(ref: PivotGridRef | null): dxPivotGrid | undefined {
  if (!ref) {
    return undefined;
  }
  if (typeof ref.instance === 'function') {
    return ref.instance();
  }
  return undefined;
}

const PivotGridBlockInner = forwardRef<PivotGridBlockHandle, PivotGridBlockProps>(
  function PivotGridBlockInner(
    {
      consultaId,
      catalog,
      rows,
      locale,
      loading,
      exportEnabled = true,
      onRefresh,
      toolbarLayoutsSlot,
      toolbarPlantillaSlot,
      toolbarExportSlot,
      toolbarExtras,
      expandAllLabel = 'Expandir todo',
      collapseAllLabel = 'Contraer todo',
      fieldChooserTitle,
      exportTitle,
      exportBasicLabel,
      exportFormattedLabel,
      exportNoDataHint,
      leadingSlot,
      fieldChooserButtonEnabled = false,
      fieldChooserButtonLabel,
    },
    ref
  ) {
    const remountKey = `${consultaId}-${locale ?? 'default'}`;
    const pivotRef = useRef<PivotGridRef>(null);
    const catalogFieldsKeyRef = useRef<string>('');
    const [pendingState, setPendingState] = useState<Record<string, unknown> | null>(null);
    const canExport = hasPivotExportableData(rows, loading);

    const pivotUiTexts = useMemo(
      () => getPivotLocalizedUiTexts(locale ?? 'es'),
      [locale]
    );

    const handleExport = useCallback(
      (mode: PivotExportMode) => {
        const instance = getPivotInstance(pivotRef.current);
        if (!instance || !canExport) {
          return;
        }
        void exportPivotGridExcel({
          consultaId,
          component: instance,
          mode,
          locale,
        });
      },
      [canExport, consultaId, locale]
    );

    const handleOpenFieldChooser = useCallback(() => {
      const instance = getPivotInstance(pivotRef.current);
      const popup = instance?.getFieldChooserPopup?.();
      popup?.show?.();
    }, []);

    const fields = useMemo(
      () =>
        mapCatalogToPivotFields(catalog, (campo) => campo.nombreVisible, {
          locale: locale ?? 'es',
        }),
      [catalog, locale]
    );

    const catalogFieldsKey = useMemo(
      () =>
        catalog.campos
          .map((campo) => `${campo.dataField}:${campo.area ?? ''}:${campo.dataType}`)
          .join('|'),
      [catalog]
    );

    const dataSource = useMemo(() => {
      const instance = getPivotInstance(pivotRef.current);
      const liveFields = instance?.getDataSource()?.fields();
      const reuseLiveFields =
        catalogFieldsKeyRef.current === catalogFieldsKey &&
        Array.isArray(liveFields) &&
        liveFields.length > 0;
      catalogFieldsKeyRef.current = catalogFieldsKey;
      return new PivotGridDataSource({
        fields: reuseLiveFields ? liveFields : fields,
        store: rows,
        retrieveFields: false,
      });
    }, [catalogFieldsKey, fields, rows]);

    const handleContextMenuPreparing = useCallback(
      (e: ContextMenuPreparingEvent) => {
        if (e.area !== 'row' && e.area !== 'column') {
          return;
        }

        const items = e.items ?? [];
        const dataSourceInstance = e.component.getDataSource();
        const areaFields = dataSourceInstance.getAreaFields(e.area, true);

        items.push(
          {
            text: expandAllLabel,
            onItemClick: () => {
              for (const field of areaFields) {
                if (field.dataField) {
                  dataSourceInstance.expandAll(field.dataField);
                }
              }
            },
          },
          {
            text: collapseAllLabel,
            onItemClick: () => {
              for (const field of areaFields) {
                if (field.dataField) {
                  dataSourceInstance.collapseAll(field.dataField);
                }
              }
            },
          }
        );

        e.items = items;
      },
      [collapseAllLabel, expandAllLabel]
    );

    useEffect(() => {
      if (!pendingState) {
        return;
      }
      const instance = getPivotInstance(pivotRef.current);
      const dataSource = instance?.getDataSource();
      if (!dataSource) {
        return;
      }
      dataSource.state(pendingState);
      setPendingState(null);
    }, [pendingState, rows]);

    useImperativeHandle(
      ref,
      () => ({
        refresh: () => onRefresh?.(),
        applyState: (stateJson) => {
          const next = applyPivotState(null, stateJson);
          setPendingState(next);
          if (next === null) {
            const instance = getPivotInstance(pivotRef.current);
            instance?.getDataSource()?.state({});
          }
        },
        captureState: () => {
          const instance = getPivotInstance(pivotRef.current);
          const dataSource = instance?.getDataSource();
          if (!dataSource) {
            return capturePivotState({ fields: [] });
          }
          const state = (dataSource.state() ?? {}) as Record<string, unknown>;
          return capturePivotState(state);
        },
      }),
      [onRefresh]
    );

    return (
      <div data-testid="pivot.gridBlock" data-pivot-instance={remountKey}>
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            gap: 8,
            marginBottom: 8,
            flexWrap: 'wrap',
          }}
        >
          {leadingSlot ? (
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>{leadingSlot}</div>
          ) : null}
          <div
            style={{
              marginLeft: 'auto',
              display: 'flex',
              alignItems: 'center',
              gap: 8,
              flexWrap: 'wrap',
            }}
          >
            {onRefresh ? (
              <PivotRefreshButton onRefresh={onRefresh} disabled={loading} />
            ) : null}
            {toolbarLayoutsSlot}
            {toolbarPlantillaSlot}
            {toolbarExportSlot ??
              (exportEnabled ? (
                <PivotExportButton
                  onExport={handleExport}
                  disabled={!canExport}
                  title={exportTitle}
                  basicLabel={exportBasicLabel}
                  formattedLabel={exportFormattedLabel}
                  noDataHint={exportNoDataHint}
                />
              ) : null)}
            {fieldChooserButtonEnabled ? (
              <Button
                text={fieldChooserButtonLabel ?? pivotUiTexts.fieldChooserTitle}
                stylingMode="outlined"
                onClick={handleOpenFieldChooser}
                elementAttr={{ 'data-testid': 'pivot.fieldChooserButton' }}
              />
            ) : null}
            {toolbarExtras}
          </div>
        </div>
        <PivotGrid
          key={remountKey}
          ref={pivotRef}
          dataSource={dataSource as never}
          allowSorting
          allowFiltering
          allowExpandAll
          showBorders
          showColumnGrandTotals
          showRowGrandTotals
          showColumnTotals
          showRowTotals
          onContextMenuPreparing={handleContextMenuPreparing}
          elementAttr={{ 'data-testid': `pivot.grid-${consultaId}` }}
        >
          <FieldPanel
            visible
            showColumnFields
            showDataFields
            showFilterFields
            showRowFields
            allowFieldDragging
            texts={pivotUiTexts.fieldPanel}
          />
          <FieldChooser
            enabled
            title={fieldChooserTitle ?? pivotUiTexts.fieldChooserTitle}
            texts={pivotUiTexts.fieldChooser}
          />
          <Export enabled={false} />
        </PivotGrid>
        <span hidden data-testid="pivot.fieldChooser" />
      </div>
    );
  }
);

/**
 * Bloque PivotGrid GEN-12. Native: null (exclusión Capacitor).
 */
export const PivotGridBlock = forwardRef<PivotGridBlockHandle, PivotGridBlockProps>(
  function PivotGridBlock(props, ref) {
    if (isNativeApp()) {
      return null;
    }
    return <PivotGridBlockInner {...props} ref={ref} />;
  }
);
