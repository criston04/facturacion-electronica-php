# Diseño — Notas de crédito/débito y temas restantes del temario

**Fecha:** 2026-06-24
**Proyecto:** Facturación Electrónica con PHP (curso CETI — última clase)
**Estado:** Diseño aprobado por el usuario.

## Contexto

El sistema emite **boletas (03)** y **facturas (01)** mediante el flujo `Venta` + `Facturalaya`:
la UI envía un POST a la clase PHP, esta guarda en `ventas`/`venta_detalle` y llama a la API
de `facturalahoy.com` (OSE/PSE) que genera el XML UBL, lo firma y lo envía a SUNAT, devolviendo
`ticket`, `estado`, CDR y el `.zip`. **El XML y la firma NO se hacen en PHP**, los hace la API.

Existe un segundo motor legado (`Factura` con tablas `factura_*`) que no está enlazado en el menú
y no se usa. Todo lo nuevo se construye sobre `Venta`/`Facturalaya`.

> Limitación conocida: aún no se cuenta con los endpoints de facturalahoy para notas, baja,
> resumen y consulta. Se modelan por analogía con la estructura SUNAT, con endpoints configurables
> marcados `// AJUSTAR según doc facturalahoy`. Si la API responde 404, el comprobante se guarda
> como `pendiente` y se avisa que el endpoint debe ajustarse.

## Decisiones tomadas

- **Modelo de datos:** reusar la tabla `ventas` (no crear tablas nuevas).
- **Alcance:** todo lo que resta del temario (notas C/D, código QR, baja real, consulta de estado, resumen de boletas).

## Diseño

### 1. Nota de crédito (07) y nota de débito (08)
- **BD:** se reusa `ventas` con `tipo_comprobante = 'NOTA_CREDITO'` / `'NOTA_DEBITO'`.
  Columnas nuevas (auto-ALTER idempotente, como ya hace `Facturalaya` con `xml_file`):
  `id_doc_afectado`, `tipo_doc_afectado` (01/03), `serie_afectada`, `correlativo_afectado`,
  `cod_motivo`, `desc_motivo`.
- **Series:** NC/factura→`FC01`, NC/boleta→`BC01`, ND/factura→`FD01`, ND/boleta→`BD01`.
  Correlativo independiente por serie (se generaliza `Venta::getNextCorrelativo`).
- **Motivos SUNAT (select):**
  - NC (catálogo 09): 01 anulación de la operación, 02 anulación por error en el RUC,
    03 corrección por error en la descripción, 04 descuento global, 05 descuento por ítem,
    06 devolución total, 07 devolución por ítem, 08 bonificación, 09 disminución en el valor,
    10 otros conceptos.
  - ND (catálogo 10): 01 intereses por mora, 02 aumento en el valor, 03 penalidades / otros conceptos.
- **UI:** desde el listado de ventas, botón "Nota C/D" en comprobantes **aceptados** → modal con
  tipo de nota, motivo, sustento y el detalle (copia los ítems del original, editable para
  devolución/montos parciales) → envía.
- **Código:** clase nueva `NotaComprobante.php` (valida que el documento original exista y esté
  aceptado, calcula totales, guarda en `ventas`) + `Facturalaya->enviarNota()` que arma el payload
  con `cod_tipo_documento` 07/08, `serie_comprobante`, `docs_referencia` = [{tipo, serie, numero}],
  `cod_motivo`, `descripcion_motivo`, reutilizando el armado de ítems/totales. Endpoints
  `$apiUrlNotaCredito` / `$apiUrlNotaDebito` configurables.

### 2. Baja real de documentos
- `Facturalaya->enviarBaja()` (comunicación de baja para facturas/notas); solo marca `baja` si
  SUNAT acepta. Las **boletas no se dan de baja por comunicación** sino por el **resumen** (punto 3)
  con motivo de anulación. Se conserva el "anular" local como respaldo.

### 3. Resumen de boletas (resumen diario / RC)
- Página "Resúmenes": se elige una fecha → agrupa las boletas de ese día → `Facturalaya->enviarResumen()`
  → guarda el ticket del resumen.

### 4. Consulta de estado por ticket
- `Facturalaya->consultarEstado($ticket)` → actualiza la venta. En el modal "Ver", botón
  "Actualizar estado SUNAT" que reconsulta el CDR.

### 5. Código QR
- En `print_venta.php`, cadena SUNAT `RUC|tipoDoc|serie|numero|IGV|total|fecha|tipoDocCliente|nroDocCliente`,
  renderizada con `qrcodejs` por CDN (sin dependencias PHP). Aplica también a las notas.

## Manejo de errores
- Transacción en BD; commit antes de la llamada externa; el comprobante se guarda siempre aunque
  SUNAT falle (`pendiente`); la consulta por ticket permite reintentar. Endpoints no confirmados →
  se guarda `pendiente` y se informa.

## Verificación (manual — no hay framework de tests)
Checklist de demo en vivo:
1. Emitir factura → emitir NC total → consultar estado → imprimir con QR.
2. Emitir boleta → enviar resumen → dar de baja.

## Archivos afectados
- **Nuevos:** `admin/classes/NotaComprobante.php`, `pages/notas.php`, `pages/js/notas.js`, `pages/resumenes.php`.
- **Modificados:** `Facturalaya.php` (enviarNota/Baja/Resumen/consultarEstado + endpoints),
  `Venta.php` (correlativo generalizado + baja real), `ventas.php`/`ventas.js` (botones NC/ND + actualizar estado),
  `print_venta.php` (QR + soporte notas), `template/cabecera.php` (menú Notas / Resúmenes).
