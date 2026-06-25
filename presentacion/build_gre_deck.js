const pptxgen = require("pptxgenjs");

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";
pres.author = "CETI";
pres.title = "Guía de Remisión Electrónica (GRE) — Implementación con PHP";

const C = {
  dark: "12243B", teal: "0D9488", seafoam: "2563EB", mint: "60A5FA", indigo: "6366F1",
  white: "FFFFFF", ink: "1E293B", muted: "64748B", tint: "EEF4FF", tint2: "F0FDFA",
  codeBg: "0F172A", codeTx: "E2E8F0", codeKey: "7DD3FC", codeCom: "A7B6CE", codeStr: "FBBF77",
  amber: "D97706",
};
const F = { head: "Cambria", body: "Calibri", mono: "Courier New" };
const W = 13.3, H = 7.5, M = 0.55;
const shadow = () => ({ type: "outer", color: "000000", blur: 8, offset: 3, angle: 90, opacity: 0.12 });

function header(slide, kicker, title) {
  slide.background = { color: C.white };
  slide.addText(kicker.toUpperCase(), { x: M, y: 0.35, w: W - 2 * M, h: 0.3, margin: 0, fontFace: F.mono, fontSize: 12.5, bold: true, color: C.seafoam, charSpacing: 1 });
  slide.addText(title, { x: M, y: 0.64, w: W - 2 * M, h: 0.7, margin: 0, fontFace: F.head, fontSize: 26, bold: true, color: C.ink, valign: "top" });
}
function card(slide, x, y, w, h, fill) {
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w, h, rectRadius: 0.08, fill: { color: fill }, line: { type: "none" }, shadow: shadow() });
}
function circle(slide, x, y, d, fill, glyph, gColor, gSize) {
  slide.addShape(pres.shapes.OVAL, { x, y, w: d, h: d, fill: { color: fill }, line: { type: "none" } });
  slide.addText(glyph, { x, y, w: d, h: d, margin: 0, align: "center", valign: "middle", fontFace: F.body, fontSize: gSize || 16, bold: true, color: gColor || C.white });
}
function codeBox(slide, x, y, w, h, lines, fs) {
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w, h, rectRadius: 0.05, fill: { color: C.codeBg }, line: { type: "none" }, shadow: shadow() });
  const runs = [];
  lines.forEach((ln, i) => {
    const last = i === lines.length - 1;
    const isObj = typeof ln === "object";
    const txt = isObj ? ln.x : ln;
    let color = C.codeTx;
    if (isObj && ln.t === "c") color = C.codeCom;
    if (isObj && ln.t === "k") color = C.codeKey;
    if (isObj && ln.t === "s") color = C.codeStr;
    runs.push({ text: txt === "" ? " " : txt, options: { color, breakLine: !last } });
  });
  slide.addText(runs, { x: x + 0.22, y: y + 0.16, w: w - 0.44, h: h - 0.32, margin: 0, valign: "top", fontFace: F.mono, fontSize: fs || 11.5, lineSpacingMultiple: 1.05 });
}
function panel(slide, x, y, w, titulo, color, bullets) {
  const h = 0.55 + bullets.length * 0.62 + 0.2;
  card(slide, x, y, w, h, C.tint);
  slide.addText(titulo, { x: x + 0.22, y: y + 0.16, w: w - 0.44, h: 0.4, margin: 0, fontFace: F.body, fontSize: 14.5, bold: true, color });
  const runs = bullets.map(b => ({ text: b, options: { bullet: { indent: 12 }, breakLine: true, color: C.ink, paraSpaceAfter: 5 } }));
  slide.addText(runs, { x: x + 0.24, y: y + 0.62, w: w - 0.46, h: h - 0.75, margin: 0, fontFace: F.body, fontSize: 12.5, valign: "top" });
  return h;
}
const N = (s, t) => s.addNotes(t);

// ===== 1 Portada =====
let s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: 10.3, y: -1.8, w: 5.0, h: 5.0, fill: { color: C.seafoam, transparency: 72 }, line: { type: "none" } });
s.addShape(pres.shapes.OVAL, { x: 11.6, y: 4.7, w: 3.3, h: 3.3, fill: { color: C.indigo, transparency: 80 }, line: { type: "none" } });
s.addText("FACTURACIÓN ELECTRÓNICA · CETI", { x: M, y: 1.6, w: 11, h: 0.4, margin: 0, fontFace: F.mono, fontSize: 15, bold: true, color: C.mint, charSpacing: 2 });
s.addText("Guía de Remisión Electrónica (GRE)", { x: M, y: 2.1, w: 12, h: 1.3, margin: 0, fontFace: F.head, fontSize: 46, bold: true, color: C.white });
s.addText("Qué es, para qué sirve y cómo implementarla en PHP — con código", { x: M, y: 3.55, w: 11.8, h: 0.8, margin: 0, fontFace: F.body, fontSize: 20, color: C.mint });
s.addText("El documento que sustenta el TRASLADO de bienes", { x: M, y: 6.4, w: 11, h: 0.4, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: "9DB4D6" });
N(s, "Tema nuevo: la guía de remisión electrónica. A diferencia de la factura (que sustenta la VENTA), la guía sustenta el TRASLADO de los bienes. Aclarar desde el inicio que técnicamente la GRE va por un camino distinto al de la factura (API REST con OAuth2), y eso lo veremos en detalle.");

// ===== 2 Qué es =====
s = pres.addSlide();
header(s, "Concepto", "¿Qué es la Guía de Remisión Electrónica?");
card(s, M, 1.85, W - 2 * M, 1.5, C.tint);
s.addText([
  { text: "Es el documento electrónico que ", options: { color: C.ink } },
  { text: "sustenta el traslado de bienes", options: { bold: true, color: C.seafoam } },
  { text: " de un punto de partida a un punto de llegada. Reemplaza a la guía de remisión en papel.", options: { color: C.ink } },
], { x: M + 0.3, y: 2.1, w: W - 2 * M - 0.6, h: 1.0, margin: 0, fontFace: F.body, fontSize: 17, valign: "middle" });
const comp = [
  ["Factura / Boleta", "Sustenta la VENTA (cuánto se cobró)", C.teal],
  ["Guía de Remisión", "Sustenta el TRASLADO (qué se mueve y a dónde)", C.seafoam],
];
let cx = M;
comp.forEach((c, i) => {
  card(s, cx, 3.7, 5.95, 2.6, i ? C.dark : C.tint2);
  s.addText(c[0], { x: cx + 0.3, y: 3.95, w: 5.4, h: 0.6, margin: 0, fontFace: F.body, fontSize: 19, bold: true, color: i ? C.white : C.ink });
  s.addText(c[1], { x: cx + 0.3, y: 4.65, w: 5.4, h: 1.4, margin: 0, fontFace: F.body, fontSize: 15, color: i ? C.mint : C.muted, valign: "top" });
  cx += 6.2;
});
N(s, "La idea más importante de toda la clase: factura = venta; guía = traslado. Ejemplo: vendes mercadería con factura, pero para llevarla en un camión necesitas la guía de remisión que dice qué bienes son, de dónde a dónde van y en qué vehículo. Son documentos distintos con propósitos distintos.");

// ===== 3 Por qué / cuándo =====
s = pres.addSlide();
header(s, "¿Cuándo se usa?", "Por qué importa la GRE");
const usos = [
  ["Venta con traslado", "Cuando entregas la mercadería al cliente"],
  ["Compra", "Cuando recoges bienes de un proveedor"],
  ["Traslado entre locales", "Mover stock de un almacén a otro"],
  ["Devoluciones", "Bienes que regresan"],
];
const uX = [M, 6.75], uY = [1.85, 4.0];
usos.forEach((u, i) => {
  const x = uX[i % 2], y = uY[Math.floor(i / 2)];
  card(s, x, y, 5.95, 1.95, C.tint);
  circle(s, x + 0.3, y + 0.32, 0.55, C.seafoam, String(i + 1), C.white, 18);
  s.addText(u[0], { x: x + 1.05, y: y + 0.32, w: 4.6, h: 0.55, margin: 0, fontFace: F.body, fontSize: 16.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(u[1], { x: x + 0.32, y: y + 1.0, w: 5.3, h: 0.85, margin: 0, fontFace: F.body, fontSize: 13.5, color: C.muted, valign: "top" });
});
s.addText("Hoy la GRE es OBLIGATORIA y electrónica: sin ella, el traslado de bienes puede ser observado o multado por SUNAT.", { x: M, y: 6.45, w: W - 2 * M, h: 0.6, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted, align: "center" });
N(s, "Siempre que se MUEVEN bienes físicamente se necesita una guía. Recalcar que ya es obligatoria y electrónica (la guía de papel quedó atrás). Sin guía válida, en un control de SUNAT en carretera pueden retener la mercadería.");

// ===== 4 Dos tipos =====
s = pres.addSlide();
header(s, "Catálogo 01 SUNAT", "Dos tipos de guía: remitente y transportista");
card(s, M, 1.9, 5.95, 4.5, C.tint);
circle(s, M + 0.32, 2.18, 0.7, C.seafoam, "09", C.white, 18);
s.addText("GRE Remitente", { x: M + 1.2, y: 2.2, w: 4.5, h: 0.6, margin: 0, fontFace: F.body, fontSize: 19, bold: true, color: C.ink, valign: "middle" });
s.addText([
  { text: "La emite quien ENVÍA los bienes", options: { bullet: true, breakLine: true } },
  { text: "(el vendedor, el dueño de la mercadería)", options: { breakLine: true, color: C.muted } },
  { text: "Tipo de documento: 09", options: { bullet: true, breakLine: true } },
  { text: "Es la más común en un negocio", options: { bullet: true } },
], { x: M + 0.32, y: 3.1, w: 5.4, h: 3.1, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6, valign: "top" });

card(s, 6.95, 1.9, 5.75, 4.5, C.dark);
circle(s, 7.27, 2.18, 0.7, C.indigo, "31", C.white, 18);
s.addText("GRE Transportista", { x: 8.15, y: 2.2, w: 4.4, h: 0.6, margin: 0, fontFace: F.body, fontSize: 19, bold: true, color: C.white, valign: "middle" });
s.addText([
  { text: "La emite la empresa de TRANSPORTE", options: { bullet: true, breakLine: true, color: C.codeTx } },
  { text: "(cuando el traslado es público)", options: { breakLine: true, color: C.mint } },
  { text: "Tipo de documento: 31", options: { bullet: true, breakLine: true, color: C.codeTx } },
  { text: "Complementa a la del remitente", options: { bullet: true, color: C.codeTx } },
], { x: 7.27, y: 3.1, w: 5.15, h: 3.1, margin: 0, fontFace: F.body, fontSize: 14.5, paraSpaceAfter: 6, valign: "top" });
N(s, "Dos tipos. La 09 (remitente) la hace el dueño de los bienes: es la que un negocio normal emite. La 31 (transportista) la hace la empresa de transporte cuando el servicio es de un tercero (transporte público). Para la clase nos enfocaremos en la 09, que es la que implementaría el sistema.");

// ===== 5 Qué datos lleva =====
s = pres.addSlide();
header(s, "Estructura", "¿Qué datos lleva una GRE?");
const secs = [
  ["Remitente y destinatario", "Quién envía y quién recibe (RUC/DNI, nombre)"],
  ["Punto de partida y llegada", "Direcciones y ubigeo de origen y destino"],
  ["Motivo del traslado", "Venta, compra, traslado entre locales... (catálogo)"],
  ["Datos del transporte", "Modalidad, transportista, placa, conductor"],
  ["Bienes trasladados", "Productos, cantidad, unidad de medida, peso"],
  ["Fechas", "Emisión y fecha de inicio del traslado"],
];
const dX = [M, 4.63, 8.66], dY = [1.8, 4.15];
secs.forEach((d, i) => {
  const x = dX[i % 3], y = dY[Math.floor(i / 3)];
  card(s, x, y, 4.0, 2.1, i % 2 ? C.tint2 : C.tint);
  circle(s, x + 0.28, y + 0.28, 0.52, C.seafoam, String(i + 1), C.white, 15);
  s.addText(d[0], { x: x + 0.92, y: y + 0.26, w: 2.95, h: 0.6, margin: 0, fontFace: F.body, fontSize: 14.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(d[1], { x: x + 0.3, y: y + 0.95, w: 3.5, h: 1.0, margin: 0, fontFace: F.body, fontSize: 12.5, color: C.muted, valign: "top" });
});
N(s, "La GRE es más rica en datos que una factura, porque describe un movimiento físico: de dónde a dónde, en qué vehículo, con qué conductor, qué bienes y cuánto pesan. Estos 6 bloques son los que pediremos en el formulario y guardaremos en la base.");

// ===== 6 Modalidad =====
s = pres.addSlide();
header(s, "Catálogo 18 SUNAT", "Modalidad de transporte: ¿quién mueve los bienes?");
card(s, M, 1.95, 5.95, 4.3, C.tint);
circle(s, M + 0.32, 2.22, 0.7, C.teal, "01", C.white, 18);
s.addText("Transporte público", { x: M + 1.2, y: 2.24, w: 4.5, h: 0.6, margin: 0, fontFace: F.body, fontSize: 19, bold: true, color: C.ink, valign: "middle" });
s.addText([
  { text: "Contratas a una empresa de transporte", options: { bullet: true, breakLine: true } },
  { text: "Se necesitan los datos del transportista", options: { bullet: true, breakLine: true } },
  { text: "Suele acompañarse con la GRE 31", options: { bullet: true } },
], { x: M + 0.32, y: 3.1, w: 5.4, h: 2.9, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6, valign: "top" });

card(s, 6.95, 1.95, 5.75, 4.3, C.tint2);
circle(s, 7.27, 2.22, 0.7, C.indigo, "02", C.white, 18);
s.addText("Transporte privado", { x: 8.15, y: 2.24, w: 4.4, h: 0.6, margin: 0, fontFace: F.body, fontSize: 19, bold: true, color: C.ink, valign: "middle" });
s.addText([
  { text: "Trasladas con TU propio vehículo", options: { bullet: true, breakLine: true } },
  { text: "Se necesitan placa y conductor", options: { bullet: true, breakLine: true } },
  { text: "Solo la GRE 09 del remitente", options: { bullet: true } },
], { x: 7.27, y: 3.1, w: 5.15, h: 2.9, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6, valign: "top" });
N(s, "La modalidad cambia qué datos pedimos. Público (01): contratas a un tercero → datos del transportista. Privado (02): usas tu propio camión → placa y conductor. En el formulario, según la modalidad mostramos unos campos u otros.");

// ===== 7 Diferencia técnica =====
s = pres.addSlide();
header(s, "Lo técnico (importante)", "La GRE va por un camino DISTINTO al de la factura");
card(s, M, 1.85, 5.95, 4.6, C.tint);
s.addText("Factura / Boleta / Nota", { x: M + 0.3, y: 2.05, w: 5.4, h: 0.5, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: C.teal });
s.addText([
  { text: "Web Service SOAP de SUNAT", options: { bullet: true, breakLine: true } },
  { text: "Autenticación con usuario SOL", options: { bullet: true, breakLine: true } },
  { text: "(o vía el OSE/PSE, como hoy)", options: { breakLine: true, color: C.muted } },
], { x: M + 0.3, y: 2.6, w: 5.4, h: 2.0, margin: 0, fontFace: F.body, fontSize: 14.5, color: C.ink, paraSpaceAfter: 6, valign: "top" });

card(s, 6.95, 1.85, 5.75, 4.6, C.dark);
s.addText("Guía de Remisión (GRE)", { x: 7.25, y: 2.05, w: 5.2, h: 0.5, margin: 0, fontFace: F.body, fontSize: 17, bold: true, color: C.mint });
s.addText([
  { text: "API REST de SUNAT (JSON)", options: { bullet: true, breakLine: true, color: C.codeTx } },
  { text: "Autenticación con OAuth2 (token)", options: { bullet: true, breakLine: true, color: C.codeTx } },
  { text: "Necesita client_id y client_secret", options: { bullet: true, breakLine: true, color: C.codeTx } },
  { text: "El XML sigue firmándose con certificado", options: { bullet: true, color: C.codeTx } },
], { x: 7.25, y: 2.6, w: 5.15, h: 3.0, margin: 0, fontFace: F.body, fontSize: 14.5, paraSpaceAfter: 6, valign: "top" });
N(s, "Punto clave para que entiendan el código que viene: la GRE NO usa el mismo mecanismo que la factura. Usa una API REST moderna con OAuth2 (un token tipo 'Bearer'). Por eso el primer paso del envío será PEDIR UN TOKEN. El XML firmado con certificado sigue existiendo; lo que cambia es cómo se autentica y se envía.");

// ===== 8 Cómo encaja =====
s = pres.addSlide();
header(s, "Arquitectura", "Cómo encaja en NUESTRO sistema");
const fl = [["Formulario\nGuía", C.seafoam], ["GuiaRemision\n.php", C.teal], ["Token OAuth2\n+ envío", C.indigo], ["API GRE\nSUNAT", C.dark]];
let fx = M; const fw = 2.85, fy = 2.2, fh = 1.7;
fl.forEach((b, i) => {
  card(s, fx, fy, fw, fh, i === 3 ? C.dark : C.tint);
  s.addText(b[0], { x: fx + 0.1, y: fy + 0.3, w: fw - 0.2, h: 1.1, margin: 0, align: "center", valign: "middle", fontFace: F.body, fontSize: 15.5, bold: true, color: i === 3 ? C.white : C.ink });
  if (i < 3) s.addText("➜", { x: fx + fw - 0.08, y: fy + 0.5, w: 0.5, h: 0.7, margin: 0, align: "center", valign: "middle", fontFace: F.body, fontSize: 26, bold: true, color: C.seafoam });
  fx += fw + 0.27;
});
s.addText([
  { text: "Mismo patrón de siempre: ", options: { bold: true, color: C.ink } },
  { text: "el formulario manda los datos → una clase los valida y guarda → un emisor obtiene el token y envía a SUNAT → guardamos el ticket y el estado.", options: { color: C.ink } },
], { x: M, y: 4.4, w: W - 2 * M, h: 1.0, margin: 0, fontFace: F.body, fontSize: 15.5, valign: "top" });
s.addText("La firma del XML se delega a una librería (Greenter) o al OSE, igual que hoy con la factura.", { x: M, y: 5.6, w: W - 2 * M, h: 0.5, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted });
N(s, "Tranquilizar a los alumnos: la GRE encaja en el MISMO patrón que ya dominan (validar → guardar → enviar). Lo único nuevo es el paso del token OAuth2 y que la firma del XML la hace una librería como Greenter o el OSE. No reinventamos nada del flujo.");

// ===== 9 Base de datos =====
s = pres.addSlide();
header(s, "Implementación · Base de datos", "Las tablas de la guía");
codeBox(s, M, 1.6, 12.2, 5.4, [
  "CREATE TABLE guia_remision (",
  "  id INT AUTO_INCREMENT PRIMARY KEY,",
  "  serie VARCHAR(10), correlativo INT,          -- ej. T001-1",
  "  tipo_guia VARCHAR(2),                         -- 09 / 31",
  "  fecha_emision DATETIME, fecha_traslado DATE,",
  "  motivo_traslado VARCHAR(2),                   -- catálogo 20",
  "  modalidad_transporte VARCHAR(2),              -- 01 / 02",
  "  peso_total DECIMAL(10,2), unidad_peso VARCHAR(5),",
  "  id_cliente INT,                               -- destinatario",
  "  ubigeo_partida VARCHAR(6), dir_partida VARCHAR(255),",
  "  ubigeo_llegada VARCHAR(6), dir_llegada VARCHAR(255),",
  "  transportista_ruc VARCHAR(11), vehiculo_placa VARCHAR(10),",
  "  conductor_doc VARCHAR(15), conductor_nombres VARCHAR(150),",
  { x: "  sunat_ticket VARCHAR(100), sunat_estado VARCHAR(30)", t: "k" },
  ");",
  "-- guia_remision_detalle: id_guia, id_producto, cantidad, unidad, descripcion",
], 11);
N(s, "Una tabla para la cabecera de la guía (con todos los bloques de datos: partida, llegada, transporte) y otra para el detalle de los bienes (igual que venta/venta_detalle). Notar los campos de catálogo: tipo_guia, motivo_traslado, modalidad_transporte; y los campos SUNAT (ticket/estado) iguales a los de la factura.");

// ===== 10 Clase GuiaRemision =====
s = pres.addSlide();
header(s, "Implementación · GuiaRemision.php", "La clase: validar, guardar, enviar");
codeBox(s, M, 1.6, 7.5, 5.1, [
  "class GuiaRemision {",
  "  public function addRegistro($data): array {",
  { x: "    // 1) validar (motivo, modalidad, bienes...)", t: "c" },
  { x: "    // 2) serie + correlativo (T001-...)", t: "c" },
  { x: "    // 3) guardar cabecera + detalle (transacción)", t: "c" },
  "    $this->con->begin_transaction();",
  "    // ... INSERT guia_remision / _detalle ...",
  "    $this->con->commit();",
  "",
  { x: "    // 4) enviar a SUNAT", t: "c" },
  "    $emisor = new EmisorGre();",
  "    $res = $emisor->enviar($guia);",
  "    // 5) guardar ticket / estado",
  "    return ['status'=>202, 'sunat'=>$res];",
  "  }",
  "}",
]);
let yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.seafoam, [
  "Mismo flujo que la nota o la venta",
  "Guarda primero, envía después",
  "Delega el envío a EmisorGre",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué así?", C.amber, [
  "Reutiliza el patrón ya conocido",
  "Separa 'guardar' de 'enviar a SUNAT'",
  "Fácil de mantener y enseñar",
]);
N(s, "La clase GuiaRemision sigue el patrón idéntico a NotaComprobante: validar, calcular serie/correlativo, guardar en transacción y enviar. Lo único distinto vive en EmisorGre->enviar(), que maneja el OAuth2. Insistir en que reconocer el patrón les permite implementar cualquier comprobante nuevo.");

// ===== 11 Token OAuth2 =====
s = pres.addSlide();
header(s, "Implementación · paso clave", "Paso 1: obtener el token OAuth2 de SUNAT");
codeBox(s, M, 1.6, 7.5, 5.1, [
  "private function obtenerToken(): string {",
  "  $url = 'https://api-seguridad.sunat.gob.pe/v1/'",
  "       . 'clientessol/'.$this->clientId.'/oauth2/token/';",
  "  $ch = curl_init($url);",
  "  curl_setopt_array($ch, [",
  "    CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,",
  { x: "    CURLOPT_POSTFIELDS => http_build_query([", t: "k" },
  "      'grant_type'    => 'password',",
  "      'scope'         => 'https://api-cpe.sunat.gob.pe',",
  "      'client_id'     => $this->clientId,",
  "      'client_secret' => $this->clientSecret,",
  "      'username' => $this->ruc.$this->usuarioSol,",
  "      'password' => $this->claveSol ]) ]);",
  "  $r = json_decode(curl_exec($ch), true);",
  "  return $r['access_token'] ?? '';",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.seafoam, [
  "Pide un token a SUNAT",
  "Manda client_id/secret + usuario SOL",
  "Devuelve el access_token",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "La API REST exige OAuth2",
  "El token autoriza los envíos",
  "Dura un rato; se reutiliza",
]);
N(s, "Aquí está LO NUEVO frente a la factura. Antes de enviar la guía, pedimos un token con nuestras credenciales de API (client_id y client_secret, que se sacan del portal SOL) más el usuario y clave SOL. SUNAT devuelve un access_token que luego mandamos en cada envío como 'Bearer'. http_build_query arma el cuerpo en formato formulario (no JSON) que pide este endpoint de seguridad.");

// ===== 12 Enviar =====
s = pres.addSlide();
header(s, "Implementación · envío", "Paso 2 a 5: firmar, comprimir y enviar");
codeBox(s, M, 1.6, 7.5, 5.1, [
  "public function enviar($guia): array {",
  "  $token = $this->obtenerToken();        // 1) OAuth2",
  "  $xml = $this->generarUBL($guia);       // 2) XML (UBL)",
  "  $xml = $this->firmar($xml);            // 3) firma XAdES",
  "  $zip = $this->comprimir($xml, $nombre);// 4) .zip",
  "",
  "  $url = 'https://api-cpe.sunat.gob.pe/v1/'",
  "       . 'contribuyente/gem/comprobantes/'.$nombre;",
  "  $ch = curl_init($url);",
  "  curl_setopt_array($ch, [ CURLOPT_POST=>true,",
  { x: "    CURLOPT_HTTPHEADER => [", t: "k" },
  { x: "      'Authorization: Bearer '.$token,", t: "k" },
  "      'Content-Type: application/json' ],",
  "    CURLOPT_POSTFIELDS => json_encode($cuerpo),",
  "    CURLOPT_RETURNTRANSFER => true ]);",
  "  return ['ticket' => $resp['numTicket'] ?? null];",
  "}",
], 11);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "Los 5 pasos", C.seafoam, [
  "Token → XML → firma → zip → envío",
  "Va con 'Authorization: Bearer'",
  "SUNAT devuelve un numTicket",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "Dónde delegar", C.amber, [
  "generarUBL y firmar: con Greenter",
  "o con el OSE (como hoy)",
  "El zip lleva el XML firmado",
]);
N(s, "El envío real: con el token ya en mano, generamos el XML de la guía (DespatchAdvice), lo firmamos con el certificado, lo comprimimos en zip y lo mandamos al endpoint de GRE con la cabecera Authorization: Bearer + token. SUNAT responde un numTicket (otra vez asíncrono: luego se consulta). generarUBL() y firmar() son los pasos que conviene delegar a Greenter o al OSE para no escribir la criptografía a mano.");

// ===== 13 Consulta del ticket =====
s = pres.addSlide();
header(s, "Implementación · resultado", "Paso 6: consultar el ticket de la GRE");
codeBox(s, M, 1.6, 7.5, 4.0, [
  { x: "// La GRE es asíncrona: SUNAT da un ticket", t: "c" },
  "$url = 'https://api-cpe.sunat.gob.pe/v1/'",
  "     . 'contribuyente/gem/comprobantes/envios/'.$ticket;",
  "$ch = curl_init($url);",
  "curl_setopt($ch, CURLOPT_HTTPHEADER,",
  "    ['Authorization: Bearer '.$token]);",
  "$cdr = json_decode(curl_exec($ch), true);",
  { x: "// $cdr['codRespuesta'] => 0 = aceptado", t: "k" },
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.seafoam, [
  "Consulta el numTicket de la guía",
  "Obtiene el CDR (aceptado/rechazado)",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Te suena?", C.amber, [
  "Es el MISMO concepto de la factura",
  "Ticket → consultar → estado real",
  "Guardamos el resultado en la base",
]);
N(s, "Igual que en la factura: la guía también es asíncrona. Enviamos y nos dan un numTicket; después consultamos ese ticket para saber si fue aceptada o rechazada (el CDR). codRespuesta = 0 significa aceptada. Conectar con lo que ya enseñamos en 'consultar estado': es exactamente el mismo patrón.");

// ===== 14 Formulario =====
s = pres.addSlide();
header(s, "Implementación · interfaz", "El formulario de la guía (campos según modalidad)");
codeBox(s, M, 1.6, 7.5, 4.6, [
  { x: "// según la modalidad, mostramos unos campos u otros", t: "c" },
  "document.getElementById('modalidad')",
  "  .addEventListener('change', e => {",
  "    const publico = e.target.value === '01';",
  "    bloqueTransportista.style.display =",
  "        publico ? 'block' : 'none';   // GRE 31",
  "    bloqueVehiculo.style.display =",
  "        publico ? 'none' : 'block';   // placa+conductor",
  "  });",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.seafoam, [
  "Muestra/oculta campos según modalidad",
  "Público → datos del transportista",
  "Privado → placa y conductor",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué?", C.amber, [
  "No pedir datos que no aplican",
  "Mejor experiencia de usuario",
  "Menos errores al enviar",
]);
N(s, "En la interfaz, la modalidad de transporte cambia qué se pide. Con un addEventListener al <select> de modalidad, mostramos el bloque del transportista (público) o el de placa+conductor (privado). Es el mismo tipo de interacción que usamos en notas.js. La idea: pedir solo lo que aplica.");

// ===== 15 Requisitos =====
s = pres.addSlide();
header(s, "Para producción", "Qué necesitas para emitir GRE de verdad");
const req = [
  ["Certificado digital", "Firma el XML (SUNAT o entidad certificadora)"],
  ["Credenciales de API", "client_id y client_secret desde el portal SOL"],
  ["Usuario SOL", "Usuario y clave (secundario recomendado)"],
  ["Generador UBL + firma", "Greenter u OSE para el XML firmado"],
  ["Catálogos SUNAT", "Motivos (20), modalidad (18), unidades, ubigeo"],
  ["Endpoints correctos", "Beta para pruebas, producción para real"],
];
const rX = [M, 4.63, 8.66], rY = [1.8, 4.15];
req.forEach((r, i) => {
  const x = rX[i % 3], y = rY[Math.floor(i / 3)];
  card(s, x, y, 4.0, 2.1, i % 2 ? C.tint2 : C.tint);
  circle(s, x + 0.28, y + 0.28, 0.52, C.seafoam, "✓", C.white, 15);
  s.addText(r[0], { x: x + 0.92, y: y + 0.26, w: 2.95, h: 0.6, margin: 0, fontFace: F.body, fontSize: 14.5, bold: true, color: C.ink, valign: "middle" });
  s.addText(r[1], { x: x + 0.3, y: y + 0.95, w: 3.5, h: 1.0, margin: 0, fontFace: F.body, fontSize: 12.5, color: C.muted, valign: "top" });
});
N(s, "Checklist honesto de lo que hace falta para emitir GRE real: certificado para firmar, credenciales de API (client_id/secret) que se generan en el portal SOL, usuario SOL, una forma de generar y firmar el UBL (Greenter o el OSE), los catálogos de SUNAT y los endpoints (beta para practicar, producción para real). Recomendar SIEMPRE probar primero en beta.");

// ===== 16 Cierre =====
s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: -1.5, y: 4.8, w: 4.2, h: 4.2, fill: { color: C.seafoam, transparency: 75 }, line: { type: "none" } });
s.addText("EN RESUMEN", { x: M, y: 1.6, w: 11, h: 0.4, margin: 0, fontFace: F.mono, fontSize: 15, bold: true, color: C.mint, charSpacing: 2 });
s.addText("La guía sustenta el traslado, no la venta", { x: M, y: 2.05, w: 12, h: 1.2, margin: 0, fontFace: F.head, fontSize: 32, bold: true, color: C.white });
s.addText([
  { text: "Dos tipos: 09 remitente · 31 transportista", options: { breakLine: true, color: C.mint } },
  { text: "Va por API REST de SUNAT con OAuth2 (token Bearer)", options: { breakLine: true, color: C.codeTx } },
  { text: "Mismo patrón del sistema: validar → guardar → enviar → consultar", options: { breakLine: true, color: C.codeTx } },
  { text: "La firma del XML se delega (Greenter / OSE)", options: { color: C.codeTx } },
], { x: M, y: 3.4, w: 12, h: 2.2, margin: 0, fontFace: F.body, fontSize: 17, valign: "top", lineSpacingMultiple: 1.25 });
s.addText("¿Preguntas?", { x: M, y: 5.95, w: 11, h: 0.7, margin: 0, fontFace: F.head, fontSize: 26, bold: true, color: C.mint });
N(s, "Cierre. Repetir las 4 ideas: la guía es del TRASLADO; hay dos tipos (09/31); técnicamente usa API REST con OAuth2 (distinto a la factura); pero encaja en el mismo patrón del sistema y la firma se delega. Si dominaron la nota y la consulta de estado, la GRE es 'lo mismo con un paso de token al inicio'.");

pres.writeFile({ fileName: "Clase_Guia_Remision_Electronica.pptx" }).then((f) => console.log("OK:", f));
