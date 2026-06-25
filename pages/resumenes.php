<?php
include_once("template/cabecera.php");

if (!$logueado) {
    header("Location: ../index.php");
    exit();
}
?>
<main class="flex-1 p-5 w-full">

    <div class="flex items-center justify-between mb-4 md:hidden">
        <button onclick="toggleMenu()" class="text-2xl">☰</button>
        <span class="font-semibold">Resúmenes</span>
    </div>

    <div class="page-hero">
        <div class="orb" style="width:220px;height:220px;right:-50px;top:-80px;"></div>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="kicker">SUNAT</p>
                <h1>Resumen Diario de Boletas</h1>
                <p>Informar a SUNAT las boletas emitidas en una fecha</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <input type="date" id="fecha" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-teal-500" value="<?= date('Y-m-d') ?>">
                <button type="button" id="btnCargar" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-search"></i> Cargar boletas
                </button>
                <button type="button" id="btnEnviar" class="btn-soft" style="background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.32);color:#fff;">
                    <i class="fas fa-paper-plane"></i> Enviar resumen a SUNAT
                </button>
            </div>
        </div>
    </div>

    <div class="app-card overflow-x-auto">
        <table class="app-table min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">N°</th>
                    <th class="px-4 py-2 text-left">Comprobante</th>
                    <th class="px-4 py-2 text-right">Subtotal</th>
                    <th class="px-4 py-2 text-right">IGV</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-center">Estado</th>
                </tr>
            </thead>
            <tbody id="tablaBoletas" class="divide-y divide-gray-200 text-gray-700"></tbody>
        </table>
        <div id="sinBoletas" class="app-card p-10 text-center text-gray-400"><i class="fas fa-inbox text-3xl mb-3"></i><p>Selecciona una fecha y pulsa “Cargar boletas”.</p></div>
    </div>

</main>

<script>
    const RESUMEN_URL = '../admin/classes/ResumenDiario.php';

    function cargarBoletas() {
        const fecha = document.getElementById('fecha').value;
        $.ajax({
            url: RESUMEN_URL, method: 'POST', dataType: 'json',
            data: { get_boletas_fecha: 1, fecha: fecha },
            success: function (rows) {
                const tbody = document.getElementById('tablaBoletas');
                tbody.innerHTML = '';
                document.getElementById('sinBoletas').style.display = rows.length ? 'none' : '';
                rows.forEach((b, i) => {
                    const tr = document.createElement('tr');
                    const num = b.serie + '-' + String(b.correlativo).padStart(6, '0');
                    const anulada = b.estado === 'ANULADO';
                    tr.innerHTML =
                        '<td class="px-4 py-2">' + (i + 1) + '</td>' +
                        '<td class="px-4 py-2 font-medium">' + num + '</td>' +
                        '<td class="px-4 py-2 text-right">S/ ' + parseFloat(b.subtotal).toFixed(2) + '</td>' +
                        '<td class="px-4 py-2 text-right">S/ ' + parseFloat(b.igv).toFixed(2) + '</td>' +
                        '<td class="px-4 py-2 text-right font-semibold">S/ ' + parseFloat(b.total).toFixed(2) + '</td>' +
                        '<td class="px-4 py-2 text-center">' +
                            '<span class="badge ' + (anulada ? 'badge-red' : 'badge-green') + '">' +
                            (anulada ? 'ANULA' : 'ADICIONA') + '</span></td>';
                    tbody.appendChild(tr);
                });
            },
            error: function () { toastr.error('No se pudieron cargar las boletas'); }
        });
    }

    function enviarResumen() {
        const fecha = document.getElementById('fecha').value;
        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Enviando...';
        $.ajax({
            url: RESUMEN_URL, method: 'POST',
            data: { enviar_resumen: 1, fecha: fecha },
            success: function (response) {
                let res; try { res = JSON.parse(response); } catch (e) { toastr.error('Respuesta inválida'); return; }
                if (res.status === 202) {
                    toastr.success(res.message + ' (' + res.total_boletas + ' boletas)');
                } else {
                    toastr.error(res.message);
                }
            },
            error: function () { toastr.error('Error al enviar el resumen'); },
            complete: function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar resumen a SUNAT';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnCargar').addEventListener('click', cargarBoletas);
        document.getElementById('btnEnviar').addEventListener('click', enviarResumen);
        cargarBoletas();
    });
</script>

<?php include_once("template/pie.php"); ?>
