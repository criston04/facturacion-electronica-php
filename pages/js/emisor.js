function load(page) {
    var query = $("#q").val();
    var per_page = 10;
    var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
    $("#loader").fadeIn('slow');
    $.ajax({
        method: 'POST',
        url: '../pages/emisor_paginar.php',
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

function abrirEmisor() {
    $('#form_emisor').trigger("reset");
    $("input[name='id']").val(0);
    $("#tipo_doc").val('6');
    $("#modalidad_envio_sunat").val('inmediato');
    $("#tipo_proceso").val('prueba');
    $("#tipo_certificado").val('pse_facturalaya');
    $("#direccion_codigopais").val('PE');
    document.getElementById('emisorModal').classList.remove('hidden');
}

function cerrarEmisor() {
    document.getElementById('emisorModal').classList.add('hidden');
}

function cancelarEliminar() {
    document.getElementById('deleteModal').classList.add('hidden');
}

$(document).ready(function () {
    load();

    $(".add-insert-upadate").on("click", function () {
        if ($('#form_emisor').valid() == false) {
            return;
        }
        $.ajax({
            url: '../admin/classes/Emisor.php',
            method: 'POST',
            data: $("#form_emisor").serialize(),
            success: function (response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    load();
                    $('#form_emisor').trigger("reset");
                    toastr.success(resp.message);
                } else if (resp.status == 303) {
                    toastr.error(resp.message);
                }
                cerrarEmisor();
            }
        })
    });

    $(document.body).on("click", ".edit-registro", function () {
        var emisor = $.parseJSON($.trim($(this).children("span").html()));
        $("#id").val(emisor.id);
        $("#ruc").val(emisor.ruc);
        $("#tipo_doc").val(emisor.tipo_doc);
        $("#razon_social").val(emisor.razon_social);
        $("#nom_comercial").val(emisor.nom_comercial);
        $("#email").val(emisor.email);
        $("#codigo_ubigeo").val(emisor.codigo_ubigeo);
        $("#direccion").val(emisor.direccion);
        $("#direccion_departamento").val(emisor.direccion_departamento);
        $("#direccion_provincia").val(emisor.direccion_provincia);
        $("#direccion_distrito").val(emisor.direccion_distrito);
        $("#direccion_codigopais").val(emisor.direccion_codigopais);
        $("#modalidad_envio_sunat").val(emisor.modalidad_envio_sunat);
        $("#logo").val(emisor.logo);
        $("#token_cliente").val(emisor.token_cliente);
        $("#ruc_proveedor").val(emisor.ruc_proveedor);
        $("#tipo_certificado").val(emisor.tipo_certificado);
        $("#tipo_proceso").val(emisor.tipo_proceso);
        document.getElementById('emisorModal').classList.remove('hidden');
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
            url: '../admin/classes/Emisor.php',
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
