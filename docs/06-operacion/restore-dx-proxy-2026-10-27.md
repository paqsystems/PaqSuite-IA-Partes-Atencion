# Punto de restauración — proxy DXXRD (2026-10-27)

Antes del fix de body/`Content-Type` en `DxReportingProxyController`.

## Tag Git (local)

```text
restore/dx-proxy-pre-fix-2026-10-27
```

Commit apuntado: `d99595e` (rama `v1.3.0-CLOUDSMITH`, working tree limpio al crear el tag).

## Volver al código anterior (solo el proxy y tests)

```powershell
cd C:\Programacion\PaqSuite-IA-Partes-Atencion
git -c safe.directory=C:/Programacion/PaqSuite-IA-Partes-Atencion checkout restore/dx-proxy-pre-fix-2026-10-27 -- backend/app/Http/Controllers/DxReportingProxyController.php backend/tests/Feature/DxReportingProxyTest.php
```

## Volver todo el repo a ese commit (cuidado: pierde cambios sin commitear)

```powershell
git -c safe.directory=C:/Programacion/PaqSuite-IA-Partes-Atencion reset --hard restore/dx-proxy-pre-fix-2026-10-27
```

El tag no se subió a `origin` por defecto. Para compartirlo: `git push origin restore/dx-proxy-pre-fix-2026-10-27`.
