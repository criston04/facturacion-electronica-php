<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Usuarios</span>
    </div>



    <!-- HEADER / TOOLBAR -->
    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <!-- TÍTULO -->
            <div>
                <p class="kicker">Administración</p>
                <h1>Usuarios</h1>
                <p>Gestión de usuarios del sistema</p>
            </div>

            <!-- BOTÓN AGREGAR -->
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                <button
                    type="button"
                    onclick="abrirUsuario();"
                    class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-plus-circle"></i>
                    Agregar
                </button>
            </div>

        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="app-card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
            <!-- INPUT -->
            <div class="md:w-64">
                <input
                    type="text"
                    name="q"
                    id="q"
                    maxlength="50"
                    placeholder="Buscar usuario..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-gray-900
             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- BOTÓN BUSCAR -->
            <button
                type="button"
                onclick="load(1);"
                class="btn-grad">
                <i class="fas fa-search"></i>
                Buscar
            </button>
        </div>
    </div>


    <div class="col-md-12">
        <div id="loader"></div><!-- Carga de datos ajax aqui -->
        <div id="resultados"></div><!-- Carga de datos ajax aqui -->
        <div class='outer_div'></div><!-- Carga de datos ajax aqui -->
    </div>


</main>

<div id="usuarioModal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">

    <!-- Caja modal -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">

        <!-- Header -->
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Registro de Usuario</h4>
            <button type="button"
                class="text-gray-500 hover:text-red-500 text-2xl font-bold"
                onclick="cerrarUsuario()">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form id="form_usuario" class="grid grid-cols-1 gap-4">
                <input type="hidden" name="idusuarios" id="idusuarios">
                <input type="hidden" name="add_update" id="add_update" value="1">

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="nombres"
                        id="nombres"
                        required
                        placeholder="Nombre..."
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="apellidos"
                        id="apellidos"
                        required
                        placeholder="Apellidos..."
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Usuario <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="usuario"
                        id="usuario"
                        required
                        placeholder="Usuario..."
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Clave <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="clave"
                        id="clave"
                        required
                        placeholder="Clave..."
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select name="enum_rol" id="enum_rol"
                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white tipoRoles_list">
                    </select>
                </div>
               
            </form>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button"
                onclick="cerrarUsuario()"
                class="btn-soft">
                Cerrar
            </button>

            <button
                type="button"
                class="add-insert-upadate btn-grad">
                <i class="fas fa-plus-circle"></i>
                Agregar
            </button>
        </div>

    </div>
</div>

<div id="usuarioCambioClaveModal"
    class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">

    <!-- Caja modal -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">

        <!-- Header -->
        <div class="flex justify-between items-center border-b px-6 py-3">
            <h4 class="text-lg font-semibold text-gray-800">Cambiar clave</h4>
            <button type="button"
                class="text-gray-500 hover:text-red-500 text-2xl font-bold"
                onclick="cerrarCambiarClave()">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form id="form_newclave" class="grid grid-cols-1 gap-4">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="update_clave" id="update_clave" value="1">

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Clave <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="newclave"
                        id="newclave"
                        required

                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>
                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Confirmar clave <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        name="confclave"
                        id="confclave"
                        required

                        class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-900
                               focus:outline-none focus:ring focus:border-blue-400 bg-white">
                </div>



            </form>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center border-t px-6 py-3 bg-gray-50">
            <button type="button"
                onclick="cerrarCambiarClave()"
                class="btn-soft">
                Cerrar
            </button>

            <button
                type="button"
                class="upadate-clave btn-grad">
                <i class="fas fa-plus-circle"></i>
                Cambiar
            </button>
        </div>

    </div>
</div>


<!-- Modal backdrop -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <!-- Modal content -->
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
<script type="text/javascript" src="./js/usuario.js"></script>