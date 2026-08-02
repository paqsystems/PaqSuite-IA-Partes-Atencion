# Checklist de temas definidos del modulo

# Fecha : 18/07/2026

## Base del modulo

- [x] Proposito del modulo
- [x] Problema que resuelve
- [x] Objetivos del MVP
- [x] Flujo E2E prioritario
- [x] Alcance funcional del modulo
- [x] Limites del MVP
- [x] Relacion del modulo con el framework comun
- [x] Valor esperado del modulo

## Actores e identidad

- [x] Asistente como actor interno principal
- [x] Supervisor como variante funcional del asistente
- [x] Cliente como actor de consulta
- [x] Administracion tecnica separada del dominio del modulo
- [x] Identidad autenticable comun del sistema
- [x] Identidad funcional propia del modulo
- [x] Resolucion funcional del usuario autenticado
- [x] Exclusividad esperada entre asistente y cliente
- [x] Perfil visible del usuario autenticado
- [x] Clientes con acceso habilitado
- [x] Delimitacion del universo funcional visible

## Dominio y entidades

- [x] Registro de tarea como entidad central
- [x] Asistente
- [x] Cliente
- [x] Tipo de cliente
- [x] Tipo de tarea
- [x] Asignacion cliente - tipo de tarea
- [x] Relaciones principales del dominio
- [x] Integridad funcional esperada
- [x] Glosario funcional minimo

## Reglas conceptuales del dominio

- [x] `supervisor` como capacidad funcional del modulo
- [x] `isGenerico` como disponibilidad general del tipo de tarea
- [x] `isDefault` como tipo de tarea unico y generico
- [x] `sinCargo` como marca funcional
- [x] `presencial` como marca funcional
- [x] `cerrado` como estado funcional del registro
- [x] Tarea cerrada con valor historico y sin edicion normal

## Maestros y catalogos

- [x] Maestro de clientes
- [x] Atributos ERP en clientes (`erp_cliente`, `erp_articulo`, max 15)
- [x] Maestro de asistentes
- [x] Catalogo de tipos de cliente
- [x] Catalogo de tipos de tarea
- [x] Regla general de inhabilitacion en maestros
- [x] Regla de asignacion de tipos de tarea a cliente
- [x] Tipos genericos disponibles para todos los clientes
- [x] Tipos no genericos por asignacion
- [x] Clientes con acceso como capacidad adicional
- [x] Criterio de bajas o eliminaciones sin romper trazabilidad

## Operacion del modulo

- [x] Carga diaria desde grilla previamente filtrada
- [x] Insercion de tareas desde grilla
- [x] Edicion de tareas desde grilla
- [x] Eliminacion de tareas desde grilla
- [x] Carga individual para usuarios no cliente en mobile
- [x] Propiedad de la tarea
- [x] Seleccion de asistente propietario por supervisor
- [x] Reglas funcionales de captura
- [x] Fecha de proceso o fecha funcional de la tarea
- [x] Regla de cliente
- [x] Regla de tipo de tarea
- [x] Regla de duracion *(tramo param GRAL default 15; UI selector `hh:mm`; grilla hh:mm + sumatoria horas decimales; paginación DX en carga web)*
- [x] Regla de observacion
- [x] Regla de marcas funcionales *(Sin cargo / Presencial visibles en grilla de carga diaria)*
- [x] Presentacion grilla carga: Cliente y Tipo = descripcion (codigos opcionales en chooser)
- [x] Estado `cerrado`
- [x] Atributo `es_tarea` en registro (true = tarea de carga; false = compra de horas)
- [x] Carga diaria: solo lista `es_tarea = true`; al grabar asigna `es_tarea = true`
- [x] Edicion y eliminacion segun rol y estado
- [x] Complemento por IA en la carga *(definido como **fuera del MVP**; evolutivo — ver checklist Evolución)*
- [x] Importación de partes desde Excel bajo Carga de Partes *(definición cerrada D-IMP-01…09: `13-importacion-partes-excel.md`; SPEC/HU/TR-009 C+C1 2026-08-02; pendiente D1)*

## Supervision

- [x] Supervision como ampliacion del mismo dominio
- [x] Vista sobre tareas de terceros
- [x] Proceso masivo sobre tareas
- [x] Proceso masivo: solo lista `es_tarea = true`
- [x] Seleccion explicita de registros *(incluye seleccionar todos del conjunto)*
- [x] Filtros previos del proceso masivo *(periodo obligatorio; cliente / asistente / estado opcionales)*
- [x] Validacion de seleccion no vacia
- [x] Reflejo inmediato del resultado
- [x] Criterio de atomicidad del procesamiento
- [x] Grilla masivo con capacidades framework: fila de filtro bajo titulos
- [x] Grilla masivo: totalizacion
- [x] Grilla masivo: seleccion de campos (column chooser)
- [x] Grilla masivo: plantillas de layout
- [x] Grilla masivo: exportacion a Excel
- [x] Accion masiva: actualizar **tipo de tarea** (prioridad)
- [x] Accion masiva: actualizar **sin cargo** (prioridad)
- [x] Accion masiva factible: **presencial**
- [x] Accion masiva factible: **asistente**
- [x] Accion masiva factible: **fecha**
- [x] Excluidos del masivo: cliente, minutos/duracion, descripcion
- [x] Accion masiva: cerrar / reabrir (`cerrado`)
- [x] Confirmacion clara de atributo(s) y valor antes de aplicar

## Consultas y analitica

- [x] Consulta detallada de tareas *(duración UI `hh:mm`; columnas Erp Cliente / Erp Articulo)*
- [x] Consultas agrupadas *(totales tiempo UI `hh:mm`; atributos ERP en grilla/pivot)*
- [x] Paquete de horas *(cuenta corriente: saldo inicial + columna Saldo; no filtra por `es_tarea`; pivot sin Saldo; mismos atributos que carga detallada)*
- [x] Consulta detallada / agrupadas / dashboard: solo `es_tarea = true`
- [x] Restricciones por perfil en consultas
- [x] Resultados vacios con acciones deshabilitadas
- [x] Reutilizacion de grillas del framework
- [x] Reutilizacion de layouts del framework
- [x] Reutilizacion de exportaciones cuando se habiliten
- [x] Uso eventual de pivots segun el proceso *(web: Pivot en **todo** Informe con grilla; dashboard no; mobile no)*
- [x] Exportacion como evolucion inmediata

## Dashboard

- [x] Dashboard como puerta de entrada analitica
- [x] Lectura del dashboard segun rol
- [x] Indicadores minimos del dashboard
- [x] Graficos simples cuando aporten valor
- [x] Refresco automatico del dashboard
- [x] Refresco manual del dashboard
- [x] Periodo inicial en mes calendario del sistema
- [x] Modificacion manual del periodo
- [x] Persistencia de filtros por rol durante refrescos

## Navegacion del modulo

- [x] Integracion del modulo con `pq_menus`
- [x] Navegacion segun framework PaqSuite
- [x] Seccion Inicio
- [x] Seccion Archivos
- [x] Seccion Partes
- [x] Seccion Informes
- [x] Criterio de no hardcodear el menu del modulo

## Mobile

- [x] Documento conceptual mobile propio
- [x] Pantalla inicial mobile
- [x] Configuracion de URL en mobile
- [x] Login mobile con empresa
- [x] Login mobile con usuario
- [x] Login mobile con contrasena
- [x] Dashboard mobile
- [x] Consulta de partes mobile
- [x] Carga individual de partes mobile
- [x] Chatbot IA como complemento en mobile
- [x] Grillas mobile en formato Kardex
- [x] Filtros por universo funcional en mobile
- [x] Acciones agregar/editar/eliminar para asistente en mobile
- [x] Informe Paquete de Horas en mobile
- [x] Exclusiones mobile: ABMs
- [x] Exclusiones mobile: pivots
- [x] Exclusiones mobile: cargas masivas
- [x] Exclusiones mobile: operaciones con Excel
- [x] Exclusiones mobile: informes impresos

## Modelo de datos tecnico

- [x] Documento tecnico separado del resto de la narrativa conceptual
- [x] Convenciones generales del esquema
- [x] Tabla `PQ_PARTES_USUARIOS`
- [x] Tabla `PQ_PARTES_CLIENTES`
- [x] Tabla `PQ_PARTES_TIPOS_CLIENTE`
- [x] Tabla `PQ_PARTES_TIPOS_TAREA`
- [x] Tabla `PQ_PARTES_CLIENTE_TIPO_TAREA`
- [x] Tabla `PQ_PARTES_REGISTRO_TAREA`
- [x] Relaciones tecnicas esperadas
- [x] Observaciones tecnicas relevantes
- [x] DDL consolidado

## Evolucion ya identificada

- [x] Cuenta corriente de horas como proceso distinto
- [x] Auditoria de partes como linea futura
- [x] Costeo de horas como linea futura
- [x] Informe de facturacion como linea futura
- [x] Carga de tareas con IA como evolucion complementaria *(distinta del chat documental del avatar)*
- [x] Asistente IA ayuda/chat documental desde menú avatar *(definición D-AI-01…04 + implementación TR-008; F1 2026-08-01 Aprobado con observaciones — Pendiente de Revisión)*
- [x] Carga cronometrada como evolucion posible
- [x] Carga masiva desde Excel *(definición de producto cerrada: `13-importacion-partes-excel.md`; distinta de auditoría Excel+mails)*

## Temas aun abiertos

- [x] Aplicacion exacta del campo `empresa` en mobile *(MVP: = `X-Paq-Cliente`; SPEC-007 §4.3)*
- [x] Frecuencia tecnica exacta del refresco automatico del dashboard *(web: param GRAL segundos default 60 / 0=off; mobile: manual/pull)*
- [x] Definicion final del perfil como solo lectura o parcialmente editable *(MVP: solo lectura, panel avatar; SPEC-002 §4.5)*
- [x] Comportamiento exacto al revocar acceso a un cliente *(MVP: `user_id = NULL`; sesión viva = revalidar `/me` + APIs dominio; SPEC-002 R-ID-11 / SPEC-003 §4.3.2)*
- [ ] Definicion conceptual detallada de auditoria de partes
- [x] Especificacion concreta del Informe Paquete de Horas *(MVP: SPEC-007 §4.8)*
- [x] Generación e implementación de SPEC-008 (Asistente IA chat documental) → A–F1 2026-08-01; HU/TR Pendiente de Revisión (`D-VERIFICACION-TR-008-asistente-ia-2026-08-01.md`)
- [x] Generación SDD de importación Excel de partes (`13` → SPEC/HU/TR-009 A→D 2026-08-02; pendiente E/F1)
