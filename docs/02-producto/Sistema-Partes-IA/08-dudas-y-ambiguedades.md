# Dudas y ambiguedades

## Objetivo

Registrar que temas del modulo `SistemaPartes` ya quedaron conceptualmente resueltos y cuales siguen necesitando una definicion adicional antes de bajar a `SPEC`, `HU` y `TR`.

Este documento ya no funciona como una lista cruda de preguntas, sino como un control de estado de definiciones pendientes.

## Definiciones ya resueltas

### Terminologia de actor interno

- `asistente` y `empleado` deben entenderse como sinonimos historicos;
- el termino preferido para esta base conceptual es **asistente**.

### Forma de la carga diaria

- la carga de tareas debe realizarse desde una **grilla previamente filtrada**;
- desde esa grilla se insertan, editan y eliminan registros;
- por lo tanto, la carga ya no debe modelarse como un formulario unitario aislado.

### Exportacion

- la exportacion no integra el nucleo minimo del MVP base;
- debe leerse como una **evolucion inmediata** del modulo, apoyada en el framework comun.

### Menu del modulo

- la definicion final del menu debe adaptarse al framework comun vigente de PaqSuite;
- por lo tanto, cualquier referencia historica a un menu lateral aislado solo se conserva como antecedente de transicion.

### Tipo de tarea por defecto

- queda adoptado que debe existir un unico tipo de tarea por defecto;
- y ese tipo por defecto debe ser generico.

### Campos `code`

- los campos `code` en tipos de cliente y tipos de tarea forman parte del modelo esperado;
- si en algun esquema previo no existieran, deben incorporarse.

### Universo funcional visible

- la condicion funcional del usuario es la primera capa de delimitacion del universo de datos;
- en el dominio actual eso se interpreta como cliente, asistente o supervisor;
- los permisos de menu o proceso pueden restringir accesos a pantallas concretas, pero no reemplazan esa delimitacion primaria.

### Resultado vacio en consultas

- cuando no haya resultados, las acciones como exportar deben seguir visibles;
- pero deben quedar deshabilitadas mientras no exista contenido valido para operar.

### Cuenta corriente de horas

- debe tratarse como un **proceso funcional diferente**;
- no solo como una variante menor de informe.

### Carga con IA

- la IA complementa la carga manual;
- no la reemplaza;
- y su funcion esperada es completar o proponer datos dentro del mismo proceso de registracion.

### Mobile

- corresponde tener un documento conceptual especifico para mobile;
- por eso se incorpora `10-mobile.md` dentro de esta carpeta;
- y el alcance principal de pantallas mobile ya queda definido en ese documento.

## Temas parcialmente resueltos o aun abiertos

### 1. Dashboard con actualizacion automatica

Quedaron definidos estos puntos:

- el refresco automatico debe estar activo;
- el dashboard debe abrir inicialmente sobre el mes calendario de la fecha del sistema;
- el usuario puede modificar el periodo.

Sigue faltando cerrar con precision:

- la frecuencia tecnica exacta del refresco automatico;
- y si algun rol o contexto puede requerir una excepcion a ese comportamiento.

### 2. Perfil del usuario

Sigue pendiente definir con mayor claridad si el perfil del modulo es:

- solo de lectura;
- o si admite algun bloque editable y bajo que alcance funcional.

### 3. Revocacion de acceso de clientes

Sigue pendiente precisar que comportamiento exacto debe adoptar el sistema cuando se revoca el acceso de un cliente:

- si solo conserva la entidad funcional;
- si debe desasociar la identidad comun;
- y que validaciones de consistencia deben imponerse.

### 4. Auditoria de partes

La linea futura llamada "auditoria de partes" sigue necesitando profundizacion conceptual.

Todavia no esta cerrado si debe tratarse como:

- una sola epica futura;
- o varias capacidades separadas, por ejemplo consulta, edicion masiva, importacion y notificacion.

### 5. Aplicacion del campo empresa en mobile

La version mobile ya incorpora como decision funcional una pantalla inicial con configuracion de URL y un login con:

- empresa;
- usuario;
- contrasena.

Lo que sigue pendiente precisar es:

- como se interpreta exactamente `empresa` en el modulo;
- si su efecto es solo de seleccion operativa, de seguridad o de contexto funcional;
- y como se relaciona esa decision con la arquitectura general del producto.

## Uso recomendado

Antes de generar nuevos `SPEC`, `HU` o `TR` del dominio propio de `SistemaPartes`, conviene verificar esta lista para no reabrir decisiones ya tomadas y para detectar si el trabajo depende de alguno de los puntos que siguen abiertos.
