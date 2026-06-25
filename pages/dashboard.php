<?php include_once("template/cabecera.php"); ?>
<main class="flex-1 p-5 w-full">
    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="kicker">Panel de control</p>
                <h1>Dashboard</h1>
                <p>Resumen general del sistema</p>
            </div>
            <button onclick="cargarDashboard()" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <div id="dashboardContent">
        <div class="text-center py-20 text-gray-400">
            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
            <p>Cargando dashboard...</p>
        </div>
    </div>
</main>

<?php include_once("template/pie.php"); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="./js/dashboard.js"></script>
