# Prompts — nuevo hilo SDD oleada 12 (CHK-28)

Documento para **copiar y pegar** en un chat nuevo (Cursor agente). Incluye trazabilidad **Jira** y rutas markdown.

## Trazabilidad

| Rol | Clave / ruta |
|-----|----------------|
| Épica SPEC-015 | [PIAPARTES-53](https://paqsystems.atlassian.net/browse/PIAPARTES-53) |
| Story HU | [PIAPARTES-80](https://paqsystems.atlassian.net/browse/PIAPARTES-80) — `HU-015-update-n` |
| Task TR | [PIAPARTES-81](https://paqsystems.atlassian.net/browse/PIAPARTES-81) — `TR-015-update-n` |
| SPEC | `docs/05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-12-remove-detail-chk28-update.md` |
| HU | `docs/03-historias-usuario/100-SistemaPartes/HU-015-update-n-remove-detalle-chk28.md` |
| TR | `docs/04-tareas/100-SistemaPartes/TR-015-update-n-remove-detalle-chk28.md` |
| D1 | `docs/04-tareas/100-SistemaPartes/d1/D1-TR-015-update-n-chk28.md` |
| Matriz | `PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json` (CHK-28) |
| Guía smoke | `docs/06-operacion/guia-manual-prompts-checklist-chk-emision-ia.md` |

**GEN:** GEN-03 + GEN-15; apply sidecar Framework (regla 19). Primera acción **remove*** en whitelist producto.

---

## Prompt canónico Smart Capture (CHK-28)

| CHK | Prompt (español) | Acción esperada |
|-----|------------------|-----------------|
| **CHK-28** | `Quitá la columna observación del detalle` | `removeDetailField` → `{ "field": "observacion" }` |

### Alias heurística (v1 orientativo)

- `Sacá la columna observación`
- `Eliminá observación del detalle`
- `Quitá observaciones del detalle`

### Precondición smoke

Layout con columna **observación** visible en el band de detalle.

### Regresión rápida

`Sacá la agrupación por cliente` → `noMappedActions` (CHK-29 sigue fuera).

---

## Prompts dispatcher (chat agente) — en orden

Dispatcher: `.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`.

1. **A / A1 (hecho en hilo origen):** SPEC oleada 12 CHK-28 — veredicto **Apto** en metadatos SPEC.
2. **B / C / C1 (hecho en hilo origen):** HU-015-update-n + TR-015-update-n → **Especificado**.
3. **D1 (hecho en hilo origen):** `d1/D1-TR-015-update-n-chk28.md`
4. **D — implementar:**  
   `Ejecutá parte D del TR-015-update-n (oleada 12 CHK-28). Framework dx-reporting + host Partes. Sin commit hasta que pida.`
5. **E:** tests PHPUnit + dotnet + script eval oleada 12.
6. **F1 / F:** verificación + cierre formal; actualizar HU/TR a Finalizado y comentar Jira.

---

## Comandos útiles post-D

```powershell
# Sidecar (Framework)
cd c:\Programacion\PaqSuite-IA-FRAMEWORK\services\dx-reporting
dotnet build
dotnet test --filter LayoutDetailFieldRemove

# Host
cd c:\Programacion\PaqSuite-IA-Partes-Atencion\backend
php vendor/bin/phpunit --filter DesignLayout

cd c:\Programacion\PaqSuite-IA-Partes-Atencion\backend\scripts
.\eval-emission-ia-oleada12-chk28-apply.ps1
```

Reiniciar sidecar **:5055** tras rebuild Framework.