function load(page) {
	var query = $("#q").val();
	var per_page = 10;
	var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
	$("#loader").fadeIn('slow');
	$.ajax({
		method: 'POST',
		url: '../pages/ventas_paginar.php',
		data: parametros,
		beforeSend: function (objeto) {
			$("#loader").html("Cargando...");
		},
		success: function (data) {
			$(".outer_div").html(data).fadeIn('slow');
			$("#loader").html("");
		}
	});
}

function abrirView(id) {
	$.ajax({
		url: '../admin/classes/Venta.php',
		method: 'POST',
		data: { get_venta_by_id: 1, id_venta: id },
		success: function (response) {
			var resp = $.parseJSON(response);
			if (resp) {
				var html = '<div class="border-b pb-4 mb-4">';
				html += '<div class="grid grid-cols-2 md:grid-cols-3 gap-4">';
				html += '<div><span class="text-sm font-medium text-gray-500">Comprobante:</span><p class="text-gray-800">' + resp.serie + '-' + String(resp.correlativo).padStart(6, '0') + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Tipo:</span><p class="text-gray-800">' + resp.tipo_comprobante + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Fecha:</span><p class="text-gray-800">' + resp.fecha_emision + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Cliente:</span><p class="text-gray-800">' + (resp.cliente_nombre || '-') + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Doc. Cliente:</span><p class="text-gray-800">' + (resp.cliente_doc || '-') + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Método Pago:</span><p class="text-gray-800">' + (resp.metodo_pago || '-') + '</p></div>';
				html += '<div><span class="text-sm font-medium text-gray-500">Estado:</span><p class="text-gray-800">' + resp.estado + '</p></div>';

				var sunatLabel = 'PENDIENTE';
				var sunatColor = 'text-yellow-600';
				if (resp.sunat_estado == 'aceptado') { sunatLabel = 'ACEPTADO'; sunatColor = 'text-green-600'; }
				else if (resp.sunat_estado == 'rechazado') { sunatLabel = 'RECHAZADO'; sunatColor = 'text-red-600'; }
				else if (resp.sunat_estado == 'baja') { sunatLabel = 'BAJA'; sunatColor = 'text-orange-600'; }

				html += '<div><span class="text-sm font-medium text-gray-500">SUNAT:</span><p class="font-semibold ' + sunatColor + '">' + sunatLabel + '</p></div>';
				if (resp.sunat_ticket) html += '<div><span class="text-sm font-medium text-gray-500">Ticket:</span><p class="text-gray-800">' + resp.sunat_ticket + '</p></div>';
				if (resp.sunat_mensaje) html += '<div class="col-span-3"><span class="text-sm font-medium text-gray-500">Mensaje:</span><p class="text-gray-800 text-sm">' + resp.sunat_mensaje + '</p></div>';
				html += '</div>';
				if (resp.descripcion_motivo) html += '<div class="mt-2"><span class="text-sm font-medium text-gray-500">Motivo Anulación:</span><p class="text-gray-800">' + resp.descripcion_motivo + '</p></div>';
				html += '</div>';

				html += '<h5 class="font-semibold text-gray-700 mb-2">Detalle de Productos</h5>';
				html += '<table class="min-w-full divide-y divide-gray-200 text-sm">';
				html += '<thead class="bg-gray-50"><tr>';
				html += '<th class="px-4 py-2 text-left">Producto</th>';
				html += '<th class="px-4 py-2 text-left">Código</th>';
				html += '<th class="px-4 py-2 text-center">Und</th>';
				html += '<th class="px-4 py-2 text-right">Cantidad</th>';
				html += '<th class="px-4 py-2 text-right">P. Unitario</th>';
				html += '<th class="px-4 py-2 text-right">Subtotal</th>';
				html += '</tr></thead><tbody>';
				if (resp.detalle) {
					$.each(resp.detalle, function (i, d) {
						html += '<tr class="border-t">';
						html += '<td class="px-4 py-2">' + (d.producto_nombre || '-') + '</td>';
						html += '<td class="px-4 py-2">' + (d.codigo_producto || '-') + '</td>';
						html += '<td class="px-4 py-2 text-center">' + (d.unidad_medida || 'NIU') + '</td>';
						html += '<td class="px-4 py-2 text-right">' + parseInt(d.cantidad) + '</td>';
						html += '<td class="px-4 py-2 text-right">' + parseFloat(d.precio_unitario).toFixed(2) + '</td>';
						html += '<td class="px-4 py-2 text-right font-semibold">' + parseFloat(d.subtotal).toFixed(2) + '</td>';
						html += '</tr>';
					});
				}
				html += '</tbody></table>';

				html += '<div class="flex justify-end mt-4 space-x-6 text-sm">';
				html += '<div><span class="text-gray-500">Subtotal:</span> <span class="font-semibold">S/ ' + parseFloat(resp.subtotal).toFixed(2) + '</span></div>';
				html += '<div><span class="text-gray-500">IGV:</span> <span class="font-semibold">S/ ' + parseFloat(resp.igv).toFixed(2) + '</span></div>';
				html += '<div><span class="text-gray-500">Total:</span> <span class="font-bold text-teal-600">S/ ' + parseFloat(resp.total).toFixed(2) + '</span></div>';
				html += '</div>';

				if (resp.xml_file) html += '<div class="mt-4 text-right"><a href="../admin/' + resp.xml_file + '" download class="btn-soft inline-flex"><i class="fas fa-file-code mr-1"></i> Descargar archivo SUNAT</a></div>';

				$("#viewContent").html(html);
				currentViewId = id;
				document.getElementById('viewModal').classList.remove('hidden');
			}
		}
	});
}

var currentViewId = null;

function cerrarView() {
	document.getElementById('viewModal').classList.add('hidden');
	currentViewId = null;
}

function imprimirVenta() {
	if (currentViewId) {
		window.open('print_venta.php?id=' + currentViewId + '&formato=ticket', '_blank', 'width=400,height=600');
	}
}

function actualizarEstadoSunat() {
	if (!currentViewId) return;
	$.ajax({
		url: '../admin/classes/Venta.php',
		method: 'POST',
		data: { consultar_estado_sunat: 1, id: currentViewId },
		success: function (response) {
			var resp = $.parseJSON(response);
			if (resp.status == 202) {
				toastr.success(resp.message);
				abrirView(currentViewId); // refresca el modal
				load(1);                   // refresca el listado
			} else {
				toastr.error(resp.message);
			}
		},
		error: function () { toastr.error('No se pudo consultar el estado SUNAT'); }
	});
}

function cerrarDelete() {
	document.getElementById('deleteModal').classList.add('hidden');
}

$(document).ready(function () {
	load();

	$(document.body).on('click', '.delete-registro', function () {
		var id = $(this).data('id');
		$("input[name='id']").val(id);
		document.getElementById('deleteModal').classList.remove('hidden');
	});

	$("#delete_form").on('submit', function (e) {
		e.preventDefault();
		$.ajax({
			url: '../admin/classes/Venta.php',
			method: 'POST',
			data: $("#delete_form").serialize(),
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					toastr.success(resp.message);
					load(1);
				} else if (resp.status == 303) {
					toastr.error(resp.message);
				}
				cerrarDelete();
			}
		});
	});

	$(document.body).on('click', '.view-registro', function () {
		var id = $(this).data('id');
		abrirView(id);
	});
});

$(function () {
	var _liveT;
	$(document).on('input', '#q', function () {
		clearTimeout(_liveT);
		_liveT = setTimeout(function () { load(1); }, 350);
	});
});
