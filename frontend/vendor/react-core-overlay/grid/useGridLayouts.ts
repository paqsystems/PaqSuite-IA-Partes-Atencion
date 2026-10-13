import { useCallback, useEffect, useMemo, useState } from 'react';
import type { BuildPlatformHeadersInput } from '../../http/headers';
import {
  createGridLayout,
  deleteGridLayout,
  formatGridLayoutDisplayName,
  getActiveGridLayout,
  GRID_LAYOUT_ORIGINAL_VALUE,
  listGridLayouts,
  setActiveGridLayout,
  updateGridLayout,
  type GridLayoutListItem,
} from './gridLayoutsClient';
import { computeLayoutToolbarSaveFlags } from './layoutToolbarSaveFlags';

export type GridLayoutSelectItem = {
  value: string;
  layoutId: number | null;
  layoutName: string;
  isOwner: boolean;
  isSystem: boolean;
  displayName: string;
};

export type UseGridLayoutsArgs = {
  proceso: string;
  gridId: string;
  accessToken?: string | null;
  platform?: BuildPlatformHeadersInput;
  enabled?: boolean;
  ownerSuffix?: string;
  originalLabel?: string;
  captureState: () => unknown;
  applyState: (state: unknown | null) => void;
};

export type UseGridLayoutsResult = {
  items: GridLayoutSelectItem[];
  selectedValue: string;
  canSave: boolean;
  /** true ⇔ Guardar abre Guardar como (plantilla sistema), no PUT. */
  saveOpensSaveAs: boolean;
  canDelete: boolean;
  busy: boolean;
  errorMessage: string | null;
  selectLayout: (value: string) => Promise<void>;
  save: () => Promise<'saved' | 'saveAs' | 'error'>;
  saveAs: (layoutName: string) => Promise<boolean>;
  remove: () => Promise<boolean>;
  reload: () => Promise<void>;
};

function authOptions(accessToken?: string | null, platform?: BuildPlatformHeadersInput) {
  return {
    platform,
    headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : undefined,
  };
}

function toSelectItem(
  layout: GridLayoutListItem,
  ownerSuffix: string
): GridLayoutSelectItem {
  return {
    value: String(layout.id),
    layoutId: layout.id,
    layoutName: layout.layoutName,
    isOwner: layout.isOwner,
    isSystem: layout.isSystem,
    displayName: formatGridLayoutDisplayName(layout.layoutName, layout.isOwner, ownerSuffix),
  };
}

/**
 * Plantillas de grilla GEN-11: listado, activo, guardar / guardar como / eliminar.
 */
export function useGridLayouts({
  proceso,
  gridId,
  accessToken,
  platform,
  enabled = true,
  ownerSuffix = ' (*)',
  originalLabel = '<Original>',
  captureState,
  applyState,
}: UseGridLayoutsArgs): UseGridLayoutsResult {
  const [layouts, setLayouts] = useState<GridLayoutListItem[]>([]);
  const [selectedValue, setSelectedValue] = useState(GRID_LAYOUT_ORIGINAL_VALUE);
  const [busy, setBusy] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const requestOptions = useMemo(
    () => authOptions(accessToken, platform),
    [accessToken, platform?.cliente, platform?.companyId, platform?.tenancy]
  );

  const items = useMemo<GridLayoutSelectItem[]>(() => {
    const original: GridLayoutSelectItem = {
      value: GRID_LAYOUT_ORIGINAL_VALUE,
      layoutId: null,
      layoutName: originalLabel,
      isOwner: false,
      isSystem: true,
      displayName: originalLabel,
    };
    return [original, ...layouts.map((layout) => toSelectItem(layout, ownerSuffix))];
  }, [layouts, originalLabel, ownerSuffix]);

  const selected = items.find((item) => item.value === selectedValue) ?? items[0];
  const canDelete = Boolean(selected?.layoutId && selected.isOwner && !selected.isSystem);
  const { canSave, saveOpensSaveAs } = computeLayoutToolbarSaveFlags({
    layoutId: selected?.layoutId ?? null,
    isOwner: Boolean(selected?.isOwner),
    isSystem: selected?.isSystem,
  });

  const reload = useCallback(async () => {
    if (!enabled || !accessToken) {
      setLayouts([]);
      return;
    }
    setBusy(true);
    setErrorMessage(null);
    try {
      const listResult = await listGridLayouts(proceso, gridId, requestOptions);
      if (listResult.kind === 'ok') {
        const rawItems = listResult.envelope.resultado.items ?? [];
        setLayouts(
          rawItems.map((item) => ({
            id: item.id,
            layoutName: item.layoutName,
            isSystem: Boolean(item.isSystem),
            isOwner: item.isOwner !== undefined ? Boolean(item.isOwner) : !item.isSystem,
          }))
        );
      }

      const activeResult = await getActiveGridLayout(proceso, gridId, requestOptions);
      if (activeResult.kind === 'ok') {
        const active = activeResult.envelope.resultado;
        if (active.layoutId != null) {
          setSelectedValue(String(active.layoutId));
          if (active.stateJson != null) {
            applyState(active.stateJson);
          }
        } else {
          setSelectedValue(GRID_LAYOUT_ORIGINAL_VALUE);
          applyState(null);
        }
      }
    } catch (error: unknown) {
      setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
    } finally {
      setBusy(false);
    }
  }, [accessToken, applyState, enabled, gridId, proceso, requestOptions]);

  useEffect(() => {
    void reload();
  }, [reload]);

  const selectLayout = useCallback(
    async (value: string) => {
      if (!enabled || !accessToken) {
        return;
      }
      setBusy(true);
      setErrorMessage(null);
      try {
        const layoutId =
          value === GRID_LAYOUT_ORIGINAL_VALUE ? null : Number(value);
        await setActiveGridLayout({ proceso, gridId, layoutId }, requestOptions);
        if (layoutId == null) {
          setSelectedValue(GRID_LAYOUT_ORIGINAL_VALUE);
          applyState(null);
          return;
        }
        const activeResult = await getActiveGridLayout(proceso, gridId, requestOptions);
        if (activeResult.kind === 'ok') {
          const active = activeResult.envelope.resultado;
          setSelectedValue(String(active.layoutId ?? layoutId));
          applyState(active.stateJson ?? null);
        }
      } catch (error: unknown) {
        setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
      } finally {
        setBusy(false);
      }
    },
    [accessToken, applyState, enabled, gridId, proceso, requestOptions]
  );

  const saveAs = useCallback(
    async (layoutName: string) => {
      const name = layoutName.trim();
      if (!enabled || !accessToken || name === '') {
        return false;
      }
      setBusy(true);
      setErrorMessage(null);
      try {
        const stateJson = captureState();
        const result = await createGridLayout(
          { proceso, gridId, layoutName: name, stateJson },
          requestOptions
        );
        if (result.kind !== 'ok') {
          const respuesta =
            result.kind === 'envelopeError' ? result.envelope.respuesta : null;
          setErrorMessage(respuesta && respuesta !== '' ? respuesta : 'layouts.saveAsFailed');
          return false;
        }
        const created = result.envelope.resultado.item;
        setLayouts((prev) => {
          const next = prev.filter((item) => item.id !== created.id);
          next.push({
            id: created.id,
            layoutName: created.layoutName,
            isSystem: Boolean(created.isSystem),
            isOwner: true,
          });
          return next.sort((a, b) => a.layoutName.localeCompare(b.layoutName));
        });
        setSelectedValue(String(created.id));
        return true;
      } catch (error: unknown) {
        setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
        return false;
      } finally {
        setBusy(false);
      }
    },
    [accessToken, captureState, enabled, gridId, proceso, requestOptions]
  );

  const save = useCallback(async (): Promise<'saved' | 'saveAs' | 'error'> => {
    if (!selected?.layoutId || selected.isSystem || !selected.isOwner) {
      return 'saveAs';
    }
    if (!enabled || !accessToken) {
      return 'error';
    }
    setBusy(true);
    setErrorMessage(null);
    try {
      const stateJson = captureState();
      const result = await updateGridLayout(selected.layoutId, { stateJson }, requestOptions);
      if (result.kind !== 'ok') {
        setErrorMessage('layouts.saveFailed');
        return 'error';
      }
      return 'saved';
    } catch (error: unknown) {
      setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
      return 'error';
    } finally {
      setBusy(false);
    }
  }, [accessToken, captureState, enabled, requestOptions, selected]);

  const remove = useCallback(async () => {
    if (!canDelete || !selected?.layoutId || !accessToken) {
      return false;
    }
    setBusy(true);
    setErrorMessage(null);
    try {
      const result = await deleteGridLayout(selected.layoutId, requestOptions);
      if (result.kind !== 'ok') {
        setErrorMessage('layouts.deleteFailed');
        return false;
      }
      await setActiveGridLayout({ proceso, gridId, layoutId: null }, requestOptions);
      setLayouts((prev) => prev.filter((item) => item.id !== selected.layoutId));
      setSelectedValue(GRID_LAYOUT_ORIGINAL_VALUE);
      applyState(null);
      return true;
    } catch (error: unknown) {
      setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
      return false;
    } finally {
      setBusy(false);
    }
  }, [
    accessToken,
    applyState,
    canDelete,
    gridId,
    proceso,
    requestOptions,
    selected?.layoutId,
  ]);

  return {
    items,
    selectedValue,
    canSave,
    saveOpensSaveAs,
    canDelete,
    busy,
    errorMessage,
    selectLayout,
    save,
    saveAs,
    remove,
    reload,
  };
}
