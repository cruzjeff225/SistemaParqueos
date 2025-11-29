<?php
session_start();

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Variables de sesión correctas
$rol = $_SESSION['user_role'];      // administrador / cajero / marcador
$usuario = $_SESSION['nombre'];     // nombre completo
?>
<?php include 'includes/header.php'; ?>

<!-- CONTENIDO DEL DASHBOARD -->

<?php include 'includes/footer.php'; ?>


<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="bg-dark text-white p-3" style="width: 250px; height: 100vh;">
        <h4 class="text-center mb-4">Parqueos</h4>
        <ul class="nav flex-column">

            <li class="nav-item">
                <a href="dashboard.php" class="nav-link text-white">📊 Dashboard</a>
            </li>

            <?php if ($rol == "marcador") { ?>
            <li class="nav-item">
                <a href="user/entrada.php" class="nav-link text-white">🚗 Registrar Entrada</a>
            </li>
            <?php } ?>

            <?php if ($rol == "cajero") { ?>
            <li class="nav-item">
                <a href="user/salida.php" class="nav-link text-white">💵 Cobro / Salida</a>
            </li>
            <?php } ?>

            <?php if ($rol == "admin") { ?>
            <li class="nav-item">
                <a href="admin/usuarios.php" class="nav-link text-white">👤 Usuarios</a>
            </li>
            <li class="nav-item">
                <a href="reports/index.php" class="nav-link text-white">📄 Reportes</a>
            </li>
            <?php } ?>

            <li class="nav-item mt-4">
                <a href="login.php" class="nav-link text-danger">🚪 Cerrar Sesión</a>
            </li>
        </ul>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="p-4" style="width: 100%;">

        <h2 class="mb-4">Bienvenido, <?= $usuario ?> 👋</h2>

        <!-- TARJETAS -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5>Total Entradas Hoy</h5>
                        <h3 class="text-primary">
                           <!-- <?php include "./includes/getEntradasHoy.php"; ?> -->
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5>Tickets Pendientes</h5>
                        <h3 class="text-warning">
                          <!--  <?php include "./includes/getPendientes.php"; ?> -->
                        </h3>
                    </div>
                </div>
            </div>

            <?php if ($rol == "admin" || $rol == "cajero") { ?>
            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5>Total Cobrado Hoy</h5>
                        <h3 class="text-success">$<?php include "./includes/getIngresosHoy.php"; ?></h3>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5>Rol</h5>
                        <h3 class="text-info text-capitalize"><?= $rol ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA: ÚLTIMOS TICKETS -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                Últimos Tickets del Día
            </div>
            <div class="card-body">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#Ticket</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                     <!--   <?php include "./includes/listaTicketsHoy.php"; ?> -->
                    </tbody>
                </table>

            </div>
        </div>

    </div>

</div>

<?php include "./includes/footer.php"; ?>
