<?php
session_start();
include "../admin/classes/Database.php";
$db = new Database();
$con = $db->connect();
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
    $query = mysqli_real_escape_string($con, trim(strip_tags($_REQUEST['query'])));

    $tables = "emisor e";
    $campos = " e.* ";
    $sWhere = " (e.razon_social LIKE '%" . $query . "%' OR e.ruc LIKE '%" . $query . "%')";
    $sWhere .= " ORDER BY e.id DESC";
    include 'pagination.php';
    $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
    $per_page = intval($_REQUEST['per_page']);
    $adjacents  = 4;
    $offset = ($page - 1) * $per_page;
    $count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $tables WHERE $sWhere");

    if ($row = mysqli_fetch_array($count_query)) {
        $numrows = $row['numrows'];
    } else {
        echo mysqli_error($con);
    }
    $total_pages = ceil($numrows / $per_page);
    $query = mysqli_query($con, "SELECT $campos FROM $tables WHERE $sWhere LIMIT $offset,$per_page");

    if ($numrows > 0) {
?>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="app-table min-w-full divide-y divide-gray-200">
                <thead class="text-sm">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold uppercase">N°</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">RUC</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Razón Social</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Nombre Comercial</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Email</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Dirección</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Tipo Proceso</th>
                        <th class="px-4 py-2 text-center font-semibold uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    <?php
                    $finales = 0;
                    while ($row = mysqli_fetch_array($query)) {
                        $id = $row['id'];
                        $ruc = $row['ruc'];
                        $razon_social = $row['razon_social'];
                        $nom_comercial = $row['nom_comercial'];
                        $email = $row['email'];
                        $direccion = $row['direccion'];
                        $tipo_proceso = $row['tipo_proceso'];

                        $finales++;
                    ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo $finales; ?></td>
                            <td class="px-4 py-2"><?php echo $ruc; ?></td>
                            <td class="px-4 py-2"><?php echo $razon_social; ?></td>
                            <td class="px-4 py-2"><?php echo $nom_comercial; ?></td>
                            <td class="px-4 py-2"><?php echo $email; ?></td>
                            <td class="px-4 py-2"><?php echo $direccion; ?></td>
                            <td class="px-4 py-2"><?php echo ucfirst($tipo_proceso); ?></td>
                            <td class="px-4 py-2 text-center flex justify-center gap-2">
                                <a href="#" class="edit-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-indigo-50 text-indigo-600 hover:bg-indigo-100" data-id="<?php echo $id; ?>" title="Modificar">
                                    <span class="hidden"><?php echo htmlspecialchars(json_encode($row)); ?></span>
                                    ✏️
                                </a>
                                <a href="#" class="delete-registro inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm transition bg-red-50 text-red-600 hover:bg-red-100" data-id="<?php echo $id; ?>" title="Eliminar">
                                    🗑️
                                </a>
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
