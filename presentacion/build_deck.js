const pptxgen = require("pptxgenjs");

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE"; // 13.3" x 7.5"
pres.author = "CETI";
pres.title = "Facturación Electrónica con PHP — Clase Final";

// ---------- Paleta (alineada al teal del sistema) ----------
const C = {
  dark: "0E2A2E",      // fondo oscuro (portada / cierre)
  teal: "0D9488",      // primario
  seafoam: "14B8A6",   // secundario
  mint: "5EEAD4",      // acento
  white: "FFFFFF",
  ink: "1E293B",       // texto oscuro
  muted: "64748B",     // texto tenue
  tint: "F0FDFA",      // tinte de tarjeta (teal-50)
  tint2: "ECFEFF",
  codeBg: "0F172A",    // fondo de código
  codeTx: "E2E8F0",
  codeKey: "5EEAD4",
  codeCom: "64748B",
  amber: "D97706",
};
const F = { head: "Cambria", body: "Calibri", mono: "Courier New" };
const W = 13.3, H = 7.5, M = 0.6;

const shadow = () => ({ type: "outer", color: "000000", blur: 8, offset: 3, angle: 90, opacity: 0.12 });

// ---------- Helpers ----------
function header(slide, kicker, title) {
  slide.background = { color: C.white };
  slide.addText(kicker.toUpperCase(), {
    x: M, y: 0.42, w: W - 2 * M, h: 0.3, margin: 0,
    fontFace: F.body, fontSize: 13, bold: true, color: C.teal, charSpacing: 2,
  });
  slide.addText(title, {
    x: M, y: 0.72, w: W - 2 * M, h: 0.85, margin: 0,
    fontFace: F.head, fontSize: 30, bold: true, color: C.ink, valign: "top",
  });
}

function circle(slide, x, y, d, fill, glyph, glyphColor, gSize) {
  slide.addShape(pres.shapes.OVAL, { x, y, w: d, h: d, fill: { color: fill }, line: { type: "none" } });
  slide.addText(glyph, {
    x, y, w: d, h: d, margin: 0, align: "center", valign: "middle",
    fontFace: F.body, fontSize: gSize || 18, bold: true, color: glyphColor || C.white,
  });
}

function card(slide, x, y, w, h, fill) {
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x, y, w, h, rectRadius: 0.08, fill: { color: fill || C.tint }, line: { type: "none" }, shadow: shadow(),
  });
}

function codeBlock(slide, x, y, w, h, runs) {
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x, y, w, h, rectRadius: 0.06, fill: { color: C.codeBg }, line: { type: "none" }, shadow: shadow(),
  });
  slide.addText(runs, {
    x: x + 0.25, y: y + 0.18, w: w - 0.5, h: h - 0.36, margin: 0, valign: "top",
    fontFace: F.mono, fontSize: 12.5, color: C.codeTx, lineSpacingMultiple: 1.08,
  });
}
// construye líneas de código con color por tipo
function code(lines) {
  const out = [];
  lines.forEach((ln, i) => {
    const last = i === lines.length - 1;
    let color = C.codeTx;
    if (ln.t === "c") color = C.codeCom;     // comentario
    if (ln.t === "k") color = C.codeKey;     // resaltado
    out.push({ text: ln.x === undefined ? ln : ln.x, options: { color, breakLine: !last, bold: !!(ln.b) } });
  });
  return out;
}

// =================================================================
// SLIDE 1 — Portada
// =================================================================
let s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: 10.6, y: -1.7, w: 4.6, h: 4.6, fill: { color: C.teal, transparency: 70 }, line: { type: "none" } });
s.addShape(pres.shapes.OVAL, { x: 11.7, y: 4.6, w: 3.4, h: 3.4, fill: { color: C.seafoam, transparency: 80 }, line: { type: "none" } });
s.addText("CURSO – TALLER · CETI", { x: M, y: 1.5, w: 11, h: 0.4, margin: 0, fontFace: F.body, fontSize: 15, bold: true, color: C.mint, charSpacing: 3 });
s.addText("Facturación Electrónica con PHP", { x: M, y: 2.0, w: 11.5, h: 1.4, margin: 0, fontFace: F.head, fontSize: 50, bold: true, color: C.white });
s.addText("Clase final: Notas de Crédito y Débito, Baja, Resumen Diario y Código QR", {
  x: M, y: 3.5, w: 11.2, h: 0.9, margin: 0, fontFace: F.body, fontSize: 22, color: C.mint,
});
s.addText("Comunicación con los Web Services de SUNAT", { x: M, y: 6.4, w: 11, h: 0.4, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: "9AE6D6" });
s.addNotes("Bienvenida. Esta es la última clase: cerramos el ciclo de comprobantes. Ya emiten boleta y factura; hoy agregamos notas de crédito/débito, la baja, el resumen diario de boletas, la consulta de estado y el código QR. La meta no es copiar código, sino entender el QUÉ, el CÓMO y el PORQUÉ de cada pieza.");

// =================================================================
// SLIDE 2 — Agenda
// =================================================================
s = pres.addSlide();
header(s, "Agenda", "Lo que construiremos y entenderemos hoy");
const agenda = [
  ["1", "Repaso de la arquitectura", "Cómo viaja un comprobante hasta SUNAT"],
  ["2", "Notas de crédito y débito", "Tipos 07 y 08, con su motivo y referencia"],
  ["3", "Código QR", "La representación impresa verificable"],
  ["4", "Consulta de estado", "Preguntar a SUNAT por el ticket"],
  ["5", "Baja de documentos", "Anular ante SUNAT correctamente"],
  ["6", "Resumen diario", "Informar las boletas del día"],
];
const aX = [M, 4.63, 8.66], aY = [1.75, 4.05];
agenda.forEach((it, i) => {
  const x = aX[i % 3], y = aY[Math.floor(i / 3)];
  card(s, x, y, 4.0, 2.05, C.tint);
  circle(s, x + 0.28, y + 0.28, 0.62, C.teal, it[0], C.white, 22);
  s.addText(it[1], { x: x + 1.05, y: y + 0.3, w: 2.8, h: 0.6, margin: 0, fontFace: F.body, fontSize: 16.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(it[2], { x: x + 0.28, y: y + 1.05, w: 3.5, h: 0.8, margin: 0, fontFace: F.body, fontSize: 13, color: C.muted, valign: "top" });
});
s.addNotes("Recorremos el temario del día. Cada bloque responde a un 'para qué' real del negocio: corregir/anular operaciones (notas y baja), cumplir con el reporte de boletas (resumen) y dar verificabilidad al comprobante (QR). Todo se apoya en lo que ya hicimos con boleta y factura.");

// =================================================================
// SLIDE 3 — Arquitectura
// =================================================================
s = pres.addSlide();
header(s, "Repaso", "¿Cómo viaja un comprobante hasta SUNAT?");
const flow = [
  ["Nuestro sistema PHP", "Venta / NotaComprobante\narma los datos", C.teal],
  ["API OSE/PSE", "facturalahoy.com\ngenera y firma el XML", C.seafoam],
  ["SUNAT", "valida y responde\nticket + CDR", C.dark],
];
let fx = M;
const fw = 3.7, fy = 2.3, fh = 1.9;
flow.forEach((b, i) => {
  card(s, fx, fy, fw, fh, i === 2 ? C.dark : C.tint);
  s.addText(b[0], { x: fx + 0.2, y: fy + 0.25, w: fw - 0.4, h: 0.6, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: i === 2 ? C.white : C.ink, align: "center" });
  s.addText(b[1], { x: fx + 0.2, y: fy + 0.9, w: fw - 0.4, h: 0.85, margin: 0, fontFace: F.body, fontSize: 13, color: i === 2 ? C.mint : C.muted, align: "center", valign: "top" });
  if (i < 2) s.addText("➜", { x: fx + fw - 0.05, y: fy + 0.5, w: 0.65, h: 0.9, margin: 0, fontFace: F.body, fontSize: 30, bold: true, color: C.teal, align: "center", valign: "middle" });
  fx += fw + 0.55;
});
s.addText("La respuesta (ticket, estado y CDR) regresa por el mismo camino y se guarda en la base de datos.", {
  x: M, y: 4.5, w: W - 2 * M, h: 0.5, margin: 0, fontFace: F.body, fontSize: 15, italic: true, color: C.muted, align: "center",
});
card(s, M, 5.25, W - 2 * M, 1.4, C.tint2);
s.addText([
  { text: "Clave del código:  ", options: { bold: true, color: C.ink } },
  { text: "toda salida a SUNAT pasa por un único método ", options: { color: C.ink } },
  { text: "Facturalaya::post()", options: { fontFace: F.mono, color: C.teal, bold: true } },
  { text: ".  Boleta, factura, nota, baja, resumen y consulta lo reutilizan.", options: { color: C.ink } },
], { x: M + 0.3, y: 5.5, w: W - 2 * M - 0.6, h: 0.9, margin: 0, fontFace: F.body, fontSize: 15, valign: "middle" });
s.addNotes("Idea central: nuestro sistema NO habla UBL ni firma certificados; arma datos (JSON) y se los pasa a un intermediario autorizado (OSE/PSE) que genera el XML, lo firma digitalmente y lo envía a SUNAT. Mencionar que centralizamos el envío HTTP en un solo método post() para no repetir cURL en cada operación: eso es reutilización y mantenibilidad.");

// =================================================================
// SLIDE 4 — Por qué la API firma el XML
// =================================================================
s = pres.addSlide();
header(s, "El porqué", "¿Quién genera y firma el XML? ¿Por qué?");
card(s, M, 1.8, 5.85, 4.7, C.tint);
s.addText("Lo que hace NUESTRO sistema", { x: M + 0.3, y: 2.0, w: 5.3, h: 0.5, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: C.teal });
s.addText([
  { text: "Registrar el comprobante en la base de datos", options: { bullet: true, breakLine: true } },
  { text: "Calcular subtotales, IGV y total", options: { bullet: true, breakLine: true } },
  { text: "Armar un JSON con emisor, cliente e ítems", options: { bullet: true, breakLine: true } },
  { text: "Enviarlo por HTTP con el token del emisor", options: { bullet: true, breakLine: true } },
  { text: "Guardar la respuesta (ticket, estado, CDR)", options: { bullet: true } },
], { x: M + 0.3, y: 2.6, w: 5.3, h: 3.7, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 8 });

card(s, 6.85, 1.8, 5.85, 4.7, C.dark);
s.addText("Lo que hace la API (OSE/PSE)", { x: 7.15, y: 2.0, w: 5.3, h: 0.5, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: C.mint });
s.addText([
  { text: "Convertir el JSON a XML UBL 2.1 de SUNAT", options: { bullet: true, breakLine: true } },
  { text: "Firmar digitalmente con el certificado", options: { bullet: true, breakLine: true } },
  { text: "Enviar a los Web Services de SUNAT", options: { bullet: true, breakLine: true } },
  { text: "Recibir el CDR (constancia de recepción)", options: { bullet: true, breakLine: true } },
  { text: "Devolvernos el resultado y el archivo", options: { bullet: true } },
], { x: 7.15, y: 2.6, w: 5.3, h: 3.7, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.codeTx, paraSpaceAfter: 8 });
s.addText("Delegamos la complejidad (UBL, criptografía, certificados) y nos enfocamos en la lógica del negocio.", {
  x: M, y: 6.7, w: W - 2 * M, h: 0.4, margin: 0, fontFace: F.body, fontSize: 13.5, italic: true, color: C.muted, align: "center",
});
s.addNotes("Aquí está el 'porqué' que más confunde a los alumnos. El flyer dice 'firma de XML con PHP', pero en la práctica eso lo resuelve el OSE. Explicar la ventaja: manejar certificados y UBL a mano es complejo y propenso a errores; delegarlo nos deja concentrarnos en datos y reglas de negocio. Si quisieran hacerlo 100% en PHP, existen librerías (greenter), pero es otro nivel de esfuerzo.");

// =================================================================
// SLIDE 5 — Nota de crédito
// =================================================================
s = pres.addSlide();
header(s, "Documento 07", "Nota de Crédito: corregir o anular a la baja");
card(s, M, 1.8, 5.6, 4.8, C.tint);
s.addText("¿Cuándo se usa?", { x: M + 0.3, y: 2.0, w: 5.0, h: 0.45, margin: 0, fontFace: F.body, fontSize: 16, bold: true, color: C.teal });
s.addText([
  { text: "Anular una operación ya emitida", options: { bullet: true, breakLine: true } },
  { text: "Devoluciones (totales o por ítem)", options: { bullet: true, breakLine: true } },
  { text: "Descuentos posteriores", options: { bullet: true, breakLine: true } },
  { text: "Disminuir el valor o corregir datos", options: { bullet: true } },
], { x: M + 0.3, y: 2.55, w: 5.0, h: 2.0, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6 });
s.addText([
  { text: "Regla de oro:  ", options: { bold: true, color: C.ink } },
  { text: "siempre reduce o corrige un comprobante que ya existe.", options: { color: C.ink } },
], { x: M + 0.3, y: 5.5, w: 5.0, h: 0.9, margin: 0, fontFace: F.body, fontSize: 14, italic: true, valign: "top" });

card(s, 6.95, 1.8, 5.75, 4.8, C.white);
s.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: 6.95, y: 1.8, w: 5.75, h: 4.8, rectRadius: 0.08, fill: { color: C.white }, line: { color: "CBD5E1", width: 1 } });
s.addText("Catálogo 09 SUNAT — motivos (extracto)", { x: 7.25, y: 2.0, w: 5.2, h: 0.45, margin: 0, fontFace: F.body, fontSize: 15, bold: true, color: C.ink });
const cat09 = [["01", "Anulación de la operación"], ["02", "Anulación por error en el RUC"], ["03", "Corrección por error en la descripción"], ["06", "Devolución total"], ["07", "Devolución por ítem"], ["09", "Disminución en el valor"], ["10", "Otros conceptos"]];
let cy = 2.55;
cat09.forEach(m => {
  circle(s, 7.25, cy, 0.4, C.seafoam, m[0], C.white, 12);
  s.addText(m[1], { x: 7.8, y: cy, w: 4.6, h: 0.4, margin: 0, fontFace: F.body, fontSize: 13.5, color: C.ink, valign: "middle" });
  cy += 0.55;
});
s.addNotes("La nota de crédito SIEMPRE juega a la baja: anula, devuelve, descuenta o disminuye. El motivo no es texto libre: SUNAT define el catálogo 09. En el sistema lo cargamos como un <select> que viene del método NotaComprobante::motivosCredito(). Pedir ejemplos a los alumnos: '¿devolvieron un producto?' -> nota de crédito 06.");

// =================================================================
// SLIDE 6 — Nota de débito
// =================================================================
s = pres.addSlide();
header(s, "Documento 08", "Nota de Débito: aumentar el valor cobrado");
card(s, M, 1.8, 5.6, 4.8, C.tint);
s.addText("¿Cuándo se usa?", { x: M + 0.3, y: 2.0, w: 5.0, h: 0.45, margin: 0, fontFace: F.body, fontSize: 16, bold: true, color: C.teal });
s.addText([
  { text: "Cobrar intereses por mora", options: { bullet: true, breakLine: true } },
  { text: "Aumentar el valor de la operación", options: { bullet: true, breakLine: true } },
  { text: "Penalidades u otros cargos", options: { bullet: true } },
], { x: M + 0.3, y: 2.55, w: 5.0, h: 1.6, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6 });
s.addText([
  { text: "Espejo de la nota de crédito:  ", options: { bold: true, color: C.ink } },
  { text: "siempre incrementa lo que el cliente debe pagar.", options: { color: C.ink } },
], { x: M + 0.3, y: 5.2, w: 5.0, h: 1.1, margin: 0, fontFace: F.body, fontSize: 14, italic: true, valign: "top" });

s.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: 6.95, y: 1.8, w: 5.75, h: 4.8, rectRadius: 0.08, fill: { color: C.white }, line: { color: "CBD5E1", width: 1 } });
s.addText("Catálogo 10 SUNAT — motivos", { x: 7.25, y: 2.0, w: 5.2, h: 0.45, margin: 0, fontFace: F.body, fontSize: 15, bold: true, color: C.ink });
const cat10 = [["01", "Intereses por mora"], ["02", "Aumento en el valor"], ["03", "Penalidades / otros conceptos"]];
let dy = 2.7;
cat10.forEach(m => {
  circle(s, 7.25, dy, 0.45, C.amber, m[0], C.white, 14);
  s.addText(m[1], { x: 7.85, y: dy, w: 4.6, h: 0.45, margin: 0, fontFace: F.body, fontSize: 15, color: C.ink, valign: "middle" });
  dy += 0.7;
});
s.addText("Mismo código, distinto catálogo: el sistema cambia el <select> según el tipo de nota.", { x: 7.25, y: 5.4, w: 5.2, h: 0.9, margin: 0, fontFace: F.body, fontSize: 13, italic: true, color: C.muted, valign: "top" });
s.addNotes("La nota de débito es el espejo: juega al alza. El caso más común en Perú es el interés por mora. Recalcar que en el código es exactamente la misma clase NotaComprobante; solo cambia el tipo (08) y el catálogo de motivos (10). Reutilizar la misma lógica para dos documentos es una decisión de diseño deliberada.");

// =================================================================
// SLIDE 7 — Decisión: reusar tabla ventas
// =================================================================
s = pres.addSlide();
header(s, "Decisión de diseño", "¿Por qué guardamos las notas en la tabla 'ventas'?");
const pros = [
  ["Misma estructura", "Cabecera + detalle + estados SUNAT: una nota encaja igual que una venta."],
  ["Menos código", "Reutilizamos listado, impresión y envío en lugar de duplicarlos."],
  ["Aparecen juntas", "Las notas se ven en el mismo listado de comprobantes, sin pantallas nuevas."],
  ["Evoluciona sola", "Las columnas extra se agregan con ALTER TABLE automático si faltan."],
];
const pX = [M, 6.75], pY = [1.8, 3.95];
pros.forEach((p, i) => {
  const x = pX[i % 2], y = pY[Math.floor(i / 2)];
  card(s, x, y, 5.95, 1.95, C.tint);
  circle(s, x + 0.3, y + 0.32, 0.55, C.teal, "✓", C.white, 18);
  s.addText(p[0], { x: x + 1.05, y: y + 0.32, w: 4.6, h: 0.55, margin: 0, fontFace: F.body, fontSize: 16.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(p[1], { x: x + 0.32, y: y + 1.0, w: 5.3, h: 0.85, margin: 0, fontFace: F.body, fontSize: 13.5, color: C.muted, valign: "top" });
});
s.addText("Columnas que agregamos a 'ventas':  id_doc_afectado · tipo_doc_afectado · serie_afectada · correlativo_afectado · cod_motivo · desc_motivo", {
  x: M, y: 6.5, w: W - 2 * M, h: 0.6, margin: 0, fontFace: F.mono, fontSize: 11.5, color: C.teal, align: "center",
});
s.addNotes("Decisión de arquitectura clave. En vez de crear tablas nota_credito/nota_debito (como otro proyecto que vimos), reutilizamos 'ventas'. Trade-off honesto: la tabla guarda varios tipos de documento, pero a cambio ganamos muchísimo: un solo listado, una sola impresión, un solo flujo de envío. Las columnas propias de la nota se añaden con un ALTER TABLE idempotente (solo si no existen) — eso es lo que llamamos esquema 'auto-evolutivo'.");

// =================================================================
// SLIDE 8 — docs_referencia
// =================================================================
s = pres.addSlide();
header(s, "Concepto + Código", "Una nota nunca vive sola: docs_referencia");
s.addText([
  { text: "Toda nota de crédito/débito DEBE apuntar al comprobante que afecta. ", options: { color: C.ink } },
  { text: "SUNAT necesita saber qué documento se está corrigiendo, con qué motivo.", options: { color: C.ink } },
], { x: M, y: 1.75, w: W - 2 * M, h: 0.7, margin: 0, fontFace: F.body, fontSize: 16, valign: "top" });
codeBlock(s, M, 2.6, W - 2 * M, 3.5, code([
  { x: "// Facturalaya::enviarNota()  — campos propios de la nota", t: "c" },
  "$payload['cod_tipo_documento'] = $esCredito ? '07' : '08';",
  "$payload['serie_comprobante']  = $nota['serie'];      // FC01 / BC01 ...",
  "",
  { x: "// el corazón: referencia al documento afectado", t: "c" },
  { x: "$payload['docs_referencia'] = [[", t: "k" },
  { x: "    'cod_tipo_documento' => '03',   // 01=factura, 03=boleta", t: "k" },
  { x: "    'serie'  => 'B001',", t: "k" },
  { x: "    'numero' => 1,", t: "k" },
  { x: "]];", t: "k" },
  "$payload['cod_tipo_nota_credito'] = $nota['cod_motivo']; // catálogo 09",
]));
s.addText("Sin docs_referencia, SUNAT rechaza la nota. Es la diferencia entre una nota válida y un error.", {
  x: M, y: 6.35, w: W - 2 * M, h: 0.5, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted, align: "center",
});
s.addNotes("Mostrar el código real. El concepto que deben memorizar: docs_referencia. Una nota es una corrección de algo, así que ese 'algo' debe identificarse con tipo+serie+número. Señalar que reutilizamos buildPayload() (el mismo que arma boletas/facturas) y solo sobre-escribimos lo propio de la nota: tipo 07/08, serie y la referencia. Reutilización otra vez.");

// =================================================================
// SLIDE 9 — Series y correlativos
// =================================================================
s = pres.addSlide();
header(s, "Numeración", "Series y correlativos de las notas");
const series = [
  ["FC01", "Nota de crédito de una FACTURA", C.teal],
  ["BC01", "Nota de crédito de una BOLETA", C.seafoam],
  ["FD01", "Nota de débito de una FACTURA", C.amber],
  ["BD01", "Nota de débito de una BOLETA", C.dark],
];
const sX = [M, 6.75], sY = [1.85, 4.05];
series.forEach((it, i) => {
  const x = sX[i % 2], y = sY[Math.floor(i / 2)];
  card(s, x, y, 5.95, 1.95, i === 3 ? C.dark : C.tint);
  s.addText(it[0], { x: x + 0.35, y: y + 0.45, w: 2.4, h: 1.0, margin: 0, fontFace: F.mono, fontSize: 40, bold: true, color: it[2] === C.dark ? C.mint : it[2] });
  s.addText(it[1], { x: x + 2.9, y: y + 0.5, w: 2.85, h: 1.0, margin: 0, fontFace: F.body, fontSize: 15, bold: true, color: i === 3 ? C.white : C.ink, valign: "middle" });
});
s.addText("Cada serie lleva su propio correlativo: getNextCorrelativo() consulta el MAX(correlativo) de esa serie y suma 1.", {
  x: M, y: 6.45, w: W - 2 * M, h: 0.6, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted, align: "center",
});
s.addNotes("La serie codifica dos cosas: tipo de nota (C/D) y tipo de documento afectado (Factura/Boleta). Por eso 4 combinaciones. El correlativo es independiente por serie y se calcula con MAX(correlativo)+1 — explicar por qué no podemos compartir el correlativo de las boletas.");

// =================================================================
// SLIDE 10 — Lógica NotaComprobante
// =================================================================
s = pres.addSlide();
header(s, "La lógica", "El flujo de NotaComprobante.php, paso a paso");
const steps = [
  ["1", "Validar", "Motivo del catálogo, documento afectado existente y con ítems."],
  ["2", "Calcular", "Subtotal, IGV (18%) y total a partir del detalle."],
  ["3", "Guardar", "Inserta cabecera + detalle en 'ventas' dentro de una transacción."],
  ["4", "Enviar", "Llama a Facturalaya->enviarNota() fuera de la transacción."],
  ["5", "Registrar", "Guarda ticket, estado y CDR que devuelve SUNAT."],
];
let stx = M;
const stw = 2.34;
steps.forEach((st, i) => {
  card(s, stx, 2.1, stw, 3.4, i % 2 ? C.tint2 : C.tint);
  circle(s, stx + (stw - 0.7) / 2, 2.4, 0.7, C.teal, st[0], C.white, 24);
  s.addText(st[1], { x: stx + 0.1, y: 3.3, w: stw - 0.2, h: 0.5, margin: 0, fontFace: F.body, fontSize: 16, bold: true, color: C.ink, align: "center" });
  s.addText(st[2], { x: stx + 0.18, y: 3.85, w: stw - 0.36, h: 1.5, margin: 0, fontFace: F.body, fontSize: 12.5, color: C.muted, align: "center", valign: "top" });
  stx += stw + 0.2;
});
s.addText([
  { text: "¿Por qué enviar a SUNAT FUERA de la transacción?  ", options: { bold: true, color: C.ink } },
  { text: "Porque una llamada de red es lenta; primero aseguramos el dato en la BD (commit) y recién entonces hablamos con SUNAT.", options: { color: C.ink } },
], { x: M, y: 5.75, w: W - 2 * M, h: 1.0, margin: 0, fontFace: F.body, fontSize: 14.5, valign: "top" });
s.addNotes("Recorrer el flujo. El punto pedagógico fuerte: separar la persistencia (transacción rápida en BD) del envío a la API (lento, por red). Hacemos commit ANTES de llamar a SUNAT para no mantener bloqueada la base esperando la red, y para no perder el comprobante si la red falla. Esto enlaza con la slide de resiliencia.");

// =================================================================
// SLIDE 11 — QR
// =================================================================
s = pres.addSlide();
header(s, "Representación impresa", "Código QR: qué guarda y por qué");
s.addText("El QR resume el comprobante en una sola cadena, separada por barras (|). Permite verificar el documento sin sistemas extra.", {
  x: M, y: 1.75, w: W - 2 * M, h: 0.7, margin: 0, fontFace: F.body, fontSize: 16, valign: "top",
});
codeBlock(s, M, 2.55, 8.4, 2.5, code([
  { x: "// Cadena QR según SUNAT (print_venta.php)", t: "c" },
  { x: "RUC | tipoDoc | serie | numero |", t: "k" },
  { x: "IGV | total | fecha |", t: "k" },
  { x: "tipoDocCliente | numDocCliente", t: "k" },
  "",
  "20123456789|03|B001|1|1.98|12.98|2026-06-24|1|45678912",
]));
// QR "glyph" decorativo
card(s, 9.3, 2.55, 3.4, 3.4, C.white);
s.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: 9.3, y: 2.55, w: 3.4, h: 3.4, rectRadius: 0.06, fill: { color: C.white }, line: { color: "CBD5E1", width: 1 } });
// patrón QR simple con cuadrados
const qp = [[0,0],[1,0],[2,0],[4,0],[0,1],[2,1],[4,1],[0,2],[1,2],[2,2],[3,2],[0,4],[2,4],[3,4],[4,4],[1,3],[4,3],[3,1]];
const qx = 9.85, qy = 3.1, qd = 0.46;
qp.forEach(([cx, ry]) => s.addShape(pres.shapes.RECTANGLE, { x: qx + cx * qd, y: qy + ry * qd, w: qd - 0.06, h: qd - 0.06, fill: { color: C.ink }, line: { type: "none" } }));
s.addText("Se genera con qrcodejs (CDN), sin librerías PHP. El mismo formato sirve para boleta, factura y notas.", {
  x: M, y: 5.4, w: 8.4, h: 0.9, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted, valign: "top",
});
s.addNotes("El QR no es decorativo: es la 'representación impresa' verificable. Explicar cada campo de la cadena. Decisión técnica: lo generamos en el navegador con qrcodejs (CDN) para no agregar dependencias de PHP, coherente con que el proyecto ya usa librerías por CDN. El QR funciona igual para todos los comprobantes porque la cadena es estándar.");

// =================================================================
// SLIDE 12 — Baja vs Resumen
// =================================================================
s = pres.addSlide();
header(s, "Dos caminos", "Anular ante SUNAT: baja vs. resumen");
card(s, M, 1.9, 5.85, 4.4, C.tint);
circle(s, M + 0.32, 2.15, 0.62, C.teal, "F", C.white, 20);
s.addText("Factura y Notas", { x: M + 1.1, y: 2.18, w: 4.5, h: 0.55, margin: 0, fontFace: F.body, fontSize: 18, bold: true, color: C.ink, valign: "middle" });
s.addText([
  { text: "Comunicación de baja", options: { bold: true, breakLine: true, color: C.teal } },
  { text: "Se anula documento por documento, enviando la baja a SUNAT.", options: { breakLine: true } },
  { text: " ", options: { breakLine: true, fontSize: 8 } },
  { text: "Facturalaya->enviarBaja()", options: { fontFace: F.mono, color: C.teal } },
], { x: M + 0.32, y: 2.95, w: 5.25, h: 3.1, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, valign: "top", paraSpaceAfter: 6 });

card(s, 6.95, 1.9, 5.75, 4.4, C.dark);
circle(s, 7.27, 2.15, 0.62, C.amber, "B", C.white, 20);
s.addText("Boletas", { x: 8.05, y: 2.18, w: 4.4, h: 0.55, margin: 0, fontFace: F.body, fontSize: 18, bold: true, color: C.white, valign: "middle" });
s.addText([
  { text: "Resumen diario", options: { bold: true, breakLine: true, color: C.mint } },
  { text: "Las boletas NO se dan de baja una por una: se informan (y se anulan) en el resumen del día.", options: { breakLine: true, color: C.codeTx } },
  { text: " ", options: { breakLine: true, fontSize: 8 } },
  { text: "Facturalaya->enviarResumen()", options: { fontFace: F.mono, color: C.mint } },
], { x: 7.27, y: 2.95, w: 5.15, h: 3.1, margin: 0, fontFace: F.body, fontSize: 14.5, valign: "top", paraSpaceAfter: 6 });
s.addText("¿Por qué distinto? Son reglas de SUNAT: la factura se identifica una a una; la boleta es consumo masivo y se reporta agregada.", {
  x: M, y: 6.5, w: W - 2 * M, h: 0.6, margin: 0, fontFace: F.body, fontSize: 13.5, italic: true, color: C.muted, align: "center",
});
s.addNotes("Error típico de principiantes: intentar 'dar de baja' una boleta con comunicación de baja. No se puede. SUNAT distingue: facturas y notas -> comunicación de baja individual; boletas -> resumen diario. En el código, deleteRegistro() decide la ruta según el tipo de comprobante. Es un buen ejemplo de cómo las reglas del negocio se traducen en ramas del código.");

// =================================================================
// SLIDE 13 — Consulta de estado
// =================================================================
s = pres.addSlide();
header(s, "Asíncrono", "Consulta de estado: ¿por qué un 'ticket'?");
const tl = [
  ["Enviamos", "El comprobante sale a SUNAT"],
  ["Ticket", "SUNAT responde un número de ticket"],
  ["Consultamos", "Preguntamos por ese ticket"],
  ["CDR", "SUNAT entrega aceptado / rechazado"],
];
let tx = M;
const tw = 2.9;
tl.forEach((t, i) => {
  card(s, tx, 2.3, tw, 2.0, C.tint);
  circle(s, tx + 0.25, 2.5, 0.55, C.seafoam, String(i + 1), C.white, 18);
  s.addText(t[0], { x: tx + 0.95, y: 2.5, w: tw - 1.1, h: 0.55, margin: 0, fontFace: F.body, fontSize: 16, bold: true, color: C.ink, valign: "middle" });
  s.addText(t[1], { x: tx + 0.28, y: 3.2, w: tw - 0.5, h: 0.95, margin: 0, fontFace: F.body, fontSize: 13, color: C.muted, valign: "top" });
  if (i < 3) s.addText("➜", { x: tx + tw - 0.12, y: 2.7, w: 0.5, h: 1.0, margin: 0, fontFace: F.body, fontSize: 24, bold: true, color: C.teal, align: "center", valign: "middle" });
  tx += tw + 0.23;
});
s.addText([
  { text: "El procesamiento en SUNAT no es instantáneo.  ", options: { bold: true, color: C.ink } },
  { text: "Por eso primero recibimos un ticket y luego, con Venta::consultarEstadoSunat(), preguntamos el resultado final y actualizamos la base.", options: { color: C.ink } },
], { x: M, y: 4.8, w: W - 2 * M, h: 1.2, margin: 0, fontFace: F.body, fontSize: 15, valign: "top" });
s.addNotes("Concepto de comunicación asíncrona. SUNAT no responde 'aceptado' al instante en el modo masivo; entrega un ticket (como el ticket de una lavandería). Después consultamos ese ticket para obtener el CDR. En el sistema agregamos el botón 'Actualizar estado SUNAT' en el modal Ver, que llama a consultarEstadoSunat() y refresca el estado.");

// =================================================================
// SLIDE 14 — Resiliencia / pendiente
// =================================================================
s = pres.addSlide();
header(s, "Resiliencia", "¿Por qué guardamos el estado 'pendiente'?");
card(s, M, 1.9, W - 2 * M, 1.5, C.tint);
s.addText([
  { text: "Regla:  ", options: { bold: true, color: C.teal } },
  { text: "el comprobante se guarda SIEMPRE, aunque el envío a SUNAT falle. ", options: { color: C.ink } },
  { text: "Nunca perdemos un documento por un problema de red.", options: { color: C.ink, bold: true } },
], { x: M + 0.3, y: 2.2, w: W - 2 * M - 0.6, h: 0.9, margin: 0, fontFace: F.body, fontSize: 16.5, valign: "middle" });
const rz = [
  ["Aceptado", "SUNAT validó el comprobante.", C.teal],
  ["Pendiente", "Guardado, pero el envío aún no se completó. Se reintenta con la consulta.", C.amber],
  ["Baja", "Anulado correctamente ante SUNAT.", C.muted],
];
let rx = M;
const rw = 4.0;
rz.forEach(r => {
  card(s, rx, 3.7, rw, 2.3, C.white);
  s.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: rx, y: 3.7, w: rw, h: 2.3, rectRadius: 0.08, fill: { color: C.white }, line: { color: "CBD5E1", width: 1 } });
  circle(s, rx + 0.3, 3.95, 0.5, r[2], "●", C.white, 12);
  s.addText(r[0], { x: rx + 0.95, y: 3.95, w: rw - 1.1, h: 0.5, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: C.ink, valign: "middle" });
  s.addText(r[1], { x: rx + 0.3, y: 4.65, w: rw - 0.6, h: 1.2, margin: 0, fontFace: F.body, fontSize: 13.5, color: C.muted, valign: "top" });
  rx += rw + 0.32;
});
s.addNotes("Resiliencia = el sistema no se rompe ante fallos. Hacemos commit en BD antes de llamar a la API; si la API o la red fallan, el comprobante queda 'pendiente' con su mensaje, y el usuario puede reintentar con la consulta de estado. Esto es importante para la nota honesta del proyecto: como aún no tenemos el endpoint real de notas, todas las notas quedan 'pendiente' hasta poner la URL correcta — y aun así no se pierde nada.");

// =================================================================
// SLIDE 15 — Buenas prácticas
// =================================================================
s = pres.addSlide();
header(s, "Calidad del código", "Buenas prácticas que aplicamos");
const bp = [
  ["Consultas preparadas", "bind_param evita inyección SQL en todas las escrituras."],
  ["Una clase, una tarea", "Venta, NotaComprobante, ResumenDiario, Facturalaya: responsabilidades claras."],
  ["No repetir (DRY)", "Un solo post() y un solo buildPayload() para todos los envíos."],
  ["Esquema idempotente", "ALTER TABLE solo si la columna no existe: seguro de re-ejecutar."],
  ["UI separada de la lógica", "Las páginas llaman por AJAX a las clases; nada de SQL en la vista."],
  ["Fallar con elegancia", "Errores controlados y mensajes claros al usuario (toastr)."],
];
const bX = [M, 4.63, 8.66], bY = [1.8, 4.15];
bp.forEach((b, i) => {
  const x = bX[i % 3], y = bY[Math.floor(i / 3)];
  card(s, x, y, 4.0, 2.1, i % 2 ? C.tint2 : C.tint);
  circle(s, x + 0.28, y + 0.28, 0.55, C.teal, "✓", C.white, 16);
  s.addText(b[0], { x: x + 0.95, y: y + 0.28, w: 2.9, h: 0.55, margin: 0, fontFace: F.body, fontSize: 15.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(b[1], { x: x + 0.3, y: y + 0.95, w: 3.5, h: 1.0, margin: 0, fontFace: F.body, fontSize: 12.5, color: C.muted, valign: "top" });
});
s.addNotes("Cerrar el 'por qué' del código bien hecho. No basta con que funcione: debe ser seguro (prepared statements), entendible (una clase por responsabilidad), mantenible (no repetir código) y robusto (idempotencia, manejo de errores). Pedir a los alumnos que identifiquen cada práctica en el código real que vimos.");

// =================================================================
// SLIDE 16 — Demo en vivo
// =================================================================
s = pres.addSlide();
header(s, "Manos a la obra", "Guion de la demostración en vivo");
const demo = [
  "Ingresar con admin / admin123 y abrir el Dashboard",
  "Emitir una boleta o factura en 'Nueva Venta'",
  "En el listado, botón 'Nota C/D' → elegir motivo → generar la nota",
  "Imprimir la nota: ver el QR y el documento afectado",
  "Modal 'Ver' → 'Actualizar estado SUNAT' (consulta por ticket)",
  "Resúmenes → cargar boletas del día y enviar el resumen",
  "Anular una factura/nota → comunicación de baja",
];
card(s, M, 1.85, W - 2 * M, 4.95, C.tint);
let gy = 2.2;
demo.forEach((d, i) => {
  circle(s, M + 0.35, gy, 0.5, C.teal, String(i + 1), C.white, 16);
  s.addText(d, { x: M + 1.05, y: gy, w: W - 2 * M - 1.5, h: 0.5, margin: 0, fontFace: F.body, fontSize: 16, color: C.ink, valign: "middle" });
  gy += 0.64;
});
s.addNotes("Guion de la demo. Hacerla en el navegador en http://localhost:8000. Recordar que el envío a SUNAT quedará 'pendiente' por el endpoint de prueba: aprovechar ese momento para explicar la slide de resiliencia. Invitar a los alumnos a predecir qué pasará en cada paso antes de hacer clic.");

// =================================================================
// SLIDE 17 — Cierre
// =================================================================
s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: -1.6, y: 4.7, w: 4.4, h: 4.4, fill: { color: C.teal, transparency: 75 }, line: { type: "none" } });
s.addShape(pres.shapes.OVAL, { x: 11.4, y: -1.4, w: 3.6, h: 3.6, fill: { color: C.seafoam, transparency: 80 }, line: { type: "none" } });
s.addText("Para cerrar", { x: M, y: 1.7, w: 11, h: 0.4, margin: 0, fontFace: F.body, fontSize: 15, bold: true, color: C.mint, charSpacing: 3 });
s.addText("Ya saben emitir TODO el ciclo de comprobantes", { x: M, y: 2.15, w: 11.8, h: 1.3, margin: 0, fontFace: F.head, fontSize: 36, bold: true, color: C.white });
s.addText([
  { text: "Boleta y factura · Notas de crédito y débito · Baja · Resumen diario · Consulta de estado · QR", options: { breakLine: true, color: C.mint } },
], { x: M, y: 3.6, w: 11.8, h: 0.8, margin: 0, fontFace: F.body, fontSize: 17, valign: "top" });
s.addText([
  { text: "Lo único pendiente para envío real: ", options: { bold: true, color: C.white } },
  { text: "confirmar los endpoints de notas/baja/resumen en facturalahoy (marcados // AJUSTAR).", options: { color: "B8C7C9" } },
], { x: M, y: 4.7, w: 11.8, h: 0.9, margin: 0, fontFace: F.body, fontSize: 15, valign: "top" });
s.addText("¿Preguntas?", { x: M, y: 5.9, w: 11, h: 0.7, margin: 0, fontFace: F.head, fontSize: 28, bold: true, color: C.mint });
s.addText("CETI · Facturación Electrónica con PHP", { x: M, y: 6.85, w: 11, h: 0.4, margin: 0, fontFace: F.body, fontSize: 13, italic: true, color: "9AE6D6" });
s.addNotes("Cierre. Resumir el logro: dominan el ciclo completo de comprobantes electrónicos en PHP. Recordar lo único que falta para producción real (los endpoints de notas), que es un cambio de una línea. Abrir a preguntas y, si hay tiempo, repasar el código de NotaComprobante con ellos.");

pres.writeFile({ fileName: "Clase_Final_Facturacion_Electronica.pptx" }).then((f) => console.log("OK:", f));
