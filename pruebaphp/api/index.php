<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/database.php';

$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
        exit;
    }

    switch ($action) {
        case 'getBodegas':
            getBodegas($db);
            break;

        case 'getSucursales':
            if (isset($_GET['bodegaId'])) {
                getSucursales($db, $_GET['bodegaId']);
            } else {
                echo json_encode([]);
            }
            break;

        case 'getMonedas':
            getMonedas($db);
            break;

        case 'guardarProducto':
            guardarProducto($db);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Funciones
function getBodegas($db) {
    $stmt = $db->query("SELECT id, nombre FROM bodegas ORDER BY nombre");
    $bodegas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($bodegas);
}

function getSucursales($db, $bodegaId) {
    $stmt = $db->prepare("SELECT id, nombre FROM sucursales WHERE bodega_id = :bodega_id ORDER BY nombre");
    $stmt->bindParam(':bodega_id', $bodegaId, PDO::PARAM_INT);
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($sucursales);
}

function getMonedas($db) {
    $stmt = $db->query("SELECT id, nombre FROM monedas ORDER BY nombre");
    $monedas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($monedas);
}

function guardarProducto($db) {
    // Recibir datos del formulario
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $bodega = $_POST['bodega_id'] ?? '';
    $sucursal = $_POST['sucursal_id'] ?? '';
    $moneda = $_POST['moneda_id'] ?? '';
    $precio = trim($_POST['precio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $materiales = isset($_POST['materiales']) ? (array)$_POST['materiales'] : [];

    // Validaciones básicas en backend (por seguridad)
    if (!$codigo) { echo json_encode(['success' => false, 'message' => 'El código del producto no puede estar en blanco']); return; }
    if (!$nombre) { echo json_encode(['success' => false, 'message' => 'El nombre del producto no puede estar en blanco']); return; }
    if (!$bodega) { echo json_encode(['success' => false, 'message' => 'Debe seleccionar una bodega']); return; }
    if (!$sucursal) { echo json_encode(['success' => false, 'message' => 'Debe seleccionar una sucursal']); return; }
    if (!$moneda) { echo json_encode(['success' => false, 'message' => 'Debe seleccionar una moneda']); return; }
    if (!$precio || !preg_match('/^\d+(\.\d{1,2})?$/', $precio)) { echo json_encode(['success' => false, 'message' => 'Precio inválido']); return; }
    if (!$descripcion || strlen($descripcion) < 10) { echo json_encode(['success' => false, 'message' => 'La descripción es demasiado corta']); return; }
    if (count($materiales) < 2) { echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos dos materiales']); return; }

    // Verificar unicidad del código
    $stmt = $db->prepare("SELECT id FROM productos WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();
    if ($stmt->rowCount() > 0) { echo json_encode(['success' => false, 'message' => 'El código del producto ya está registrado']); return; }

    // Insertar producto
    $stmt = $db->prepare("INSERT INTO productos (codigo, nombre, bodega_id, sucursal_id, moneda_id, precio, descripcion) 
                          VALUES (:codigo, :nombre, :bodega_id, :sucursal_id, :moneda_id, :precio, :descripcion)");
    $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':bodega_id' => $bodega,
        ':sucursal_id' => $sucursal,
        ':moneda_id' => $moneda,
        ':precio' => $precio,
        ':descripcion' => $descripcion
    ]);

    $producto_id = $db->lastInsertId();

    // Insertar materiales del producto
    $stmtMat = $db->prepare("INSERT INTO productos_materiales (producto_id, material_id) VALUES (:producto_id, :material_id)");
    foreach ($materiales as $material_id) {
        $stmtMat->execute([
            ':producto_id' => $producto_id,
            ':material_id' => $material_id
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Producto guardado correctamente']);
};


