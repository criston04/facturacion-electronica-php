<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Productos</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">INVENTARIO</p>
                <h1>Productos</h1>
                <p>Gestión de productos</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <button type="button" onclick="abrirProducto();"
                    class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
    </div>

    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="md:w-64">
                <input type="text" name="q" id="q" maxlength="50" placeholder="Buscar producto..."
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

<div id="productoModal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Registro de Producto</h4>
            <button type="button" class="text-gray-500 hover:text-red-500 text-2xl font-bold" onclick="cerrarProducto()">&times;</button>
        </div>
        <div class="p-6">
            <form id="form_producto" class="grid grid-cols-1 md:grid-cols-2 gap-4" enctype="multipart/form-data">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="add_update" id="add_update" value="1">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <input type="text" name="codigo" id="codigo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Barras</label>
                    <input type="text" name="codigo_barras" id="codigo_barras"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="2"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categoría <span class="text-red-500">*</span></label>
                    <select name="id_categoria" id="id_categoria" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white categoria_list">
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Proveedor</label>
                    <select name="id_proveedor" id="id_proveedor"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white proveedor_list">
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio Venta <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="precio_venta" id="precio_venta" required
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Costo Compra</label>
                    <input type="number" step="0.01" name="costo_compra" id="costo_compra"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                    <input type="number" name="stock_actual" id="stock_actual"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="stock_minimo"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Imagen</label>
                    <input type="file" name="imagen" id="imagen" accept="image/*"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <div id="imagen_preview" class="mt-2 hidden">
                        <img id="imagen_preview_img" src="" alt="Vista previa" class="w-24 h-24 object-cover rounded border">
                    </div>
                    <input type="hidden" name="imagen_actual" id="imagen_actual">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="estado" id="estado"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900 bg-white">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button" onclick="cerrarProducto()"
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
<script type="text/javascript" src="./js/producto.js"></script>
