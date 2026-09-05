# Smoke mobile Partes (TR-007)

Humo emulador Android. Backend en el host (p. ej. puerto `8010` / `8088`).

## Build + sync

```powershell
cd frontend
npm run build:mobile
npx cap add android   # primera vez
npx cap add ios       # primera vez (Mac)
npx cap sync
npx cap open android
```

## Config API en emulador

Engranaje GEN (`mobileConfigOpen`): `http://10.0.2.2:<puerto>/api/v1`  
En este repo el BE local de Partes suele estar en **`:8010`** (no `:8000`, a menudo es otro producto).  
`capacitor.config.ts` lleva `server.cleartext` + `android.allowMixedContent` para que el WebView `https://localhost` pueda pegarle a HTTP de lab.  
Login: empresa (`loginTenant`) + usuario + contraseña.

## Checklist

1. Health OK + save en `MobileConfigPanel`.
2. Login → dashboard (`dashboardContainer` / `partesDashboardRoot`).
3. Kardex (`consultaKardexList`): día actual; alta; editar; cerrada RO.
4. Informe paquete horas: totales + kardex + gráfico barra (`partesPaqueteChart`).
5. Menú native (`mobileMenuShell`) sin ABM/masivo/pivot.
6. Chat avatar in-app (`partesChatAssistantHost`).
