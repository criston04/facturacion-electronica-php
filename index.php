<?php
include "admin/classes/Database.php";
$db = new Database();
$con = $db->connect();
session_start();
if ($_POST) {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $result = mysqli_query($con, "SELECT u.idusuarios,
        u.nombres,
        u.apellidos,
        u.usuario,
        u.clave,
        u.enum_rol,
        e.nombre as desRol,
        u.estado
    FROM usuarios u
    left join enumerados e on e.valor= u.enum_rol and e.tipo=1
    WHERE u.estado=1 and  u.usuario = '" . $usuario . "' ");
    if ($row = mysqli_fetch_array($result)) {

        if (password_verify($contrasena, $row['clave'])) {
            $_SESSION['idusuarios'] = $row['idusuarios'];
            $_SESSION['nombres'] = $row['nombres'];
            $_SESSION['apellidos'] = $row['apellidos'];
            $_SESSION['enum_rol'] = $row['enum_rol'];
            $_SESSION['rol'] = $row['desRol'];
            echo "<script language='javascript'>window.location='pages/dashboard.php'</script>;";
        } else {
            $errormsg = "Contraseña incorrecta";
        }
    } else {
        $errormsg = "Errror: Usuario y o contraseña incorrecta ";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login | Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind -->
    <script src="pages/css/tailwind.min.css"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(1000px 520px at 0% 0%, rgba(20, 184, 166, .22), transparent 60%),
                radial-gradient(900px 520px at 100% 100%, rgba(16, 185, 129, .18), transparent 55%),
                #0b3b3a;
        }
        h1, h2 { font-family: 'Outfit', sans-serif; }
        .grid-pat { background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, .14) 1px, transparent 0); background-size: 18px 18px; }
        .brand-mark { background: linear-gradient(135deg, #0f766e, #14b8a6 55%, #10b981); }
        @keyframes inUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        .in-up { animation: inUp .5s ease both; }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md in-up">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <!-- Brand band -->
            <div class="relative bg-gradient-to-br from-teal-700 via-teal-500 to-emerald-500 px-8 pt-9 pb-14 text-center">
                <div class="absolute inset-0 grid-pat opacity-50"></div>
                <div class="absolute -right-10 -top-12 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="mx-auto w-16 h-16 rounded-2xl brand-mark text-white flex items-center justify-center text-2xl font-bold shadow-lg" style="font-family:'Outfit',sans-serif">FE</div>
                    <h1 class="text-2xl font-bold text-white mt-4">Facturación Electrónica</h1>
                    <p class="text-teal-50/90 text-sm mt-1">Ingresa tus credenciales para continuar</p>
                </div>
            </div>

            <div class="px-8 py-7 -mt-6 bg-white rounded-t-3xl relative">

                <?php if (!empty($errormsg)): ?>
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 text-center">
                        <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($errormsg) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" novalidate class="space-y-5">
                    <div>
                        <label class="text-slate-600 text-sm mb-1 block font-medium">Email / Usuario</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="usuario" required
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                                placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <div>
                        <label class="text-slate-600 text-sm mb-1 block font-medium">Contraseña</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="password" type="password" name="contrasena" required
                                class="w-full pl-10 pr-12 py-3 rounded-xl bg-slate-50 text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl active:scale-[.98] flex items-center justify-center gap-2">
                        <i class="fas fa-right-to-bracket"></i> Ingresar
                    </button>
                </form>

                <p class="text-center text-slate-400 text-xs mt-7">© 2026 · CETI — Facturación Electrónica con PHP</p>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>

</html>