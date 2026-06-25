<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}

require_once("../admin/classes/Producto.php");
require_once("../admin/classes/Cliente.php");

$producto_obj = new Producto();
$productos = $producto_obj->getAllProductos();
$cliente_obj = new Cliente();
$clientes = $cliente_obj->getAllClientes();
?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Nueva Venta</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">Facturación</p>
                <h1>Nueva Venta</h1>
                <p>Generar boleta o factura electrónica</p>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <form id="ventaForm" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Datos del Comprobante</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo Comprobante</label>
                    <select id="tipo_comprobante" name="tipo_comprobante" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="BOLETA">BOLETA</option>
                        <option value="FACTURA">FACTURA</option>
                    </select>
                </div>
                <div>
                    <label for="serie" class="block text-sm font-medium text-gray-700">Serie</label>
                    <input type="text" id="serie" name="serie" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label for="correlativo" class="block text-sm font-medium text-gray-700">Correlativo</label>
                    <input type="text" id="correlativo" name="correlativo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 bg-gray-100" readonly>
                </div>
                <input type="hidden" id="fecha_emision" name="fecha_emision" value="">
                <div>
                    <label for="id_cliente" class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select id="id_cliente" name="id_cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione cliente</option>
                        <?php foreach ($clientes as $cli) {
                            echo "<option value='{$cli['id']}'>{$cli['nombre_razon_social']}</option>";
                        } ?>
                    </select>
                </div>
                <div>
                    <label for="metodo_pago" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                    <select id="metodo_pago" name="metodo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="EFECTIVO">EFECTIVO</option>
                        <option value="TARJETA">TARJETA</option>
                        <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                        <option value="YAPE">YAPE</option>
                        <option value="PLIN">PLIN</option>
                    </select>
                </div>
                <div>
                    <label for="monto_recibido" class="block text-sm font-medium text-gray-700">Monto Recibido</label>
                    <input type="number" step="0.01" id="monto_recibido" name="monto_recibido" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="0.00" min="0">
                </div>
                <div>
                    <label for="cambio" class="block text-sm font-medium text-gray-700">Cambio</label>
                    <input type="number" step="0.01" id="cambio" name="cambio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 bg-gray-100" value="0.00" readonly>
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-700 mb-6">Detalle de Productos</h2>

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
                <button type="button" id="addProductoBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    <i class="fa fa-plus mr-2"></i> Añadir Producto
                </button>
            </div>

            <div class="flex justify-end space-x-4 mb-8">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Subtotal:</p>
                    <p class="text-lg font-semibold text-gray-900" id="subtotalDisplay">0.00</p>
                    <input type="hidden" name="subtotal" id="subtotalInput" value="0.00">
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">IGV (18%):</p>
                    <p class="text-lg font-semibold text-gray-900" id="igvDisplay">0.00</p>
                    <input type="hidden" name="igv" id="igvInput" value="0.00">
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Total:</p>
                    <p class="text-xl font-bold text-teal-600" id="totalDisplay">0.00</p>
                    <input type="hidden" name="total" id="totalInput" value="0.00">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="ventas.php" class="btn-soft">
                    Cancelar
                </a>
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
                <button type="button" onclick="imprimirSunat()" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 hidden" id="btnPrintSunat">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <a href="ventas.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ir a Ventas</a>
                <button type="button" onclick="cerrarSunatModal()" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
            </div>
        </div>
    </div>

</main>

<script>
    let productList = <?= json_encode($productos) ?>;
    let detalleItems = [];

    function actualizarSerieCorrelativo() {
        const tipo = document.getElementById('tipo_comprobante').value;
        document.getElementById('serie').value = tipo === 'FACTURA' ? 'F001' : 'B001';
        $.ajax({
            url: '../admin/classes/Venta.php',
            method: 'POST',
            data: { get_next_correlativo: 1, tipo_comprobante: tipo },
            dataType: 'json',
            success: function(resp) {
                const num = String(resp.correlativo).padStart(6, '0');
                document.getElementById('correlativo').value = num;
            }
        });
    }

    function addDetalleRow() {
        const newRowId = detalleItems.length;
        const newRow = {
            id: newRowId,
            id_producto: '',
            codigo_producto: '',
            unidad_medida: 'NIU',
            tipo_operacion: '10',
            cantidad: 1,
            precio_unitario: 0,
            subtotal: 0
        };
        detalleItems.push(newRow);

        const tableBody = document.getElementById('detalleTableBody');
        const rowElement = document.createElement('tr');
        rowElement.id = `detalle-row-${newRowId}`;
        rowElement.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <select name="detalle[${newRowId}][id_producto]" class="product-select mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" data-row-id="${newRowId}" required>
                    <option value="">Seleccione producto</option>
                </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="text" name="detalle[${newRowId}][unidad_medida]" class="unidad-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" value="NIU" data-row-id="${newRowId}">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" name="detalle[${newRowId}][cantidad]" class="cantidad-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" value="1" min="1" data-row-id="${newRowId}" required>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" step="0.01" name="detalle[${newRowId}][precio_unitario]" class="precio-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" value="0.00" min="0" data-row-id="${newRowId}" readonly required>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <span class="subtotal-display font-semibold" id="subtotal_display_${newRowId}">0.00</span>
                <input type="hidden" name="detalle[${newRowId}][subtotal]" id="subtotal_input_${newRowId}" value="0.00">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button type="button" class="text-red-600 hover:text-red-900 remove-row-btn" data-row-id="${newRowId}">Eliminar</button>
            </td>
        `;
        tableBody.appendChild(rowElement);

        const select = rowElement.querySelector('.product-select');
        productList.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.nombre + (p.codigo ? ` (${p.codigo})` : '');
            select.appendChild(opt);
        });

        select.addEventListener('change', handleProductChange);
        rowElement.querySelector('.cantidad-input').addEventListener('input', handleInputChange);
        rowElement.querySelector('.precio-input').addEventListener('input', handleInputChange);
        rowElement.querySelector('.unidad-input').addEventListener('input', handleInputChange);
        rowElement.querySelector('.remove-row-btn').addEventListener('click', removeDetalleRow);
        calculateTotals();
    }

    function removeDetalleRow(event) {
        const rowId = parseInt(event.target.dataset.rowId);
        detalleItems = detalleItems.filter(item => item.id !== rowId);
        document.getElementById(`detalle-row-${rowId}`).remove();
        calculateTotals();
    }

    function handleProductChange(event) {
        const rowId = parseInt(event.target.dataset.rowId);
        const selectedId = event.target.value;
        const product = productList.find(p => String(p.id) === selectedId);
        if (product) {
            const item = detalleItems.find(i => i.id === rowId);
            item.id_producto = product.id;
            item.codigo_producto = product.codigo || '';
            item.precio_unitario = parseFloat(product.precio_venta);
            item.unidad_medida = 'NIU';
            document.querySelector(`#detalle-row-${rowId} .precio-input`).value = item.precio_unitario.toFixed(2);
            document.querySelector(`#detalle-row-${rowId} .unidad-input`).value = 'NIU';
            calculateLineTotals(rowId);
        }
    }

    function handleInputChange(event) {
        const rowId = parseInt(event.target.dataset.rowId);
        const name = event.target.name;
        const value = parseFloat(event.target.value) || 0;
        const item = detalleItems.find(i => i.id === rowId);
        if (name.includes('cantidad')) item.cantidad = value;
        else if (name.includes('precio_unitario')) item.precio_unitario = value;
        else if (name.includes('unidad_medida')) item.unidad_medida = event.target.value;
        calculateLineTotals(rowId);
    }

    function calculateLineTotals(rowId) {
        const item = detalleItems.find(i => i.id === rowId);
        item.subtotal = item.cantidad * item.precio_unitario;
        document.getElementById(`subtotal_display_${rowId}`).textContent = item.subtotal.toFixed(2);
        document.getElementById(`subtotal_input_${rowId}`).value = item.subtotal.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        detalleItems.forEach(item => { subtotal += item.subtotal; });
        const igv = subtotal * 0.18;
        const total = subtotal + igv;
        document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('igvDisplay').textContent = igv.toFixed(2);
        document.getElementById('igvInput').value = igv.toFixed(2);
        document.getElementById('totalDisplay').textContent = total.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);

        const recibido = parseFloat(document.getElementById('monto_recibido').value) || 0;
        document.getElementById('cambio').value = Math.max(0, recibido - total).toFixed(2);
    }

    let lastVentaId = null;

    function cerrarSunatModal() {
        document.getElementById('sunatModal').classList.add('hidden');
        document.getElementById('btnPrintSunat').classList.add('hidden');
        lastVentaId = null;
    }

    function imprimirSunat() {
        if (lastVentaId) {
            window.open('print_venta.php?id=' + lastVentaId + '&formato=ticket', '_blank', 'width=400,height=600');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        actualizarSerieCorrelativo();
        addDetalleRow();

        document.getElementById('tipo_comprobante').addEventListener('change', actualizarSerieCorrelativo);
        document.getElementById('addProductoBtn').addEventListener('click', addDetalleRow);
        document.getElementById('monto_recibido').addEventListener('input', calculateTotals);

        document.getElementById('ventaForm').addEventListener('submit', function(event) {
            event.preventDefault();

            if (detalleItems.length === 0) {
                toastr.error('Debe añadir al menos un producto');
                return;
            }
            if (detalleItems.some(i => !i.id_producto || i.cantidad <= 0 || i.precio_unitario <= 0)) {
                toastr.error('Complete todos los productos con datos válidos');
                return;
            }

            const fechaFormateada = '';

            const cabecera = {
                tipo_comprobante: document.getElementById('tipo_comprobante').value,
                id_cliente: parseInt(document.getElementById('id_cliente').value) || null,
                id_usuario: <?= $idusuario ?? 0 ?>,
                fecha_emision: fechaFormateada,
                subtotal: parseFloat(document.getElementById('subtotalInput').value),
                igv: parseFloat(document.getElementById('igvInput').value),
                total: parseFloat(document.getElementById('totalInput').value),
                metodo_pago: document.getElementById('metodo_pago').value,
                monto_recibido: parseFloat(document.getElementById('monto_recibido').value) || 0,
                cambio: parseFloat(document.getElementById('cambio').value) || 0,
            };

            const detalle = detalleItems.map(item => ({
                id_producto: item.id_producto,
                codigo_producto: item.codigo_producto,
                unidad_medida: item.unidad_medida,
                tipo_operacion: item.tipo_operacion,
                cantidad: item.cantidad,
                precio_unitario: item.precio_unitario,
                subtotal: item.subtotal,
            }));

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Enviando...';

            $.ajax({
                url: '../admin/classes/Venta.php',
                type: 'POST',
                data: {
                    add_venta: 1,
                    cabecera: cabecera,
                    detalle: JSON.stringify(detalle)
                },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 202) {
                        toastr.success(res.message);
                        let html = '<div class="space-y-3">';
                        html += '<p><strong>Venta #' + res.id_venta + '</strong></p>';
                        html += '<p>' + res.message + '</p>';
                        if (res.sunat) {
                            html += '<hr class="my-2">';
                            html += '<p><strong>HTTP Code:</strong> ' + (res.sunat.http_code || '-') + '</p>';
                            html += '<p><strong>Ticket:</strong> ' + (res.sunat.ticket || '-') + '</p>';
                            html += '<p><strong>Estado SUNAT:</strong> ' + (res.sunat.estado_sunat || '-') + '</p>';
                            if (res.sunat.response) {
                                html += '<pre class="bg-gray-100 p-3 rounded text-xs mt-2 max-h-40 overflow-auto">' +
                                    JSON.stringify(res.sunat.response, null, 2) + '</pre>';
                            }
                        }
                        html += '</div>';
                        document.getElementById('sunatResultContent').innerHTML = html;
                        lastVentaId = res.id_venta;
                        document.getElementById('btnPrintSunat').classList.remove('hidden');
                        document.getElementById('sunatModal').classList.remove('hidden');
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error('Error: ' + textStatus);
                    console.error(jqXHR.responseText);
                },
                complete: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-save mr-2"></i> Generar y Enviar a SUNAT';
                }
            });
        });
    });
</script>

<?php include_once("template/pie.php"); ?>
