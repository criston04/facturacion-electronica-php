
function load(page) {
	var query = $("#q").val();
	var per_page = 10;
	var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
	$("#loader").fadeIn('slow');
	$.ajax({
		method: 'POST',
		url: '../pages/empresa_paginar.php',
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
function abrirCambiarClave() {
	document.getElementById('usuarioCambioClaveModal').classList.remove('hidden');
}
function cerrarCambiarClave() {
	document.getElementById('usuarioCambioClaveModal').classList.add('hidden');
}

function abrirUsuario() {
	$('#form_producto').trigger("reset");
	$("input[name='id_producto']").val(0);
	document.getElementById('productoModal').classList.remove('hidden');
}
function cerrarUsuario() {
	document.getElementById('productoModal').classList.add('hidden');
}
function cancelarEliminar() {
	document.getElementById('deleteModal').classList.add('hidden');
}
$(document).ready(function () {
	// Renderiza la tabla


	load();





	$(".add-insert-upadate").on("click", function () {
		if ($('#form_empresa').valid() == false) {
			return;
		}
		$.ajax({
			url: '../admin/classes/Empresa.php',
			method: 'POST',
			data: $("#form_empresa").serialize(),
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					load();
					$('#form_empresa').trigger("reset");
					toastr.success(resp.message);
				} else if (resp.status == 303) {
					toastr.error(resp.message);
				}
				cerrarUsuario();
			}
		})
	});
	$(document.body).on("click", ".edit-registro", function () {
		var empresa = $.parseJSON($.trim($(this).children("span").html()));
		$("#id_empresa").val(empresa.id_empresa);
		$("#ruc").val(empresa.ruc);
		$("#nombre_razon_social").val(empresa.nombre_razon_social);
		$("#nombre_comercial").val(empresa.nombre_comercial);
		$("#direccion_fiscal").val(empresa.direccion_fiscal);
		$("#codigo_establecimiento").val(empresa.codigo_establecimiento);
		$("#codigo_punto_emision").val(empresa.codigo_punto_emision);
		document.getElementById('productoModal').classList.remove('hidden');
	});


});
