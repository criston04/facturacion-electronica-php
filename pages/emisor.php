<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Emisor</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">Configuración</p>
                <h1>Emisor</h1>
                <p>Gestión de datos del emisor</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <button type="button" onclick="abrirEmisor();"
                    class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
    </div>

    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="md:w-64">
                <input type="text" name="q" id="q" maxlength="50" placeholder="Buscar emisor..."
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

<div id="emisorModal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Registro de Emisor</h4>
            <button type="button" class="text-gray-500 hover:text-red-500 text-2xl font-bold" onclick="cerrarEmisor()">&times;</button>
        </div>
        <div class="p-6">
            <form id="form_emisor" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="add_update" id="add_update" value="1">

                <div>
                    <label class="block text-sm font-medium text-gray-700">RUC <span class="text-red-500">*</span></label>
                    <input type="text" name="ruc" id="ruc" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo Doc.</label>
                    <select name="tipo_doc" id="tipo_doc"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="6">6 - RUC</option>
                        <option value="0">0 - OTROS</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Razón Social <span class="text-red-500">*</span></label>
                    <input type="text" name="razon_social" id="razon_social" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nombre Comercial</label>
                    <input type="text" name="nom_comercial" id="nom_comercial"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Ubigeo</label>
                    <input type="text" name="codigo_ubigeo" id="codigo_ubigeo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Departamento</label>
                    <input type="text" name="direccion_departamento" id="direccion_departamento"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Provincia</label>
                    <input type="text" name="direccion_provincia" id="direccion_provincia"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Distrito</label>
                    <input type="text" name="direccion_distrito" id="direccion_distrito"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código País</label>
                    <input type="text" name="direccion_codigopais" id="direccion_codigopais" value="PE"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Modalidad Envío SUNAT</label>
                    <select name="modalidad_envio_sunat" id="modalidad_envio_sunat"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="inmediato">Inmediato</option>
                        <option value="diferido">Diferido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo Proceso</label>
                    <select name="tipo_proceso" id="tipo_proceso"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="prueba">Prueba</option>
                        <option value="produccion">Producción</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">RUC Proveedor</label>
                    <input type="text" name="ruc_proveedor" id="ruc_proveedor"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo Certificado</label>
                    <select name="tipo_certificado" id="tipo_certificado"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="pse_facturalaya">PSE Facturalaya</option>
                        <option value="certificado_digital">Certificado Digital</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Token Cliente</label>
                    <input type="text" name="token_cliente" id="token_cliente"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Logo (URL)</label>
                    <input type="text" name="logo" id="logo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
            </form>
        </div>
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button" onclick="cerrarEmisor()"
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
                <button type="button" onclick="cancelarEliminar()" class="btn-soft">Cancelar</button>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl font-medium delete-registro-btn">Eliminar</button>
            </div>
        </div>
    </form>
</div>

<?php include_once("template/pie.php"); ?>
<script type="text/javascript" src="./js/emisor.js"></script>
