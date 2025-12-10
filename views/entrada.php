<?php
require_once '../config/database.php';
redirectIfNotLoggedIn();

$usuario = $_SESSION['nombre'];
$rol = $_SESSION['user_role'];

$pageTitle = 'Entrada de Vehículos - Sistema de Parqueo';
$bodyClass = 'bg-light';

include 'includes/header.php';
?>

<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-1 fw-bold text-dark">
                <i class="fas fa-car-side text-primary me-2"></i>
                Registrar Entrada
            </h1>
            <p class="text-muted mb-0">Genera un nuevo ticket de entrada para un vehículo</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-car-side text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <button id="btnGenerar" class="btn btn-primary btn-lg px-5 py-3 shadow">
                        <i class="fas fa-plus-circle me-2"></i>
                        <span class="fw-bold">NUEVA ENTRADA</span>
                    </button>
                    <p class="text-muted mt-3 mb-0 small">Genera un ticket de entrada para registrar el vehículo</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 d-none" id="ticket-info">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div id="print-area-entrada" class="card-body p-4">
                    
                    <div class="text-center mb-3">
                        <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Sistema de Parqueo</h6>
                        <h3 class="fw-bold mb-1">TICKET DE ENTRADA</h3>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;" id="ticket-fecha-completa">-</p>
                    </div>

                    <div class="mb-3">
                        <div class="bg-light rounded p-3 border text-center">
                            <small class="text-muted text-uppercase d-block mb-2" style="font-size: 0.65rem; letter-spacing: 0.5px;">Número de Ticket</small>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;" id="ticket-num">-</h5>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="bg-light rounded p-3 border text-center">
                            <small class="text-muted text-uppercase d-block mb-2" style="font-size: 0.65rem; letter-spacing: 0.5px;">Hora de Entrada</small>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;" id="ticket-hora">-</h5>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-3 py-2">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle me-2 mt-1" style="font-size: 0.9rem;"></i>
                            <div style="font-size: 0.8rem;">
                                <p class="mb-1"><strong>Tarifa:</strong> $2.00 por hora o fracción</p>
                                <p class="mb-0"><strong>Importante:</strong> Conserve este ticket para su salida</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted border-top pt-2" style="font-size: 0.8rem;">
                        <p class="mb-1">Atendido por: <strong><?php echo htmlspecialchars($usuario); ?></strong></p>
                        <p class="mb-0">¡Gracias por su visita!</p>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-0 no-print">
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-primary w-100" onclick="imprimirTicketEntrada()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir Ticket
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-secondary w-100" onclick="cerrarTicket()">
                                <i class="fas fa-times me-2"></i>
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Tickets Activos
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
                                <p class="text-muted mb-0">Cargando tickets activos...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 -->
    
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #print-area-entrada, #print-area-entrada * {
        visibility: visible;
    }
    #print-area-entrada {
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
    h5 { font-size: 0.9rem !important; }
    h6 { font-size: 0.65rem !important; margin-bottom: 0.2rem !important; }
    p, small { font-size: 0.75rem !important; line-height: 1.2 !important; }
    .mb-1 { margin-bottom: 0.15rem !important; }
    .mb-2 { margin-bottom: 0.3rem !important; }
    .mb-3 { margin-bottom: 0.5rem !important; }
    .g-2 { gap: 0.3rem !important; }
    .py-2 { padding-top: 0.3rem !important; padding-bottom: 0.3rem !important; }
    .pt-2 { padding-top: 0.3rem !important; }
    .p-3 { padding: 0.5rem !important; }
    .alert { padding: 0.4rem !important; margin-bottom: 0.5rem !important; }
    .border-top { padding-top: 0.3rem !important; }
}
</style>

<?php
$pageScripts = "
<script>
    $('#btnGenerar').click(function() {
        alertify.confirm(
            'Confirmar Generación',
            '¿Deseas generar un nuevo ticket de entrada?',
            function() {
                const btn = $('#btnGenerar');
                btn.prop('disabled', true).html(
                    '<span class=\"spinner-border spinner-border-sm me-2\"></span>Generando...'
                );

                $.ajax({
                    url: '../controllers/ticketController.php',
                    type: 'POST',
                    data: { action: 'crear_ticket' },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            alertify.success('¡Ticket generado exitosamente!');
                            
                            $('#ticket-num').text(res.data.numero);
                            $('#ticket-hora').text(res.data.hora);
                            
                            const ahora = new Date();
                            const opciones = { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            };
                            const fechaFormateada = ahora.toLocaleDateString('es-SV', opciones);
                            $('#ticket-fecha-completa').text(fechaFormateada + ' - ' + res.data.hora);
                            
                            $('#ticket-info').removeClass('d-none');
                            
                            cargarTicketsActivos();
                            
                            $('html, body').animate({
                                scrollTop: $('#ticket-info').offset().top - 100
                            }, 500);
                        } else {
                            alertify.error(res.message || 'Error al generar ticket');
                        }
                    },
                    error: function() {
                        alertify.error('Error de conexión con el servidor');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class=\"fas fa-plus-circle me-2\"></i><span class=\"fw-bold\">NUEVA ENTRADA</span>'
                        );
                    }
                });
            },
            function() {}
        ).set('labels', {ok:'Sí, Generar', cancel:'Cancelar'});
    });

    function imprimirTicketEntrada() {
        window.print();
    }

    function cerrarTicket() {
        $('#ticket-info').addClass('d-none');
        $('html, body').animate({scrollTop: 0}, 500);
    }

    function cargarTicketsActivos() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'listar_activos' },
            success: function(html) {
                $('#tablaActivos').html(html);
                
                const count = $('#tablaActivos table tbody tr').length;
                $('#badge-count').text(count > 0 ? count : '0');
            },
            error: function() {
                $('#tablaActivos').html(
                    '<div class=\"text-center py-5\">' +
                    '<i class=\"fas fa-exclamation-circle text-danger\" style=\"font-size: 3rem;\"></i>' +
                    '<p class=\"text-muted mt-3 mb-0\">Error al cargar tickets activos</p>' +
                    '</div>'
                );
            }
        });
    }

    $(document).ready(function() {
        cargarTicketsActivos();
        setInterval(cargarTicketsActivos, 30000);
    });
</script>
";

include 'includes/footer.php';
?>