// Guía de Remisión Electrónica — formulario, modalidad dinámica y envío.
(function () {
    const URL = '../admin/classes/GuiaRemision.php';
    const data = window.GUIA_DATA || { productos: [], idUsuario: 0, motivos: {} };
    const el = (id) => document.getElementById(id);
    let items = [];

    function nextCorrelativo() {
        $.post(URL, { get_next_gre: 1 }, function (r) {
            el('correlativo').value = String(r.correlativo).padStart(6, '0');
        }, 'json');
    }

    function toggleModalidad() {
        const pub = el('modalidad_transporte').value === '01';
        el('bloquePublico').classList.toggle('hidden', !pub);
        el('bloquePrivado').classList.toggle('hidden', pub);
    }

    function renderItems() {
        const tb = el('detalleBody');
        tb.innerHTML = '';
        items.forEach((it, i) => {
            const tr = document.createElement('tr');
            let opts = '<option value="">Producto…</option>';
            data.productos.forEach(p => {
                const sel = String(p.id) === String(it.id_producto) ? 'selected' : '';
                opts += `<option value="${p.id}" ${sel}>${(p.nombre || '').replace(/"/g, '')}</option>`;
            });
            tr.innerHTML =
                `<td><select class="rounded-lg border-gray-300 p-2 text-sm w-44" data-i="${i}" data-f="prod">${opts}</select></td>
                 <td><input type="text" value="${it.descripcion || ''}" class="rounded-lg border-gray-300 p-2 text-sm w-56" data-i="${i}" data-f="descripcion"></td>
                 <td><input type="text" value="${it.unidad || 'NIU'}" class="rounded-lg border-gray-300 p-2 text-sm w-20" data-i="${i}" data-f="unidad"></td>
                 <td><input type="number" min="1" step="1" value="${it.cantidad || 1}" class="rounded-lg border-gray-300 p-2 text-sm w-24" data-i="${i}" data-f="cantidad"></td>
                 <td><button type="button" class="text-red-500 hover:text-red-700" data-i="${i}" data-f="del"><i class="fas fa-trash"></i></button></td>`;
            tb.appendChild(tr);
        });
        tb.querySelectorAll('select,input,button').forEach(elm => {
            elm.addEventListener(elm.tagName === 'BUTTON' ? 'click' : (elm.tagName === 'SELECT' ? 'change' : 'input'), onItemChange);
        });
    }

    function onItemChange(e) {
        const i = +e.target.dataset.i, f = e.target.dataset.f;
        if (f === 'del') { items.splice(i, 1); renderItems(); return; }
        if (f === 'prod') {
            const p = data.productos.find(x => String(x.id) === e.target.value);
            items[i].id_producto = e.target.value;
            if (p) { items[i].codigo = p.codigo || ('P' + p.id); items[i].descripcion = p.nombre || ''; renderItems(); }
            return;
        }
        items[i][f] = e.target.value;
    }

    function loadLista() {
        $.post(URL, { get_all_guias: 1 }, function (rows) {
            const tb = el('guiasBody');
            if (!rows || !rows.length) { tb.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-gray-400"><i class="fas fa-inbox text-2xl block mb-2"></i>Sin guías emitidas</td></tr>'; return; }
            tb.innerHTML = '';
            rows.forEach(g => {
                const num = g.serie + '-' + String(g.correlativo).padStart(6, '0');
                let badge = 'badge-gray', txt = (g.sunat_estado || 'pendiente').toUpperCase();
                if (g.sunat_estado === 'aceptado') badge = 'badge-green';
                else if (g.sunat_estado === 'rechazado') badge = 'badge-red';
                else if (g.sunat_estado === 'pendiente') badge = 'badge-amber';
                const motivo = (data.motivos[g.motivo_traslado] || g.motivo_traslado || '');
                const acc = '<button onclick="window.open(\'print_guia.php?id=' + g.id + '\',\'_blank\')" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition" title="Imprimir"><i class="fas fa-print"></i></button> '
                    + '<button onclick="consultarGuia(' + g.id + ')" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition" title="Consultar estado"><i class="fas fa-sync"></i></button>';
                tb.insertAdjacentHTML('beforeend',
                    `<tr><td class="font-medium">${num}</td><td>${g.cliente_nombre || '-'}</td><td>${g.fecha_traslado || '-'}</td><td>${motivo}</td><td><span class="badge ${badge}">${txt}</span></td><td class="text-center whitespace-nowrap">${acc}</td></tr>`);
            });
        }, 'json');
    }

    window.consultarGuia = function (id) {
        $.post(URL, { consultar_guia: 1, id: id }, function (r) {
            if (r.status === 202) { toastr.success(r.message); loadLista(); }
            else toastr.error(r.message || 'No se pudo consultar');
        }, 'json').fail(function () { toastr.error('Error al consultar'); });
    };

    document.addEventListener('DOMContentLoaded', function () {
        nextCorrelativo();
        loadLista();
        items = [{ id_producto: '', codigo: '', descripcion: '', unidad: 'NIU', cantidad: 1 }];
        renderItems();
        toggleModalidad();

        el('modalidad_transporte').addEventListener('change', toggleModalidad);
        el('addItem').addEventListener('click', () => { items.push({ id_producto: '', codigo: '', descripcion: '', unidad: 'NIU', cantidad: 1 }); renderItems(); });

        el('guiaForm').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!el('id_cliente').value) { toastr.error('Seleccione el destinatario'); return; }
            const detalle = items.filter(i => i.descripcion).map(i => ({
                id_producto: i.id_producto, codigo: i.codigo || 'P1', descripcion: i.descripcion, unidad: i.unidad || 'NIU', cantidad: i.cantidad || 1
            }));
            if (!detalle.length) { toastr.error('Añada al menos un bien'); return; }

            const btn = this.querySelector('button[type=submit]');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando…';

            $.post(URL, {
                add_guia: 1,
                id_cliente: el('id_cliente').value, id_usuario: data.idUsuario,
                motivo_traslado: el('motivo_traslado').value, modalidad_transporte: el('modalidad_transporte').value,
                fecha_traslado: el('fecha_traslado').value, peso_total: el('peso_total').value,
                ubigeo_partida: el('ubigeo_partida').value, dir_partida: el('dir_partida').value,
                ubigeo_llegada: el('ubigeo_llegada').value, dir_llegada: el('dir_llegada').value,
                transportista_ruc: el('transportista_ruc').value, transportista_razon: el('transportista_razon').value, transportista_mtc: el('transportista_mtc').value,
                vehiculo_placa: el('vehiculo_placa').value,
                conductor_doc: el('conductor_doc').value, conductor_nombres: el('conductor_nombres').value, conductor_apellidos: el('conductor_apellidos').value, conductor_licencia: el('conductor_licencia').value,
                observacion: el('observacion').value,
                detalle: JSON.stringify(detalle)
            }, function (resp) {
                let r; try { r = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch (x) { toastr.error('Respuesta inválida'); return; }
                if (r.status === 202) {
                    const ok = r.sunat && r.sunat.success;
                    const st = ok ? { c: 'emerald', i: 'check' } : { c: 'amber', i: 'triangle-exclamation' };
                    el('resBody').innerHTML =
                        `<div class="flex items-start gap-3"><div class="w-10 h-10 rounded-full bg-${st.c}-500 text-white flex items-center justify-center"><i class="fas fa-${st.i}"></i></div>
                         <div><p class="font-semibold text-${st.c}-700">${r.serie ? (r.serie + '-' + String(r.correlativo).padStart(6,'0')) : 'Guía'}</p>
                         <p class="text-sm text-gray-600 mt-1">${r.message || ''}</p>
                         ${r.sunat && r.sunat.xml ? '<p class="text-xs text-gray-400 mt-2"><i class=\"fas fa-file-code\"></i> XML firmado generado.</p>' : ''}</div></div>`;
                    el('resModal').classList.remove('hidden');
                    nextCorrelativo(); loadLista();
                    items = [{ id_producto: '', codigo: '', descripcion: '', unidad: 'NIU', cantidad: 1 }]; renderItems();
                } else { toastr.error(r.message || 'No se pudo generar la guía'); }
            }).fail(function () { toastr.error('Error de envío'); })
              .always(function () { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Generar y Enviar a SUNAT'; });
        });
    });
})();
