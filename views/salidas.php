<?php
require_once '../config/database.php';
date_default_timezone_set('America/El_Salvador');

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['nombre'] ?? 'Cajero';

$pageTitle = 'Salida de Vehículos - Sistema de Parqueo';
$bodyClass = 'bg-light';

include 'includes/header.php';
?>

<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-1 fw-bold text-dark">
                <i class="fas fa-sign-out-alt text-success me-2"></i>
                Cobro / Salida
            </h1>
            <p class="text-muted mb-0">Cobra y registra la salida de un vehículo</p>
        </div>
    </div>

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

    <div id="cobro-area" class="d-none"></div>

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

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #print-area, #print-area * {
        visibility: visible;
    }
    #print-area {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        width: 80mm;
        max-width: 100%;
        padding: 5mm;
    }
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        page-break-after: avoid;
    }
    h3 { font-size: 1.3rem !important; margin-bottom: 0.3rem !important; }
    h6 { font-size: 0.65rem !important; margin-bottom: 0.2rem !important; }
    p, small { font-size: 0.75rem !important; line-height: 1.2 !important; }
    .mb-1 { margin-bottom: 0.15rem !important; }
    .mb-2 { margin-bottom: 0.3rem !important; }
    .mb-3 { margin-bottom: 0.5rem !important; }
    .py-2 { padding-top: 0.3rem !important; padding-bottom: 0.3rem !important; }
    .pt-2 { padding-top: 0.3rem !important; }
    .alert { padding: 0.4rem !important; margin-bottom: 0.5rem !important; }
    table { margin-bottom: 0.3rem !important; }
    .border-top { padding-top: 0.3rem !important; }
}
</style>

<?php
$pageScripts = "
<script>
    const TARIFA_POR_HORA = 2.00;
    let metodoActual = 'rapida';
    const nombreUsuario = '" . htmlspecialchars($usuario) . "';

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

    function cargarTicketsActivos() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'listar_activos' },
            success: function(html) {
                $('#tablaActivos').html(html);
                
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
        let html = '<div class=\"row mb-4\"><div class=\"col-12\"><div class=\"card border-0 shadow-sm\">' +
            '<div class=\"card-body\">' +
            '<h5 class=\"mb-4 fw-bold\"><i class=\"fas fa-file-invoice-dollar text-primary me-2\"></i>Detalle del Ticket</h5>' +
            
            '<div class=\"row g-3 mb-4\">' +
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded text-center\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem;\">Número</small>' +
            '<strong class=\"d-block fs-6\">' + data.numeroTicket + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded text-center\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem;\">Tiempo</small>' +
            '<strong class=\"d-block fs-6\">' + data.tiempoTotal + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded text-center\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem;\">Entrada</small>' +
            '<strong class=\"d-block fs-6\">' + formatearHora(data.fechaHoraEntrada) + '</strong></div></div>' +
            
            '<div class=\"col-md-3\"><div class=\"p-3 bg-light rounded text-center\">' +
            '<small class=\"text-muted text-uppercase d-block mb-1\" style=\"font-size: 0.7rem;\">Salida</small>' +
            '<strong class=\"d-block fs-6\">' + formatearHora(data.fechaHoraSalida) + '</strong></div></div>' +
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
            '<input type=\"hidden\" name=\"numeroTicket\" value=\"' + data.numeroTicket + '\">' +
            '<input type=\"hidden\" name=\"fechaHoraEntrada\" value=\"' + data.fechaHoraEntrada + '\">' +
            
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
                    let datosRecibo = $.extend({}, res.data, {
                        numeroTicket: $('#formCobrar input[name=\"numeroTicket\"]').val(),
                        fechaHoraEntrada: $('#formCobrar input[name=\"fechaHoraEntrada\"]').val()
                    });
                    mostrarRecibo(datosRecibo);
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
        const ahora = new Date();
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = ahora.toLocaleDateString('es-SV', opciones) + ' - ' + formatearHora(data.fechaHoraSalida);
        
        let html = '<div class=\"row mb-4\"><div class=\"col-12\"><div class=\"card border-0 shadow-sm\">' +
            '<div id=\"print-area\" class=\"card-body p-4\">' +
            
            '<div class=\"text-center mb-3\">' +
            '<h6 class=\"text-muted text-uppercase mb-2\" style=\"font-size: 0.7rem; letter-spacing: 1px;\">Sistema de Parqueo</h6>' +
            '<h3 class=\"fw-bold mb-1\">RECIBO DE PAGO</h3>' +
            '<p class=\"text-muted mb-0\" style=\"font-size: 0.85rem;\">' + fechaFormateada + '</p></div>' +
            
            '<div class=\"row mb-2\">' +
            '<div class=\"col-6\"><small class=\"text-muted text-uppercase d-block\" style=\"font-size: 0.65rem;\">Ticket</small>' +
            '<p class=\"mb-0 fw-semibold\" style=\"font-size: 0.9rem;\">' + data.numeroTicket + '</p></div>' +
            '<div class=\"col-6 text-end\"><small class=\"text-muted text-uppercase d-block\" style=\"font-size: 0.65rem;\">Tiempo Total</small>' +
            '<p class=\"mb-0 fw-semibold\" style=\"font-size: 0.9rem;\">' + data.tiempoTotal + '</p></div>' +
            '</div>' +
            
            '<div class=\"border-top border-bottom py-2 mb-2\">' +
            '<div class=\"row\">' +
            '<div class=\"col-6\"><small class=\"text-muted text-uppercase d-block\" style=\"font-size: 0.65rem;\">Entrada</small>' +
            '<p class=\"mb-0\" style=\"font-size: 0.85rem;\">' + formatearFechaCompleta(data.fechaHoraEntrada) + '</p></div>' +
            '<div class=\"col-6 text-end\"><small class=\"text-muted text-uppercase d-block\" style=\"font-size: 0.65rem;\">Salida</small>' +
            '<p class=\"mb-0\" style=\"font-size: 0.85rem;\">' + formatearFechaCompleta(data.fechaHoraSalida) + '</p></div>' +
            '</div></div>' +
            
            '<table class=\"table table-sm table-borderless mb-2\">' +
            '<tr><td class=\"text-muted\">Subtotal:</td><td class=\"text-end fw-semibold\">$' + parseFloat(data.costoTotal).toFixed(2) + '</td></tr>' +
            '<tr><td class=\"text-muted\">Recibido:</td><td class=\"text-end fw-semibold\">$' + parseFloat(data.montoRecibido).toFixed(2) + '</td></tr>' +
            '<tr class=\"border-top\"><td class=\"fw-bold pt-2\">Cambio:</td><td class=\"text-end fw-bold text-success pt-2\" style=\"font-size: 1.25rem;\">$' + parseFloat(data.cambio).toFixed(2) + '</td></tr>' +
            '</table>' +
            
            '<div class=\"alert alert-info border-0 mb-3 py-2\">' +
            '<div class=\"d-flex align-items-start\">' +
            '<i class=\"fas fa-info-circle me-2 mt-1\" style=\"font-size: 0.9rem;\"></i>' +
            '<div style=\"font-size: 0.8rem;\">' +
            '<p class=\"mb-0\"><strong>Tarifa:</strong> $2.00 por hora o fracción</p>' +
            '</div></div></div>' +
            
            '<div class=\"text-center text-muted border-top pt-2\" style=\"font-size: 0.8rem;\">' +
            '<p class=\"mb-1\">Atendido por: <strong>' + nombreUsuario + '</strong></p>' +
            '<p class=\"mb-0\">¡Gracias por su visita!</p></div>' +
            '</div>' +
            
            '<div class=\"card-footer bg-white border-0 no-print\">' +
            '<div class=\"row g-2\">' +
            '<div class=\"col-6\"><button class=\"btn btn-primary w-100\" onclick=\"window.print()\">' +
            '<i class=\"fas fa-print me-2\"></i>Imprimir</button></div>' +
            '<div class=\"col-6\"><button class=\"btn btn-secondary w-100\" onclick=\"nuevoTicket()\">' +
            '<i class=\"fas fa-plus me-2\"></i>Nuevo</button></div>' +
            '</div></div></div></div></div>';
        $('#cobro-area').html(html);
    }

    function formatearHora(fechaHora) {
        const fecha = new Date(fechaHora);
        return fecha.toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function formatearFechaCompleta(fechaHora) {
        const fecha = new Date(fechaHora);
        return fecha.toLocaleString('es-SV', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit',
            hour: '2-digit', 
            minute: '2-digit'
        });
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