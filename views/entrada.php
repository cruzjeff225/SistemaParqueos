<?php
require_once '../config/database.php';
redirectIfNotLoggedIn();

$usuario = $_SESSION['nombre'];
$rol = $_SESSION['user_role'];

// Variables para el header
$pageTitle = 'Entrada de Vehículos - Sistema de Parqueo';
$bodyClass = 'bg-light';

// Incluir header
include 'includes/header.php';
?>

<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-1 fw-bold text-dark">
                <i class="fas fa-car-side text-primary me-2"></i>
                Registrar Entrada
            </h1>
            <p class="text-muted mb-0">Genera un nuevo ticket de entrada para un vehículo</p>
        </div>
    </div>

    <!-- Generate Ticket Card -->
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

    <!-- Ticket Info (Hidden by default) -->
    <div class="row mb-4 d-none" id="ticket-info">
        <div class="col-12">
            <div class="card border-0 border-start border-success border-4 shadow-sm">
                <div id="print-area-entrada" class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-check-circle text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-success">¡Ticket Generado Exitosamente!</h5>
                            <small class="text-muted">El vehículo ha sido registrado en el sistema</small>
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Sistema de Parqueo</h6>
                        <h3 class="fw-bold mb-1">TICKET DE ENTRADA</h3>
                        <p class="text-muted mb-0" id="ticket-fecha-completa">-</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 border">
                                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Número de Ticket</small>
                                <h4 class="mb-0 fw-bold text-dark" id="ticket-num">-</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 border">
                                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Hora de Entrada</small>
                                <h4 class="mb-0 fw-bold text-dark" id="ticket-hora">-</h4>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div class="small">
                                <strong>Tarifa:</strong> $2.00 por hora o fracción<br>
                                <strong>Importante:</strong> Conserve este ticket para su salida
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted small">
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

    <!-- Active Tickets -->
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
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<?php
// Scripts
$pageScripts = "
<script>
    // Generar ticket
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
                            
                            // Mostrar info del ticket
                            $('#ticket-num').text(res.data.numero);
                            $('#ticket-hora').text(res.data.hora);
                            
                            // Formatear fecha completa
                            const fecha = new Date(res.data.fecha);
                            const opciones = { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            };
                            $('#ticket-fecha-completa').text(
                                fecha.toLocaleDateString('es-SV', opciones) + ' - ' + res.data.hora
                            );
                            
                            $('#ticket-info').removeClass('d-none');
                            
                            // Recargar tabla
                            cargarTicketsActivos();
                            
                            // Scroll al ticket generado
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

    // Imprimir ticket de entrada
    function imprimirTicketEntrada() {
        window.print();
    }

    // Cerrar ticket
    function cerrarTicket() {
        $('#ticket-info').addClass('d-none');
        $('html, body').animate({scrollTop: 0}, 500);
    }

    // Cargar tickets activos
    function cargarTicketsActivos() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'listar_activos' },
            success: function(html) {
                $('#tablaActivos').html(html);
                
                // Contar tickets y actualizar badge
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

    // Cargar al iniciar
    $(document).ready(function() {
        cargarTicketsActivos();
        
        // Actualizar cada 30 segundos
        setInterval(cargarTicketsActivos, 30000);
    });
</script>
";

// Incluir footer
include 'includes/footer.php';
?>