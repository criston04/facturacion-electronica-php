<?php
session_start();
include "../admin/classes/Database.php";
$db = new Database();
$con = $db->connect();
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
    $query = mysqli_real_escape_string($con, trim((strip_tags($_REQUEST['query'], ENT_QUOTES))));
    $tables = "clientes c";
    $campos = " c.* ";
    $sWhere = " (c.nombres LIKE '%" . $query . "%' OR c.apellido_paterno LIKE '%" . $query . "%' OR c.apellido_materno LIKE '%" . $query . "%' OR c.razon_social LIKE '%" . $query . "%' OR c.numero_documento LIKE '%" . $query . "%')";
    $sWhere .= " ORDER BY c.id desc";
    
    include 'pagination.php';
    $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
    $per_page = intval($_REQUEST['per_page']);
    $adjacents = 4;
    $offset = ($page - 1) * $per_page;
    
    $count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $tables where $sWhere");
    $row = mysqli_fetch_array($count_query);
    $numrows = $row['numrows'];
    $total_pages = ceil($numrows / $per_page);
    $sql = "SELECT $campos FROM $tables where $sWhere LIMIT $offset,$per_page";
    $query = mysqli_query($con, $sql);

    if ($numrows > 0) {
?>
    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="app-table min-w-full divide-y divide-gray-200">
        <thead class="text-sm">
          <tr>
            <th class="px-4 py-2 text-left font-semibold uppercase">N°</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Tipo Doc</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Nro Doc</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Razón Social</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Correo</th>
            <th class="px-4 py-2 text-left font-semibold uppercase">Teléfono</th>
            <th class="px-4 py-2 text-center font-semibold uppercase">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
          <?php
          $i = $offset + 1;
          while ($row = mysqli_fetch_array($query)) {
          ?>
            <tr>
              <td class="px-4 py-2"><?php echo $i++; ?></td>
              <td class="px-4 py-2"><?php echo $row['tipo_documento']; ?></td>
              <td class="px-4 py-2"><?php echo $row['numero_documento']; ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($row['razon_social'] ?: trim(implode(' ', array_filter([$row['nombres'], $row['apellido_paterno'], $row['apellido_materno']])))); ?></td>
              <td class="px-4 py-2"><?php echo $row['email']; ?></td>
              <td class="px-4 py-2"><?php echo $row['telefono']; ?></td>
              <td class="px-4 py-2 text-center flex justify-center gap-2">
                  <a href="#" class="edit-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="Editar">
                    <span class="hidden"><?php echo htmlspecialchars(json_encode($row)); ?></span>
                    ✏️
                  </a>
                  <a href="#" class="delete-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-red-50 text-red-600 hover:bg-red-100" title="Eliminar" data-cid="<?php echo $row['id']; ?>">
                    🗑
                  </a>
              </td>
            </tr>
          <?php } ?>
          <tr class="bg-gray-50 text-sm text-gray-600">
            <td colspan="7" class="px-4 py-3">
              <?php
              $inicios = $offset + 1;
              $finales = $i - 1;
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