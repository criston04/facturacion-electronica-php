function load(page) {
    var query = $("#q").val();
    var per_page = 10;
    var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
    $("#loader").fadeIn('slow');
    $.ajax({
        method: 'POST',
        url: '../pages/categoria_paginar.php',
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

function abrirCategoria() {
    $('#form_categoria').trigger("reset");
    $("input[name='id']").val(0);
    $("#estado").val('ACTIVO');
    document.getElementById('categoriaModal').classList.remove('hidden');
}

function cerrarCategoria() {
    document.getElementById('categoriaModal').classList.add('hidden');
}

function cancelarEliminar() {
    document.getElementById('deleteModal').classList.add('hidden');
}

$(document).ready(function () {
    load();

    $(".add-insert-upadate").on("click", function () {
        if ($('#form_categoria').valid() == false) {
            return;
        }
        $.ajax({
            url: '../admin/classes/Categoria.php',
            method: 'POST',
            data: $("#form_categoria").serialize(),
            success: function (response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    load();
                    $('#form_categoria').trigger("reset");
                    toastr.success(resp.message);
                } else if (resp.status == 303) {
                    toastr.error(resp.message);
                }
                cerrarCategoria();
            }
        })
    });

    $(document.body).on("click", ".edit-registro", function () {
        var cat = $.parseJSON($.trim($(this).children("span").html()));
        $("#id").val(cat.id);
        $("#nombre").val(cat.nombre);
        $("#descripcion").val(cat.descripcion);
        $("#estado").val(cat.estado);
        document.getElementById('categoriaModal').classList.remove('hidden');
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
            url: '../admin/classes/Categoria.php',
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
