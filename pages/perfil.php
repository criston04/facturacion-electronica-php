<?php 
include_once("template/cabecera.php"); 

// Redirigir al login si no está logueado para evitar errores de variables nulas
if (!$logueado) {
    header("Location: ../index.php");
    exit();
}
?>
<main class="flex-1 p-5 w-full">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Perfil</span>
    </div>


    <!-- HEADER / TOOLBAR -->
    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <!-- TÍTULO -->
            <div>
                <p class="kicker">Mi cuenta</p>
                <h1>Perfil de usuario</h1>
                <p>Detalles de la cuenta activa en el sistema</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="app-card overflow-hidden">
            <div class="p-8">
                <div class="flex items-center gap-6 mb-8">
                    <div class="w-20 h-20 bg-teal-500 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-teal-100">
                        <?= strtoupper(substr($nombre ?? '', 0, 1) . substr($_SESSION['apellidos'] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars(($nombre ?? '') . ' ' . ($_SESSION['apellidos'] ?? '')) ?></h2>
                        <span class="px-3 py-1 bg-teal-50 text-teal-600 rounded-full text-xs font-semibold uppercase tracking-wider">
                            <?= ($rol ?? 0) == 1 ? 'Administrador' : 'Usuario' ?>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-400 font-medium">ID de Usuario</p>
                        <p class="text-gray-700 font-semibold">#<?= htmlspecialchars($idusuario ?? '0') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-400 font-medium">Nombres</p>
                        <p class="text-gray-700 font-semibold"><?= htmlspecialchars($nombre ?? 'No registrado') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-400 font-medium">Apellidos</p>
                        <p class="text-gray-700 font-semibold"><?= htmlspecialchars($_SESSION['apellidos'] ?? 'No registrado') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-400 font-medium">Rol</p>
                        <p class="text-gray-700 font-semibold"><?= htmlspecialchars($desRol ?? 'Sin rol') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>
<?php include_once("template/pie.php"); ?>