<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">
    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">Gestión</p>
                <h1>Clientes</h1>
                <p>Gestión de clientes del sistema</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <button type="button" onclick="abrirModal();" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
    </div>

    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="md:w-64">
                <input type="text" id="q" placeholder="Buscar clientes..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="button" onclick="load(1);" class="btn-grad">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>

    <div id="loader"></div>
    <div class='outer_div'></div>
</main>

<!-- Modal Registro -->
<!-- <div id="clienteModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Registro de Cliente</h4>
            <button type="button" class="text-gray-500 hover:text-red-500 text-2xl" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="p-6">
            <form id="form_cliente" class="grid grid-cols-1 gap-4">
                <input type="hidden" name="id_cliente" id="id_cliente" value="0">
                <input type="hidden" name="add_update" value="1">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo Documento <span class="text-red-500">*</span></label>
                     <select name="tipo_documento" id="tipoDocumento" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Número Documento <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Razón Social <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre_razon_social" id="nombre_razon_social" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Correo</label>
                    <input type="email" name="correo" id="correo" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
            </form>
        </div>
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button" onclick="cerrarModal()" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
            <button type="button" class="btn-guardar bg-teal-500 hover:bg-teal-600 text-white px-5 py-2 rounded shadow">Guardar</button>
        </div>
    </div>
</div> -->
<div id="clienteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-8">
    <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b px-6 py-4">
            <div>
                <h4 id="clientModalTitle" class="text-lg font-semibold text-gray-800">Registro de cliente</h4>
                <p class="text-sm text-gray-500">Completa los datos para guardar o actualizar el registro</p>
            </div>
            <button type="button" class="text-3xl font-bold leading-none text-gray-500 hover:text-red-500" onclick="cerrarModal()">&times;</button>
        </div>

        <div class="max-h-[72vh] overflow-y-auto p-6">
            <form id="form_cliente" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input type="hidden" name="add_update" value="1">
                <input type="hidden" name="idcliente" id="idcliente" value="0">

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tipo de documento</label>
                    <select name="tipo_documento" id="tipoDocumento" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Número de documento</label>
                    <input type="text" name="numero_documento" id="numeroDocumento" placeholder="Ingresa DNI o RUC" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nombre / Razón Social</label>
                    <input type="text" name="nombres" id="nombre" placeholder="Nombre o razón social" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Dirección</label>
                    <textarea name="direccion" id="direccion" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                    <select name="estado_cliente" id="estado_cliente" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="flex items-center justify-between gap-3 border-t bg-gray-50 px-6 py-4">
            <button type="button" onclick="cerrarModal()" class="btn-soft">
                <i class="fa fa-times"></i> Cerrar
            </button>
            <button type="button" class="add-insert-update btn-grad">
                <i class="fa fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>


<!-- Modal Eliminar -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <form id="delete_form">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-lg font-semibold mb-4">¿Eliminar registro?</h2>
            <input type="hidden" name="cid" id="delete_cid">
            <input type="hidden" name="eliminar_registro" value="1">
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cerrarDelete()" class="btn-soft">Cancelar</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl font-medium">Eliminar</button>
            </div>
        </div>
    </form>
</div>

<?php include_once("template/pie.php"); ?>
<script src="./js/clientes.js"></script>