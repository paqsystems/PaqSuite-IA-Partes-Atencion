# IA en diseño de informes — limitaciones MVP (camino B)

| Campo | Valor |
|-------|--------|
| Veredicto piloto | **B** — Smart Capture bajo el diseñador DevExpress |
| Relacionado | [SPEC-015 oleada 2](../../05-open-spec/updates/100-SistemaPartes/SPEC-015-ia-diseno-informes-oleada-2-b-update.md) · [19-ia-diseno-informes](./19-ia-diseno-informes.md) · [14-smart-capture](./14-smart-capture.md) |
| Camino A | **Cancelado** en Partes (sin entrega productiva) |
| Última actualización | 2026-11-10 |

Este documento fija el **alcance acotado** de la primera versión productiva con asistencia IA vía **GEN-03**. Las filas pueden darse de alta progresivamente en versiones posteriores.

---

## Lista de limitaciones (L-B)

|     ID     | Limitación MVP | Evolución futura (orientativa) |
|------------|----------------|--------------------------------|
| **L-B-01** | Solo el proceso emisible **`partes.informes.consultaDetallada`**. | Otros procesos (p. ej. paquete de horas). |
| **L-B-02** | Solo **acciones de layout en whitelist** (agrupar, subtotales, título, quitar/ajustar bandas simples, columnas tabulares). | Más tipos (filtros visuales, gráficos, formato condicional). |
| **L-B-03** | El asistente **no genera ni pisa el informe como un archivo interno opaco**; traduce el pedido a **pasos concretos del catálogo** que el programa ejecuta y muestra en el diseñador. Ver [§ L-B-03 en lenguaje humano](#l-b-03-en-lenguaje-humano). | Nuevas acciones en la lista, no “liberar” REPX crudo. |
| **L-B-04** | **Edición incremental** acotada; no “rehacer todo el informe” en un solo mensaje abierto. | Plantillas + flujos guiados. |
| **L-B-05** | **Sin** wizard **Prompt-to-Report** nativo de DevExpress (camino A apagado en Partes). | — |
| **L-B-06** | **GEN-16** obligatorio para el turno: sin credencial LLM del usuario, panel degradado; el **diseño manual** sigue. | — |
| **L-B-07** | Solo **escritorio**; excluido mobile (coherente SPEC-011 / HU-011). | — |
| **L-B-08** | Dictado e imágenes del panel SC **opcionales / fuera de v1** si simplifican el primer entregable. | Paridad con Carga diaria. |
| **L-B-09** | Campos **solo** del schema autorizado del proceso; pedidos con conceptos fuera de catálogo → **rechazo o mensaje claro** (prueba §4.4 #7). | Ampliar sinónimos en JSON de schema. |
| **L-B-10** | **Sin auto-guardar**: después de aplicar cambios, el usuario **Guardar / Guardar como** en el diseñador (GEN-15). | — |
| **L-B-11** | La **calidad visual** del layout no se garantiza al nivel del motor DX nativo; el usuario **revisa** en el lienzo. | Más acciones + pruebas de regresión. |
| **L-B-12** | *(Referencia)* Informes de **laboratorio** del camino A cancelado **no** forman parte del backlog de mantenimiento. No hubo entrega productiva con A; **no** se migran ni reparan como deuda de producto. | — |

---

## L-B-03 en lenguaje humano

Vos pedís en castellano, por ejemplo: *“agrupá por cliente y sumá las horas”*.

El sistema **no** arma un informe completo en un formato interno que reemplace de golpe lo que tenés en pantalla sin pasos intermedios revisables.

En cambio:

1. El backend interpreta el pedido.
2. Lo convierte en **acciones permitidas** del producto (p. ej. “agregar agrupación por cliente”, “sumar minutos en pie de grupo”).
3. El programa **ejecuta esas acciones** sobre el diseñador gráfico.
4. Vos **ves el resultado** en el lienzo y decidís si **guardás**.

Lo que **no** entra en MVP: un solo mensaje muy abierto tipo “haceme un informe ejecutivo con logo y tres pestañas” que no encaje en ninguna acción de la lista.

Cada capacidad nueva se **suma al catálogo de acciones** con desarrollo y prueba, no porque el modelo “lo inventó” en un archivo opaco.

---

## Uso en SDD / F1

- La TR oleada 2 debe **referenciar** esta lista en alcance y pruebas.
- Ampliar el MVP implica **nueva fila** o mover una limitación a “cerrado” en historial de este archivo o en el SPEC-update.
