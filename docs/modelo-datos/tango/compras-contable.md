# Diseño de Tablas – Compras Contable

Documentación de las tablas de asientos contables de compras obtenidas desde SQL Server mediante MCP.

**Tablas:** `ASIENTO_COMPROBANTE_CP`, `ASIENTO_CP`, `AUXILIAR_ASIENTO_CP`, `SUBAUXILIAR_ASIENTO_CP`


### Diagrama ER (Mermaid)

```mermaid
erDiagram
    ASIENTO_COMPROBANTE_CP {
        int ID_ASIENTO_COMPROBANTE_CP PK
        float NCOMP_IN_C
        char ASIENTO_ANULACION
        char CONTABILIZADO
        char TRANSFERIDO_CN
    }
    ASIENTO_CP {
        int ID_ASIENTO_CP PK
        int ID_ASIENTO_COMPROBANTE_CP FK
        int ID_CUENTA FK
        char D_H
        money IMPORTE_RENGLON_BASE_CP
        money IMPORTE_RENGLON_ALTER_CP
    }
    AUXILIAR_ASIENTO_CP {
        int ID_ASIENTO_CP PK
        int ID_AUXILIAR PK
        money PORC_APROPIACION
        money IMPORTE_RENGLON_BASE_CP
        money IMPORTE_RENGLON_ALTER_CP
    }
    SUBAUXILIAR_ASIENTO_CP {
        int ID_ASIENTO_CP PK
        int ID_AUXILIAR PK
        int ID_SUBAUXILIAR PK
        money PORC_APROPIACION
    }
    CUENTA {
        int ID_CUENTA PK
    }
    AUXILIAR {
        int ID_AUXILIAR PK
    }
    SUBAUXILIAR {
        int ID_SUBAUXILIAR PK
    }
    CPA04 {
        int ID_CPA04 PK
        float NCOMP_IN_C
    }
    ASIENTO_COMPROBANTE_CP }o--|| CPA04 : "ref NCOMP_IN_C"
    ASIENTO_COMPROBANTE_CP ||--o{ ASIENTO_CP : contiene
    ASIENTO_CP }o--|| CUENTA : "usa cuenta"
    ASIENTO_CP ||--o{ AUXILIAR_ASIENTO_CP : desglosa
    AUXILIAR_ASIENTO_CP }o--|| AUXILIAR : "referencia"
    AUXILIAR_ASIENTO_CP ||--o{ SUBAUXILIAR_ASIENTO_CP : desglosa
    SUBAUXILIAR_ASIENTO_CP }o--|| SUBAUXILIAR : "referencia"
```

---

## Dependencias previas

Para ejecutar estos scripts, deben existir:

- Tablas: `CUENTA`, `AUXILIAR`, `SUBAUXILIAR`
- Tipos de usuario (UDT): `D_ID`, `D_SINO_NO`, `D_SINO_SI`, `D_NRO_ORDEN`, `D_IMPORTE_CP`, `D_LEYENDA_ASIENTO`, `D_DEBE_HABER`, `D_PORCENTAJES`, `ENTEROXL_TG`

---

## 1. Crear tipos de usuario (si no existen)

```sql
-- Tipos base usados en compras-contable
CREATE TYPE dbo.D_ID FROM int;
CREATE TYPE dbo.D_SINO_NO FROM char(1);
CREATE TYPE dbo.D_SINO_SI FROM char(1);
CREATE TYPE dbo.D_NRO_ORDEN FROM int;
CREATE TYPE dbo.D_IMPORTE_CP FROM money;
CREATE TYPE dbo.D_LEYENDA_ASIENTO FROM varchar(100);
CREATE TYPE dbo.D_DEBE_HABER FROM char(1);
CREATE TYPE dbo.D_PORCENTAJES FROM money;
CREATE TYPE dbo.ENTEROXL_TG FROM float;
```

---

## 2. Secuencias

```sql
CREATE SEQUENCE dbo.SEQUENCE_ASIENTO_COMPROBANTE_CP
    AS int
    START WITH 1
    INCREMENT BY 1
    MINVALUE -9223372036854775808
    MAXVALUE 9223372036854775807
    NO CYCLE;

CREATE SEQUENCE dbo.SEQUENCE_ASIENTO_CP
    AS int
    START WITH 1
    INCREMENT BY 1
    MINVALUE -9223372036854775808
    MAXVALUE 9223372036854775807
    NO CYCLE;
```

---

## 3. Tabla ASIENTO_COMPROBANTE_CP

Encabezado de comprobantes contables de compras.

```sql
CREATE TABLE dbo.ASIENTO_COMPROBANTE_CP (
    ID_ASIENTO_COMPROBANTE_CP  D_ID NOT NULL
        DEFAULT (NEXT VALUE FOR dbo.SEQUENCE_ASIENTO_COMPROBANTE_CP),
    NCOMP_IN_C                 ENTEROXL_TG NOT NULL,
    ASIENTO_ANULACION          D_SINO_NO NOT NULL,
    CONTABILIZADO              D_SINO_NO NOT NULL,
    USUARIO_CONTABILIZACION    varchar(120) NULL,
    FECHA_CONTABILIZACION      datetime NULL,
    TERMINAL_CONTABILIZACION   varchar(255) NULL,
    TRANSFERIDO_CN             D_SINO_NO NOT NULL,

    CONSTRAINT PK_ASIENTO_COMPROBANTE_CP PRIMARY KEY (ID_ASIENTO_COMPROBANTE_CP)
);
```

---

## 4. Tabla ASIENTO_CP

Renglones del asiento contable de compras.

```sql
CREATE TABLE dbo.ASIENTO_CP (
    ID_ASIENTO_CP              D_ID NOT NULL
        DEFAULT (NEXT VALUE FOR dbo.SEQUENCE_ASIENTO_CP),
    ID_ASIENTO_COMPROBANTE_CP  D_ID NOT NULL,
    NRO_RENGLON_ASIENTO_CP     D_NRO_ORDEN NOT NULL,
    ID_CUENTA                  D_ID NOT NULL,
    D_H                        D_DEBE_HABER NOT NULL,
    IMPORTE_RENGLON_BASE_CP    D_IMPORTE_CP NOT NULL,
    IMPORTE_RENGLON_ALTER_CP   D_IMPORTE_CP NOT NULL,
    DESC_LEYENDA               D_LEYENDA_ASIENTO NULL,
    EDITA_CUENTA               D_SINO_NO NOT NULL,

    CONSTRAINT PK_ASIENTO_CP PRIMARY KEY (ID_ASIENTO_CP),
    CONSTRAINT FK_ASIENTO_COMP_CP FOREIGN KEY (ID_ASIENTO_COMPROBANTE_CP)
        REFERENCES dbo.ASIENTO_COMPROBANTE_CP (ID_ASIENTO_COMPROBANTE_CP),
    CONSTRAINT FK_CUENTA_ASIENTO_CP FOREIGN KEY (ID_CUENTA)
        REFERENCES dbo.CUENTA (ID_CUENTA)
);

-- Índice para FK
CREATE NONCLUSTERED INDEX CUENTA_ASIENTO_CP_FK
    ON dbo.ASIENTO_CP (ID_CUENTA);
```

---

## 5. Tabla AUXILIAR_ASIENTO_CP

Desglose por auxiliar (cuenta contable) en asientos de compras.

```sql
CREATE TABLE dbo.AUXILIAR_ASIENTO_CP (
    ID_ASIENTO_CP              D_ID NOT NULL,
    ID_AUXILIAR                D_ID NOT NULL,
    PORC_APROPIACION           D_PORCENTAJES NOT NULL,
    IMPORTE_RENGLON_BASE_CP    D_IMPORTE_CP NOT NULL,
    IMPORTE_RENGLON_ALTER_CP   D_IMPORTE_CP NOT NULL,
    EDITA_APROPIACION          D_SINO_SI NOT NULL,

    CONSTRAINT PK_AUXILIAR_ASIENTO_CP PRIMARY KEY (ID_ASIENTO_CP, ID_AUXILIAR),
    CONSTRAINT FK_ASIENTO_CP_AUXILIAR FOREIGN KEY (ID_ASIENTO_CP)
        REFERENCES dbo.ASIENTO_CP (ID_ASIENTO_CP),
    CONSTRAINT FK_AUXILIAR_ASIENTO_CP FOREIGN KEY (ID_AUXILIAR)
        REFERENCES dbo.AUXILIAR (ID_AUXILIAR)
);
```

---

## 6. Tabla SUBAUXILIAR_ASIENTO_CP

Desglose por subauxiliar en asientos de compras.

```sql
CREATE TABLE dbo.SUBAUXILIAR_ASIENTO_CP (
    ID_ASIENTO_CP              D_ID NOT NULL,
    ID_AUXILIAR                D_ID NOT NULL,
    ID_SUBAUXILIAR             D_ID NOT NULL,
    PORC_APROPIACION           D_PORCENTAJES NOT NULL,
    IMPORTE_RENGLON_BASE_CP    D_IMPORTE_CP NOT NULL,
    IMPORTE_RENGLON_ALTER_CP   D_IMPORTE_CP NOT NULL,
    EDITA_APROPIACION          D_SINO_SI NOT NULL,

    CONSTRAINT PK_SUBAUXILIAR_ASIENTO_CP PRIMARY KEY (ID_ASIENTO_CP, ID_AUXILIAR, ID_SUBAUXILIAR),
    CONSTRAINT FK_AUXILIAR_SUBAUXILIAR_ASIENTO_CP FOREIGN KEY (ID_ASIENTO_CP, ID_AUXILIAR)
        REFERENCES dbo.AUXILIAR_ASIENTO_CP (ID_ASIENTO_CP, ID_AUXILIAR),
    CONSTRAINT FK_SUBAUXILIAR_ASIENTO_CP FOREIGN KEY (ID_SUBAUXILIAR)
        REFERENCES dbo.SUBAUXILIAR (ID_SUBAUXILIAR)
);

-- Índice para FK
CREATE NONCLUSTERED INDEX SUBAUXILIAR_ASIENTO_CP_FK
    ON dbo.SUBAUXILIAR_ASIENTO_CP (ID_SUBAUXILIAR);
```

---

## Diagrama de relaciones

```
ASIENTO_COMPROBANTE_CP (1) ─────── (*) ASIENTO_CP
         │                              │
         └── CPA04 (NCOMP_IN_C)        └── CUENTA
                                        │
                                        (1) ──── (*) AUXILIAR_ASIENTO_CP ─── AUXILIAR
                                                        │
                                                        (1) ─── (*) SUBAUXILIAR_ASIENTO_CP ─── SUBAUXILIAR
```

---

## Diferencias entre módulos (Compras vs Ventas vs Tesorería)

| Aspecto | Compras (CP) | Ventas (GV) | Tesorería (SB) |
|---------|--------------|-------------|----------------|
| Columna comprobante | NCOMP_IN_C | NCOMP_IN_V | N_INTERNO |
| Tipo importes | D_IMPORTE_CP | D_IMPORTE_GV | D_IMPORTE_SB |
| Importes en ASIENTO | NOT NULL | NULL | NOT NULL |
| Moneda en ASIENTO | No | No | Sí (ID_MONEDA, etc.) |
| FKs ASIENTO | CUENTA | CUENTA | CUENTA, MONEDA, TIPO_COTIZACION |

---

## Mapeo de tipos UDT a tipos base

| UDT               | Tipo base    | Notas             |
|-------------------|-------------|-------------------|
| D_ID              | int         | Identificadores   |
| D_SINO_NO         | char(1)     | Sí/No             |
| D_SINO_SI         | char(1)     | Sí/No             |
| D_NRO_ORDEN       | int         | Número de orden   |
| D_IMPORTE_CP      | money       | Importes compras  |
| D_LEYENDA_ASIENTO | varchar(100)| Leyenda           |
| D_DEBE_HABER      | char(1)     | D/H               |
| D_PORCENTAJES     | money       | Porcentajes       |
| ENTEROXL_TG       | float       | Número interno    |

---

*Documento generado a partir del esquema de SQL Server vía MCP user-mssql.*
