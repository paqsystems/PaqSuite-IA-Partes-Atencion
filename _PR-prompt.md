# Pull Request Report

## Resumen

Smart Capture y DateBox de Partes interpretan fechas según el idioma de la sesión (`en` = MM/dd/yyyy; es/pt/fr/it = dd/MM/yyyy) y persisten ISO. Los fallos duros de turno o de Guardar con el modal abierto se muestran en un diálogo, no en el hilo ni en la grilla detrás.

## Contexto funcional

- Un turno con fecha `07/11/2026` en español es 7 de noviembre; en inglés es 11 de julio.
- Si el LLM o el extractor no completan el turno, el usuario ve `FormErrorModal` sobre el formulario.
- El período del proceso masivo sigue el mismo formato de fecha.

## SPEC / HU / TR relacionadas

- SPEC: SPEC-010, SPEC-013, SPEC-005
- HU: HU-010, HU-013
- TR: TR-010, TR-013
- Producto: `14-smart-capture.md` (D-SC-21 / D-SC-22)
- Jira: PIAPARTES-10, PIAPARTES-24, PIAPARTES-38, PIAPARTES-43, PIAPARTES-47, PIAPARTES-48, PIAPARTES-5

## Cambios realizados

### Backend

- `PartesLocaleDateParser` + `draftContext.locale` en el turno Smart Capture.
- Límite de tiempo PHP, envelope JSON 500 y extractor de respaldo si el LLM falla.
- Prompt LLM: interpretar barras según locale.

### Frontend

- `FormErrorModal`; `listError` / `formError` en carga diaria y paquetes.
- DateBox y período masivo con `resolvePartesDateDisplayFormat`.
- i18n `partes.smartCapture.turnError` en 5 locales.

### Base de datos

- Sin migraciones ni seeds.

### Tests

- PHPUnit: parser de fecha + feature de turno es/en.
- Vitest: draftContext locale, duración, masivo, turnos SC.

### Documentación

- SPEC/HU/TR-010/013, producto SC, manuales 004/013, catálogo de errores, operación diaria / SPEC-005.

### DevOps

- Sin cambios de `.env` ni pins SDK.

## Validaciones y tests ejecutados

- PHPUnit parser y feature de fecha (sesión previa, verdes).
- Vitest de duración/draft/masivo (sesión previa, verdes).

## Evidencia

- Parser: ISO passthrough; `en` MDY; resto DMY; día > 12 no se invierte.
- UI: error duro en modal; DateBox alineado al locale.

## Riesgos

- El DateBox GEN del Framework sigue dd/MM en los 5 idiomas; Partes especializa `en` en el host.
- Fechas ambiguas 01–12 dependen del idioma activo.

## Pendientes / follow-ups

- Ninguno de deploy (sin migrate/seed).

## Checklist para reviewer

- [ ] Turno en ES con `07/11/2026` aplica 2026-11-07.
- [ ] Turno en EN con `07/11/2026` aplica 2026-07-11.
- [ ] Fallo de turno abre modal sobre el formulario, no banner de grilla.
- [ ] DateBox de carga/consultas en EN muestra MM/dd/yyyy.

## Notas para QA

- Probar carga diaria y paquetes (supervisor) en ES y EN.
- Confirmar que la lista numerada de coincidencias y la fecha futura siguen en el hilo.

## Observaciones deploy

- Sin `migrate`, seed ni variables `.env` nuevas. Solo código + docs.
