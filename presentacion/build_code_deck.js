const pptxgen = require("pptxgenjs");

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE"; // 13.3 x 7.5
pres.author = "CETI";
pres.title = "Explicación del Código — Clase Final";

const C = {
  dark: "0E2A2E", teal: "0D9488", seafoam: "14B8A6", mint: "5EEAD4",
  white: "FFFFFF", ink: "1E293B", muted: "64748B", tint: "F0FDFA", tint2: "ECFEFF",
  codeBg: "0F172A", codeTx: "E2E8F0", codeKey: "5EEAD4", codeCom: "7C8AA0", codeStr: "FBBF77",
  amber: "D97706",
};
const F = { head: "Cambria", body: "Calibri", mono: "Courier New" };
const W = 13.3, H = 7.5, M = 0.55;
const shadow = () => ({ type: "outer", color: "000000", blur: 8, offset: 3, angle: 90, opacity: 0.12 });

function header(slide, kicker, title) {
  slide.background = { color: C.white };
  slide.addText(kicker.toUpperCase(), { x: M, y: 0.35, w: W - 2 * M, h: 0.3, margin: 0, fontFace: F.mono, fontSize: 12.5, bold: true, color: C.teal, charSpacing: 1 });
  slide.addText(title, { x: M, y: 0.64, w: W - 2 * M, h: 0.7, margin: 0, fontFace: F.head, fontSize: 26, bold: true, color: C.ink, valign: "top" });
}
// code box (dark). lines: array of strings or {x, t:'c'|'k'|'s'}
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
    runs.push({ text: txt === "" ? " " : txt, options: { color, breakLine: !last, bold: !!(isObj && ln.b) } });
  });
  slide.addText(runs, { x: x + 0.22, y: y + 0.16, w: w - 0.44, h: h - 0.32, margin: 0, valign: "top", fontFace: F.mono, fontSize: fs || 11.5, lineSpacingMultiple: 1.05 });
}
function panel(slide, x, y, w, titulo, color, bullets) {
  const h = 0.55 + bullets.length * 0.62 + 0.2;
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w, h, rectRadius: 0.07, fill: { color: C.tint }, line: { type: "none" }, shadow: shadow() });
  slide.addText(titulo, { x: x + 0.22, y: y + 0.16, w: w - 0.44, h: 0.4, margin: 0, fontFace: F.body, fontSize: 14.5, bold: true, color });
  const runs = bullets.map((b, i) => ({ text: b, options: { bullet: { indent: 12 }, breakLine: true, color: C.ink, paraSpaceAfter: 5 } }));
  slide.addText(runs, { x: x + 0.24, y: y + 0.62, w: w - 0.46, h: h - 0.75, margin: 0, fontFace: F.body, fontSize: 12.5, valign: "top" });
  return h;
}
function notes(slide, t) { slide.addNotes(t); }

// ===== SLIDE 1 — Portada =====
let s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: 10.4, y: -1.7, w: 4.8, h: 4.8, fill: { color: C.teal, transparency: 70 }, line: { type: "none" } });
s.addText("EXPLICACIÓN DEL CÓDIGO", { x: M, y: 1.7, w: 11, h: 0.4, margin: 0, fontFace: F.mono, fontSize: 16, bold: true, color: C.mint, charSpacing: 2 });
s.addText("Recorrido por el código de la clase final", { x: M, y: 2.2, w: 11.8, h: 1.2, margin: 0, fontFace: F.head, fontSize: 42, bold: true, color: C.white });
s.addText("Cada bloque: qué hace · por qué · para qué", { x: M, y: 3.6, w: 11.5, h: 0.7, margin: 0, fontFace: F.body, fontSize: 20, color: C.mint });
s.addText("Notas de crédito/débito · Consulta · Baja · Resumen · Código QR", { x: M, y: 6.4, w: 11.5, h: 0.4, margin: 0, fontFace: F.body, fontSize: 13, italic: true, color: "9AE6D6" });
notes(s, "Este deck es solo código. Vamos archivo por archivo y método por método explicando qué hace, por qué se hizo así y para qué sirve. Recomendación: tener VS Code al lado abierto en cada archivo mientras se proyecta la diapositiva correspondiente.");

// ===== SLIDE 2 — Patrón general =====
s = pres.addSlide();
header(s, "Antes de empezar", "El patrón que repite TODO el código nuevo");
const fl = [["Página + JS", C.teal], ["Clase PHP", C.seafoam], ["Base de datos", C.teal], ["API → SUNAT", C.dark]];
let fx = M; const fw = 2.85, fy = 2.1, fh = 1.5;
fl.forEach((b, i) => {
  slide_card(s, fx, fy, fw, fh, i === 3 ? C.dark : C.tint);
  s.addText(b[0], { x: fx + 0.1, y: fy + 0.45, w: fw - 0.2, h: 0.6, margin: 0, align: "center", valign: "middle", fontFace: F.body, fontSize: 16, bold: true, color: i === 3 ? C.white : C.ink });
  if (i < 3) s.addText("➜", { x: fx + fw - 0.08, y: fy + 0.4, w: 0.5, h: 0.7, margin: 0, align: "center", valign: "middle", fontFace: F.body, fontSize: 26, bold: true, color: C.teal });
  fx += fw + 0.27;
});
s.addText([
  { text: "Cada función nueva encaja en este flujo. ", options: { bold: true, color: C.ink } },
  { text: "Si entiendes este camino, entiendes todo el código: el navegador pide, la clase decide y guarda, y la API habla con SUNAT.", options: { color: C.ink } },
], { x: M, y: 4.1, w: W - 2 * M, h: 1.0, margin: 0, fontFace: F.body, fontSize: 16, valign: "top" });
s.addText("Regla de oro del proyecto: primero guardamos en la base (rápido y seguro), y recién después hablamos con SUNAT (lento, por internet).", { x: M, y: 5.3, w: W - 2 * M, h: 0.8, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: C.muted, valign: "top" });
notes(s, "Recordatorio del patrón. Insistir: la clase PHP es el cerebro (valida, calcula, guarda); Facturalaya es el cartero hacia SUNAT. La 'regla de oro' (guardar antes de enviar) aparecerá varias veces; anticiparla aquí.");

// ===== SLIDE 3 — post() =====
s = pres.addSlide();
header(s, "Facturalaya.php · método post()", "Un solo lugar para hablar con SUNAT");
codeBox(s, M, 1.6, 7.5, 5.2, [
  { x: "// Un único método para TODOS los envíos", t: "c" },
  "private function post($apiUrl, $payload): array",
  "{",
  "    $ch = curl_init($apiUrl);",
  "    curl_setopt_array($ch, [",
  "        CURLOPT_POST => true,",
  { x: "        CURLOPT_HTTPHEADER => [", t: "k" },
  { x: "          'token: '.$this->emisor['token_cliente']],", t: "k" },
  "        CURLOPT_POSTFIELDS => json_encode($payload),",
  "        CURLOPT_RETURNTRANSFER => true,",
  "    ]);",
  "    $response = curl_exec($ch);",
  "    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);",
  "    return [",
  { x: "      'success' => $http >= 200 && $http < 300,", t: "k" },
  "      'ticket'  => $data['ticket'] ?? null,",
  "      'estado_sunat' => ... ];",
  "}",
]);
let yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Empaqueta los datos en JSON",
  "Los envía por cURL con el token del emisor",
  "Devuelve siempre la MISMA respuesta",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "DRY: no repetir el cURL 6 veces",
  "Boleta, factura, nota, baja, resumen y consulta lo reutilizan",
  "Un cambio futuro → un solo lugar",
]);
notes(s, "Explicar cURL en una frase: es la forma en que PHP hace una petición HTTP a otro servidor. El token identifica al emisor ante la API. Lo IMPORTANTE: antes este código vivía dentro de enviar(); lo extrajimos para reutilizarlo. Preguntar a los alumnos: '¿qué pasaría si tuviéramos el cURL copiado en 6 métodos y cambia la URL?' → tendríamos que editar 6 lugares. Eso es lo que evita DRY.");

// ===== SLIDE 4 — enviarNota() =====
s = pres.addSlide();
header(s, "Facturalaya.php · enviarNota()", "Una nota = una venta que apunta a otra");
codeBox(s, M, 1.6, 7.5, 5.2, [
  "public function enviarNota($nota, $detalle,",
  "                           $cliente, $docAfectado)",
  "{",
  "  $esCredito = $nota['tipo'] === 'NOTA_CREDITO';",
  { x: "  // reutiliza el armado de una venta normal", t: "c" },
  "  $payload = $this->buildPayload($base,$detalle,$cliente);",
  "",
  "  $payload['cod_tipo_documento'] = $esCredito?'07':'08';",
  { x: "  $payload['docs_referencia'] = [[", t: "k" },
  { x: "    'cod_tipo_documento' => $afecta==='FACTURA'?'01':'03',", t: "k" },
  { x: "    'serie'  => $docAfectado['serie'],", t: "k" },
  { x: "    'numero' => (int)$docAfectado['correlativo'],", t: "k" },
  { x: "  ]];", t: "k" },
  "  $payload['cod_tipo_nota_credito'] = $nota['motivo'];",
  "  return $this->post($apiUrl, $payload);",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Arma la nota reutilizando buildPayload()",
  "Añade tipo 07/08 y el motivo SUNAT",
  "Agrega docs_referencia",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Una nota corrige un documento previo",
  "docs_referencia le dice a SUNAT cuál",
  "Sin él, SUNAT RECHAZA la nota",
]);
notes(s, "El concepto clave de toda la clase: docs_referencia. Explicar con analogía: 'una nota de crédito es como una nota al margen de una factura; si no dices a qué factura te refieres, no sirve'. Resaltar la reutilización de buildPayload(): no reescribimos cómo se calculan ítems e IGV, lo heredamos de la venta. 07 = crédito, 08 = débito; 01 = factura, 03 = boleta.");

// ===== SLIDE 5 — ensureColumns =====
s = pres.addSlide();
header(s, "NotaComprobante.php · ensureColumns()", "El esquema que se arregla solo");
codeBox(s, M, 1.6, 7.5, 4.6, [
  { x: "// Agrega columnas a 'ventas' SOLO si no existen", t: "c" },
  "private function ensureColumns(): void {",
  "  $cols = [",
  "    'id_doc_afectado' => 'ADD COLUMN id_doc_afectado INT',",
  "    'cod_motivo'      => 'ADD COLUMN cod_motivo VARCHAR(2)',",
  "    // ... serie_afectada, correlativo_afectado, ...",
  "  ];",
  "  foreach ($cols as $name => $ddl) {",
  "    $r = $this->con->query(",
  "        \"SHOW COLUMNS FROM ventas LIKE '$name'\");",
  { x: "    if ($r && $r->num_rows === 0) {   // si NO existe", t: "k" },
  "      $this->con->query(\"ALTER TABLE ventas $ddl\");",
  "    }",
  "  }",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Revisa si la columna ya existe",
  "Si falta, la crea con ALTER TABLE",
  "Si ya está, no hace nada",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Idempotente: re-ejecutable sin error",
  "El alumno no corre SQL a mano",
  "La nota necesita columnas extra",
]);
notes(s, "Palabra nueva para el pizarrón: IDEMPOTENTE = ejecutar muchas veces da el mismo resultado, sin romper. SHOW COLUMNS pregunta si la columna existe; si num_rows es 0, no existe, entonces la crea. Por qué importa: el sistema 'se auto-actualiza' la primera vez que se usa una nota, sin que nadie modifique la base manualmente.");

// ===== SLIDE 6 — catálogos static =====
s = pres.addSlide();
header(s, "NotaComprobante.php · motivos", "Los motivos son códigos oficiales, no texto libre");
codeBox(s, M, 1.6, 7.5, 4.3, [
  { x: "// Catálogo 09 de SUNAT (nota de crédito)", t: "c" },
  "public static function motivosCredito(): array {",
  "  return [",
  { x: "    '01' => 'Anulación de la operación',", t: "s" },
  { x: "    '06' => 'Devolución total',", t: "s" },
  { x: "    '09' => 'Disminución en el valor',", t: "s" },
  { x: "    '10' => 'Otros conceptos',", t: "s" },
  "  ];",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Devuelve el listado código → texto",
  "Es static: se usa sin crear objeto",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "SUNAT define qué motivos valen",
  "Llenan el <select> de la página",
  "Débito usa otro catálogo (10)",
]);
notes(s, "Por qué static: porque la página (notas.php) los necesita para pintar el <select> sin tener que crear un objeto NotaComprobante. El motivo NO es libre: si mandas un código que no está en el catálogo, SUNAT lo rechaza. Mostrar que motivosDebito() es idéntico en forma pero con el catálogo 10.");

// ===== SLIDE 7 — serie + correlativo =====
s = pres.addSlide();
header(s, "NotaComprobante.php · serie y número", "Cada serie codifica el tipo y lleva su propio número");
codeBox(s, M, 1.6, 7.5, 5.0, [
  "private function serieNota($tipoNota, $tipoDoc) {",
  "  $esFactura = $tipoDoc === 'FACTURA';",
  { x: "  if ($tipoNota === 'NOTA_CREDITO')", t: "k" },
  { x: "      return $esFactura ? 'FC01' : 'BC01';", t: "k" },
  { x: "  return $esFactura ? 'FD01' : 'BD01';", t: "k" },
  "}",
  "",
  "private function getNextCorrelativo($serie): int {",
  "  $stmt = $this->con->prepare(",
  "    'SELECT MAX(correlativo) ultimo FROM ventas",
  "     WHERE serie = ?');",
  "  $stmt->bind_param('s', $serie);",
  "  // ...  return ultimo + 1;",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Elige la serie (FC01/BC01/FD01/BD01)",
  "Calcula el siguiente número de esa serie",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "La serie dice: tipo de nota + documento",
  "Cada serie numera independiente",
  "MAX(correlativo)+1 = el siguiente",
]);
notes(s, "La serie tiene significado: primera letra/par = sobre qué (Factura/Boleta), y C/D = Crédito/Débito. El correlativo es por serie: la NC de facturas (FC01) tiene su propia numeración, distinta de la NC de boletas (BC01). MAX(correlativo)+1 es el truco simple para 'el siguiente número'.");

// ===== SLIDE 8 — addRegistro validación =====
s = pres.addSlide();
header(s, "NotaComprobante.php · addRegistro() ①", "Paso 1: validar ANTES de tocar la base");
codeBox(s, M, 1.6, 7.5, 4.9, [
  { x: "// 1) VALIDAR todo lo que llega del navegador", t: "c" },
  "if (!in_array($tipoNota,['NOTA_CREDITO','NOTA_DEBITO']))",
  "    return ['status'=>303,'message'=>'Tipo inválido'];",
  "if ($idDoc <= 0)       return error('Indique el doc.');",
  "if ($codMotivo === '') return error('Elija un motivo');",
  "if (empty($detalle))   return error('Agregue ítems');",
  "",
  "$doc = $this->getDocumentoAfectado($idDoc);",
  { x: "if (!$doc)  return error('No existe el comprobante');", t: "k" },
  { x: "if ($doc['estado']==='ANULADO')", t: "k" },
  { x: "    return error('Ya está anulado');", t: "k" },
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Revisa tipo, documento, motivo, ítems",
  "Trae el documento original",
  "Corta si algo está mal",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "NUNCA confiar en el navegador",
  "Fallar temprano y con mensaje claro",
  "No anular sobre algo ya anulado",
]);
notes(s, "Principio de seguridad: 'nunca confíes en la entrada del usuario'. Cualquiera puede mandar un POST con datos basura. Por eso validamos todo antes de escribir en la base. status 303 = error (lo entiende el JavaScript y muestra un toastr rojo). Fallar temprano = más fácil de depurar y más seguro.");

// ===== SLIDE 9 — totales =====
s = pres.addSlide();
header(s, "NotaComprobante.php · addRegistro() ②", "Paso 2: calcular los montos");
codeBox(s, M, 1.6, 7.5, 3.6, [
  { x: "// 2) CALCULAR (el precio es valor de venta sin IGV)", t: "c" },
  "$subtotal = 0;",
  "foreach ($detalle as $item)",
  "    $subtotal += (float)$item['subtotal'];",
  "",
  "$igv   = round($subtotal * 0.18, 2);   // IGV 18%",
  "$total = round($subtotal + $igv, 2);",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Suma los subtotales de cada ítem",
  "Calcula IGV (18%) y total",
  "Redondea a 2 decimales",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "El monto de la nota = lo que se corrige",
  "round(): evitar 12.979999...",
  "Mismo cálculo que una venta",
]);
notes(s, "El IGV en Perú es 18%. round(...,2) evita errores de decimales flotantes (12.979999). Importante para la clase: una nota de crédito parcial puede tener menos ítems o menos cantidad que el original; por eso recalculamos, no copiamos el total a ciegas.");

// ===== SLIDE 10 — transacción + bind_param =====
s = pres.addSlide();
header(s, "NotaComprobante.php · addRegistro() ③", "Paso 3: guardar con transacción y bind_param");
codeBox(s, M, 1.6, 7.5, 5.0, [
  { x: "// 3) GUARDAR cabecera + detalle (todo o nada)", t: "c" },
  "$this->con->begin_transaction();",
  "try {",
  "  $stmt = $this->con->prepare(",
  "    'INSERT INTO ventas (tipo_comprobante, serie,",
  "       correlativo, ...) VALUES (?, ?, ?, ...)');",
  { x: "  $stmt->bind_param('ssiiisddd...',", t: "k" },
  "      $tipoNota, $serie, $correlativo, ...);",
  "  $stmt->execute();",
  "  // ... insertar cada línea del detalle ...",
  { x: "  $this->con->commit();      // confirma", t: "k" },
  "} catch (Exception $e) {",
  { x: "  $this->con->rollback();    // deshace TODO", t: "k" },
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "bind_param('ssi…')", C.teal, [
  "s = string   i = entero   d = decimal",
  "El orden de letras = orden de los ?",
]) + 0.22;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Consulta preparada → anti inyección SQL",
  "Transacción → cabecera y detalle juntas",
  "rollback si algo falla a la mitad",
]);
notes(s, "Dos conceptos fuertes. (1) Consulta preparada: los datos van por bind_param, no concatenados en el texto SQL; así nadie puede 'inyectar' SQL malicioso. La cadena 'ssiiisddd...' dice el tipo de cada ? en orden: s=string, i=int, d=double/decimal. (2) Transacción: begin → commit/rollback. Si la cabecera se guarda pero el detalle falla, rollback borra todo y no queda una nota a medias. Analogía: como un cajero que registra todo el pedido o nada.");

// ===== SLIDE 11 — envío + guardar respuesta =====
s = pres.addSlide();
header(s, "NotaComprobante.php · addRegistro() ④", "Paso 4 y 5: enviar a SUNAT y guardar la respuesta");
codeBox(s, M, 1.6, 7.5, 4.0, [
  { x: "// 4) ENVIAR a SUNAT — FUERA de la transacción", t: "c" },
  "$facturalaya = new Facturalaya();",
  "$resultado = $facturalaya->enviarNota(",
  "    $notaData, $detalleSunat, $cliente, $docAfectado);",
  "",
  { x: "// 5) GUARDAR ticket / estado / CDR en la fila", t: "c" },
  "$facturalaya->actualizarVenta($idNota, $resultado);",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Llama a la API con los datos de la nota",
  "Guarda lo que SUNAT devuelve",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "commit YA pasó → el dato está a salvo",
  "Si la red falla, la nota no se pierde",
  "Queda 'pendiente' para reintentar",
]);
notes(s, "Aquí se ve la 'regla de oro': el commit ya ocurrió en el paso 3, así que aunque SUNAT o internet fallen, la nota YA está guardada. enviarNota() puede tardar (red). actualizarVenta() escribe el ticket y el estado en la misma fila. Si falló el envío, el estado queda 'pendiente' y luego se puede reintentar con la consulta de estado.");

// ===== SLIDE 12 — handlers AJAX =====
s = pres.addSlide();
header(s, "NotaComprobante.php · handlers", "El puente entre el JavaScript y la clase");
codeBox(s, M, 1.6, 7.5, 4.1, [
  { x: "// Se ejecuta cuando el navegador hace POST", t: "c" },
  "if (isset($_POST['add_nota'])) {",
  "  $n = new NotaComprobante();",
  "  echo json_encode($n->addRegistro([",
  "    'id_doc_afectado' => $_POST['id_doc_afectado'],",
  "    'tipo_nota'       => $_POST['tipo_nota'],",
  "    'cod_motivo'      => $_POST['cod_motivo'],",
  "    'detalle' => json_decode($_POST['detalle'], true),",
  "  ]));",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Detecta la acción 'add_nota'",
  "Crea el objeto y ejecuta la lógica",
  "Responde con JSON",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "El JS no llama métodos PHP directo",
  "Habla por HTTP (POST) y recibe JSON",
  "json_encode = traducir PHP → JSON",
]);
notes(s, "El navegador y PHP están en mundos separados: el JS no puede llamar addRegistro() directamente. Le manda un POST con un campo 'add_nota'; este bloque lo detecta, ejecuta la lógica y devuelve la respuesta como JSON con json_encode. json_decode hace lo contrario: convierte el texto JSON del detalle en un arreglo PHP.");

// ===== SLIDE 13 — consultarEstadoSunat =====
s = pres.addSlide();
header(s, "Venta.php · consultarEstadoSunat()", "Preguntar a SUNAT por el ticket");
codeBox(s, M, 1.6, 7.5, 4.3, [
  "$res = $f->consultarEstado($row['sunat_ticket']);",
  "",
  { x: "// actualiza SOLO estado/mensaje/CDR", t: "c" },
  { x: "// (NO toca el ticket: lo seguimos necesitando)", t: "c" },
  "$up = $this->con->prepare(",
  "  'UPDATE ventas SET sunat_estado = ?,",
  "      sunat_mensaje = ?, sunat_cdr = ?",
  "   WHERE id = ?');",
  "$up->bind_param('sssi', $estado, $msg, $cdr, $id);",
  "$up->execute();",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Toma el ticket guardado",
  "Pregunta a SUNAT su resultado",
  "Actualiza el estado en la base",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "SUNAT responde asíncrono (con ticket)",
  "El estado real llega después",
  "No tocamos el ticket: sirve para reconsultar",
]);
notes(s, "Comunicación asíncrona: SUNAT no dice 'aceptado' al instante; entrega un ticket (como el de una lavandería) y luego consultas ese ticket para saber el resultado (el CDR). Por eso guardamos el ticket al enviar y aquí solo actualizamos estado/mensaje/CDR, dejando el ticket intacto para poder volver a consultar.");

// ===== SLIDE 14 — deleteRegistro baja =====
s = pres.addSlide();
header(s, "Venta.php · deleteRegistro()", "La baja decide el camino según el tipo");
codeBox(s, M, 1.6, 7.5, 4.6, [
  { x: "// Factura y notas → comunicación de baja", t: "c" },
  "if (in_array($doc['tipo_comprobante'],",
  "    ['FACTURA','NOTA_CREDITO','NOTA_DEBITO'])) {",
  "    $res = $f->enviarBaja($doc, $motivoTxt);",
  "    $sunatEstado = $res['success'] ? 'baja'",
  "                                   : 'pendiente';",
  "} else {",
  { x: "    // Boleta → se anula en el resumen diario", t: "c" },
  "    $sunatMsg = 'Informar en el resumen diario';",
  "}",
  "// luego: UPDATE ventas SET estado='ANULADO' ...",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Mira el tipo de comprobante",
  "Factura/nota: comunicación de baja",
  "Boleta: marca para el resumen",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Es una regla de SUNAT",
  "Las boletas NO se dan de baja una a una",
  "El if traduce la regla a código",
]);
notes(s, "Ejemplo perfecto de cómo una REGLA DE NEGOCIO se vuelve un if en el código. SUNAT exige: facturas y notas se anulan con 'comunicación de baja'; las boletas se anulan informándolas en el resumen diario. El alumno debe ver que el código no es arbitrario: refleja la norma.");

// ===== SLIDE 15 — ResumenDiario =====
s = pres.addSlide();
header(s, "ResumenDiario.php · enviar()", "Juntar las boletas del día en un solo envío");
codeBox(s, M, 1.6, 7.5, 4.3, [
  "public function enviar($fecha): array {",
  { x: "  // todas las boletas de esa fecha", t: "c" },
  "  $boletas = $this->getBoletasPorFecha($fecha);",
  "  if (empty($boletas))",
  "      return error('No hay boletas en esa fecha');",
  "",
  "  $f = new Facturalaya();",
  "  return $f->enviarResumen($fecha, $boletas);",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Busca las boletas de la fecha",
  "Las manda a SUNAT en un solo resumen",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Boletas = consumo masivo",
  "Se reportan agrupadas, no una a una",
  "getBoletasPorFecha usa DATE(fecha)=?",
]);
notes(s, "Las boletas son de consumo masivo (muchas, montos chicos), así que SUNAT permite informarlas juntas en un resumen diario en vez de una por una. getBoletasPorFecha filtra con DATE(fecha_emision)=? para tomar solo las de ese día. Es el mismo patrón: la clase arma los datos, Facturalaya los envía.");

// ===== SLIDE 16 — PHP → JS =====
s = pres.addSlide();
header(s, "notas.php · pasar datos a JavaScript", "PHP sabe los motivos; el JS los necesita");
codeBox(s, M, 1.6, 7.5, 4.4, [
  "<?php $mc = NotaComprobante::motivosCredito(); ?>",
  "",
  "<script>",
  "  window.NOTA_DATA = {",
  { x: "    motivos: {", t: "k" },
  { x: "      NOTA_CREDITO: <?= json_encode($mc) ?>,", t: "k" },
  { x: "      NOTA_DEBITO:  <?= json_encode($md) ?>", t: "k" },
  "    },",
  "    idUsuario: <?= $idusuario ?>",
  "  };",
  "</script>",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Toma los motivos desde la clase PHP",
  "Los inyecta en una variable JS",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "PHP corre en el servidor, JS en el navegador",
  "json_encode los 'traduce' a JS",
  "Así el <select> se llena sin otra petición",
]);
notes(s, "PHP se ejecuta en el servidor y termina; el JavaScript corre después en el navegador. Para que el JS tenga los motivos sin hacer otra llamada, PHP los 'imprime' dentro de una variable JS con json_encode. Es un puente muy común en PHP+JS. Mostrar que window.NOTA_DATA luego lo lee notas.js.");

// ===== SLIDE 17 — notas.js + bug =====
s = pres.addSlide();
header(s, "notas.js · cargar detalle + una trampa real", "AJAX para precargar, y un bug de nombres");
codeBox(s, M, 1.6, 7.5, 5.0, [
  { x: "// helper de DOM — OJO con el nombre", t: "c" },
  { x: "const el = (id) => document.getElementById(id);", t: "k" },
  { x: "// ❌ si lo llamas $ , choca con jQuery $.ajax", t: "c" },
  "",
  "function loadDocumento(id) {",
  "  $.ajax({ url: NOTA_URL, method: 'POST',",
  "    data: { get_documento_afectado: 1, id: id },",
  "    success: function (r) {",
  "      detalleItems = r.detalle.map(d => ({...}));",
  "      renderDetalle();   // pinta la tabla",
  "    }",
  "  });",
  "}",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Pide el detalle del comprobante por AJAX",
  "Lo precarga en la tabla editable",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "La lección", C.amber, [
  "Nombré un helper '$' y pisó a jQuery",
  "$.ajax dejó de existir → error",
  "Solución: renombrar a 'el'",
]);
notes(s, "Caso real que vivimos en clase: definí const $ = getElementById, y eso sobrescribió el $ de jQuery, así que $.ajax dejó de funcionar y el detalle no cargaba. Lección práctica de oro: cuidado al reutilizar nombres que ya usa una librería. Lo renombramos a 'el' y funcionó. AJAX = pedir datos al servidor sin recargar la página; con la respuesta (r.detalle) llenamos la tabla.");

// ===== SLIDE 18 — QR =====
s = pres.addSlide();
header(s, "print_venta.php · código QR", "El QR es una cadena de datos dibujada");
codeBox(s, M, 1.6, 7.5, 4.6, [
  { x: "// PHP: arma la cadena que exige SUNAT", t: "c" },
  "$qrData = implode('|', [",
  "   $ruc, $tipoDoc, $serie, $numero,",
  "   $igv, $total, $fecha,",
  "   $tipoDocCliente, $numDocCliente ]);",
  "",
  { x: "// JS: la dibuja como QR (librería por CDN)", t: "c" },
  "new QRCode(document.getElementById('qrcode'), {",
  "   text: \"<?= $qrData ?>\",",
  "   width: 120, height: 120",
  "});",
]);
yy = 1.6;
yy += panel(s, 8.3, yy, 4.45, "¿Qué hace?", C.teal, [
  "Une los datos clave con '|'",
  "qrcodejs convierte el texto en QR",
]) + 0.25;
panel(s, 8.3, yy, 4.45, "¿Por qué / para qué?", C.amber, [
  "Representación impresa verificable",
  "Orden de campos definido por SUNAT",
  "Sin librerías PHP: todo en el navegador",
]);
notes(s, "Desmitificar el QR: no es magia, es una cadena de texto con los datos del comprobante separados por '|', en el orden exacto que pide SUNAT (RUC, tipo, serie, número, IGV, total, fecha, doc del cliente). implode('|', [...]) une el arreglo con barras. Luego una librería JS (qrcodejs por CDN) dibuja esa cadena como código QR. Sin instalar nada en PHP.");

// ===== SLIDE 19 — cierre / mapa =====
s = pres.addSlide();
s.background = { color: C.dark };
s.addShape(pres.shapes.OVAL, { x: -1.5, y: 4.8, w: 4.2, h: 4.2, fill: { color: C.teal, transparency: 75 }, line: { type: "none" } });
s.addText("MAPA DEL CÓDIGO", { x: M, y: 1.5, w: 11, h: 0.4, margin: 0, fontFace: F.mono, fontSize: 15, bold: true, color: C.mint, charSpacing: 2 });
s.addText("Dónde vive cada cosa", { x: M, y: 1.95, w: 11.8, h: 1.0, margin: 0, fontFace: F.head, fontSize: 34, bold: true, color: C.white });
s.addText([
  { text: "NotaComprobante.php", options: { fontFace: F.mono, color: C.mint, bold: true } },
  { text: "  → toda la lógica de la nota", options: { color: C.codeTx, breakLine: true } },
  { text: "Facturalaya.php", options: { fontFace: F.mono, color: C.mint, bold: true } },
  { text: "  → envío a SUNAT (post, enviarNota, baja, resumen, consulta)", options: { color: C.codeTx, breakLine: true } },
  { text: "Venta.php", options: { fontFace: F.mono, color: C.mint, bold: true } },
  { text: "  → consulta de estado y baja", options: { color: C.codeTx, breakLine: true } },
  { text: "ResumenDiario.php", options: { fontFace: F.mono, color: C.mint, bold: true } },
  { text: "  → resumen de boletas", options: { color: C.codeTx, breakLine: true } },
  { text: "notas.php / notas.js / print_venta.php", options: { fontFace: F.mono, color: C.mint, bold: true } },
  { text: "  → la cara visible (formulario, AJAX, QR)", options: { color: C.codeTx } },
], { x: M, y: 3.2, w: 12, h: 3.0, margin: 0, fontFace: F.body, fontSize: 16, valign: "top", lineSpacingMultiple: 1.15 });
s.addText("La idea que se llevan: el mismo patrón (validar → guardar → enviar) resuelve TODO.", { x: M, y: 6.6, w: 12, h: 0.5, margin: 0, fontFace: F.body, fontSize: 14, italic: true, color: "9AE6D6" });
notes(s, "Cierre del recorrido de código. Repasar el mapa: cada archivo tiene una responsabilidad. Y la gran idea: el patrón validar → calcular → guardar (transacción) → enviar → guardar respuesta se repite en notas, baja, resumen y consulta. Si dominan ese patrón, pueden agregar cualquier comprobante nuevo.");

function slide_card(slide, x, y, w, h, fill) {
  slide.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w, h, rectRadius: 0.08, fill: { color: fill }, line: { type: "none" }, shadow: shadow() });
}

pres.writeFile({ fileName: "Clase_Final_Explicacion_Codigo.pptx" }).then((f) => console.log("OK:", f));
