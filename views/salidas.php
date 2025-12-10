<?php
require_once '../config/database.php';
//Zona horaria server
date_default_timezone_set('America/El_Salvador');

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['nombre'] ?? 'Cajero';

// Variables para el header
$pageTitle = 'Salida de Vehículos - Sistema de Parqueo';
$bodyClass = 'bg-light';

// Incluir header
include 'includes/header.php';
?>

<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-1 fw-bold text-dark">
                <i class="fas fa-sign-out-alt text-success me-2"></i>
                Cobro / Salida
            </h1>
            <p class="text-muted mb-0">Cobra y registra la salida de un vehículo</p>
        </div>
    </div>

    <!-- Search Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-search text-primary me-2"></i>
                            Buscar Ticket
                        </h5>
                        <span class="badge bg-primary" id="methodIndicator">Búsqueda Rápida</span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Method Toggle -->
                    <div class="btn-group w-100 mb-4" role="group">
                        <input type="radio" class="btn-check" name="searchMethod" id="btnRapida" checked>
                        <label class="btn btn-outline-primary" for="btnRapida" onclick="cambiarMetodo('rapida')">
                            <i class="fas fa-bolt me-2"></i>
                            Búsqueda Rápida
                        </label>
                        
                        <input type="radio" class="btn-check" name="searchMethod" id="btnCompleta">
                        <label class="btn btn-outline-primary" for="btnCompleta" onclick="cambiarMetodo('completa')">
                            <i class="fas fa-keyboard me-2"></i>
                            Búsqueda Completa
                        </label>
                    </div>

                    <!-- Fast Search Form -->
                    <form id="formBuscarRapida" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fas fa-ticket-alt me-1"></i>
                                Número de Ticket
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white fw-bold border-0" id="ticketPrefix">
                                    T-<?php echo date('Ymd'); ?>-
                                </span>
                                <input 
                                    type="text" 
                                    id="digitosInput" 
                                    class="form-control border-0 shadow-sm text-center fw-bold fs-5" 
                                    placeholder="0001"
                                    maxlength="4"
                                    required
                                    autofocus
                                    style="letter-spacing: 3px;"
                                >
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Solo ingresa los últimos 4 dígitos del ticket
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                                <i class="fas fa-search me-2"></i>
                                BUSCAR
                            </button>
                        </div>
                    </form>

                    <!-- Complete Search Form -->
                    <form id="formBuscarCompleta" class="row g-3 align-items-end" style="display: none;">
                        <div class="col-md-8">
                            <label for="numTicketCompleto" class="form-label fw-semibold text-dark">
                                <i class="fas fa-ticket-alt me-1"></i>
                                Número de Ticket Completo
                            </label>
                            <input 
                                type="text" 
                                id="numTicketCompleto" 
                                class="form-control form-control-lg border-0 shadow-sm" 
                                placeholder="Ej: T-20251127-0001"
                            >
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ingresa el número completo del ticket
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                                <i class="fas fa-search me-2"></i>
                                BUSCAR
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Charge Area (Hidden) -->
    <div id="cobro-area" class="d-none"></div>

    <!-- Active Tickets -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Tickets Activos Pendientes
                        </h5>
                        <span class="badge bg-warning text-dark" id="badge-count">0</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="tablaActivos">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="text-muted mb-0">Cargando tickets...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$pageScripts = "
<script>
    const TARIFA_POR_HORA = 2.00;
    let metodoActual = 'rapida';

    // Cambiar método
    function cambiarMetodo(metodo) {
        metodoActual = metodo;
        
        if (metodo === 'rapida') {
            $('#formBuscarRapida').show();
            $('#formBuscarCompleta').hide();
            $('#methodIndicator').text('Búsqueda Rápida');
            $('#digitosInput').focus();
        } else {
            $('#formBuscarRapida').hide();
            $('#formBuscarCompleta').show();
            $('#methodIndicator').text('Búsqueda Completa');
            $('#numTicketCompleto').focus();
        }
    }

    // Auto-format
    $('#digitosInput').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    $('#numTicketCompleto').on('input', function() {
        let value = this.value.toUpperCase();
        if (value && !value.startsWith('T-')) {
            this.value = 'T-' + value.replace(/[^0-9-]/g, '');
        } else {
            this.value = value.replace(/[^T0-9-]/g, '');
        }
    });

    // Load active tickets
    function cargarTicketsActivos() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'listar_activos' },
            success: function(html) {
                $('#tablaActivos').html(html);
                
                // Contar tickets
                const count = $('#tablaActivos table tbody tr').length;
                $('#badge-count').text(count > 0 ? count : '0');
                
                $('.btn-cobrar').click(function() {
                    let numero = $(this).data('numero');
                    let digitos = numero.split('-')[2];
                    cambiarMetodo('rapida');
                    $('#digitosInput').val(digitos);
                    $('#formBuscarRapida').submit();
                    $('html, body').animate({scrollTop: 0}, 500);
                });
            },
            error: function() {
                $('#tablaActivos').html(
                    '<div class=\"text-center py-5\">' +
                    '<i class=\"fas fa-exclamation-circle text-danger\" style=\"font-size: 3rem;\"></i>' +
                    '<p class=\"text-muted mt-3 mb-0\">Error al cargar tickets</p></div>'
                );
            }
        });
    }

    // Search forms
    $('#formBuscarRapida').submit(function(e) {
        e.preventDefault();
        let digitos = $('#digitosInput').val().padStart(4, '0');
        let fecha = '" . date('Ymd') . "';
        let numeroTicket = 'T-' + fecha + '-' + digitos;

        buscarTicket(numeroTicket);
    });

    $('#formBuscarCompleta').submit(function(e) {
        e.preventDefault();
        let numeroTicket = $('#numTicketCompleto').val().trim();
        if (!numeroTicket) {
            alertify.error('Ingresa un número de ticket');
            return;
        }
        buscarTicket(numeroTicket);
    });

    function buscarTicket(numeroTicket) {
        $('#cobro-area').html(
            '<div class=\"row mb-4\"><div class=\"col-12\"><div class=\"card border-0 shadow-sm\">' +
            '<div class=\"card-body text-center py-5\">' +
            '<div class=\"spinner-border text-primary mb-3\"></div>' +
            '<p class=\"text-muted mb-0\">Calculando cobro...</p></div></div></div></div>'
        ).removeClass('d-none');

        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'obtener_info_cobro', numeroTicket: numeroTicket },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    mostrarFormularioCobro(res.data);
                    $('html, body').animate({scrollTop: $('#cobro-area').offset().top - 100}, 500);
                } else {
                    alertify.error(res.message);
                    $('#cobro-area').addClass('d-none');
                }
            },
            error: function() {
                alertify.error('Error al buscar el ticket');
                $('#cobro-area').addClass('d-none');
            }
        });
    }

    function mostrarFormularioCobro(data) {
        let html = '<div class=\"row mb-4\"><div class=\"col-12\"><div class=\"card border-0 border-start border-primary border-4 shadow-sm\">' +
            '<div class=\"card-header bg-white border-0 py-3\">' +
            '<h5 class=\"mb-0 fw-bold\"><i class=\"fas fa-file-invoice-dollar text-primary me-2\"></i>Detalle del Ticket</h5></div>' +
            '<div class=\"card-body\">' +
            
            '<div class=\"row g-3 mb-4\">' +
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem; letter-spacing: 0.5px;\">Número</small>' +
            '<strong class=\"d-block fs-6\">' + data.numeroTicket + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem; letter-spacing: 0.5px;\">Tiempo</small>' +
            '<strong class=\"d-block fs-6\">' + data.tiempoTotal + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem; letter-spacing: 0.5px;\">Entrada</small>' +
            '<strong class=\"d-block fs-6\">' + new Date(data.fechaHoraEntrada).toLocaleTimeString('es-SV') + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem; letter-spacing: 0.5px;\">Salida</small>' +
            '<strong class=\"d-block fs-6\">' + new Date(data.fechaHoraSalida).toLocaleTimeString('es-SV') + '</strong></div></div>' +
            '</div>' +
            
            '<div class=\"alert alert-primary border-0 shadow-sm text-center py-4 mb-4\">' +
            '<p class=\"mb-2 text-uppercase small fw-bold\" style=\"letter-spacing: 1px;\">MONTO A PAGAR</p>' +
            '<h2 class=\"mb-0 fw-bold\" style=\"font-size: 3rem;\">$' + parseFloat(data.costoTotal).toFixed(2) + '</h2></div>' +
            
            '<form id=\"formCobrar\">' +
            '<input type=\"hidden\" name=\"action\" value=\"procesar_cobro\">' +
            '<input type=\"hidden\" name=\"idTicket\" value=\"' + data.idTicket + '\">' +
            '<input type=\"hidden\" name=\"costoTotal\" value=\"' + data.costoTotal + '\">' +
            '<input type=\"hidden\" name=\"tiempoTotal\" value=\"' + data.tiempoTotal + '\">' +
            '<input type=\"hidden\" name=\"fechaHoraSalida\" value=\"' + data.fechaHoraSalida + '\">' +
            
            '<div class=\"mb-4\"><label class=\"form-label fw-semibold text-dark\">' +
            '<i class=\"fas fa-money-bill-wave text-success me-2\"></i>Monto Recibido ($)</label>' +
            '<input type=\"number\" step=\"0.01\" id=\"montoRecibido\" name=\"montoRecibido\" ' +
            'class=\"form-control form-control-lg border-0 shadow-sm text-center fw-bold fs-4\" ' +
            'required min=\"' + data.costoTotal + '\" placeholder=\"0.00\"></div>' +
            
            '<button type=\"submit\" class=\"btn btn-success btn-lg w-100 shadow\">' +
            '<i class=\"fas fa-check-circle me-2\"></i>PROCESAR PAGO</button>' +
            '</form></div></div></div></div>';
        
        $('#cobro-area').html(html);
        setTimeout(() => $('#montoRecibido').focus(), 100);
    }

    $(document).on('submit', '#formCobrar', function(e) {
        e.preventDefault();
        $(this).find('button[type=\"submit\"]').prop('disabled', true)
            .html('<span class=\"spinner-border spinner-border-sm me-2\"></span>Procesando...');

        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alertify.success('¡Pago procesado exitosamente!');
                    mostrarRecibo(res.data);
                    cargarTicketsActivos();
                } else {
                    alertify.error(res.message);
                    $('#formCobrar button[type=\"submit\"]').prop('disabled', false)
                        .html('<i class=\"fas fa-check-circle me-2\"></i>PROCESAR PAGO');
                }
            }
        });
    });

    function mostrarRecibo(data) {
        let html = '<div class=\"row mb-4\"><div class=\"col-12\"><div class=\"card border-0 border-start border-success border-4 shadow-sm\">' +
            '<div id=\"print-area\" class=\"card-body\">' +
            
            '<div class=\"text-center mb-4\">' +
            '<div class=\"d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3\" style=\"width: 80px; height: 80px;\">' +
            '<i class=\"fas fa-check-circle text-success\" style=\"font-size: 3rem;\"></i></div>' +
            '<h4 class=\"fw-bold text-success mb-2\">¡Pago Procesado Exitosamente!</h4>' +
            '<p class=\"text-muted mb-0\">Sistema de Parqueo</p>' +
            '<small class=\"text-muted\">' + new Date().toLocaleString('es-SV') + '</small></div>' +
            
            '<div class=\"table-responsive\">' +
            '<table class=\"table table-borderless\">' +
            '<tr class=\"border-bottom\"><th class=\"text-muted py-3\">Ticket:</th><td class=\"text-end py-3 fw-semibold\">' + data.numeroTicket + '</td></tr>' +
            '<tr class=\"border-bottom\"><th class=\"text-muted py-3\">Entrada:</th><td class=\"text-end py-3\">' + new Date(data.fechaHoraEntrada).toLocaleString('es-SV') + '</td></tr>' +
            '<tr class=\"border-bottom\"><th class=\"text-muted py-3\">Salida:</th><td class=\"text-end py-3\">' + new Date(data.fechaHoraSalida).toLocaleString('es-SV') + '</td></tr>' +
            '<tr class=\"border-bottom\"><th class=\"text-muted py-3\">Tiempo:</th><td class=\"text-end py-3\">' + data.tiempoTotal + '</td></tr>' +
            '<tr class=\"border-bottom bg-light\"><th class=\"py-3 fw-bold\">Total:</th><td class=\"text-end py-3 fw-bold fs-5\">$' + parseFloat(data.costoTotal).toFixed(2) + '</td></tr>' +
            '<tr class=\"border-bottom\"><th class=\"text-muted py-3\">Recibido:</th><td class=\"text-end py-3\">$' + parseFloat(data.montoRecibido).toFixed(2) + '</td></tr>' +
            '<tr class=\"bg-success bg-opacity-10\"><th class=\"py-3 text-success fw-bold\">Cambio:</th><td class=\"text-end py-3 text-success fw-bold fs-4\">$' + parseFloat(data.cambio).toFixed(2) + '</td></tr>' +
            '</table></div>' +
            
            '<p class=\"text-center text-muted mt-4 mb-0\">¡Gracias por su visita!</p></div>' +
            
            '<div class=\"card-footer bg-white border-0 no-print\">' +
            '<div class=\"row g-2\">' +
            '<div class=\"col-6\"><button class=\"btn btn-primary w-100\" onclick=\"window.print()\">' +
            '<i class=\"fas fa-print me-2\"></i>Imprimir</button></div>' +
            '<div class=\"col-6\"><button class=\"btn btn-secondary w-100\" onclick=\"nuevoTicket()\">' +
            '<i class=\"fas fa-plus me-2\"></i>Nuevo</button></div>' +
            '</div></div></div></div></div>';
        $('#cobro-area').html(html);
    }

    function nuevoTicket() {
        $('#cobro-area').addClass('d-none').html('');
        $('#digitosInput').val('');
        $('#numTicketCompleto').val('');
        if (metodoActual === 'rapida') $('#digitosInput').focus();
        else $('#numTicketCompleto').focus();
    }

    $(document).ready(function() {
        cargarTicketsActivos();
        setInterval(cargarTicketsActivos, 30000);
    });
</script>
";

include 'includes/footer.php';
?>