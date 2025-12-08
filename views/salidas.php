<?php
//Zona horaria server
date_default_timezone_set('America/El_Salvador');
require_once '../config/database.php';

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['nombre'] ?? 'Cajero';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salida de Vehículos y Cobro - Sistema Parqueo</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alertify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

</head>
<body class="salida-page">
    
    <div class="container">

        <!-- Header -->
        <header class="page-header">
            <h1 class="page-title">
                <i class="fas fa-sign-out-alt"></i>
                Salida de Vehículos y Cobro
            </h1>
            <div class="page-header-actions">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($usuario); ?></span>
                </div>
                <a href="entrada.php" class="btn btn-outline-primary">
                    <i class="fas fa-car-side"></i>
                    Entrada
                </a>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a href="../controllers/authController.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <!-- Search Card -->
        <div class="search-card">
            <div class="search-card-header">
                <h2 class="search-card-title">
                    <i class="fas fa-search"></i>
                    Buscar Ticket
                </h2>
                <span class="method-indicator" id="methodIndicator">Búsqueda Rápida</span>
            </div>

            <!-- Toggle Method -->
            <div class="search-method-toggle">
                <button type="button" class="btn btn-outline-primary active" id="btnRapida" onclick="cambiarMetodo('rapida')">
                    <i class="fas fa-bolt"></i>
                    Búsqueda Rápida (4 dígitos)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnCompleta" onclick="cambiarMetodo('completa')">
                    <i class="fas fa-keyboard"></i>
                    Búsqueda Completa
                </button>
            </div>

            <!-- Fast Search Form -->
            <form id="formBuscarRapida" class="search-form">
                <div class="search-form-group">
                    <label class="form-label">Número de Ticket</label>
                    <div class="ticket-input-wrapper">
                        <span class="ticket-prefix" id="ticketPrefix">T-<?php echo date('Ymd'); ?>-</span>
                        <input 
                            type="text" 
                            id="digitosInput" 
                            class="input-digits" 
                            placeholder="0001"
                            maxlength="4"
                            pattern="\d{1,4}"
                            required
                            autofocus
                        >
                    </div>
                    <div class="help-text">
                        <i class="fas fa-info-circle"></i>
                        Solo ingresa los últimos 4 dígitos del ticket
                    </div>
                </div>
                <button type="submit" class="btn btn-search btn-primary">
                    <i class="fas fa-search"></i>
                    BUSCAR
                </button>
            </form>

            <!-- Complete Search Form -->
            <form id="formBuscarCompleta" class="search-form" style="display: none;">
                <div class="search-form-group">
                    <label for="numTicketCompleto" class="form-label">Número de Ticket Completo</label>
                    <input 
                        type="text" 
                        id="numTicketCompleto" 
                        class="form-control" 
                        placeholder="Ej: T-20251127-0001"
                        pattern="T-\d{8}-\d{4}"
                    >
                    <div class="help-text">
                        <i class="fas fa-info-circle"></i>
                        Ingresa el número completo del ticket
                    </div>
                </div>
                <button type="submit" class="btn btn-search btn-primary">
                    <i class="fas fa-search"></i>
                    BUSCAR
                </button>
            </form>
        </div>

        <!-- Charge Area (Hidden) -->
        <div id="cobro-area" class="d-none"></div>

        <!-- Active Tickets -->
        <section class="tickets-section">
            <div class="tickets-section-header">
                <h3 class="tickets-section-title">
                    <i class="fas fa-clock"></i>
                    Tickets Activos Pendientes
                </h3>
            </div>
            <div class="tickets-section-body">
                <div class="table-responsive">
                    <div id="tablaActivos">
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Cargando tickets...</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <script>
        const TARIFA_POR_HORA = 2.00;
        let metodoActual = 'rapida';

        alertify.set('notifier', 'position', 'top-right');

        // Cambiar método de búsqueda
        function cambiarMetodo(metodo) {
            metodoActual = metodo;
            
            if (metodo === 'rapida') {
                $('#formBuscarRapida').show();
                $('#formBuscarCompleta').hide();
                $('#btnRapida').addClass('active');
                $('#btnCompleta').removeClass('active');
                $('#methodIndicator').text('Búsqueda Rápida');
                $('#digitosInput').focus();
            } else {
                $('#formBuscarRapida').hide();
                $('#formBuscarCompleta').show();
                $('#btnCompleta').addClass('active');
                $('#btnRapida').removeClass('active');
                $('#methodIndicator').text('Búsqueda Completa');
                $('#numTicketCompleto').focus();
            }
        }

        // Auto-format inputs
        $("#digitosInput").on('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
        });

        $("#numTicketCompleto").on('input', function() {
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
                url: "../controllers/ticketController.php",
                type: "POST",
                data: { action: "listar_activos" },
                success: function(html) {
                    $("#tablaActivos").html(html);
                    
                    $(".btn-cobrar").click(function() {
                        let numero = $(this).data('numero');
                        let digitos = numero.split('-')[2];
                        
                        cambiarMetodo('rapida');
                        $("#digitosInput").val(digitos);
                        $("#formBuscarRapida").submit();
                        
                        $('html, body').animate({
                            scrollTop: $(".search-card").offset().top - 20
                        }, 500);
                    });
                },
                error: function() {
                    $("#tablaActivos").html(
                        '<div class="empty-state">' +
                        '<i class="fas fa-exclamation-circle"></i>' +
                        '<p>Error al cargar tickets</p>' +
                        '</div>'
                    );
                }
            });
        }

        // Fast search
        $("#formBuscarRapida").submit(function(e) {
            e.preventDefault();
            let digitos = $("#digitosInput").val().padStart(4, '0');
            let fecha = '<?php echo date("Ymd"); ?>';
            let numeroTicket = `T-${fecha}-${digitos}`;
            buscarTicket(numeroTicket);
        });

        // Complete search
        $("#formBuscarCompleta").submit(function(e) {
            e.preventDefault();
            let numeroTicket = $("#numTicketCompleto").val().trim();
            if (!numeroTicket) {
                alertify.error('Por favor ingresa un número de ticket');
                return;
            }
            buscarTicket(numeroTicket);
        });

        // Unified search function
        function buscarTicket(numeroTicket) {
            $("#cobro-area").html(`
                <div class="detail-card">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p>Calculando cobro...</p>
                    </div>
                </div>
            `).removeClass("d-none");

            $.ajax({
                url: "../controllers/ticketController.php",
                type: "POST",
                data: { action: "obtener_info_cobro", numeroTicket: numeroTicket },
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        mostrarFormularioCobro(res.data);
                        $('html, body').animate({
                            scrollTop: $("#cobro-area").offset().top - 20
                        }, 500);
                    } else {
                        alertify.error(res.message);
                        $("#cobro-area").addClass("d-none");
                    }
                },
                error: function() {
                    alertify.error("Error al buscar el ticket");
                    $("#cobro-area").addClass("d-none");
                }
            });
        }

        // Show charge form
        function mostrarFormularioCobro(data) {
            let html = `
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3 class="detail-card-title">Detalle del Ticket</h3>
                    </div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-item-label">Número de Ticket</span>
                            <span class="detail-item-value">${data.numeroTicket}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Tiempo Transcurrido</span>
                            <span class="detail-item-value">${data.tiempoTotal}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Hora de Entrada</span>
                            <span class="detail-item-value">${new Date(data.fechaHoraEntrada).toLocaleString('es-SV')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Hora de Salida</span>
                            <span class="detail-item-value">${new Date(data.fechaHoraSalida).toLocaleString('es-SV')}</span>
                        </div>
                    </div>

                    <div class="amount-highlight">
                        <div class="amount-highlight-label">Monto a Pagar</div>
                        <div class="amount-highlight-value">$${parseFloat(data.costoTotal).toFixed(2)}</div>
                    </div>

                    <form id="formCobrar" class="payment-form">
                        <input type="hidden" name="action" value="procesar_cobro">
                        <input type="hidden" name="idTicket" value="${data.idTicket}">
                        <input type="hidden" name="costoTotal" value="${data.costoTotal}">
                        <input type="hidden" name="tiempoTotal" value="${data.tiempoTotal}">
                        <input type="hidden" name="fechaHoraSalida" value="${data.fechaHoraSalida}">
                        
                        <div class="payment-input-group">
                            <label for="montoRecibido" class="form-label">
                                <i class="fas fa-money-bill-wave"></i>
                                Monto Recibido ($)
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="montoRecibido" 
                                name="montoRecibido" 
                                class="form-control" 
                                required 
                                min="${data.costoTotal}"
                                placeholder="0.00"
                            >
                        </div>
                        
                        <button type="submit" class="btn btn-process">
                            <i class="fas fa-check-circle"></i>
                            PROCESAR PAGO
                        </button>
                    </form>
                </div>
            `;
            
            $("#cobro-area").html(html);
            setTimeout(() => $("#montoRecibido").focus(), 100);
        }

        // Process payment
        $(document).on('submit', '#formCobrar', function(e) {
            e.preventDefault();
            
            let formData = $(this).serialize();
            
            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            $.ajax({
                url: "../controllers/ticketController.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        alertify.success('¡Pago procesado exitosamente!');
                        mostrarRecibo(res.data);
                        cargarTicketsActivos();
                    } else {
                        alertify.error(res.message);
                        $("#formCobrar").find('button[type="submit"]')
                            .prop('disabled', false)
                            .html('<i class="fas fa-check-circle"></i> PROCESAR PAGO');
                    }
                },
                error: function() {
                    alertify.error("Error al procesar el pago");
                    $("#formCobrar").find('button[type="submit"]')
                        .prop('disabled', false)
                        .html('<i class="fas fa-check-circle"></i> PROCESAR PAGO');
                }
            });
        });

        // Show receipt
        function mostrarRecibo(data) {
            let html = `
                <div class="receipt-card">
                    <div id="print-area">
                        <div class="receipt-header">
                            <div class="receipt-success-badge">
                                <i class="fas fa-check-circle"></i>
                                Pago Procesado
                            </div>
                            <h2 class="receipt-title">Recibo de Pago</h2>
                            <p class="receipt-subtitle">Sistema de Parqueo</p>
                            <p class="receipt-subtitle">${new Date().toLocaleString('es-SV')}</p>
                        </div>

                        <table class="receipt-table">
                            <tr>
                                <th>Ticket:</th>
                                <td>${data.numeroTicket}</td>
                            </tr>
                            <tr>
                                <th>Entrada:</th>
                                <td>${new Date(data.fechaHoraEntrada).toLocaleString('es-SV')}</td>
                            </tr>
                            <tr>
                                <th>Salida:</th>
                                <td>${new Date(data.fechaHoraSalida).toLocaleString('es-SV')}</td>
                            </tr>
                            <tr>
                                <th>Tiempo:</th>
                                <td>${data.tiempoTotal}</td>
                            </tr>
                            <tr class="total-row">
                                <th>Total:</th>
                                <td>$${parseFloat(data.costoTotal).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Recibido:</th>
                                <td>$${parseFloat(data.montoRecibido).toFixed(2)}</td>
                            </tr>
                            <tr class="cambio-row">
                                <th>Cambio:</th>
                                <td>$${parseFloat(data.cambio).toFixed(2)}</td>
                            </tr>
                        </table>

                        <div class="receipt-footer">
                            ¡Gracias por su visita!
                        </div>
                    </div>

                    <div class="receipt-actions no-print">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i>
                            Imprimir
                        </button>
                        <button class="btn btn-outline-primary" onclick="nuevoTicket()">
                            <i class="fas fa-plus"></i>
                            Nuevo
                        </button>
                    </div>
                </div>
            `;
            
            $("#cobro-area").html(html);
        }

        // New ticket
        function nuevoTicket() {
            $("#cobro-area").addClass("d-none").html('');
            $("#digitosInput").val('');
            $("#numTicketCompleto").val('');
            
            if (metodoActual === 'rapida') {
                $("#digitosInput").focus();
            } else {
                $("#numTicketCompleto").focus();
            }
        }

        // Initial load
        $(document).ready(function() {
            cargarTicketsActivos();
            setInterval(cargarTicketsActivos, 30000);
        });
    </script>

</body>
</html>