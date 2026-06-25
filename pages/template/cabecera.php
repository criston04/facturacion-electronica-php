<?php
session_start();
$pagina = basename($_SERVER['PHP_SELF']);
$active = "nav-link active";
$normal = "nav-link";
/* session_start();
if (!isset($_SESSION['idusuario'])) {
    header("Location: ../index.php");
}
$nombre = $_SESSION['nombres'];
$rol = $_SESSION['idrol'];
$idusuario = $_SESSION['idusuario']; */

$logueado = false;
$nombre = null;
$rol = null;
$idusuario = null;

if (isset($_SESSION['idusuarios'])) {
    $logueado = true;
    $nombre = $_SESSION['nombres'];
    $rol = $_SESSION['enum_rol'];
    $desRol = $_SESSION['rol'];
    $idusuario = $_SESSION['idusuarios'];
}
$cartCount = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cartCount += $item['cantidad']; // suma cantidades
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Productos | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="./css/tailwind.min.css"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/jquery.validate.min.js"></script>
    <script src="./js/toastr.min.js"></script>
    <script>
        function toggleSubmenu(btn) {
            var submenu = btn.nextElementSibling;
            var chevron = btn.querySelector('.fa-chevron-down');
            while (submenu && !submenu.classList.contains('submenu')) {
                submenu = submenu.nextElementSibling;
            }
            if (submenu) {
                submenu.classList.toggle('hidden');
                if (chevron) chevron.classList.toggle('rotated');
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.submenu').forEach(function (el) {
                var links = el.querySelectorAll('a');
                var shouldOpen = false;
                links.forEach(function (link) {
                    var page = window.location.pathname.split('/').pop();
                    if (link.getAttribute('href') === page) {
                        shouldOpen = true;
                    }
                });
                if (shouldOpen) {
                    el.classList.remove('hidden');
                    var btn = el.previousElementSibling;
                    while (btn && btn.tagName !== 'BUTTON') {
                        btn = btn.previousElementSibling;
                    }
                    if (btn) {
                        var chevron = btn.querySelector('.fa-chevron-down');
                        if (chevron) chevron.classList.add('rotated');
                    }
                }
            });
        });
    </script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/app.css">
    <script type="text/javascript" src="./js/validation.js"></script>
    <style>
        .error {
            border-color: #dc3545;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #dc3545;
        }
        .submenu {
            overflow: hidden;
            transition: max-height 0.25s ease;
        }
        .submenu.open {
            max-height: 300px;
        }
        .chevron.rotated {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="app-sidebar fixed md:sticky md:top-0 md:self-start z-40 w-80 h-screen flex flex-col justify-between p-5 transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto">

            <div>
                <!-- BRAND -->
                <div class="app-brand">
                    <div class="mark">FE</div>
                    <div>
                        <div class="name">Facturación<br>Electrónica</div>
                        <div class="sub">SUNAT · PHP</div>
                    </div>
                </div>

                <?php if ($logueado): ?>
                    <div class="app-user">
                        <div class="av"><?= strtoupper(substr($nombre ?? 'U', 0, 1)) ?></div>
                        <div>
                            <div class="who"><?= htmlspecialchars($nombre) ?></div>
                            <div class="role"><?= htmlspecialchars($desRol ?? 'Usuario') ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- MENU -->
                <nav>
                    <a href="dashboard.php" class="<?= $pagina == 'dashboard.php' ? $active : $normal ?>">
                        <i class="fa fa-chart-pie"></i> Dashboard
                    </a>

                    <?php if ($logueado && $rol == 1): ?>
                        <button onclick="toggleSubmenu(this)" class="nav-section">
                            <span><i class="fa fa-cogs mr-2"></i> Configuración</span>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div class="submenu hidden">
                            <a href="emisor.php" class="<?= $pagina == 'emisor.php' ? $active : $normal ?>"><i class="fa fa-file-invoice"></i> Emisor</a>
                            <a href="modo_emision.php" class="<?= $pagina == 'modo_emision.php' ? $active : $normal ?>"><i class="fa fa-certificate"></i> Modo de Emisión</a>
                        </div>

                        <button onclick="toggleSubmenu(this)" class="nav-section">
                            <span><i class="fa fa-boxes mr-2"></i> Inventario</span>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div class="submenu hidden">
                            <a href="categoria.php" class="<?= $pagina == 'categoria.php' ? $active : $normal ?>"><i class="fa fa-list"></i> Categorías</a>
                            <a href="producto.php" class="<?= $pagina == 'producto.php' ? $active : $normal ?>"><i class="fa fa-box"></i> Productos</a>
                            <a href="proveedor.php" class="<?= $pagina == 'proveedor.php' ? $active : $normal ?>"><i class="fa fa-truck"></i> Proveedores</a>
                        </div>

                        <button onclick="toggleSubmenu(this)" class="nav-section">
                            <span><i class="fa fa-users mr-2"></i> Personas</span>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div class="submenu hidden">
                            <a href="usuario.php" class="<?= $pagina == 'usuario.php' ? $active : $normal ?>"><i class="fa fa-user-gear"></i> Usuarios</a>
                            <a href="clientes.php" class="<?= $pagina == 'clientes.php' ? $active : $normal ?>"><i class="fa fa-user"></i> Clientes</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($logueado): ?>
                        <button onclick="toggleSubmenu(this)" class="nav-section">
                            <span><i class="fa fa-file-invoice-dollar mr-2"></i> Facturación</span>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div class="submenu hidden">
                            <a href="ventas.php" class="<?= $pagina == 'ventas.php' ? $active : $normal ?>"><i class="fa fa-receipt"></i> Ventas</a>
                            <a href="venta_nueva.php" class="<?= $pagina == 'venta_nueva.php' ? $active : $normal ?>"><i class="fa fa-plus-circle"></i> Nueva Venta</a>
                            <a href="notas.php" class="<?= $pagina == 'notas.php' ? $active : $normal ?>"><i class="fa fa-file-invoice-dollar"></i> Notas C/D</a>
                            <a href="resumenes.php" class="<?= $pagina == 'resumenes.php' ? $active : $normal ?>"><i class="fa fa-layer-group"></i> Resúmenes</a>
                            <a href="guias.php" class="<?= $pagina == 'guias.php' ? $active : $normal ?>"><i class="fa fa-truck-fast"></i> Guías de Remisión</a>
                        </div>

                        <button onclick="toggleSubmenu(this)" class="nav-section">
                            <span><i class="fa fa-id-card mr-2"></i> Cuenta</span>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div class="submenu hidden">
                            <a href="perfil.php" class="<?= $pagina == 'perfil.php' ? $active : $normal ?>"><i class="fa fa-user-gear"></i> Perfil</a>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>

            <?php if ($logueado): ?>
                <a href="cerrarsesion.php" class="app-logout"><i class="fa fa-right-from-bracket mr-1"></i> Cerrar sesión</a>
            <?php else: ?>
                <a href="../index.php" class="app-logout" style="color:#a7f3d0;border-color:rgba(16,185,129,.4);background:rgba(16,185,129,.1)"><i class="fa fa-user-plus mr-1"></i> Iniciar sesión</a>
            <?php endif; ?>
        </aside>


        <!-- OVERLAY MOBILE -->
        <div id="overlay" class="fixed inset-0 bg-black/40 hidden md:hidden" onclick="toggleMenu()"></div>