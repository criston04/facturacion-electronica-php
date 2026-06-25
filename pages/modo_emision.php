<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}

require_once("../admin/classes/ConfigEmision.php");
$cfg  = ConfigEmision::get();
$cert = ConfigEmision::estadoCertificado();
$modo = $cfg['modo'];
$s    = $cfg['sunat'];
?>
<style>
    .me-fade { animation: meFade .5s ease both; }
    @keyframes meFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .me-card { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background-color .18s ease; }
    .me-card:hover { transform: translateY(-3px); box-shadow: 0 14px 30px -12px rgba(13,148,136,.35); }
    .me-card.activa { border-color: #0d9488; background: #f0fdfa; box-shadow: 0 0 0 4px rgba(13,148,136,.12); }
    .me-check { opacity: 0; transform: scale(.5); transition: all .2s ease; }
    .me-card.activa .me-check { opacity: 1; transform: scale(1); }
    .seg { transition: all .2s ease; }
    .seg.on { background: #0d9488; color: #fff; box-shadow: 0 4px 12px -4px rgba(13,148,136,.6); }
    .me-grid-bg {
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.18) 1px, transparent 0);
        background-size: 18px 18px;
    }
    .pulse-dot { animation: pulseDot 1.6s infinite; }
    @keyframes pulseDot { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
    .save-bar { backdrop-filter: blur(8px); }
</style>

<main class="flex-1 p-5 w-full pb-28">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Modo de Emisión</span>
    </div>

    <!-- HERO -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500 text-white p-7 mb-6 shadow-lg">
        <div class="absolute inset-0 me-grid-bg opacity-60"></div>
        <div class="absolute -right-10 -top-16 w-56 h-56 rounded-full bg-white/10"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-teal-50/80 text-xs font-semibold tracking-widest uppercase mb-1">Configuración</p>
                <h1 class="text-3xl font-bold">Modo de Emisión</h1>
                <p class="text-teal-50/90 mt-1">Elige cómo el sistema envía los comprobantes a SUNAT.</p>
            </div>
            <div class="bg-white/15 border border-white/25 rounded-xl px-4 py-3">
                <p class="text-[11px] uppercase tracking-wider text-teal-50/80">Activo ahora</p>
                <p class="font-semibold flex items-center gap-2 mt-0.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-300 pulse-dot"></span>
                    <span id="pillModo"><?= $modo === 'certificado' ? 'Certificado propio' : 'Proveedor (OSE/PSE)' ?></span>
                    <span class="text-teal-50/70" id="pillEntorno"><?= $modo === 'certificado' ? '· ' . strtoupper($s['entorno']) : '' ?></span>
                </p>
            </div>
        </div>
    </div>

    <!-- SELECCIÓN DE MODO -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <label class="me-card cursor-pointer relative bg-white rounded-2xl border-2 border-gray-200 p-6 block <?= $modo === 'tercero' ? 'activa' : '' ?>" data-modo="tercero">
            <input type="radio" name="modo" value="tercero" class="hidden" <?= $modo === 'tercero' ? 'checked' : '' ?>>
            <span class="me-check absolute top-4 right-4 w-7 h-7 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm"><i class="fas fa-check"></i></span>
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fas fa-cloud"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Proveedor (OSE/PSE)</h3>
                    <p class="text-sm text-gray-500 mt-1">Un tercero (facturalahoy) genera el XML, lo firma y lo envía. No necesitas certificado.</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="text-[11px] font-medium bg-sky-50 text-sky-600 px-2 py-1 rounded-full">Más simple</span>
                        <span class="text-[11px] font-medium bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Depende del proveedor</span>
                    </div>
                </div>
            </div>
        </label>

        <label class="me-card cursor-pointer relative bg-white rounded-2xl border-2 border-gray-200 p-6 block <?= $modo === 'certificado' ? 'activa' : '' ?>" data-modo="certificado">
            <input type="radio" name="modo" value="certificado" class="hidden" <?= $modo === 'certificado' ? 'checked' : '' ?>>
            <span class="me-check absolute top-4 right-4 w-7 h-7 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm"><i class="fas fa-check"></i></span>
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Emisor propio · Certificado</h3>
                    <p class="text-sm text-gray-500 mt-1">Tú firmas con tu certificado digital y envías directo a SUNAT (Greenter).</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="text-[11px] font-medium bg-teal-50 text-teal-600 px-2 py-1 rounded-full">Sin intermediario</span>
                        <span class="text-[11px] font-medium bg-emerald-50 text-emerald-600 px-2 py-1 rounded-full">Control total</span>
                    </div>
                </div>
            </div>
        </label>
    </div>

    <!-- PANEL CERTIFICADO -->
    <div id="panelCertificado" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7 mb-6 <?= $modo === 'certificado' ? '' : 'hidden' ?>">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <h2 class="text-xl font-semibold text-gray-700"><i class="fas fa-sliders-h text-teal-500 mr-2"></i>Configuración del emisor</h2>
            <!-- Segmented entorno -->
            <div class="inline-flex bg-gray-100 rounded-xl p-1" id="segEntorno">
                <button type="button" data-entorno="beta" class="seg px-4 py-1.5 rounded-lg text-sm font-medium <?= $s['entorno'] === 'beta' ? 'on' : 'text-gray-500' ?>">
                    <i class="fas fa-flask mr-1"></i> Beta (pruebas)
                </button>
                <button type="button" data-entorno="produccion" class="seg px-4 py-1.5 rounded-lg text-sm font-medium <?= $s['entorno'] === 'produccion' ? 'on' : 'text-gray-500' ?>">
                    <i class="fas fa-globe mr-1"></i> Producción
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RUC del emisor</label>
                <div class="relative">
                    <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="ruc" type="text" maxlength="11" value="<?= htmlspecialchars($s['ruc']) ?>" class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario SOL</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="usuario_sol" type="text" value="<?= htmlspecialchars($s['usuario_sol']) ?>" class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Clave SOL</label>
                <div class="relative">
                    <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="clave_sol" type="password" value="<?= htmlspecialchars($s['clave_sol']) ?>" class="w-full pl-9 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    <button type="button" id="toggleClave" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-teal-600"><i class="fas fa-eye"></i></button>
                </div>
            </div>
        </div>

        <!-- Certificado -->
        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div id="certIcon" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white shrink-0 bg-gray-400">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Certificado digital</p>
                        <p id="certEstadoTxt" class="text-sm text-gray-500">Cargando…</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span id="certBadge" class="hidden text-xs font-semibold px-3 py-1.5 rounded-full self-center"></span>
                    <label class="cursor-pointer inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-teal-400 hover:text-teal-600 text-gray-600 text-sm px-4 py-2 rounded-lg transition">
                        <i class="fas fa-upload"></i> Subir .pem
                        <input id="fileCert" type="file" accept=".pem" class="hidden">
                    </label>
                    <button type="button" id="btnGenerarCert" class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-teal-400 hover:text-teal-600 text-gray-600 text-sm px-4 py-2 rounded-lg transition">
                        <i class="fas fa-wand-magic-sparkles"></i> Generar de prueba
                    </button>
                </div>
            </div>
        </div>

        <!-- Probar emisión -->
        <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-3">
            <button type="button" id="btnProbar" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition shadow-sm">
                <i class="fas fa-paper-plane"></i> Probar emisión a SUNAT
            </button>
            <p class="text-xs text-gray-400">Envía una boleta de prueba con la configuración actual y muestra la respuesta de SUNAT.</p>
        </div>
        <div id="resultadoProbar" class="hidden mt-4"></div>
    </div>

    <!-- PANEL TERCERO -->
    <div id="panelTercero" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7 mb-6 <?= $modo === 'tercero' ? '' : 'hidden' ?>">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-circle-info"></i></div>
            <div>
                <h2 class="text-lg font-semibold text-gray-700">Emisión por proveedor</h2>
                <p class="text-sm text-gray-500 mt-1 max-w-2xl">En este modo, el proveedor (facturalahoy) genera el XML, lo firma y lo envía a SUNAT. Las credenciales (token) se administran en <a href="emisor.php" class="text-teal-600 hover:underline">Configuración → Emisor</a>. No necesitas certificado ni Composer.</p>
            </div>
        </div>
    </div>

</main>

<!-- BARRA GUARDAR (sticky) -->
<div class="save-bar fixed bottom-0 left-0 right-0 md:left-80 bg-white/85 border-t border-gray-200 px-6 py-3 flex items-center justify-end gap-3 z-30">
    <span id="dirtyHint" class="hidden text-sm text-amber-600 mr-auto"><i class="fas fa-circle-exclamation mr-1"></i> Tienes cambios sin guardar</span>
    <a href="dashboard.php" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2">Cancelar</a>
    <button type="button" id="btnGuardar" class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg shadow transition">
        <i class="fas fa-floppy-disk"></i> Guardar cambios
    </button>
</div>

<script>
    window.MODO_DATA = {
        modo: <?= json_encode($modo) ?>,
        sunat: {
            entorno: <?= json_encode($s['entorno']) ?>,
            ruc: <?= json_encode($s['ruc']) ?>,
            usuario_sol: <?= json_encode($s['usuario_sol']) ?>,
            cert_nombre: <?= json_encode($s['cert_nombre'] ?? 'certificado_prueba.pem') ?>
        },
        cert: <?= json_encode($cert, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<?php include_once("template/pie.php"); ?>
<script src="./js/modo_emision.js"></script>
