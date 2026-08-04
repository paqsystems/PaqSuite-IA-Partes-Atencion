# SPEC-001-06 - Emisión

> Nota de adopción: este documento se replica como base para SistemaPartes. En este repositorio debe leerse como pendiente de programación, salvo que artefactos locales posteriores indiquen otra cosa.


| Campo | Valor |
|-------|--------|
| **HU relacionadas** | Ninguna en MVP portal (mail operativo en `SPEC-101`) |
| **Estado** | Especificado |
| **Revisión A1** | Documental — sin bloqueo MVP (2026-05-28) |

## Objetivo

Definir lineamientos iniciales del subsistema de emisión (mail e integración por archivo) y su arquitectura conceptual para futuras implementaciones.

## Estado de ejecución

Documental para este bloque inicial (preparación de especificaciones de implementación).

## Entradas requeridas

- Documentación de emisión en `docs/00-contexto/_mono/emision/`.
- Reglas de envío mail definidas en OpenSpec de producto.

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/emision/`

- `00-index-arquitectura-emision.md`
- `01-arquitectura-general-motor-emision.md`
- `02-contenedores-motor-emision.md`
- `03-flujo-general-emision.md`
- `04-estrategias-salida.md`
- `05-desktop-vs-mobile.md`
- `06-secuencia-envio-mail.md`
- `07-secuencia-archivo-integracion.md`
- `08-modelo-conceptual-subsistema-emision.md`
- `09-modelo-datos-subsistema-emision.md`
- `10-matriz-decisiones-funcionales-subsistema-emision.md`
- `11-c4-nivel2-sistema-completo-emision.md`
- `12-c4-subsistema-emision.md`
- `13-integraciones-externas-emision.md`
- `14-mapa-maestro-subsistema-emision.md`
- `99-historias-usuario.md`

## Alcance

- Arquitectura y flujo del motor de emisión.
- Estrategias de salida (mail/archivo) para MVP.
- Integraciones externas asociadas.

## Fuera de alcance

- Implementación completa del motor multiprotocolo.
- Automatizaciones no priorizadas para MVP.

## Entregables verificables

- Mapa de flujo de emisión MVP (mail/archivo) con entradas y salidas.
- Lista de decisiones técnicas pendientes para SPEC funcional de emisión.

## Criterios de aceptación medibles

- Flujo de emisión MVP documentado con secuencia clara.
- Dependencias externas identificadas y trazables.








