import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { BuildPlatformHeadersInput } from '../../http/headers';
import {
  createPivotLayout,
  deletePivotLayout,
  formatPivotLayoutDisplayName,
  getActivePivotLayout,
  listPivotLayouts,
  PIVOT_LAYOUT_ORIGINAL_VALUE,
  setActivePivotLayout,
  updatePivotLayout,
  type PivotLayoutListItem,
} from './pivotLayoutsClient';
import { computeLayoutToolbarSaveFlags } from '../grid/layoutToolbarSaveFlags';

export type PivotLayoutSelectItem = {
  value: string;
  layoutId: number | null;
  layoutName: string;
  isOwner: boolean;
  displayName: string;
};

export type UsePivotLayoutsArgs = {
  consultaId: string;
  accessToken?: string | null;
  platform?: BuildPlatformHeadersInput;
  enabled?: boolean;
  ownerSuffix?: string;
  originalLabel?: string;
  captureState: () => string;
  applyState: (stateJson: string | null) => void;
};

export type UsePivotLayoutsResult = {
  items: PivotLayoutSelectItem[];
  selectedValue: string;
  canSave: boolean;
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

function toSelectItem(layout: PivotLayoutListItem, ownerSuffix: string): PivotLayoutSelectItem {
  return {
    value: String(layout.id),
    layoutId: layout.id,
    layoutName: layout.layoutName,
    isOwner: layout.isOwner,
    displayName: formatPivotLayoutDisplayName(layout.layoutName, layout.isOwner, ownerSuffix),
  };
}

/**
 * Plantillas de pivot GEN-12: listado, activo, guardar / guardar como / eliminar.
 */
export function usePivotLayouts({
  consultaId,
  accessToken,
  platform,
  enabled = true,
  ownerSuffix = ' (*)',
  originalLabel = '<Original>',
  captureState,
  applyState,
}: UsePivotLayoutsArgs): UsePivotLayoutsResult {
  const [layouts, setLayouts] = useState<PivotLayoutListItem[]>([]);
  const [selectedValue, setSelectedValue] = useState(PIVOT_LAYOUT_ORIGINAL_VALUE);
  const [busy, setBusy] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const appliedInitialStateRef = useRef(false);

  const requestOptions = useMemo(
    () => authOptions(accessToken, platform),
    [accessToken, platform?.cliente, platform?.companyId, platform?.tenancy]
  );

  const items = useMemo<PivotLayoutSelectItem[]>(() => {
    const original: PivotLayoutSelectItem = {
      value: PIVOT_LAYOUT_ORIGINAL_VALUE,
      layoutId: null,
      layoutName: originalLabel,
      isOwner: false,
      displayName: originalLabel,
    };
    return [original, ...layouts.map((layout) => toSelectItem(layout, ownerSuffix))];
  }, [layouts, originalLabel, ownerSuffix]);

  const selected = items.find((item) => item.value === selectedValue) ?? items[0];
  const canDelete = Boolean(selected?.layoutId && selected.isOwner);
  const { canSave, saveOpensSaveAs } = computeLayoutToolbarSaveFlags({
    layoutId: selected?.layoutId ?? null,
    isOwner: Boolean(selected?.isOwner),
  });

  const reload = useCallback(async () => {
    if (!enabled || !accessToken || !consultaId) {
      setLayouts([]);
      return;
    }
    setBusy(true);
    setErrorMessage(null);
    try {
      const listResult = await listPivotLayouts(consultaId, requestOptions);
      if (listResult.kind === 'ok') {
        setLayouts(listResult.envelope.resultado.items ?? []);
      }

      const activeResult = await getActivePivotLayout(consultaId, requestOptions);
      if (activeResult.kind === 'ok') {
        const active = activeResult.envelope.resultado;
        if (active.layoutId != null) {
          setSelectedValue(String(active.layoutId));
          if (!appliedInitialStateRef.current) {
            appliedInitialStateRef.current = true;
            applyState(active.stateJson);
          }
        } else {
          setSelectedValue(PIVOT_LAYOUT_ORIGINAL_VALUE);
          appliedInitialStateRef.current = true;
        }
      }
    } catch (error: unknown) {
      setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
    } finally {
      setBusy(false);
    }
  }, [accessToken, applyState, consultaId, enabled, requestOptions]);

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
        const layoutId = value === PIVOT_LAYOUT_ORIGINAL_VALUE ? null : Number(value);
        await setActivePivotLayout({ consultaId, layoutId }, requestOptions);
        if (layoutId == null) {
          setSelectedValue(PIVOT_LAYOUT_ORIGINAL_VALUE);
          applyState(null);
          return;
        }
        const activeResult = await getActivePivotLayout(consultaId, requestOptions);
        if (activeResult.kind === 'ok') {
          const active = activeResult.envelope.resultado;
          setSelectedValue(String(active.layoutId ?? layoutId));
          applyState(active.stateJson);
        }
      } catch (error: unknown) {
        setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
      } finally {
        setBusy(false);
      }
    },
    [accessToken, applyState, consultaId, enabled, requestOptions]
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
        const result = await createPivotLayout(
          { consultaId, layoutName: name, stateJson },
          requestOptions
        );
        if (result.kind !== 'ok') {
          const respuesta =
            result.kind === 'envelopeError' ? result.envelope.respuesta : null;
          setErrorMessage(respuesta && respuesta !== '' ? respuesta : 'layouts.saveAsFailed');
          return false;
        }
        const created = result.envelope.resultado;
        if (created.layoutId == null) {
          setErrorMessage('layouts.saveAsFailed');
          return false;
        }
        setLayouts((prev) => {
          const next = prev.filter((item) => item.id !== created.layoutId);
          next.push({
            id: created.layoutId,
            layoutName: created.layoutName ?? name,
            isOwner: true,
            updatedAt: new Date().toISOString(),
          });
          return next.sort((a, b) => a.layoutName.localeCompare(b.layoutName));
        });
        setSelectedValue(String(created.layoutId));
        return true;
      } catch (error: unknown) {
        setErrorMessage(error instanceof Error ? error.message : 'layouts.error');
        return false;
      } finally {
        setBusy(false);
      }
    },
    [accessToken, captureState, consultaId, enabled, requestOptions]
  );

  const save = useCallback(async (): Promise<'saved' | 'saveAs' | 'error'> => {
    if (!selected?.layoutId || !selected.isOwner) {
      return 'saveAs';
    }
    if (!enabled || !accessToken) {
      return 'error';
    }
    setBusy(true);
    setErrorMessage(null);
    try {
      const stateJson = captureState();
      const result = await updatePivotLayout(selected.layoutId, { stateJson }, requestOptions);
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
      const result = await deletePivotLayout(selected.layoutId, requestOptions);
      if (result.kind !== 'ok') {
        setErrorMessage('layouts.deleteFailed');
        return false;
      }
      await setActivePivotLayout({ consultaId, layoutId: null }, requestOptions);
      setLayouts((prev) => prev.filter((item) => item.id !== selected.layoutId));
      setSelectedValue(PIVOT_LAYOUT_ORIGINAL_VALUE);
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
    consultaId,
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
