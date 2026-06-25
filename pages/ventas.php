<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">
    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">Facturación</p>
                <h1>Ventas</h1>
                <p>Gestión de comprobantes electrónicos</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <a href="venta_nueva.php" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i> Nueva Venta
                </a>
            </div>
        </div>
    </div>

    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="md:w-64">
                <input type="text" id="q" placeholder="Buscar venta..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="button" onclick="load(1);" class="btn-grad">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>

    <div id="loader"></div>
    <div class='outer_div'></div>
</main>

<!-- Modal Ver Venta -->
<div id="viewModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center border-b px-6 py-4">
            <h4 class="text-lg font-semibold text-gray-800" id="viewModalTitle"><i class="fas fa-receipt text-teal-500 mr-2"></i>Detalle de Venta</h4>
            <button type="button" class="text-gray-400 hover:text-red-500 text-2xl" onclick="cerrarView()">&times;</button>
        </div>
        <div class="p-6">
            <div id="viewContent" class="grid grid-cols-1 gap-4"></div>
        </div>
        <div class="flex justify-end border-t px-6 py-3 bg-gray-50/70 gap-2">
            <button type="button" onclick="actualizarEstadoSunat()" class="btn-soft">
                <i class="fas fa-sync"></i> Actualizar estado SUNAT
            </button>
            <button type="button" onclick="imprimirVenta()" class="btn-grad">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button type="button" onclick="cerrarView()" class="btn-soft">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <form id="delete_form">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-red-100 text-red-500 flex items-center justify-center text-lg"><i class="fas fa-triangle-exclamation"></i></div>
                <h2 class="text-lg font-semibold text-gray-800">¿Anular esta venta?</h2>
            </div>
            <input type="hidden" name="id" id="delete_id">
            <input type="hidden" name="eliminar_venta" value="1">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de anulación</label>
                <textarea name="motivo_anulacion" id="motivo_anulacion" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500" placeholder="Opcional"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="cerrarDelete()" class="btn-soft">Cancelar</button>
                <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl shadow transition"><i class="fas fa-ban"></i> Anular</button>
            </div>
        </div>
    </form>
</div>

<?php include_once("template/pie.php"); ?>
<script src="./js/ventas.js"></script>
