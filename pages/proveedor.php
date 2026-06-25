<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Proveedores</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">COMPRAS</p>
                <h1>Proveedores</h1>
                <p>Gestión de proveedores</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <button type="button" onclick="abrirProveedor();"
                    class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
    </div>

    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="md:w-64">
                <input type="text" name="q" id="q" maxlength="50" placeholder="Buscar proveedor..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="button" onclick="load(1);"
                class="btn-grad">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>

    <div class="col-md-12">
        <div id="loader"></div>
        <div id="resultados"></div>
        <div class='outer_div'></div>
    </div>

</main>

<div id="proveedorModal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Registro de Proveedor</h4>
            <button type="button" class="text-gray-500 hover:text-red-500 text-2xl font-bold" onclick="cerrarProveedor()">&times;</button>
        </div>
        <div class="p-6">
            <form id="form_proveedor" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="add_update" id="add_update" value="1">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Empresa <span class="text-red-500">*</span></label>
                    <input type="text" name="empresa" id="empresa" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nombre Comercial</label>
                    <input type="text" name="nombre_comercial" id="nombre_comercial"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">RUC <span class="text-red-500">*</span></label>
                    <input type="text" name="ruc" id="ruc" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Condición</label>
                    <input type="text" name="condicion" id="condicion"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado RUC</label>
                    <input type="text" name="estado_ruc" id="estado_ruc"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <input type="text" name="tipo" id="tipo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Inscripción</label>
                    <input type="text" name="inscripcion" id="inscripcion"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Ubigeo</label>
                    <input type="text" name="codigo_ubigeo" id="codigo_ubigeo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sistema Emisión</label>
                    <input type="text" name="sistema_emision" id="sistema_emision"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Actividad Exterior</label>
                    <input type="text" name="actividad_exterior" id="actividad_exterior"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sistema Contabilidad</label>
                    <input type="text" name="sistema_contabilidad" id="sistema_contabilidad"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Emisión Electrónica</label>
                    <input type="text" name="emision_electronica" id="emision_electronica"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">PLE</label>
                    <input type="text" name="ple" id="ple"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Respuesta API</label>
                    <input type="text" name="respuesta_api" id="respuesta_api"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contacto</label>
                    <input type="text" name="contacto" id="contacto"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telefono" id="telefono"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="estado" id="estado"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
            </form>
        </div>
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button" onclick="cerrarProveedor()"
                class="btn-soft">Cerrar</button>
            <button type="button"
                class="add-insert-upadate btn-grad">
                <i class="fas fa-plus-circle"></i> Guardar
            </button>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <form name="delete_registro_form" id="delete_registro_form">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">¿Estás seguro que deseas eliminar este registro?</h2>
            <p class="text-sm text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
            <input type="hidden" name="cid" id="cid">
            <input type="hidden" name="eliminar_registro" value="1">
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cancelarEliminar()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded delete-registro-btn">Eliminar</button>
            </div>
        </div>
    </form>
</div>

<?php include_once("template/pie.php"); ?>
<script type="text/javascript" src="./js/proveedor.js"></script>
