<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}

require_once("../admin/classes/NotaComprobante.php");

$nota_obj = new NotaComprobante();
$documentos = $nota_obj->getDocumentosAfectables();
$motivosCredito = NotaComprobante::motivosCredito();
$motivosDebito = NotaComprobante::motivosDebito();

$preselectDoc = isset($_GET['doc']) ? (int)$_GET['doc'] : 0;
?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Nueva Nota</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">SUNAT</p>
                <h1>Nueva Nota de Crédito / Débito</h1>
                <p>Emitir una nota electrónica referida a un comprobante</p>
            </div>
            <a href="ventas.php" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;"><i class="fas fa-list mr-1"></i> Ver comprobantes</a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <form id="notaForm" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Datos de la Nota</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Nota</label>
                    <select id="tipo_nota" name="tipo_nota" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="NOTA_CREDITO">NOTA DE CRÉDITO (07)</option>
                        <option value="NOTA_DEBITO">NOTA DE DÉBITO (08)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Comprobante afectado</label>
                    <select id="id_doc_afectado" name="id_doc_afectado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione comprobante</option>
                        <?php foreach ($documentos as $d) {
                            $etiqueta = $d['tipo_comprobante'] . ' ' . $d['serie'] . '-' . str_pad($d['correlativo'], 6, '0', STR_PAD_LEFT)
                                . ' — ' . ($d['cliente_nombre'] ?: 'Cliente general')
                                . ' — S/ ' . number_format($d['total'], 2);
                            $sel = ($preselectDoc === (int)$d['id']) ? 'selected' : '';
                            echo "<option value='{$d['id']}' data-tipo='{$d['tipo_comprobante']}' $sel>" . htmlspecialchars($etiqueta) . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Motivo (SUNAT)</label>
                    <select id="cod_motivo" name="cod_motivo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required></select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Sustento / descripción</label>
                    <input type="text" id="desc_motivo" name="desc_motivo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" placeholder="Detalle del motivo (opcional)">
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-700 mb-2">Detalle</h2>
            <p class="text-sm text-gray-500 mb-4">Se precarga con los ítems del comprobante. Edita cantidades o montos para una nota parcial.</p>

            <div class="mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Und</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Unitario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="detalleTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
                <p id="detalleVacio" class="text-sm text-gray-400 mt-4">Selecciona un comprobante para cargar su detalle.</p>
            </div>

            <div class="flex justify-end space-x-4 mb-8">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Subtotal:</p>
                    <p class="text-lg font-semibold text-gray-900" id="subtotalDisplay">0.00</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">IGV (18%):</p>
                    <p class="text-lg font-semibold text-gray-900" id="igvDisplay">0.00</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Total:</p>
                    <p class="text-xl font-bold text-teal-600" id="totalDisplay">0.00</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="ventas.php" class="btn-soft">Cancelar</a>
                <button type="submit" class="btn-grad">
                    <i class="fa fa-save mr-2"></i> Generar y Enviar a SUNAT
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Resultado SUNAT -->
    <div id="sunatModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center border-b px-6 py-3">
                <h4 class="text-lg font-semibold text-gray-800">Resultado SUNAT</h4>
                <button type="button" class="text-gray-500 hover:text-red-500 text-2xl" onclick="cerrarSunatModal()">&times;</button>
            </div>
            <div class="p-6" id="sunatResultContent"></div>
            <div class="flex justify-end border-t px-6 py-3 bg-gray-50 gap-2">
                <button type="button" onclick="imprimirNota()" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 hidden" id="btnPrintNota">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <a href="ventas.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ir a Comprobantes</a>
                <button type="button" onclick="cerrarSunatModal()" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
            </div>
        </div>
    </div>

</main>

<script>
    window.NOTA_DATA = {
        motivos: {
            NOTA_CREDITO: <?= json_encode($motivosCredito, JSON_UNESCAPED_UNICODE) ?>,
            NOTA_DEBITO: <?= json_encode($motivosDebito, JSON_UNESCAPED_UNICODE) ?>
        },
        idUsuario: <?= (int)($idusuario ?? 0) ?>,
        preselectDoc: <?= $preselectDoc ?>
    };
</script>
<?php include_once("template/pie.php"); ?>
<script src="./js/notas.js"></script>
