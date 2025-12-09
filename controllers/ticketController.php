<?php
//Zona horaria server
date_default_timezone_set('America/El_Salvador');
require_once '../config/database.php';
require_once '../models/Ticket.php';

// Asegúrate de que esta función exista en config/database.php
if (!function_exists('replyJson')) {
    function replyJson($success, $message, $data = []) {
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Asegurar que está logueado
if (!isset($_SESSION['user_id'])) {
    replyJson(false, "No autorizado");
}

$action = $_POST['action'] ?? '';

// Conexión a la DB
$conn = dbConnect();
$ticketModel = new Ticket($conn);

// --- ACCIONES PRINCIPALES ---
switch ($action) {

    case 'crear_ticket':
        crearTicket($ticketModel);
        break;

    case 'listar_activos':
        listarActivos($ticketModel);
        break;

    case 'obtener_estadisticas':
        obtenerEstadisticas($ticketModel);
        break;

    case 'listar_ultimos_tickets':
        listarUltimosTickets($ticketModel);
        break;

    // --- NUEVAS ACCIONES DE COBRO ---
    case 'obtener_info_cobro':
        obtenerInfoCobro($ticketModel);
        break;

    case 'procesar_cobro':
        procesarCobro($ticketModel);
        break;
    // --- FIN NUEVAS ACCIONES ---

    default:
        replyJson(false, "Acción no válida");
}

// ----------------------
// CREAR TICKET
// ----------------------
function crearTicket($model)
{
    $usuarioId = $_SESSION['user_id'];

    // obtener último ticket de hoy
    $ultimo = $model->ultimoTicketHoy();

    if ($ultimo) {
        $num = intval(substr($ultimo, -4)) + 1;
    } else {
        $num = 1;
    }

    // Formato: T-20250205-0001
    $numeroTicket = "T-" . date("Ymd") . "-" . str_pad($num, 4, "0", STR_PAD_LEFT);

    // insertar
    if ($model->crear($numeroTicket, $usuarioId)) {

        replyJson(true, "Ticket generado", [
            "numero" => $numeroTicket,
            "hora" => date("H:i:s")
        ]);
    } else {
        replyJson(false, "Error al crear el ticket");
    }
}

// ----------------------
// LISTAR TICKETS ACTIVOS (MODIFICADA para añadir botón de cobro)
// ----------------------
function listarActivos($model)
{
    // Asegúrate de que este método en Ticket.php también obtenga nombreCompleto del usuario (JOIN)
    $result = $model->listarActivos(); 

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<p class='text-muted'>No hay tickets generados hoy.</p>";
        exit;
    }

    // tabla HTML
    $html = "
    <table class='table table-striped table-hover'>
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Hora Entrada</th>
                <th>Usuario</th>
                <th>Acción</th> </tr>
        </thead>
        <tbody>
    ";

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "
            <tr>
                <td>{$row['numeroTicket']}</td>
                <td>" . date('H:i:s', strtotime($row['fechaHoraEntrada'])) . "</td>
                <td>{$row['nombreCompleto']}</td>
                <td>
                    <button type='button' class='btn btn-sm btn-success btn-cobrar' data-numero='{$row['numeroTicket']}'>Cobrar</button>
                </td>
            </tr>
        ";
    }

    $html .= "</tbody></table>";

    echo $html;
    exit;
}

// ----------------------
// OBTENER INFO Y CALCULAR COBRO (NUEVA FUNCIÓN)
// ----------------------
function obtenerInfoCobro($model)
{
    // Recupera la tarifa (idealmente de una tabla de configuración)
    $TARIFA_POR_HORA = 2.00; 
    
    $numeroTicket = $_POST['numeroTicket'] ?? '';
    
    // El modelo DEBE tener la función obtenerPorNumeroPendiente()
    $ticket = $model->obtenerPorNumeroPendiente($numeroTicket); 

    if (!$ticket) {
        replyJson(false, "Ticket no encontrado, no está activo o ya fue pagado.");
    }

    // 1. Calcular tiempo (Usando la zona horaria del servidor o configurada)
    // date_default_timezone_set('America/El_Salvador'); // Descomenta si es necesario
    $entrada = new DateTime($ticket['fechaHoraEntrada']);
    $ahora = new DateTime("now");
    $intervalo = $entrada->diff($ahora);

    // Cálculo de horas (redondeo hacia arriba: 1h 1min cuenta como 2h)
    $minutos_totales = $intervalo->days * 24 * 60 + $intervalo->h * 60 + $intervalo->i;
    $horas_a_cobrar = ceil($minutos_totales / 60);
    if ($horas_a_cobrar == 0) $horas_a_cobrar = 1; // Mínimo 1 hora

    // 2. Calcular costo
    $costoTotal = number_format($horas_a_cobrar * $TARIFA_POR_HORA, 2, '.', '');
    $tiempoTotal = $intervalo->format('%a días, %H horas, %i minutos');
    $fechaHoraSalida = $ahora->format('Y-m-d H:i:s');

    replyJson(true, "Datos listos", [
        'idTicket' => $ticket['idTicket'],
        'numeroTicket' => $ticket['numeroTicket'],
        'fechaHoraEntrada' => $ticket['fechaHoraEntrada'],
        'fechaHoraSalida' => $fechaHoraSalida,
        'tiempoTotal' => $tiempoTotal,
        'costoTotal' => $costoTotal
    ]);
}

// ----------------------
// PROCESAR PAGO Y ACTUALIZAR DB (NUEVA FUNCIÓN)
// ----------------------
function procesarCobro($model)
{
    $id = $_POST['idTicket'] ?? 0;
    $costoTotal = $_POST['costoTotal'] ?? 0.00;
    $montoRecibido = $_POST['montoRecibido'] ?? 0.00;
    $tiempoTotal = $_POST['tiempoTotal'] ?? '';
    $fechaHoraSalida = $_POST['fechaHoraSalida'] ?? date('Y-m-d H:i:s');
    
    // 1. Validación
    if ($montoRecibido < $costoTotal) {
        replyJson(false, "El monto recibido es insuficiente. Recibido: $$montoRecibido, Total: $$costoTotal");
    }

    $cambio = number_format($montoRecibido - $costoTotal, 2, '.', '');
    
    // 2. Ejecutar UPDATE (El modelo DEBE tener la función actualizarCobro())
    if ($model->actualizarCobro($id, $fechaHoraSalida, $tiempoTotal, $costoTotal, $montoRecibido, $cambio)) {
        
        // 3. Obtener info completa para el recibo final
        $ticketInfo = $model->obtenerPorId($id); // El modelo DEBE tener la función obtenerPorId()

        replyJson(true, "Pago procesado con éxito.", [
            'cambio' => $cambio,
            'fechaHoraEntrada' => $ticketInfo['fechaHoraEntrada'],
            'fechaHoraSalida' => $fechaHoraSalida,
            'tiempoTotal' => $tiempoTotal,
            'costoTotal' => $costoTotal,
            'montoRecibido' => $montoRecibido
        ]);
    } else {
        replyJson(false, "Error al procesar el pago o el ticket ya fue marcado como pagado.");
    }
}

// ----------------------
// FUNCIONES DE RESPUESTA (De tu compañero)
// ----------------------
function replyJson($success, $message, $data = [])
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data"    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener estadísticas para el dashboard
function obtenerEstadisticas($model)
{
    
    $conn = $GLOBALS['conn'] ?? null;
    if (!$conn) {
        $conn = dbConnect();
    }

    // Total de entradas hoy
    $queryEntradas = "SELECT COUNT(*) as total FROM tickets WHERE DATE(fechaHoraEntrada) = CURDATE()";
    $resultEntradas = mysqli_query($conn, $queryEntradas);
    $entradasHoy = mysqli_fetch_assoc($resultEntradas)['total'] ?? 0;

    // Tickets pendientes
    $queryPendientes = "SELECT COUNT(*) as total FROM tickets WHERE estado = 'pendiente' AND DATE(fechaHoraEntrada) = CURDATE()";
    $resultPendientes = mysqli_query($conn, $queryPendientes);
    $pendientes = mysqli_fetch_assoc($resultPendientes)['total'] ?? 0;

    // Total cobrado hoy
    $queryCobrado = "SELECT COALESCE(SUM(costoTotal), 0) as total FROM tickets WHERE estado = 'pagado' AND DATE(fechaHoraEntrada) = CURDATE()";
    $resultCobrado = mysqli_query($conn, $queryCobrado);
    $totalCobrado = mysqli_fetch_assoc($resultCobrado)['total'] ?? 0;

    replyJson(true, "Estadísticas obtenidas", [
        'entradasHoy' => (int)$entradasHoy,
        'pendientes' => (int)$pendientes,
        'totalCobrado' => (float)$totalCobrado
    ]);
}

// Listar los últimos 10 tickets generados hoy
function listarUltimosTickets($model)
{
    $conn = $GLOBALS['conn'] ?? null;
    if (!$conn) {
        $conn = dbConnect();
    }

    $query = "SELECT 
                t.numeroTicket, 
                t.fechaHoraEntrada, 
                t.fechaHoraSalida, 
                t.estado,
                u.nombreCompleto
              FROM tickets t
              LEFT JOIN usuarios u ON t.idUsuario = u.idUsuario
              WHERE DATE(t.fechaHoraEntrada) = CURDATE()
              ORDER BY t.idTicket DESC
              LIMIT 10";

    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<tr><td colspan="4" class="text-center py-4 text-muted">No hay tickets registrados hoy</td></tr>';
        return;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $estado = $row['estado'] === 'pagado' 
            ? '<span class="badge bg-success">Pagado</span>' 
            : '<span class="badge bg-warning">Pendiente</span>';

        $salida = $row['fechaHoraSalida'] ?? '—';

        echo "
        <tr>
            <td><strong>{$row['numeroTicket']}</strong></td>
            <td>" . date('H:i:s', strtotime($row['fechaHoraEntrada'])) . "</td>
            <td>" . ($salida !== '—' ? date('H:i:s', strtotime($salida)) : '—') . "</td>
            <td>{$estado}</td>
        </tr>
        ";
    }
}