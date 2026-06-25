<?php
session_start();

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio después de cerrar la sesión
header("Location: ../index.php");
exit();
?>