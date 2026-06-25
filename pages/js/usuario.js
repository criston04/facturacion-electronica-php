
function load(page) {
	var query = $("#q").val();
	var per_page = 10;
	var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
	$("#loader").fadeIn('slow');
	$.ajax({
		method: 'POST',
		url: '../pages/usuario_paginar.php',
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
	$("input[name='idusuarios']").val(0);
	document.getElementById('usuarioModal').classList.remove('hidden');
}
function cerrarUsuario() {
	document.getElementById('usuarioModal').classList.add('hidden');
}
function cancelarEliminar() {
	document.getElementById('deleteModal').classList.add('hidden');
}
$(document).ready(function () {
	// Renderiza la tabla


	load();


	function getRoles() {
		$.ajax({
			url: '../admin/classes/Usuario.php',
			method: 'POST',
			data: { GET_ROLES: 1 },
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {

					var catSelectHTML = '<option value="">Seleccione</option>';
					$.each(resp.message.roles, function (index, value) {
						catSelectHTML += '<option value="' + value.valor + '">' + value.nombre + '</option>';
					});
					$(".tipoRoles_list").html(catSelectHTML);

				}
			}
		});
	}
	getRoles();




	$(".add-insert-upadate").on("click", function () {
		if ($('#form_usuario').valid() == false) {
			return;
		}
		$.ajax({
			url: '../admin/classes/Usuario.php',
			method: 'POST',
			data: $("#form_usuario").serialize(),
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					load();
					$('#form_usuario').trigger("reset");
					toastr.success(resp.message);
				} else if (resp.status == 303) {
					toastr.error(resp.message);
				}
				cerrarUsuario();
			}
		})
	});
	$(document.body).on("click", ".edit-registro", function () {
		var usuario = $.parseJSON($.trim($(this).children("span").html()));
		$("#idusuarios").val(usuario.idusuarios);
		$("#nombres").val(usuario.nombres);
		$("#apellidos").val(usuario.apellidos);
		$("#usuario").val(usuario.usuario);
		$("#clave").val('********');
		$("#enum_rol").val(usuario.enum_rol);
		document.getElementById('usuarioModal').classList.remove('hidden');
	});

	$(document.body).on('click', '.delete-registro', function () {
		var cid = $(this).data('cid');
		$("input[name='cid']").val(cid);
		document.getElementById('deleteModal').classList.remove('hidden');
	});

	$(".delete-registro-btn").on('click', function (e) {
		e.preventDefault();
		$.ajax({
			url: '../admin/classes/Usuario.php',
			method: 'POST',
			data: $("#delete_registro_form").serialize(),
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					toastr.success(resp.message);
					load(1);
				} else if (resp.status == 303) {
					toastr.error(resp.message);
				}
				cancelarEliminar();

			}
		});
	});
	$(document.body).on('click', '.change-pass', function () {
		var cid = $(this).data('cid');
		$("input[name='id']").val(cid);
		document.getElementById('usuarioCambioClaveModal').classList.remove('hidden');
	});


	$(".upadate-clave").on("click", function () {
		if ($('#form_newclave').valid() == false) {
			return;
		}
		$.ajax({
			url: '../admin/classes/Usuario.php',
			method: 'POST',
			data: $("#form_newclave").serialize(),
			success: function (response) {
				var resp = $.parseJSON(response);
				if (resp.status == 202) {
					load();
					$('#form_newclave').trigger("reset");
					toastr.success(resp.message);
				} else if (resp.status == 303) {
					toastr.error(resp.message);
				}
				cerrarCambiarClave();
			}
		})
	});
});

$(function () {
	var _liveT;
	$(document).on('input', '#q', function () {
		clearTimeout(_liveT);
		_liveT = setTimeout(function () { load(1); }, 350);
	});
});
