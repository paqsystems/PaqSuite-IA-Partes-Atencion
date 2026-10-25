---
specId: SPEC-013
titulo: Carga de paquetes de horas
estado: publicado
moduloCodigo: Partes
ultimaActualizacion: 2026-10-21
openSpec: docs/05-open-spec/100-SistemaPartes/SPEC-013-carga-paquetes-horas.md
procesoErrores: P12
---

# Carga de paquetes de horas

> Manual de usuario — corpus Asistente IA. No incluir detalles de implementación, infraestructura ni codificación.

## Resumen

Permite al **supervisor** registrar **compras o créditos de horas** de un cliente (movimientos que no son tareas del día). Esos registros alimentan el informe **Paquete de horas**. No se mezcla con la carga diaria.

## Funcionamiento

### Alta y edición

1. Entrá a **Partes → Carga de paquetes** (solo supervisor).
2. El listado abre con el **mes calendario en curso**. Podés filtrar por fechas y cliente.
3. **Nuevo** o **Editar** abre un formulario: cliente, fecha, duración (horas enteras de 8 a 240) y observación.
4. Guardá. Si la fecha es futura, el sistema pide confirmación.
5. Eliminar pide confirmación.

### Captura inteligente en el formulario

En el mismo modal podés describir la compra (cliente, fecha, duración, observación). Completa campos del formulario; **no** es el **Asistente IA** del menú avatar (ese chat no crea paquetes).

## Particularidades

- Solo **supervisor**. Asistentes y clientes no ven el menú ni pueden operar.
- No está disponible en la **app móvil** (v1).
- La duración debe ser una cantidad entera de horas entre 8 y 240, inclusive.
- El propietario del registro queda en el supervisor que lo carga; no se cambia al editar.

## Condiciones de uso

- Perfil supervisor Partes y permiso de menú.
- Cliente usable (activo) para altas o cambios de cliente.
- Credencial de modelo (Preferencias) si usás captura inteligente.
