# Bug: validación `unique` en Company DB / Dictionary DB (Laravel 10)

**Fecha:** 2026-03-20  
**Contexto:** Módulo Partes Producción (p. ej. alta de máquinas) y administración de empresas.  
**Framework:** Laravel 10.x (`laravel/framework ^10.10`).

---

## 1) Síntoma

Al guardar un registro (POST) que valida unicidad en una tabla que **no está en la conexión por defecto** (`sqlsrv` / `DB_DATABASE`), la API responde **500** con:

```text
Call to undefined method Illuminate\Validation\Rules\Unique::connection()
```

Ejemplo detectado en `MaquinaController::store` al validar `codigo` único en `PQ_PRD_MAQUINAS` (Company DB).

---

## 2) Causa

Se usaba la cadena de llamadas:

```php
Rule::unique('TABLA', 'COLUMNA')->connection('company')
```

En **Laravel 10**, la clase `Illuminate\Validation\Rules\Unique` **no expone** el método `connection()`. Esa API no aplica a este objeto de regla (a diferencia de lo que a veces se ve en documentación antigua o en otros contextos).

La validación `unique` debe resolver la conexión mediante la **notación punto** en el primer argumento: `nombre_conexion.nombre_tabla`.

---

## 3) Corrección

Reemplazar:

| Incorrecto (Laravel 10) | Correcto |
|-------------------------|----------|
| `Rule::unique('PQ_PRD_MAQUINAS', 'CODIGO_MAQUINA')->connection('company')` | `Rule::unique('company.PQ_PRD_MAQUINAS', 'CODIGO_MAQUINA')` |
| `Rule::unique('pq_empresa', $col)->connection('dictionary')` | `Rule::unique('dictionary.pq_empresa', $col)` |

Con `ignore()` en actualización, el patrón se mantiene:

```php
Rule::unique('dictionary.pq_empresa', $nombreBdCol)->ignore($id, $idCol)
```

Las conexiones `company` y `dictionary` están definidas en `backend/config/database.php`.

---

## 4) Archivos modificados (referencia)

- **Company DB (Partes Producción):**  
  `MaquinaController`, `OperacionController`, `TurnoController`, `TipoTareaController`, `ConceptoTiempoController`, `OrdenTrabajoController`, `EstandarArticuloOperacionController` (`exists` operación), `ParteOperarioController` (`exists` turno), `ParteEntradaController` (`exists` concepto tiempo), `AsignacionController` (`exists` turno / tipo tarea)
- **Dictionary DB (admin empresas):**  
  `EmpresaAdminController` (`dictionary.pq_empresa`)

---

## 5) Regla para el equipo

> Para validar `unique` contra una conexión distinta del default en Laravel 10, usar siempre **`'conexion.tabla'`** en la regla, **no** `->connection()`.

Para **`exists`**, alternativas válidas:

- Notación cadena: `exists:company.PQ_PRD_OPERACIONES,ID_OPERACION` (o regla equivalente con prefijo de conexión).
- **Modelo Eloquent** con conexión definida en el modelo: `Rule::exists(PqPrdOperacion::class, 'ID_OPERACION')` (la regla usa la conexión del modelo).

No usar `Rule::exists('TABLA', 'COL')->connection('company')`: **`Exists` tampoco expone `connection()`** en Laravel 10 (mismo síntoma que con `Unique`).

Documentación oficial (concepto): [Validation – Laravel 10.x](https://laravel.com/docs/10.x/validation) (reglas `unique` / tablas con prefijo de conexión).

---

## 6) `orderBy` + query `dir` — Error 500 en listados (Laravel 10)

### Síntoma

Al abrir pantallas de listado (grillas) de Partes Producción u otros módulos:

```text
InvalidArgumentException: Order direction must be "asc" or "desc".
```

(Origen típico: `Illuminate\Database\Query\Builder::orderBy`.)

Ejemplos reportados: **Máquinas**, **Tipos de tarea**, y el mismo patrón afectaba el resto de catálogos con orden dinámico.

### Causas (dos a la vez)

1. **Anti-patrón con `input('dir')` sin default en la rama “true”:**  
   Se validaba con `input('dir', 'asc')` pero luego se asignaba `strtoupper($request->input('dir'))`. Si `dir` **no** estaba en la URL, el segundo `input('dir')` devolvía `null` → cadena vacía tras `strtoupper` → valor inválido para `orderBy`.

2. **Mayúsculas:** Laravel 10 valida que la dirección sea exactamente `'asc'` o `'desc'` en **minúsculas**. Pasar `'ASC'` / `'DESC'` también puede disparar la excepción.

### Patrón correcto (copiar en nuevos `index`)

Default **asc:**

```php
$dirRaw = $request->input('dir', 'asc');
$dir = in_array(strtoupper((string) $dirRaw), ['ASC', 'DESC'])
    ? strtolower((string) $dirRaw)
    : 'asc';
$query->orderBy($sort, $dir);
```

Default **desc** (p. ej. listados por fecha reciente):

```php
$dirRaw = $request->input('dir', 'desc');
$dir = in_array(strtoupper((string) $dirRaw), ['ASC', 'DESC'])
    ? strtolower((string) $dirRaw)
    : 'desc';
$query->orderBy($sort, $dir);
```

### Helper reutilizable (recomendado en código nuevo)

`App\Support\QuerySortDirection::forOrderBy($request, 'asc')` o `..., 'desc')` — misma lógica centralizada; ver `backend/app/Support/QuerySortDirection.php`.

### Controladores alineados (referencia 2026-03-20)

`MaquinaController`, `TipoTareaController`, `TurnoController`, `ConceptoTiempoController`, `EstandarArticuloOperacionController`, `OperacionController`, `OperarioController`, `OrdenTrabajoController`, `AsignacionController`, `PartesRevisarController`, `MisPartesController`.

### Regla para el equipo

> Nunca usar `orderBy($sort, strtoupper($request->input('dir')))` sin un default coherente en **la misma** expresión que valida. Siempre pasar a `orderBy` solo `'asc'` o `'desc'` en minúsculas.
