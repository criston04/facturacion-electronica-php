function formatMoney(n) {
    return 'S/ ' + parseFloat(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

var mesChart = null;

function cargarDashboard() {
    $('#dashboardContent').html(`
        <div class="text-center py-20 text-gray-400">
            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
            <p>Cargando dashboard…</p>
        </div>
    `);

    $.ajax({
        type: 'POST',
        url: '../admin/classes/Dashboard.php',
        data: { get_dashboard_data: 1 },
        dataType: 'json',
        success: function (r) {
            if (r.status !== 202) {
                $('#dashboardContent').html('<div class="app-card p-6 text-red-600">Error al cargar datos</div>');
                return;
            }
            renderDashboard(r);
        },
        error: function () {
            $('#dashboardContent').html('<div class="app-card p-6 text-red-600">Error de conexión</div>');
        }
    });
}

function badgeEstado(estado) {
    if (estado === 'COMPLETADO') return '<span class="badge badge-green">COMPLETADO</span>';
    if (estado === 'ANULADO') return '<span class="badge badge-red">ANULADO</span>';
    return '<span class="badge badge-amber">' + estado + '</span>';
}

function renderDashboard(d) {
    var html = '';

    // CARDS
    html += '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">';
    html += card('Productos Activos', d.total_productos, 'fa-box', 'from-blue-400 to-blue-600');
    html += card('Clientes Activos', d.total_clientes, 'fa-users', 'from-emerald-400 to-emerald-600');
    html += card('Ventas Totales', d.total_ventas, 'fa-receipt', 'from-violet-400 to-violet-600');
    html += card('Ingresos Totales', formatMoney(d.total_ingresos), 'fa-sack-dollar', 'from-amber-400 to-amber-600');
    html += '</div>';

    // HOY + GRÁFICO
    html += '<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">';

    html += '<div class="app-card p-5">';
    html += '<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4"><i class="fas fa-calendar-day text-teal-500 mr-2"></i>Ventas de hoy</h3>';
    html += '<div class="grid grid-cols-2 gap-4">';
    html += '<div><p class="text-3xl font-bold text-gray-800">' + (d.ventas_hoy.total || 0) + '</p><p class="text-xs text-gray-400 mt-1">Comprobantes</p></div>';
    html += '<div><p class="text-3xl font-bold text-emerald-600">' + formatMoney(d.ventas_hoy.monto || 0) + '</p><p class="text-xs text-gray-400 mt-1">Monto</p></div>';
    html += '</div></div>';

    html += '<div class="app-card p-5 lg:col-span-2">';
    html += '<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4"><i class="fas fa-chart-column text-teal-500 mr-2"></i>Ingresos por mes</h3>';
    html += '<div style="height:220px"><canvas id="chartMes"></canvas></div>';
    html += '</div>';
    html += '</div>';

    // TABLAS
    html += '<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">';

    // VENTAS RECIENTES
    html += '<div class="app-card p-5">';
    html += '<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3"><i class="fas fa-clock text-teal-500 mr-2"></i>Últimas ventas</h3>';
    html += '<div class="overflow-x-auto"><table class="app-table w-full"><thead><tr><th>Comprobante</th><th>Cliente</th><th>Total</th><th class="text-right">Estado</th></tr></thead><tbody>';
    if (d.ventas_recientes && d.ventas_recientes.length) {
        d.ventas_recientes.forEach(function (v) {
            html += '<tr>';
            html += '<td class="font-medium">' + v.serie + '-' + String(v.correlativo).padStart(6, '0') + '</td>';
            html += '<td>' + (v.cliente_nombre || '-') + '</td>';
            html += '<td class="font-medium">' + formatMoney(v.total) + '</td>';
            html += '<td class="text-right">' + badgeEstado(v.estado) + '</td>';
            html += '</tr>';
        });
    } else {
        html += '<tr><td colspan="4" class="py-6 text-center text-gray-400"><i class="fas fa-inbox text-2xl block mb-2"></i>Sin ventas recientes</td></tr>';
    }
    html += '</tbody></table></div></div>';

    // STOCK BAJO
    html += '<div class="app-card p-5">';
    html += '<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3"><i class="fas fa-triangle-exclamation text-amber-500 mr-2"></i>Productos con stock bajo</h3>';
    html += '<div class="overflow-x-auto"><table class="app-table w-full"><thead><tr><th>Producto</th><th>Categoría</th><th class="text-right">Stock</th><th class="text-right">Mínimo</th></tr></thead><tbody>';
    if (d.stock_bajo && d.stock_bajo.length) {
        d.stock_bajo.forEach(function (p) {
            var peligro = p.stock_actual == 0 ? 'text-red-600 font-bold' : 'text-amber-600 font-semibold';
            html += '<tr>';
            html += '<td>' + p.nombre + '</td>';
            html += '<td class="text-gray-500">' + (p.categoria_nombre || '-') + '</td>';
            html += '<td class="text-right ' + peligro + '">' + p.stock_actual + '</td>';
            html += '<td class="text-right text-gray-500">' + p.stock_minimo + '</td>';
            html += '</tr>';
        });
    } else {
        html += '<tr><td colspan="4" class="py-6 text-center text-gray-400"><i class="fas fa-check-circle text-2xl block mb-2 text-emerald-400"></i>Todo en stock óptimo</td></tr>';
    }
    html += '</tbody></table></div></div>';

    html += '</div>';

    $('#dashboardContent').html(html);
    renderChart(d.ventas_por_mes || []);
}

function renderChart(meses) {
    var ctx = document.getElementById('chartMes');
    if (!ctx || typeof Chart === 'undefined') return;
    if (mesChart) { mesChart.destroy(); mesChart = null; }

    var labels = meses.map(function (m) { return m.mes; });
    var ingresos = meses.map(function (m) { return parseFloat(m.total_ingresos); });
    if (!labels.length) { labels = ['Sin datos']; ingresos = [0]; }

    var g = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
    g.addColorStop(0, 'rgba(13,148,136,.9)');
    g.addColorStop(1, 'rgba(16,185,129,.45)');

    mesChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Ingresos (S/)', data: ingresos, backgroundColor: g, borderRadius: 8, maxBarThickness: 46 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return formatMoney(c.parsed.y); } } } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#eef2f6' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
            }
        }
    });
}

function card(label, value, icon, grad) {
    return '<div class="app-card hoverable p-5 flex items-center gap-4">\
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br ' + grad + ' text-white flex items-center justify-center text-xl shadow-md">\
            <i class="fas ' + icon + '"></i>\
        </div>\
        <div>\
            <p class="text-xs text-gray-400 uppercase tracking-wide">' + label + '</p>\
            <p class="text-2xl font-bold text-gray-800">' + value + '</p>\
        </div>\
    </div>';
}

$(document).ready(function () {
    cargarDashboard();
});
