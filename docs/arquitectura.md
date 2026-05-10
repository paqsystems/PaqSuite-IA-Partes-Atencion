# Arquitectura del Sistema – MVP

## Visión general
Arquitectura clásica de tres capas, optimizada para un MVP. Un solo código base de frontend sirve tanto web como mobile.

```
Clientes (Web + Mobile)
         |
         v
Backend API (REST)
         |
         v
    Base de Datos
```

---

## Componentes

### Frontend
- **Web:** Aplicación SPA (React + Vite)
- **Mobile:** Misma aplicación empaquetada como app nativa iOS/Android mediante Capacitor
- Un solo código base; Capacitor copia el build (`dist/`) a proyectos nativos
- Funciones:
  - Login
  - Registro de tareas
  - Listado y resumen
- Comunicación vía API REST (ambos clientes)
- Diseño responsivo: breakpoints Mobile (< 768px), Tablet (768–1024px), Desktop (> 1024px)

---

### Backend
- API REST
- Responsabilidades:
  - Autenticación
  - Validaciones de negocio
  - Persistencia de datos
- Control de acceso por usuario autenticado

---

### Base de datos
- Relacional
- Entidades normalizadas
- Índices en claves foráneas y fechas

---

## Decisiones clave
- No se usan microservicios.
- No se usan colas ni eventos.
- Se prioriza claridad sobre escalabilidad futura.
- Web y mobile comparten código; Capacitor empaqueta el mismo build para iOS/Android.
- **API agnóstica de cliente:** La API REST sirve a todos los frontends (web, mobile, etc.); un solo contrato compartido.

---

## Referencias
- **Frontend mobile:** `docs/mobile/README.md` – Build, comandos y configuración Capacitor
- **Migración PAQSuite:** `MIGRACION-PAQSUITE-IA.md` – Plan de migración y stack completo
