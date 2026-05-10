# Glosario de tablas – Nombre técnico ↔ Nombre conceptual

Referencia central para asociar el **nombre técnico/mnemónico** de cada tabla con su **nombre conceptual/funcional**.  
Usar los nombres conceptuales en definiciones y especificaciones para mayor claridad.

**Fuentes:**  
[Tablas Compras](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablascompras_oper/) |  
[Tablas Stock](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablastock_oper/) |  
[Tablas Tesorería](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablastesoreria_oper/) |  
[Tablas Ventas](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablasventas_oper/)

---

## Cómo usar este glosario

- **Para el agente IA:** al recibir definiciones con nombres conceptuales (ej. "Comprobantes de compra"), buscar aquí el nombre técnico (CPA04) y aplicar cambios en el archivo correspondiente.
- **Para programadores:** al revisar esquemas o scripts, usar este glosario para interpretar las tablas por su rol funcional.

---

## Módulo Compras

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| CPA01 | Proveedores | compras-proveedores.md |
| CPA04 | Comprobantes de compra | compras.md |
| CPA04TY | Tipos/formularios de comprobantes | compras.md |
| CPA05 | Cancelaciones de comprobantes | compras.md |
| CPA08 | Exenciones/retenciones por proveedor | compras-proveedores.md |
| CPA14 | Impuestos IVA compras (alícuotas) | compras-proveedores.md, articulos.md |
| CPA15 | Artículos por proveedor | compras-proveedores.md |
| CPA18 | Impuestos por comprobante | compras.md |
| CPA20 | Datos proveedor en comprobante | compras.md |
| CPA45 | Conceptos de compra (maestro) | — (externa) |
| CPA46 | Renglones de artículos | compras.md |
| CPA47 | Renglones de conceptos | compras.md |
| CPA54 | Vencimientos | compras.md |
| CPA63 | Alícuotas IVA por comprobante | compras.md |
| CPA_CONCEPTOS_PROVEEDOR | Conceptos por proveedor | compras-proveedores.md |
| COMPROBANTE_AFIP_COMPRAS | Comprobantes AFIP importados | compras.md |
| CONDICION_COMPRA | Condiciones de compra | compras-proveedores.md |
| RENGLON_CONDICION_COMPRA | Renglones de condiciones de compra | compras-proveedores.md |

---

## Módulo Ventas

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| GVA01 | Condiciones de venta | ventas-clientes.md |
| GVA03 | Renglones de presupuesto | ventas.md |
| GVA05 | Zonas de venta | ventas-clientes.md |
| GVA07 | Notas de crédito (cancelaciones) | ventas.md |
| GVA10 | Listas de precios | ventas-clientes.md |
| GVA12 | Comprobantes de venta | ventas.md |
| GVA12DE | Datos extendidos comprobante | ventas.md |
| GVA12_JSON | Comprobante en JSON | ventas.md |
| GVA12TY | Tipos de comprobante | ventas.md |
| GVA14 | Clientes | ventas-clientes.md |
| GVA15 | Talonarios de ventas | ventas.md |
| GVA18 | Provincias | ventas-clientes.md |
| GVA21 | Presupuestos | ventas.md |
| GVA23 | Vendedores | ventas-clientes.md |
| GVA24 | Transportes | ventas-clientes.md |
| GVA27 | Contactos de clientes | ventas-clientes.md |
| GVA38 | Cuentas corrientes cliente | ventas.md |
| GVA41 | Alícuotas/impuestos ventas | articulos.md |
| GVA42 | Datos cliente en comprobante | ventas.md |
| GVA45 | Renglones de comprobante (artículos) | ventas.md |
| GVA46 | Impuestos por comprobante | ventas.md |
| GVA53 | Renglones de comprobante (ítems) | ventas.md |
| GVA63 | Alícuotas IVA por comprobante | ventas.md |
| GVA67 | Leyendas por comprobante | ventas.md |
| CATEGORIA_IVA | Categorías impositivas IVA | ventas-clientes.md |
| DIRECCION_ENTREGA | Direcciones de entrega | ventas-clientes.md |
| POS_IMPUESTO_POR_RENGLON | Impuestos por renglón | ventas.md |
| RENGLON_CONDICION_VENTA | Renglones de condiciones de venta | ventas-clientes.md |
| TIPO_DOCUMENTO_GV | Tipos de documento | ventas-clientes.md, compras-proveedores.md |

---

## Módulo Stock

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| STA09 | Stock por depósito/artículo | stock.md |
| STA10 | Depósitos / Almacenes | stock.md |
| STA11 | Artículos (maestro) | stock-articulos.md |
| STA14 | Movimientos de stock | stock.md |
| STA19 | Detalle de movimientos | stock.md |
| STA20 | Stock actual / auxiliar | stock.md |
| STA22 | Cabecera de movimientos | stock.md |
| STA32 | Escalas de artículos | stock-articulos.md |
| STA33 | Valores de escala | stock-articulos.md |

---

## Módulo Tesorería

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| BANCO | Bancos | tesoreria.md |
| SBA01 | Cuentas bancarias | tesoreria.md |
| SBA02 | Tipos de cuenta / comprobantes | tesoreria.md |
| SBA04 | Comprobantes de tesorería | tesoreria.md |
| SBA05 | Movimientos / pagos | tesoreria.md |
| SBA13 | Chequeras | tesoreria.md |
| SBA14 | Movimientos cheques recibidos | tesoreria.md |
| SBA15 | Movimientos cheques emitidos | tesoreria.md |
| COMPROBANTE_COTIZACION_SB | Cotizaciones de moneda | tesoreria.md |
| MOVIMIENTO_CHEQUE_TERCERO | Movimientos de cheques de terceros | tesoreria.md |

---

## Módulo Contabilidad (asientos)

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| ASIENTO_COMPROBANTE_CP | Cabecera asientos compras | compras-contable.md |
| ASIENTO_COMPROBANTE_GV | Cabecera asientos ventas | ventas-contable.md |
| ASIENTO_COMPROBANTE_SB | Cabecera asientos tesorería | tesoreria-contable.md |
| ASIENTO_CP | Renglones asiento compras | compras-contable.md |
| ASIENTO_GV | Renglones asiento ventas | ventas-contable.md |
| ASIENTO_SB | Renglones asiento tesorería | tesoreria-contable.md |
| AUXILIAR_ASIENTO_CP | Auxiliares asiento compras | compras-contable.md |
| AUXILIAR_ASIENTO_GV | Auxiliares asiento ventas | ventas-contable.md |
| AUXILIAR_ASIENTO_SB | Auxiliares asiento tesorería | tesoreria-contable.md |
| SUBAUXILIAR_ASIENTO_CP | Subauxiliares asiento compras | compras-contable.md |
| SUBAUXILIAR_ASIENTO_GV | Subauxiliares asiento ventas | ventas-contable.md |
| SUBAUXILIAR_ASIENTO_SB | Subauxiliares asiento tesorería | tesoreria-contable.md |

---

## Tablas transversales / auxiliares

| Nombre técnico | Nombre conceptual | Archivo |
|----------------|-------------------|---------|
| CATEGORIA_IVA | Categorías impositivas IVA | ventas-clientes.md |
| GVA151 | Rubros (ventas/compras) | compras-proveedores.md |
| MEDIDA | Unidades de medida | stock-articulos.md |
| PROVINCIA | Provincias | compras-proveedores.md |
| TIPO_DOCUMENTO_GV | Tipos de documento | ventas-clientes.md, compras-proveedores.md |

---

## Entidades genéricas (módulos externos)

| Nombre conceptual | Tablas que lo referencian | Columna de enlace |
|-------------------|---------------------------|-------------------|
| **ARTICULOS** | GVA03, GVA53, STA10, STA19, STA20 | COD_ARTICU |
| **CLIENTES** | GVA21, GVA12 | COD_CLIENT |
| **PROVEEDORES** | CPA04, CPA08, CPA15, CPA20, CPA_CONCEPTOS_PROVEEDOR | COD_PROVEE |
| **CONCEPTOS DE COMPRA** | CPA_CONCEPTOS_PROVEEDOR, CPA47 | COD_CONCEP / ID_CPA45 |

---

## Convenciones de prefijos

| Prefijo | Módulo |
|---------|--------|
| CPA | Compras |
| GVA | Ventas (Gestión Ventas) |
| STA | Stock |
| SBA | Tesorería (Sistema Bancario) |
| GLA | General / Contabilidad |

---

## Mantenimiento

Para añadir o corregir asociaciones:

1. Editar este archivo `_glosario-tablas.md`.
2. Completar o validar contra la documentación oficial de Axoft.
3. Si se agrega una tabla nueva en algún diseño, incorporarla aquí.
