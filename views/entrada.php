<?php
require_once '../config/database.php';

redirectIfNotLoggedIn();

//Datos del usuario en sesion
$usuario = $_SESSION['nombre'];
$rol = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada de Vehículos - Sistema Parqueo</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alertify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

    <style>
        body {
            background-color: #f5f7fa;
        }
        .card {
            border-radius: 15px;
        }
        .btn-generar {
            padding: 30px;
            font-size: 28px;
            border-radius: 20px;
        }
        .ticket-box {
            background: #e7f1ff;
            border-left: 5px solid #0d6efd;
        }
    </style>    
</head>
<body>
    <div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-car-side"></i> Entrada de Vehículos</h3>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?php echo $usuario; ?></span>
            <a href="../controllers/authController.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>

    <!-- Botón principal -->
    <div class="card p-4 mb-4 text-center">
        <button id="btnGenerar" class="btn btn-primary btn-generar">
            <i class="fas fa-ticket-alt"></i> GENERAR TICKET
        </button>
    </div>

    <!-- Último ticket generado -->
    <div id="ticket-info" class="ticket-box p-3 d-none">
        <h5 class="fw-bold mb-2"><i class="fas fa-check-circle text-success"></i> Ticket generado</h5>
        <p class="mb-1"><b>Número:</b> <span id="ticket-num"></span></p>
        <p class="mb-1"><b>Hora:</b> <span id="ticket-hora"></span></p>
    </div>

    <!-- Tabla de tickets activos -->
    <div class="card p-4 mt-4">
        <h5 class="fw-bold mb-3"><i class="fas fa-clock"></i> Tickets activos</h5>

        <div id="tablaActivos">Cargando...</div>
    </div>

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<script>
    // Generar ticket
    $("#btnGenerar").click(function() {
        alertify.confirm(
            "Confirmar",
            "¿Deseas generar un nuevo ticket?",
            function() {
                $.ajax({
                    url: "../controllers/ticketController.php",
                    type: "POST",
                    data: { action: "crear_ticket" },
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            alertify.success("Ticket generado");

                            $("#ticket-num").text(res.data.numero);
                            $("#ticket-hora").text(res.data.hora);
                            $("#ticket-info").removeClass("d-none");

                            cargarTicketsActivos();
                        } else {
                            alertify.error(res.message);
                        }
                    }
                });
            },
            function() {}
        );
    });

    // Cargar tickets activos
    function cargarTicketsActivos() {
        $.ajax({
            url: "../controllers/ticketController.php",
            type: "POST",
            data: { action: "listar_activos" },
            success: function(html) {
                $("#tablaActivos").html(html);
            }
        });
    }

    // Cargar la tabla al iniciar
    cargarTicketsActivos();
</script>
</body>
</html>