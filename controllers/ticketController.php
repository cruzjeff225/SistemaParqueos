<?php
require_once '../config/database.php';
require_once '../models/Ticket.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Asegurar que está logueado
if (!isset($_SESSION['user_id'])) {
    replyJson(false, "No autorizado");
}

$action = $_POST['action'] ?? '';

$conn = dbConnect();
$ticketModel = new Ticket($conn);

// ACCIONES
switch ($action) {

    case 'crear_ticket':
        crearTicket($ticketModel);
        break;

    case 'listar_activos':
        listarActivos($ticketModel);
        break;

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
// LISTAR TICKETS ACTIVOS
// ----------------------
function listarActivos($model)
{
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
            </tr>
        </thead>
        <tbody>
    ";

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "
            <tr>
                <td>{$row['numeroTicket']}</td>
                <td>" . date('H:i:s', strtotime($row['fechaHoraEntrada'])) . "</td>
                <td>{$row['nombreCompleto']}</td>
            </tr>
        ";
    }

    $html .= "</tbody></table>";

    echo $html;
    exit;
}

// ----------------------
// FUNCIONES DE RESPUESTA
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
