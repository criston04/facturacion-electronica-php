<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}

require_once("../admin/classes/Producto.php");
require_once("../admin/classes/Cliente.php");
require_once("../admin/classes/GuiaRemision.php");

$productos = (new Producto())->getAllProductos();
$clientes  = (new Cliente())->getAllClientes();
$motivos   = GuiaRemision::motivosTraslado();
$modalidades = GuiaRemision::modalidades();
?>
<main class="flex-1 p-5 w-full pb-10">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Guía de Remisión</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">Facturación</p>
                <h1>Guía de Remisión Electrónica</h1>
                <p>Sustenta el traslado de bienes (GRE remitente · tipo 09)</p>
            </div>
            <a href="#listaGuias" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;"><i class="fas fa-list"></i> Ver emitidas</a>
        </div>
    </div>

    <form id="guiaForm" class="app-card p-7 mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-5"><i class="fas fa-truck-fast text-teal-500 mr-2"></i>Datos del traslado</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Serie</label>
                <input type="text" id="serie" value="T001" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 p-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correlativo</label>
                <input type="text" id="correlativo" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 p-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de traslado</label>
                <input type="date" id="fecha_traslado" value="<?= date('Y-m-d') ?>" class="w-full rounded-lg border-gray-300 p-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peso total (KG)</label>
                <input type="number" step="0.01" id="peso_total" value="1" min="0" class="w-full rounded-lg border-gray-300 p-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Destinatario</label>
                <select id="id_cliente" class="w-full rounded-lg border-gray-300 p-2.5 text-sm" required>
                    <option value="">Seleccione cliente</option>
                    <?php foreach ($clientes as $c) echo "<option value='{$c['id']}'>" . htmlspecialchars($c['nombre_razon_social']) . "</option>"; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del traslado</label>
                <select id="motivo_traslado" class="w-full rounded-lg border-gray-300 p-2.5 text-sm">
                    <?php foreach ($motivos as $k => $v) echo "<option value='$k'>$k - " . htmlspecialchars($v) . "</option>"; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
                <select id="modalidad_transporte" class="w-full rounded-lg border-gray-300 p-2.5 text-sm">
                    <?php foreach ($modalidades as $k => $v) echo "<option value='$k'>$k - " . htmlspecialchars($v) . "</option>"; ?>
                </select>
            </div>
        </div>

        <!-- Partida / Llegada -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-location-dot text-teal-500 mr-1"></i> Punto de partida</p>
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" id="ubigeo_partida" maxlength="6" value="150101" placeholder="Ubigeo" class="rounded-lg border-gray-300 p-2.5 text-sm">
                    <input type="text" id="dir_partida" placeholder="Dirección" class="col-span-2 rounded-lg border-gray-300 p-2.5 text-sm">
                </div>
            </div>
            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-flag-checkered text-emerald-500 mr-1"></i> Punto de llegada</p>
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" id="ubigeo_llegada" maxlength="6" value="150140" placeholder="Ubigeo" class="rounded-lg border-gray-300 p-2.5 text-sm">
                    <input type="text" id="dir_llegada" placeholder="Dirección" class="col-span-2 rounded-lg border-gray-300 p-2.5 text-sm">
                </div>
            </div>
        </div>

        <!-- Transporte (dinámico) -->
        <div id="bloquePrivado" class="rounded-xl border border-gray-200 p-4 mb-5">
            <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-car text-teal-500 mr-1"></i> Transporte privado — vehículo y conductor</p>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <input type="text" id="vehiculo_placa" placeholder="Placa" value="ABC123" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="conductor_doc" placeholder="DNI conductor" value="44556677" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="conductor_nombres" placeholder="Nombres" value="JUAN" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="conductor_apellidos" placeholder="Apellidos" value="PEREZ" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="conductor_licencia" placeholder="Licencia" value="Q12345678" class="rounded-lg border-gray-300 p-2.5 text-sm">
            </div>
        </div>
        <div id="bloquePublico" class="rounded-xl border border-gray-200 p-4 mb-5 hidden">
            <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-truck text-indigo-500 mr-1"></i> Transporte público — transportista</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" id="transportista_ruc" placeholder="RUC transportista" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="transportista_razon" placeholder="Razón social" class="rounded-lg border-gray-300 p-2.5 text-sm">
                <input type="text" id="transportista_mtc" placeholder="N° registro MTC" class="rounded-lg border-gray-300 p-2.5 text-sm">
            </div>
        </div>

        <!-- Bienes -->
        <h2 class="text-lg font-semibold text-gray-700 mb-3"><i class="fas fa-boxes-stacked text-teal-500 mr-2"></i>Bienes a trasladar</h2>
        <div class="overflow-x-auto mb-3">
            <table class="app-table w-full">
                <thead><tr><th>Producto</th><th>Descripción</th><th>Und</th><th>Cantidad</th><th></th></tr></thead>
                <tbody id="detalleBody"></tbody>
            </table>
        </div>
        <button type="button" id="addItem" class="btn-soft mb-2"><i class="fas fa-plus"></i> Añadir bien</button>

        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
            <input type="text" id="observacion" placeholder="Opcional" class="w-full rounded-lg border-gray-300 p-2.5 text-sm">
        </div>

        <div class="flex justify-end gap-3">
            <a href="dashboard.php" class="btn-soft">Cancelar</a>
            <button type="submit" class="btn-grad"><i class="fas fa-paper-plane"></i> Generar y Enviar a SUNAT</button>
        </div>
    </form>

    <!-- Lista de guías emitidas -->
    <div id="listaGuias" class="app-card p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-clock-rotate-left text-teal-500 mr-2"></i>Guías emitidas</h2>
        <div class="overflow-x-auto">
            <table class="app-table w-full">
                <thead><tr><th>Guía</th><th>Destinatario</th><th>Fecha traslado</th><th>Motivo</th><th>SUNAT</th><th class="text-center">Acción</th></tr></thead>
                <tbody id="guiasBody"><tr><td colspan="6" class="text-center text-gray-400 py-4">Cargando…</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Modal resultado -->
    <div id="resModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
        <div class="app-card w-full max-w-lg p-0 overflow-hidden">
            <div class="flex justify-between items-center border-b px-6 py-3">
                <h4 class="font-semibold text-gray-800">Resultado de la Guía</h4>
                <button type="button" onclick="document.getElementById('resModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 text-2xl">&times;</button>
            </div>
            <div id="resBody" class="p-6"></div>
            <div class="flex justify-end border-t px-6 py-3 gap-2">
                <button type="button" onclick="document.getElementById('resModal').classList.add('hidden')" class="btn-soft">Cerrar</button>
            </div>
        </div>
    </div>

</main>

<script>
    window.GUIA_DATA = {
        productos: <?= json_encode($productos, JSON_UNESCAPED_UNICODE) ?>,
        idUsuario: <?= (int)($idusuario ?? 0) ?>,
        motivos: <?= json_encode($motivos, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<?php include_once("template/pie.php"); ?>
<script src="./js/guias.js"></script>
