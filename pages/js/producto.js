function load(page) {
    var query = $("#q").val();
    var per_page = 10;
    var parametros = { "action": "ajax", "page": page, 'query': query, 'per_page': per_page };
    $("#loader").fadeIn('slow');
    $.ajax({
        method: 'POST',
        url: '../pages/producto_paginar.php',
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

function abrirProducto() {
    $('#form_producto').trigger("reset");
    $("input[name='id']").val(0);
    $("#estado").val('ACTIVO');
    $("#imagen_preview").addClass('hidden');
    $("#imagen_actual").val('');
    loadCategorias();
    loadProveedores();
    document.getElementById('productoModal').classList.remove('hidden');
}

function cerrarProducto() {
    document.getElementById('productoModal').classList.add('hidden');
}

function cancelarEliminar() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function loadCategorias() {
    $.ajax({
        url: '../admin/classes/Categoria.php',
        method: 'POST',
        data: { getAllCategorias: 1 },
        dataType: 'json',
        success: function (resp) {
            var html = '<option value="">Seleccione</option>';
            $.each(resp, function (i, item) {
                html += '<option value="' + item.id + '">' + item.nombre + '</option>';
            });
            $(".categoria_list").html(html);
        }
    });
}

function loadProveedores() {
    $.ajax({
        url: '../admin/classes/Proveedor.php',
        method: 'POST',
        data: { getAllProveedores: 1 },
        dataType: 'json',
        success: function (resp) {
            var html = '<option value="">Seleccione</option>';
            $.each(resp, function (i, item) {
                html += '<option value="' + item.id + '">' + item.empresa + '</option>';
            });
            $(".proveedor_list").html(html);
        }
    });
}

$(document).ready(function () {
    load();

    $("#imagen").on("change", function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#imagen_preview_img").attr("src", e.target.result);
                $("#imagen_preview").removeClass('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    $(".add-insert-upadate").on("click", function () {
        if ($('#form_producto').valid() == false) {
            return;
        }
        var formData = new FormData($("#form_producto")[0]);
        $.ajax({
            url: '../admin/classes/Producto.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                var resp = $.parseJSON(response);
                if (resp.status == 202) {
                    load();
                    $('#form_producto').trigger("reset");
                    $("#imagen_preview").addClass('hidden');
                    toastr.success(resp.message);
                } else if (resp.status == 303) {
                    toastr.error(resp.message);
                }
                cerrarProducto();
            }
        })
    });

    $(document.body).on("click", ".edit-registro", function () {
        var prod = $.parseJSON($.trim($(this).children("span").html()));
        $("#id").val(prod.id);
        $("#codigo").val(prod.codigo);
        $("#nombre").val(prod.nombre);
        $("#descripcion").val(prod.descripcion);
        loadCategorias();
        loadProveedores();
        setTimeout(function () {
            $("#id_categoria").val(prod.id_categoria);
            $("#id_proveedor").val(prod.id_proveedor);
        }, 300);
        $("#precio_venta").val(prod.precio_venta);
        $("#costo_compra").val(prod.costo_compra);
        $("#stock_actual").val(prod.stock_actual);
        $("#stock_minimo").val(prod.stock_minimo);
        $("#codigo_barras").val(prod.codigo_barras);
        $("#estado").val(prod.estado);
        if (prod.imagen) {
            $("#imagen_preview_img").attr("src", "../uploads/productos/" + prod.imagen);
            $("#imagen_preview").removeClass('hidden');
            $("#imagen_actual").val(prod.imagen);
        } else {
            $("#imagen_preview").addClass('hidden');
            $("#imagen_actual").val('');
        }
        document.getElementById('productoModal').classList.remove('hidden');
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
            url: '../admin/classes/Producto.php',
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
