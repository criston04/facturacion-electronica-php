<?php
session_start();
include "../admin/classes/Database.php";
$db = new Database();
$con = $db->connect();
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
  $idrol    = $_SESSION['idrol'] ?? null;

  $query = mysqli_real_escape_string($con, trim((strip_tags($_REQUEST['query'], ENT_QUOTES))));

  $tables = "usuarios u left join enumerados e on e.valor=u.enum_rol and e.tipo=1";
  $campos = " u.idusuarios, u.nombres, u.apellidos, u.usuario, u.estado, u.enum_rol, e.nombre as DescRol";
  $sWhere = "  (u.nombres LIKE '%" . $query . "%')";
  $sWhere .= " ORDER BY u.idusuarios desc";
  include 'pagination.php';
  $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
  $per_page = intval($_REQUEST['per_page']);
  $adjacents  = 4;
  $offset = ($page - 1) * $per_page;
  $count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $tables where $sWhere");

  if ($row = mysqli_fetch_array($count_query)) {
    $numrows = $row['numrows'];
  } else {
    echo mysqli_error($con);
  }
  $total_pages = ceil($numrows / $per_page);
  $query = mysqli_query($con, "SELECT $campos FROM  $tables where $sWhere LIMIT $offset,$per_page");

  if ($numrows > 0) {
?>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="app-table min-w-full divide-y divide-gray-200">
        <thead class="text-sm">
          <tr>
            <th class="px-4 py-2 text-left font-semibold uppercase">N°</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Nombres</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Apellidos</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Usuario</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Rol</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Estado</th>
            <th class="px-4 py-2 text-center font-semibold uppercase">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
          <?php
          $finales = 0;
          while ($row = mysqli_fetch_array($query)) {
            $id = $row['idusuarios'];
            $nombres = $row['nombres'];
            $apellidos = $row['apellidos'];
            $usuario = $row['usuario'];
            $DescRol = $row['DescRol'];
            $enum_rol = $row['enum_rol'];
            $estado = $row['estado'];
            $finales++;
          ?>
            <tr>
              <td class="px-4 py-2"><?php echo $finales; ?></td>
              <td class="px-4 py-2"><?php echo $nombres; ?></td>
              <td class="px-4 py-2"><?php echo $apellidos; ?></td>
              <td class="px-4 py-2"><?php echo $usuario; ?></td>
              <td class="px-4 py-2"><?php echo $DescRol; ?></td>
              <td class="px-4 py-2">
                <?php if ($estado == 1) { ?>
                  <span class="badge badge-green">Activo</span>
                <?php } else { ?>
                  <span class="badge badge-red">Inactivo</span>
                <?php } ?>
              </td>
              <td class="px-4 py-2 text-center flex justify-center gap-2">
                <?php if ($estado == 1) { ?>
                  <a href="#"
                    class="change-pass inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-indigo-50 text-indigo-600 hover:bg-indigo-100"
                    title="Cambiar Clave"
                    data-cid="<?php echo $id; ?>">
                    🔒
                  </a>
                  <a href="#" class="edit-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-indigo-50 text-indigo-600 hover:bg-indigo-100" data-id="<?php echo $id; ?>" title="Modificar Usuario">
                    <span class="hidden"><?php echo htmlspecialchars(json_encode($row)); ?></span>
                    ✏️
                  </a>
                  <a href="#"
                    class="delete-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-red-50 text-red-600 hover:bg-red-100"
                    title="Eliminar"
                    data-cid="<?php echo $id; ?>">
                    🗑
                  </a>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          <tr class="bg-gray-50 text-sm text-gray-600">
            <td colspan="8" class="px-4 py-3">
              <?php
              $inicios = $offset + 1;
              $finales += $inicios - 1;
              echo "Mostrando $inicios al $finales de $numrows registros";
              echo paginate($page, $total_pages, $adjacents);
              ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

<?php
  }
}
?>