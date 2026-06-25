<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}

require_once("../admin/classes/Producto.php");
require_once("../admin/classes/Cliente.php");
require_once("../admin/classes/Empresa.php");
require_once("../admin/classes/Commun.php");

$producto_obj = new Producto();
$productos = $producto_obj->getAllProductos();
$cliente_obj = new Cliente();
$clientes = $cliente_obj->getAllClientes();
$empresa_obj = new Empresa();
$empresas = $empresa_obj->getAllEmpresas();
$commun_obj = new Commun();
$tipos_comprobante = $commun_obj->getTipoComprobante();
$monedas = $commun_obj->getMoneda();
$metodos_pago = $commun_obj->getMetodoPago();
$estados_factura = $commun_obj->getEstadoFactura();

$json_productos = json_encode($productos);
?>
<main class="flex-1 p-5 w-full">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Nueva Factura</span>
    </div>

    <!-- HEADER / TOOLBAR -->
    <div class="bg-white p-6 rounded-xl shadow mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <!-- TÍTULO -->
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Nueva Factura</h1>
                <p class="text-sm text-gray-500">Generar un nuevo comprobante de venta</p>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <form id="facturaForm" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Datos de la Cabecera</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Serie -->
                <div>
                    <label for="serie" class="block text-sm font-medium text-gray-700">Serie</label>
                    <input type="text" id="serie" name="serie" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="F001" required>
                </div>
                <!-- Número de Factura -->
                <div>
                    <label for="numero_factura" class="block text-sm font-medium text-gray-700">Número de Factura</label>
                    <input type="text" id="numero_factura" name="numero_factura" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="000001" required>
                </div>
                <!-- Fecha Emisión -->
                <div>
                    <label for="fecha_emision" class="block text-sm font-medium text-gray-700">Fecha Emisión</label>
                    <input type="date" id="fecha_emision" name="fecha_emision" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="<?= date('Y-m-d') ?>" required>
                </div>
                <!-- Hora Emisión -->
                <div>
                    <label for="hora_emision" class="block text-sm font-medium text-gray-700">Hora Emisión</label>
                    <input type="time" id="hora_emision" name="hora_emision" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="<?= date('H:i') ?>" required>
                </div>
                <!-- Fecha Vencimiento -->
                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700">Fecha Vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                </div>
                <!-- Empresa -->
                <div>
                    <label for="id_empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
                    <select id="id_empresa" name="id_empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione una empresa</option>
                        <?php if (!empty($empresas)) { foreach ($empresas as $emp) { echo "<option value='{$emp['id_empresa']}'>{$emp['nombre_razon_social']}</option>"; } } ?>
                    </select>
                </div>
                <!-- Cliente -->
                <div>
                    <label for="id_cliente" class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select id="id_cliente" name="id_cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione un cliente</option>
                        <?php if (!empty($clientes)) { foreach ($clientes as $cli) { echo "<option value='{$cli['id_cliente']}'>{$cli['nombre_razon_social']}</option>"; } } ?>
                    </select>
                </div>
                <!-- Tipo Comprobante -->
                <div>
                    <label for="id_tipo_comp" class="block text-sm font-medium text-gray-700">Tipo Comprobante</label>
                    <select id="id_tipo_comp" name="id_tipo_comp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione tipo</option>
                        <?php if (!empty($tipos_comprobante['message']['enumerado'])) { foreach ($tipos_comprobante['message']['enumerado'] as $t) { echo "<option value='{$t['id_tipo_comp']}'>{$t['nombre']}</option>"; } } ?>
                    </select>
                </div>
                <!-- Moneda -->
                <div>
                    <label for="id_moneda" class="block text-sm font-medium text-gray-700">Moneda</label>
                    <select id="id_moneda" name="id_moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione moneda</option>
                        <?php if (!empty($monedas['message']['enumerado'])) { foreach ($monedas['message']['enumerado'] as $m) { echo "<option value='{$m['id_moneda']}'>{$m['nombre']}</option>"; } } ?>
                    </select>
                </div>
                <!-- Tipo Cambio -->
                <div>
                    <label for="tipo_cambio" class="block text-sm font-medium text-gray-700">Tipo de Cambio</label>
                    <input type="number" step="0.001" id="tipo_cambio" name="tipo_cambio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" value="1.000" required>
                </div>
                <!-- Método de Pago -->
                <div>
                    <label for="id_metodo_pago" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                    <select id="id_metodo_pago" name="id_metodo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione método</option>
                        <?php if (!empty($metodos_pago['message']['enumerado'])) { foreach ($metodos_pago['message']['enumerado'] as $mp) { echo "<option value='{$mp['id_metodo_pago']}'>{$mp['nombre']}</option>"; } } ?>
                    </select>
                </div>
                <!-- Estado -->
                <div>
                    <label for="id_estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select id="id_estado" name="id_estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2" required>
                        <option value="">Seleccione estado</option>
                        <?php if (!empty($estados_factura['message']['enumerado'])) { foreach ($estados_factura['message']['enumerado'] as $e) { echo "<option value='{$e['id_estado']}'>{$e['nombre']}</option>"; } } ?>
                    </select>
                </div>
            </div>

            <!-- Observación -->
            <div class="mb-8">
                <label for="observacion" class="block text-sm font-medium text-gray-700">Observación</label>
                <textarea id="observacion" name="observacion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2"></textarea>
            </div>

            <h2 class="text-xl font-semibold text-gray-700 mb-6">Detalle de Productos</h2>

            <div class="mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Unitario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">V. Venta</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Impuesto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Línea</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="detalleTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Filas de detalle se añadirán aquí dinámicamente -->
                    </tbody>
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
                    <p class="text-sm font-medium text-gray-700">Total Impuestos:</p>
                    <p class="text-lg font-semibold text-gray-900" id="totalImpuestosDisplay">0.00</p>
                    <input type="hidden" name="total_impuestos" id="totalImpuestosInput" value="0.00">
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Valor Venta:</p>
                    <p class="text-lg font-semibold text-gray-900" id="valorVentaDisplay">0.00</p>
                    <input type="hidden" name="valor_venta" id="valorVentaInput" value="0.00">
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Total:</p>
                    <p class="text-xl font-bold text-teal-600" id="totalDisplay">0.00</p>
                    <input type="hidden" name="total" id="totalInput" value="0.00">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa fa-save mr-2"></i> Guardar Factura
                </button>
            </div>
        </form>
    </div>

</main>

<script>
    let productList = <?= json_encode($productos) ?>; // Cargamos los productos reales desde PHP
    let detalleItems = []; // This will store the current items in the invoice detail

    // Ya no necesitamos simular la carga, usamos la lista de arriba
    function fetchProducts() {
        updateProductDropdowns();
    }

    function updateProductDropdowns() {
        document.querySelectorAll('.product-select').forEach(selectElement => {
            if (selectElement.options.length <= 1) { // Only populate if not already populated
                productList.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id_producto;
                    option.textContent = product.descripcion;
                    selectElement.appendChild(option);
                });
            }
        });
    }

    function addDetalleRow() {
        const newRowId = detalleItems.length;
        const newRow = {
            id: newRowId,
            id_producto: '',
            cantidad: 1,
            precio_unitario: 0,
            valor_venta: 0,
            valor_impuesto: 0,
            total_linea: 0
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
                <input type="number" name="detalle[${newRowId}][cantidad]" class="cantidad-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" value="1" min="1" data-row-id="${newRowId}" required>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" step="0.01" name="detalle[${newRowId}][precio_unitario]" class="precio-unitario-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" value="0.00" min="0" data-row-id="${newRowId}" readonly required>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <span id="valor_venta_display_${newRowId}">0.00</span>
                <input type="hidden" name="detalle[${newRowId}][valor_venta]" id="valor_venta_input_${newRowId}" value="0.00">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <span id="valor_impuesto_display_${newRowId}">0.00</span>
                <input type="hidden" name="detalle[${newRowId}][valor_impuesto]" id="valor_impuesto_input_${newRowId}" value="0.00">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <span id="total_linea_display_${newRowId}" class="font-semibold">0.00</span>
                <input type="hidden" name="detalle[${newRowId}][total_linea]" id="total_linea_input_${newRowId}" value="0.00">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button type="button" class="text-red-600 hover:text-red-900 remove-row-btn" data-row-id="${newRowId}">Eliminar</button>
            </td>
        `;
        tableBody.appendChild(rowElement);

        // Re-populate product dropdowns for the new row
        const newProductSelect = rowElement.querySelector('.product-select');
        productList.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id_producto;
            option.textContent = product.descripcion;
            newProductSelect.appendChild(option);
        });

        // Add event listeners for the new row
        newProductSelect.addEventListener('change', handleProductChange);
        rowElement.querySelector('.cantidad-input').addEventListener('input', handleInputChange);
        rowElement.querySelector('.precio-unitario-input').addEventListener('input', handleInputChange);
        rowElement.querySelector('.remove-row-btn').addEventListener('click', removeDetalleRow);

        calculateTotals();
    }

    function removeDetalleRow(event) {
        const rowIdToRemove = parseInt(event.target.dataset.rowId);
        detalleItems = detalleItems.filter(item => item.id !== rowIdToRemove);
        document.getElementById(`detalle-row-${rowIdToRemove}`).remove();
        calculateTotals();
    }

    function handleProductChange(event) {
        const rowId = parseInt(event.target.dataset.rowId);
        const selectedProductId = event.target.value;
        const selectedProduct = productList.find(p => String(p.id_producto) === selectedProductId);

        if (selectedProduct) {
            const item = detalleItems.find(i => i.id === rowId);
            item.id_producto = selectedProduct.id_producto;
            item.precio_unitario = parseFloat(selectedProduct.precio_unitario);
            document.querySelector(`#detalle-row-${rowId} .precio-unitario-input`).value = item.precio_unitario.toFixed(2);
            calculateLineTotals(rowId);
        }
    }

    function handleInputChange(event) {
        const rowId = parseInt(event.target.dataset.rowId);
        const fieldName = event.target.name.split('[')[2].replace(']', ''); // e.g., 'cantidad' or 'precio_unitario'
        const value = parseFloat(event.target.value);

        const item = detalleItems.find(i => i.id === rowId);
        item[fieldName] = value;
        calculateLineTotals(rowId);
    }

    function calculateLineTotals(rowId) {
        const item = detalleItems.find(i => i.id === rowId);
        const product = productList.find(p => p.id_producto === item.id_producto);

        if (!product) {
            item.valor_venta = 0;
            item.valor_impuesto = 0;
            item.total_linea = 0;
        } else {
            item.valor_venta = item.cantidad * item.precio_unitario;
            item.valor_impuesto = item.valor_venta * ((parseFloat(product.impuesto_porcentaje) || 0) / 100);
            item.total_linea = item.valor_venta + item.valor_impuesto;
        }

        document.getElementById(`valor_venta_display_${rowId}`).textContent = item.valor_venta.toFixed(2);
        document.getElementById(`valor_venta_input_${rowId}`).value = item.valor_venta.toFixed(2);
        document.getElementById(`valor_impuesto_display_${rowId}`).textContent = item.valor_impuesto.toFixed(2);
        document.getElementById(`valor_impuesto_input_${rowId}`).value = item.valor_impuesto.toFixed(2);
        document.getElementById(`total_linea_display_${rowId}`).textContent = item.total_linea.toFixed(2);
        document.getElementById(`total_linea_input_${rowId}`).value = item.total_linea.toFixed(2);

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalImpuestos = 0;
        let valorVenta = 0;
        let total = 0;

        detalleItems.forEach(item => {
            subtotal += item.valor_venta;
            totalImpuestos += item.valor_impuesto;
            valorVenta += item.valor_venta; // Assuming valor_venta is the sum of all line valor_venta
            total += item.total_linea;
        });

        document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('totalImpuestosDisplay').textContent = totalImpuestos.toFixed(2);
        document.getElementById('totalImpuestosInput').value = totalImpuestos.toFixed(2);
        document.getElementById('valorVentaDisplay').textContent = valorVenta.toFixed(2);
        document.getElementById('valorVentaInput').value = valorVenta.toFixed(2);
        document.getElementById('totalDisplay').textContent = total.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchProducts(); // Load products when the page loads
        addDetalleRow(); // Add an initial row for convenience

        document.getElementById('addProductoBtn').addEventListener('click', addDetalleRow);

        document.getElementById('facturaForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Basic validation (can be enhanced with jQuery Validate)
            if (detalleItems.length === 0) {
                toastr.error('Debe añadir al menos un producto al detalle de la factura.');
                return;
            }
            if (detalleItems.some(item => !item.id_producto || item.cantidad <= 0 || item.precio_unitario <= 0)) {
                toastr.error('Asegúrese de que todos los productos en el detalle tienen un producto seleccionado, cantidad y precio unitario válidos.');
                return;
            }

            const formData = new FormData(this);
            const cabeceraData = {};
            formData.forEach((value, key) => {
                if (!key.startsWith('detalle[')) {
                    cabeceraData[key] = value;
                }
            });

            // Ensure numeric values are parsed as numbers
            cabeceraData.tipo_cambio = parseFloat(cabeceraData.tipo_cambio);
            cabeceraData.subtotal = parseFloat(cabeceraData.subtotal);
            cabeceraData.total_impuestos = parseFloat(cabeceraData.total_impuestos);
            cabeceraData.valor_venta = parseFloat(cabeceraData.valor_venta);
            cabeceraData.total = parseFloat(cabeceraData.total);
            cabeceraData.id_empresa = parseInt(cabeceraData.id_empresa);
            cabeceraData.id_cliente = parseInt(cabeceraData.id_cliente);
            cabeceraData.id_tipo_comp = parseInt(cabeceraData.id_tipo_comp);
            cabeceraData.id_moneda = parseInt(cabeceraData.id_moneda);
            cabeceraData.id_metodo_pago = parseInt(cabeceraData.id_metodo_pago);
            cabeceraData.id_estado = parseInt(cabeceraData.id_estado);


            // Prepare detalle data
            const detalleToSend = detalleItems.map(item => ({
                id_producto: item.id_producto,
                cantidad: item.cantidad,
                precio_unitario: item.precio_unitario,
                valor_venta: item.valor_venta,
                valor_impuesto: item.valor_impuesto,
                total_linea: item.total_linea
            }));

            // Send data via AJAX
            $.ajax({
                url: '../admin/classes/Factura.php', // Endpoint for Factura class
                type: 'POST',
                data: {
                    add_factura_completa: 1,
                    cabecera: cabeceraData,
                    detalle: JSON.stringify(detalleToSend) // Send detail as JSON string
                },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 202) {
                        toastr.success(res.message);
                        // Optionally reset form or redirect
                        document.getElementById('facturaForm').reset();
                        detalleItems = [];
                        document.getElementById('detalleTableBody').innerHTML = '';
                        addDetalleRow();
                        calculateTotals();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error('Error al guardar la factura: ' + textStatus);
                    console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                }
            });
        });
    });
</script>

<?php include_once("template/pie.php"); ?>