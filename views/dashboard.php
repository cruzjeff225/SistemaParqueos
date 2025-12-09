<?php
session_start();

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['user_role'];
$usuario = $_SESSION['nombre'];

// Variables para el header
$pageTitle = 'Dashboard - Sistema de Parqueo';
$bodyClass = 'bg-light';

// Header
include 'includes/header.php';
?>

<div class="container-fluid">
    
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-start-primary">
                <div class="card-body">
                    <h1 class="h3 mb-2">
                        <i class="fas fa-hand-wave text-primary me-2"></i>
                        ¡Bienvenido, <?= htmlspecialchars($usuario) ?>!
                    </h1>
                    <p class="text-muted mb-0">Panel de control del sistema de parqueo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Entradas -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card stat-card-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Total Entradas Hoy</p>
                            <h2 class="mb-0 fw-bold text-primary" id="stat-entradas">
                                <span class="spinner-border spinner-border-sm"></span>
                            </h2>
                        </div>
                        <div class="stat-icon stat-icon-primary">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Pendientes -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Tickets Pendientes</p>
                            <h2 class="mb-0 fw-bold text-warning" id="stat-pendientes">
                                <span class="spinner-border spinner-border-sm"></span>
                            </h2>
                        </div>
                        <div class="stat-icon stat-icon-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cobrado -->
        <?php if ($rol == "administrador" || $rol == "cajero"): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card stat-card-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Total Cobrado Hoy</p>
                            <h2 class="mb-0 fw-bold text-success" id="stat-cobrado">
                                <span class="spinner-border spinner-border-sm"></span>
                            </h2>
                        </div>
                        <div class="stat-icon stat-icon-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rol -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card stat-card-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Rol Actual</p>
                            <h2 class="mb-0 fw-bold text-info text-capitalize"><?= htmlspecialchars($rol) ?></h2>
                        </div>
                        <div class="stat-icon stat-icon-info">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="row g-3 mb-4">
        <?php if ($rol == "marcador" || $rol == "administrador"): ?>
        <div class="col-12 col-md-6">
            <div class="card border-start-primary h-100">
                <div class="card-body text-center">
                    <div class="stat-icon stat-icon-primary mx-auto mb-3">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h4 class="card-title mb-2">Registrar Entrada</h4>
                    <p class="text-muted mb-3">Genera un nuevo ticket de entrada para un vehículo</p>
                    <a href="entrada.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Nueva Entrada
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($rol == "cajero" || $rol == "administrador"): ?>
        <div class="col-12 col-md-6">
            <div class="card border-start-success h-100">
                <div class="card-body text-center">
                    <div class="stat-icon stat-icon-success mx-auto mb-3">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h4 class="card-title mb-2">Procesar Salida</h4>
                    <p class="text-muted mb-3">Cobra y registra la salida de un vehículo</p>
                    <a href="salidas.php" class="btn btn-success">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Ir a Cobros
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Tickets Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Últimos Tickets del Día
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-ultimos">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="text-muted mt-2 mb-0">Cargando tickets...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php 
// Scripts específicos de la página
$pageScripts = "
<script>
    // Cargar estadísticas
    function cargarEstadisticas() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'obtener_estadisticas' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#stat-entradas').text(res.data.entradasHoy);
                    $('#stat-pendientes').text(res.data.pendientes);
                    $('#stat-cobrado').text('$' + parseFloat(res.data.totalCobrado).toFixed(2));
                }
            },
            error: function() {
                $('#stat-entradas').text('--');
                $('#stat-pendientes').text('--');
                $('#stat-cobrado').text('$0.00');
                alertify.error('Error al cargar estadísticas');
            }
        });
    }

    // Cargar últimos tickets
    function cargarUltimosTickets() {
        $.ajax({
            url: '../controllers/ticketController.php',
            type: 'POST',
            data: { action: 'listar_ultimos_tickets' },
            success: function(html) {
                $('#tabla-ultimos').html(html);
            },
            error: function() {
                $('#tabla-ultimos').html(
                    '<tr><td colspan=\"4\" class=\"text-center py-4\">' +
                    '<i class=\"fas fa-exclamation-circle text-danger fs-1\"></i>' +
                    '<p class=\"text-muted mt-2 mb-0\">Error al cargar tickets</p>' +
                    '</td></tr>'
                );
            }
        });
    }

    // Cargar datos al iniciar
    $(document).ready(function() {
        cargarEstadisticas();
        cargarUltimosTickets();

        // Actualizar cada 30 segundos
        setInterval(function() {
            cargarEstadisticas();
            cargarUltimosTickets();
        }, 30000);
    });
</script>
";

// Footer
include 'includes/footer.php';
?>