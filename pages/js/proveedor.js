function load(page) {
    var query = $("#q").val();
    var per_page = 10;
    var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
    $("#loader").fadeIn('slow');
    $.ajax({
        method: 'POST',
        url: '../pages/proveedor_paginar.php',
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

function abrirProveedor() {
    $('#form_proveedor').trigger("reset");
    $("input[name='id']").val(0);
    $("#estado").val('ACTIVO');
    document.getElementById('proveedorModal').classList.remove('hidden');
}

function cerrarProveedor() {
    document.getElementById('proveedorModal').classList.add('hidden');
}

function cancelarEliminar() {
    document.getElementById('deleteModal').classList.add('hidden');
}

$(document).ready(function () {
    load();

    $(".add-insert-upadate").on("click", function () {
        if ($('#form_proveedor').valid() == false) {
            return;
        }
        $.ajax({
            url: '../admin/classes/Proveedor.php',
            method: 'POST',
            data: $("#form_proveedor").serialize(),
            success: function (response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    load();
                    $('#form_proveedor').trigger("reset");
                    toastr.success(resp.message);
                } else if (resp.status == 303) {
                    toastr.error(resp.message);
                }
                cerrarProveedor();
            }
        })
    });

    $(document.body).on("click", ".edit-registro", function () {
        var prov = $.parseJSON($.trim($(this).children("span").html()));
        $("#id").val(prov.id);
        $("#empresa").val(prov.empresa);
        $("#nombre_comercial").val(prov.nombre_comercial);
        $("#condicion").val(prov.condicion);
        $("#estado_ruc").val(prov.estado_ruc);
        $("#tipo").val(prov.tipo);
        $("#inscripcion").val(prov.inscripcion);
        $("#codigo_ubigeo").val(prov.codigo_ubigeo);
        $("#sistema_emision").val(prov.sistema_emision);
        $("#actividad_exterior").val(prov.actividad_exterior);
        $("#sistema_contabilidad").val(prov.sistema_contabilidad);
        $("#emision_electronica").val(prov.emision_electronica);
        $("#ple").val(prov.ple);
        $("#respuesta_api").val(prov.respuesta_api);
        $("#ruc").val(prov.ruc);
        $("#contacto").val(prov.contacto);
        $("#telefono").val(prov.telefono);
        $("#email").val(prov.email);
        $("#direccion").val(prov.direccion);
        $("#estado").val(prov.estado);
        document.getElementById('proveedorModal').classList.remove('hidden');
    });

    $(document.body).on("click", ".delete-registro", function () {
        var id = $(this).data('id');
        $("#cid").val(id);
        document.getElementById('deleteModal').classList.remove('hidden');
    });

    $("#delete_registro_form").on("submit", function (e) {
        e.preventDefault();
    });

    $(document.body).on("click", ".delete-registro-btn", function () {
        $.ajax({
            url: '../admin/classes/Proveedor.php',
            method: 'POST',
            data: $("#delete_registro_form").serialize(),
            success: function (response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    load();
                    toastr.success(resp.message);
                } else {
                    toastr.error(resp.message);
                }
                cancelarEliminar();
            }
        });
    });
});

$(function () {
    var _liveT;
    $(document).on('input', '#q', function () {
        clearTimeout(_liveT);
        _liveT = setTimeout(function () { load(1); }, 350);
    });
});
