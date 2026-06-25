<?php
session_start();
include "../admin/classes/Database.php";
$db = new Database();
$con = $db->connect();
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
    $query = mysqli_real_escape_string($con, trim(strip_tags($_REQUEST['query'])));

    $tables = "productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id";
    $campos = " p.*, c.nombre as categoria_nombre, pr.empresa as proveedor_empresa ";
    $sWhere = " (p.nombre LIKE '%" . $query . "%' OR p.codigo LIKE '%" . $query . "%' OR c.nombre LIKE '%" . $query . "%')";
    $sWhere .= " ORDER BY p.id DESC";
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
                        <th></th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">N°</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Código</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Nombre</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Categoría</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Proveedor</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Precio Venta</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Stock</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase">Estado</th>
                        <th class="px-4 py-2 text-center font-semibold uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    <?php
                    $finales = 0;
                    while ($row = mysqli_fetch_array($query)) {
                        $id = $row['id'];
                        $codigo = $row['codigo'];
                        $nombre = $row['nombre'];
                        $categoria_nombre = $row['categoria_nombre'];
                        $proveedor_empresa = $row['proveedor_empresa'];
                        $precio_venta = $row['precio_venta'];
                        $stock_actual = $row['stock_actual'];
                        $estado = $row['estado'];
                        $imagen = $row['imagen'];

                        $finales++;
                    ?>
                        <tr>
                            <td class="px-4 py-2">
                                <?php if (!empty($row['imagen'])): ?>
                                    <img src="../uploads/productos/<?php echo $row['imagen']; ?>" class="w-10 h-10 rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-300 flex items-center justify-center"><i class="fas fa-box"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2"><?php echo $finales; ?></td>
                            <td class="px-4 py-2"><?php echo $codigo; ?></td>
                            <td class="px-4 py-2"><?php echo $nombre; ?></td>
                            <td class="px-4 py-2"><?php echo $categoria_nombre; ?></td>
                            <td class="px-4 py-2"><?php echo $proveedor_empresa; ?></td>
                            <td class="px-4 py-2">S/ <?php echo number_format($precio_venta, 2); ?></td>
                            <td class="px-4 py-2"><?php echo $stock_actual; ?></td>
                            <td class="px-4 py-2">
                                <span class="badge <?php echo $estado == 'ACTIVO' ? 'badge-green' : 'badge-red'; ?>">
                                    <?php echo $estado; ?>
                                </span>
                            </td>
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
                        <td colspan="10" class="px-4 py-3">
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
