import { useRef, useState } from 'react';
import Popup from 'devextreme-react/popup';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import { DialogActionButton } from '../../ui/process/DialogActionButton';
import LoadPanel from 'devextreme-react/load-panel';
import { ExcelImportErrorsGrid } from './ExcelImportErrorsGrid';
import { downloadBlob } from './downloadBlob';
import type { UseExcelImportState } from './useExcelImport';

const excelAccept =
  '.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

export type ExcelImportModalProps = {
  visible: boolean;
  onClose: () => void;
  importState: UseExcelImportState;
  processCode: string;
  t?: (key: string) => string;
};

/**
 * Modal DX de carga / validación / errores / proceso (GEN-14). Controles 100 %
 * DevExtreme para acciones; la selección de archivo usa `<input type="file">` nativo
 * + drop zone manual (no FileUploader `useForm`/`dialogTrigger`: en Popup rompe el
 * explorador y el drop, y puede disparar submit/recarga de la página host).
 */
export function ExcelImportModal({
  visible,
  onClose,
  importState,
  processCode,
  t,
}: ExcelImportModalProps) {
  const translate = t ?? ((key: string) => key);
  const busy = importState.uiState === 'validating' || importState.uiState === 'processing';
  const [dropZoneActive, setDropZoneActive] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleExport = () => {
    void importState.exportErrors().then((blob) => {
      if (blob) {
        downloadBlob(blob, `${processCode}-errores.xlsx`);
      }
    });
  };

  const applyFile = (file: File | undefined | null) => {
    if (!file) {
      return;
    }
    importState.selectFile(file);
  };

  const openFileDialog = () => {
    fileInputRef.current?.click();
  };

  const selectedName = importState.selectedFileName;

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      showTitle
      title={translate('excelImport.modal.title')}
      width={720}
      height="auto"
      wrapperAttr={{ 'data-testid': 'excelImport.modal' }}
    >
      <LoadPanel visible={busy} />
      <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
        <p
          style={{
            margin: 0,
            fontSize: '0.85rem',
            fontWeight: 600,
            color: '#475569',
          }}
        >
          {translate('excelImport.dropZone.title')}
        </p>
        <div
          data-testid="excelImport.dropZone"
          onDragEnter={(event) => {
            event.preventDefault();
            event.stopPropagation();
            setDropZoneActive(true);
          }}
          onDragOver={(event) => {
            event.preventDefault();
            event.stopPropagation();
            setDropZoneActive(true);
          }}
          onDragLeave={(event) => {
            event.preventDefault();
            event.stopPropagation();
            setDropZoneActive(false);
          }}
          onDrop={(event) => {
            event.preventDefault();
            event.stopPropagation();
            setDropZoneActive(false);
            applyFile(event.dataTransfer.files?.[0] ?? null);
          }}
          style={{
            minHeight: 112,
            border: `2px dashed ${dropZoneActive ? '#2563eb' : '#94a3b8'}`,
            borderRadius: 8,
            padding: '16px 12px',
            background: dropZoneActive ? '#eff6ff' : '#f8fafc',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            gap: 8,
            textAlign: 'center',
            transition: 'background 0.15s ease, border-color 0.15s ease',
          }}
        >
          <i
            className="dx-icon dx-icon-xlsxfile"
            style={{ fontSize: 28, color: dropZoneActive ? '#2563eb' : '#64748b' }}
            aria-hidden
          />
          <strong style={{ fontSize: '0.95rem', color: '#334155' }}>
            {translate('excelImport.dropZone.primary')}
          </strong>
          <span style={{ fontSize: '0.8rem', color: '#64748b' }}>
            {translate('excelImport.dropZone.or')}
          </span>
          <Button
            text={translate('excelImport.selectFile')}
            stylingMode="outlined"
            type="normal"
            useSubmitBehavior={false}
            onClick={openFileDialog}
            elementAttr={{ 'data-testid': 'excelImport.selectFile' }}
          />
          <span style={{ fontSize: '0.75rem', color: '#64748b' }}>
            {translate('excelImport.dropZone.hint')}
          </span>
          {selectedName ? (
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: 8,
                flexWrap: 'wrap',
                justifyContent: 'center',
              }}
            >
              <span
                data-testid="excelImport.selectedFile"
                style={{ fontSize: '0.8rem', color: '#0f172a', fontWeight: 600 }}
              >
                {selectedName}
              </span>
              <Button
                text={translate('excelImport.clearFile')}
                stylingMode="text"
                type="normal"
                useSubmitBehavior={false}
                disabled={busy}
                onClick={() => {
                  importState.reset();
                  if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                  }
                }}
                elementAttr={{ 'data-testid': 'excelImport.clearFile' }}
              />
            </div>
          ) : null}
        </div>
        <input
          ref={fileInputRef}
          type="file"
          accept={excelAccept}
          style={{ display: 'none' }}
          data-testid="excelImport.fileInput"
          onChange={(event) => {
            applyFile(event.target.files?.[0] ?? null);
            event.target.value = '';
          }}
        />

        {importState.selectedFileName ? (
          <div data-testid="excelImport.sheetField">
            <SelectBox
              dataSource={importState.sheetNames}
              value={importState.selectedSheetName}
              disabled={busy || importState.sheetNames.length === 0}
              searchEnabled={importState.sheetNames.length > 5}
              placeholder={translate('excelImport.sheet.placeholder')}
              label={translate('excelImport.sheet.label')}
              labelMode="floating"
              onValueChanged={(event) => {
                importState.selectSheet(
                  typeof event.value === 'string' ? event.value : null,
                );
              }}
              elementAttr={{ 'data-testid': 'excelImport.sheet' }}
            />
          </div>
        ) : null}

        {importState.uiState === 'invalid' ? (
          <p data-testid="excelImport.structuralError" data-i18n-key="excelImport.error.4606">
            {translate('excelImport.error.4606')}
          </p>
        ) : null}

        {importState.errorKey ? (
          <p data-i18n-key={importState.errorKey}>{translate(importState.errorKey)}</p>
        ) : null}

        {importState.batch && importState.rowErrors.length > 0 ? (
          <ExcelImportErrorsGrid
            rowErrors={importState.rowErrors}
            onExport={handleExport}
            t={t}
          />
        ) : null}

        {importState.batch && importState.uiState === 'completed' ? (
          <div data-testid="excelImport.summary">
            {`${translate('excelImport.summary')}: ${importState.batch.processedRows ?? 0} / ${importState.batch.totalRows}`}
          </div>
        ) : null}

        <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end', flexWrap: 'wrap' }}>
          <DialogActionButton
            text={translate('excelImport.close')}
            useSubmitBehavior={false}
            onClick={onClose}
            elementAttr={{ 'data-testid': 'excelImport.close' }}
          />
          <DialogActionButton
            text={translate('excelImport.validate')}
            useSubmitBehavior={false}
            disabled={
              importState.uiState === 'idle' ||
              busy ||
              !importState.selectedSheetName
            }
            onClick={() => void importState.validate()}
            elementAttr={{ 'data-testid': 'excelImport.validate' }}
          />
          <DialogActionButton
            text={translate('excelImport.process')}
            type="default"
            stylingMode="contained"
            useSubmitBehavior={false}
            disabled={!importState.processEnabled || busy}
            hint={
              importState.processDisabledReasonKey
                ? translate(importState.processDisabledReasonKey)
                : undefined
            }
            onClick={() => void importState.process()}
            elementAttr={{ 'data-testid': 'excelImport.process' }}
          />
        </div>
      </div>
    </Popup>
  );
}
