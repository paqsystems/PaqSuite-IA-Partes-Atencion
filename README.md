# PaqSuite IA — Partes de Atención

Monorepo en modo **MONO** (una sola BD, sin `X-Company-Id`). Scaffold según `docs/_base/00-inicio-arquitectura.md` y `prompts/scaffold-fullstack-inicio-proyecto.md`.

## Requisitos

- PHP 8.1+, Composer, extensiones Laravel habituales
- Node.js 20+ y npm
- MySQL (producción) o SQLite (desarrollo rápido; el `backend/.env` actual usa SQLite)

## Versión de producto

El archivo **`VERSION`** en la raíz alimenta `VITE_APP_VERSION` en el frontend vía `frontend/.env`.

## Backend (`backend/`)

```bash
cd backend
composer install
cp .env.example .env   # o mantener .env con SQLite / MySQL
php artisan key:generate
# SQLite: crear database/database.sqlite vacío
php artisan migrate
php artisan db:seed
php artisan l5-swagger:generate
php artisan serve --port=8000
```

- API: `http://127.0.0.1:8000/api/v1/...`
- OpenAPI UI: `http://127.0.0.1:8000/api/documentation`
- Usuario semilla: `dev@example.com` / `password`

```bash
php artisan test
```

## Frontend (`frontend/`)

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

- SPA: `http://localhost:3000` (proxy `/api` → backend 8000)

```bash
npm run test
npm run test:e2e    # Vite en puerto 3101 dedicado
npm run test:all
npm run build:release
```

### Licencia DevExtreme

Definir `VITE_DEVEXTREME_LICENSE` en `frontend/.env` para builds de release (ver documentación DevExpress).

## Notas

- El frontend generado usa **Vite 8** y **React 19** (plantilla actual de `create-vite`); el lineamiento corporativo cita React 18 / Vite 5 — alinear versiones si el estándar lo exige.
- Sin commit automático: gestionar cambios en git según tu flujo.
