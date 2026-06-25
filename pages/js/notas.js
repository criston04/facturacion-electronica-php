// Emisión de notas de crédito/débito. Reusa el endpoint ../admin/classes/NotaComprobante.php
(function () {
    const data = window.NOTA_DATA || { motivos: {}, idUsuario: 0, preselectDoc: 0 };
    const NOTA_URL = '../admin/classes/NotaComprobante.php';
    let detalleItems = [];
    let lastNotaId = null;

    // Helper de DOM. Ojo: no usar el nombre "$" para no chocar con jQuery ($.ajax).
    const el = (id) => document.getElementById(id);

    function populateMotivos() {
        const tipo = el('tipo_nota').value;
        const motivos = data.motivos[tipo] || {};
        const sel = el('cod_motivo');
        sel.innerHTML = '';
        Object.keys(motivos).forEach((cod) => {
            const opt = document.createElement('option');
            opt.value = cod;
            opt.textContent = cod + ' - ' + motivos[cod];
            sel.appendChild(opt);
        });
    }

    function recalc() {
        let subtotal = 0;
        detalleItems.forEach((it) => { subtotal += it.subtotal; });
        const igv = subtotal * 0.18;
        const total = subtotal + igv;
        el('subtotalDisplay').textContent = subtotal.toFixed(2);
        el('igvDisplay').textContent = igv.toFixed(2);
        el('totalDisplay').textContent = total.toFixed(2);
    }

    function renderDetalle() {
        const tbody = el('detalleTableBody');
        tbody.innerHTML = '';
        el('detalleVacio').style.display = detalleItems.length === 0 ? '' : 'none';

        detalleItems.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${item.producto_nombre || 'Producto'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.unidad_medida || 'NIU'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" min="0" step="1" value="${item.cantidad}" data-idx="${idx}" data-field="cantidad"
                        class="cant-input w-24 rounded-md border-gray-300 shadow-sm sm:text-sm p-2">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" min="0" step="0.01" value="${item.precio_unitario.toFixed(2)}" data-idx="${idx}" data-field="precio_unitario"
                        class="prec-input w-28 rounded-md border-gray-300 shadow-sm sm:text-sm p-2">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold subtotal-cell">${item.subtotal.toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button type="button" class="text-red-600 hover:text-red-900 remove-btn" data-idx="${idx}">Quitar</button>
                </td>`;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll('.cant-input, .prec-input').forEach((inp) => {
            inp.addEventListener('input', onLineInput);
        });
        tbody.querySelectorAll('.remove-btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const idx = parseInt(e.target.dataset.idx);
                detalleItems.splice(idx, 1);
                renderDetalle();
            });
        });
        recalc();
    }

    function onLineInput(e) {
        const idx = parseInt(e.target.dataset.idx);
        const field = e.target.dataset.field;
        const val = parseFloat(e.target.value) || 0;
        const item = detalleItems[idx];
        item[field] = val;
        item.subtotal = (item.cantidad || 0) * (item.precio_unitario || 0);
        e.target.closest('tr').querySelector('.subtotal-cell').textContent = item.subtotal.toFixed(2);
        recalc();
    }

    function loadDocumento(id) {
        if (!id) { detalleItems = []; renderDetalle(); return; }
        $.ajax({
            url: NOTA_URL,
            method: 'POST',
            data: { get_documento_afectado: 1, id: id },
            dataType: 'json',
            success: function (resp) {
                detalleItems = (resp && resp.detalle ? resp.detalle : []).map((d) => ({
                    id_producto: parseInt(d.id_producto) || 0,
                    codigo_producto: d.codigo_producto || d.producto_codigo || '',
                    producto_nombre: d.producto_nombre || 'Producto',
                    unidad_medida: d.unidad_medida || 'NIU',
                    tipo_operacion: d.tipo_operacion || '10',
                    cantidad: parseFloat(d.cantidad) || 0,
                    precio_unitario: parseFloat(d.precio_unitario) || 0,
                    subtotal: parseFloat(d.subtotal) || 0
                }));
                renderDetalle();
            },
            error: function () { toastr.error('No se pudo cargar el comprobante'); }
        });
    }

    function cerrarSunatModal() {
        el('sunatModal').classList.add('hidden');
        el('btnPrintNota').classList.add('hidden');
        lastNotaId = null;
    }
    window.cerrarSunatModal = cerrarSunatModal;
    window.imprimirNota = function () {
        if (lastNotaId) window.open('print_venta.php?id=' + lastNotaId + '&formato=ticket', '_blank', 'width=400,height=600');
    };

    document.addEventListener('DOMContentLoaded', function () {
        populateMotivos();
        el('tipo_nota').addEventListener('change', populateMotivos);
        el('id_doc_afectado').addEventListener('change', (e) => loadDocumento(e.target.value));

        if (data.preselectDoc) {
            el('id_doc_afectado').value = String(data.preselectDoc);
            loadDocumento(data.preselectDoc);
        }

        el('notaForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (!el('id_doc_afectado').value) { toastr.error('Seleccione el comprobante afectado'); return; }
            if (detalleItems.length === 0) { toastr.error('La nota debe tener al menos un ítem'); return; }
            if (detalleItems.some((i) => i.cantidad <= 0 || i.precio_unitario <= 0)) {
                toastr.error('Revise cantidades y precios del detalle'); return;
            }

            const detalle = detalleItems.map((i) => ({
                id_producto: i.id_producto,
                codigo_producto: i.codigo_producto,
                producto_nombre: i.producto_nombre,
                unidad_medida: i.unidad_medida,
                tipo_operacion: i.tipo_operacion,
                cantidad: i.cantidad,
                precio_unitario: i.precio_unitario,
                subtotal: i.subtotal
            }));

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Enviando...';

            $.ajax({
                url: NOTA_URL,
                method: 'POST',
                data: {
                    add_nota: 1,
                    id_doc_afectado: el('id_doc_afectado').value,
                    tipo_nota: el('tipo_nota').value,
                    cod_motivo: el('cod_motivo').value,
                    desc_motivo: el('desc_motivo').value,
                    id_usuario: data.idUsuario,
                    detalle: JSON.stringify(detalle)
                },
                success: function (response) {
                    let res;
                    try { res = JSON.parse(response); } catch (err) { toastr.error('Respuesta inválida del servidor'); return; }
                    if (res.status === 202) {
                        toastr.success(res.message);
                        let html = '<div class="space-y-2">';
                        html += '<p><strong>' + res.serie + '-' + String(res.correlativo).padStart(6, '0') + '</strong></p>';
                        html += '<p>' + res.message + '</p>';
                        if (res.sunat) {
                            html += '<hr class="my-2">';
                            html += '<p><strong>HTTP:</strong> ' + (res.sunat.http_code || '-') + '</p>';
                            html += '<p><strong>Estado SUNAT:</strong> ' + (res.sunat.estado_sunat || '-') + '</p>';
                        }
                        html += '</div>';
                        el('sunatResultContent').innerHTML = html;
                        lastNotaId = res.id_nota;
                        el('btnPrintNota').classList.remove('hidden');
                        el('sunatModal').classList.remove('hidden');
                    } else {
                        toastr.error(res.message || 'No se pudo generar la nota');
                    }
                },
                error: function (jqXHR, textStatus) {
                    toastr.error('Error: ' + textStatus);
                    console.error(jqXHR.responseText);
                },
                complete: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-save mr-2"></i> Generar y Enviar a SUNAT';
                }
            });
        });
    });
})();
