<?php
// views/salidas.php

require_once '../config/database.php';
// Asegura que el usuario esté logueado (asumiendo que esta función existe)
if (!function_exists('redirectIfNotLoggedIn') || !redirectIfNotLoggedIn(false)) {
    // Si la función no existe o devuelve falso, redirigimos manualmente
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Datos del usuario en sesión
$usuario = $_SESSION['nombre'] ?? 'Cajero';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salida de Vehículos y Cobro - Sistema Parqueo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

    <style>
        body { background-color: #f5f7fa; }
        .card { border-radius: 15px; margin-bottom: 20px; }
        .ticket-info p { margin: 5px 0; font-size: 1.1em; }
        @media print {
            body * { visibility: hidden; }
            #print-area, #print-area * { visibility: visible; }
            #print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            /* Ocultar botones de imprimir en la impresión */
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-sign-out-alt"></i> Salida de Vehículos y Cobro</h3>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo $usuario; ?></span>
                <a href="../controllers/authController.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-search"></i> Buscar Ticket</h5>
            <form id="formBuscar">
                <label for="numTicket" class="form-label">Número de Ticket:</label>
                <input type="text" id="numTicket" class="form-control mb-3" required placeholder="Ingrese el NÚMERO completo (Ej: T-20251127-0001)">
                <button type="submit" class="btn btn-primary">Buscar Ticket</button>
            </form>
        </div>

        <div id="cobro-area" class="card p-4 d-none">
            <h5 class="fw-bold mb-3"><i class="fas fa-file-invoice-dollar"></i> Detalle de Cobro</h5>
            </div>

        <div class="card p-4 mt-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-clock"></i> Tickets activos pendientes</h5>
            <div id="tablaActivos">Cargando...</div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<script>
    const TARIFA_POR_HORA = 2.00; // Tarifa base

    // Función para recargar la tabla de activos
    function cargarTicketsActivos() {
        $.ajax({
            url: "../controllers/ticketController.php",
            type: "POST",
            data: { action: "listar_activos" },
            success: function(html) {
                $("#tablaActivos").html(html);
                // Adjuntar listener de click a los nuevos botones 'Cobrar'
                $(".btn-cobrar").click(function() {
                    let numero = $(this).data('numero');
                    $("#numTicket").val(numero);
                    $("#formBuscar").submit();
                });
            }
        });
    }

    // 1. LÓGICA DE BÚSQUEDA Y CÁLCULO (Activada por el formulario principal o botón "Cobrar")
    $("#formBuscar").submit(function(e) {
        e.preventDefault();
        let numeroTicket = $("#numTicket").val();
        $("#cobro-area").html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Calculando...</p>').removeClass("d-none");

        // Llamar al controlador para obtener los datos calculados
        $.ajax({
            url: "../controllers/ticketController.php",
            type: "POST",
            data: { 
                action: "obtener_info_cobro", 
                numeroTicket: numeroTicket, 
                tarifa: TARIFA_POR_HORA 
            },
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    let data = res.data;
                    
                    // Cumplimiento: Muestra Hora entrada, Hora salida, Tiempo total, Monto a pagar
                    let html_cobro = `
                        <div class="ticket-info">
                            <p><strong>Número Ticket:</strong> ${data.numeroTicket}</p>
                            <p><strong>Hora Entrada:</strong> ${data.fechaHoraEntrada}</p>
                            <p><strong>Hora Salida:</strong> ${data.fechaHoraSalida}</p>
                            <p><strong>Tiempo Estancia:</strong> ${data.tiempoTotal}</p>
                        </div>
                        <h4 class="text-danger mt-3">Total a Pagar: $${data.costoTotal}</h4>
                        
                        <form id="formCobrar" class="mt-3">
                            <input type="hidden" name="action" value="procesar_cobro">
                            <input type="hidden" name="idTicket" value="${data.idTicket}">
                            <input type="hidden" name="costoTotal" value="${data.costoTotal}">
                            <input type="hidden" name="tiempoTotal" value="${data.tiempoTotal}">
                            <input type="hidden" name="fechaHoraSalida" value="${data.fechaHoraSalida}">
                            
                            <label for="montoRecibido" class="form-label">Monto Recibido ($):</label>
                            <input type="number" step="0.01" id="montoRecibido" name="montoRecibido" class="form-control mb-3" required min="${data.costoTotal}">
                            <button type="submit" class="btn btn-success"><i class="fas fa-money-check-alt"></i> Procesar Pago</button>
                        </form>
                    `;
                    $("#cobro-area").html(html_cobro);
                } else {
                    alertify.error(res.message);
                    $("#cobro-area").addClass("d-none");
                }
            },
            error: function(xhr, status, error) {
                alertify.error("Error en la comunicación con el servidor: " + error);
                $("#cobro-area").addClass("d-none");
            }
        });
    });

    // 2. LÓGICA DE PROCESAMIENTO DE PAGO (Activada al enviar formCobrar)
    $(document).on('submit', '#formCobrar', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        // Llamar al controlador para procesar el pago y el UPDATE
        $.ajax({
            url: "../controllers/ticketController.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    // Cumplimiento: Muestra y permite generar/imprimir ticket final
                    alertify.success(`Pago procesado! Cambio: $${res.data.cambio}`);
                    
                    let ticket_final = `
                        <div id="print-area" class="alert alert-success mt-3" role="alert">
                            <h5 class="alert-heading fw-bold">✅ TICKET PAGADO</h5>
                            <p><strong>Número:</strong> ${res.data.numeroTicket}</p>
                            <hr>
                            <p><strong>Entrada:</strong> ${res.data.fechaHoraEntrada}</p>
                            <p><strong>Salida:</strong> ${res.data.fechaHoraSalida}</p>
                            <p><strong>Tiempo:</strong> ${res.data.tiempoTotal}</p>
                            <hr>
                            <p class="mb-0"><strong>Total:</strong> $${res.data.costoTotal}</p>
                            <p class="mb-0"><strong>Recibido:</strong> $${res.data.montoRecibido}</p>
                            <p class="mb-0 text-danger"><strong>Cambio:</strong> $${res.data.cambio}</p>
                        </div>
                        <button class="btn btn-sm btn-info mt-2 no-print" onclick="window.print()"><i class="fas fa-print"></i> Imprimir Recibo</button>
                    `;
                    $("#cobro-area").html(ticket_final);
                    cargarTicketsActivos(); // Refrescar la tabla
                } else {
                    alertify.error(res.message);
                }
            }
        });
    });

    // Cargar la tabla al iniciar
    cargarTicketsActivos();
</script>
</body>
</html>

   // prueba 