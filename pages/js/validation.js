$(document).ready(function () {
    $('#form_categoria').validate({
        rules: {
            categoria: {
                required: true,
                minlength: 2
            },
        },
        messages: {
            categoria: {
                required: "Campo obligatorio",
                minlength: "Mínimo 2 letras"
            },
        }
    });
    $('#productoForm').validate({

        rules: {
            nombre: {
                required: true,
                minlength: 2
            },
            descripcion: {
                required: true,
                minlength: 2
            },
            idcategorias: {
                required: true,
            },
            precio: {
                required: true,
            },

        },
        messages: {
            nombre: {
                required: "Campo obligatorio",
                minlength: "Mínimo 2 letras"
            },
            descripcion: {
                required: "Campo obligatorio",
                minlength: "Mínimo 2 letras"
            },
            idcategorias: {
                required: "Campo obligatorio"
            },
            precio: {
                required: "Campo obligatorio"
            },

        }
    });
    $('#form_baner').validate({
        rules: {
            imagen: {
                required: true,

            },
        },
        messages: {
            imagen: {
                required: "Campo obligatorio",

            },
        }
    });
    $('#form_usuario').validate({
        rules: {
            nombres: {
                required: true,
            },
            apellidos: {
                required: true,
            },
            usuario: {
                required: true,
            },
            clave: {
                required: true,
            },
            idrol: {
                required: true,
            },
            lugar_entraga: {
                required: true,
            },
        },
        messages: {
            nombres: {
                required: "Nombres es obligatorio",
            },
            apellidos: {
                required: "Apellidos es obligatorio",
            },
            usuario: {
                required: "Email es obligatorio",
            },
            clave: {
                required: "Clave es obligatorio",
            },
            enum_rol: {
                required: "Rol es obligatorio",
            },
            
        }
    });

    $('#form_newclave').validate({
        rules: {
            newclave: {
                required: true,
            },
            confclave: {
                required: true,
            },
        },
        messages: {
            newclave: {
                required: "Campo obligatorio",
            },
            confclave: {
                required: "Campo obligatorio",
            },
        }
    });
    $('#form_producto').validate({
        rules: {
            descripcion: {
                required: true,
            },
            id_unidad: {
                required: true,
            },
            precio_unitario: {
                required: true,
            },
            id_impuesto: {
                required: true,
            },
        },
        messages: {
            descripcion: {
                required: "Descripción obligatorio",
            },
            id_unidad: {
                required: "Unidad Medida obligatorio",
            },
            precio_unitario: {
                required: "Precio Unitario obligatorio",
            },
            id_impuesto: {
                required: "Tipo impuesto obligatorio",
            },
        }
    });

});