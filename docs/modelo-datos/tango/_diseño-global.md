# Diseño Global – Módulos Contables

Vista de alto nivel de los módulos del sistema y sus relaciones. Sin detalle de tablas.

---

## Diagrama ER (Mermaid)

```mermaid
erDiagram
    MOD_VENTAS {
        string id PK
    }
    MOD_COMPRAS {
        string id PK
    }
    MOD_TESORERIA {
        string id PK
    }
    MOD_CONTABILIDAD {
        string id PK
    }
    MOD_STOCK {
        string id PK
    }
    REL_VTA_STK {
        string id PK
    }
    REL_CMP_STK {
        string id PK
    }
    ASIENTO_VENTAS {
        string id PK
    }
    ASIENTO_COMPRAS {
        string id PK
    }
    ASIENTO_TESORERIA {
        string id PK
    }
    MOD_VENTAS ||--o{ ASIENTO_VENTAS : genera
    MOD_COMPRAS ||--o{ ASIENTO_COMPRAS : genera
    MOD_TESORERIA ||--o{ ASIENTO_TESORERIA : genera
    ASIENTO_VENTAS }o--|| MOD_CONTABILIDAD : aporta
    ASIENTO_COMPRAS }o--|| MOD_CONTABILIDAD : aporta
    ASIENTO_TESORERIA }o--|| MOD_CONTABILIDAD : aporta
    MOD_VENTAS ||--o{ REL_VTA_STK : vincula
    REL_VTA_STK }o--o{ MOD_STOCK : vincula
    MOD_COMPRAS ||--o{ REL_CMP_STK : vincula
    REL_CMP_STK }o--o{ MOD_STOCK : vincula
```

---

## Diagrama simplificado (solo módulos)

```mermaid
erDiagram
    VENTAS {
        string id PK
    }
    COMPRAS {
        string id PK
    }
    TESORERIA {
        string id PK
    }
    CONTABILIDAD {
        string id PK
    }
    STOCK {
        string id PK
    }
    REL_VTA_STK {
        string id PK
    }
    REL_CMP_STK {
        string id PK
    }
    VENTAS ||--o{ CONTABILIDAD : asientos
    COMPRAS ||--o{ CONTABILIDAD : asientos
    TESORERIA ||--o{ CONTABILIDAD : asientos
    VENTAS ||--o{ REL_VTA_STK : vincula
    REL_VTA_STK }o--o{ STOCK : vincula
    COMPRAS ||--o{ REL_CMP_STK : vincula
    REL_CMP_STK }o--o{ STOCK : vincula
```

---

## Leyenda

| Entidad | Descripción |
|---------|-------------|
| **VENTAS** | Módulo de ventas |
| **COMPRAS** | Módulo de compras |
| **TESORERIA** | Módulo de tesorería |
| **CONTABILIDAD** | Módulo contable (asientos GV, CP, SB) |
| **STOCK** | Módulo de inventarios |
| **ASIENTO_VENTAS** | Comprobantes contables de ventas |
| **ASIENTO_COMPRAS** | Comprobantes contables de compras |
| **ASIENTO_TESORERIA** | Comprobantes contables de tesorería |
| **REL_VTA_STK** | Relación Ventas–Stock (se intercala entre MOD_VENTAS y MOD_STOCK) |
| **REL_CMP_STK** | Relación Compras–Stock (se intercala entre MOD_COMPRAS y MOD_STOCK) |

