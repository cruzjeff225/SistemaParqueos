<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
$isLoggedIn = isset($_SESSION['user_id']);
$usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['user_role'] ?? '';

// Determinar página actual para marcar el enlace activo
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Parqueo">
    <title><?php echo $pageTitle ?? 'Sistema de Parqueo'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alertify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../public/css/custom.css">
    
    <!-- Page Specific CSS -->
    <?php if (isset($pageCSS)): ?>
        <link rel="stylesheet" href="<?php echo $pageCSS; ?>">
    <?php endif; ?>
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">

<?php if ($isLoggedIn): ?>
<!-- Navbar Bootstrap -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
    <div class="container-fluid">
        
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <div class="brand-icon bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                <i class="fas fa-parking fs-5"></i>
            </div>
            <span class="fw-bold d-none d-md-inline">Sistema Parqueo</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            
            <!-- Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active fw-semibold' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-chart-line me-1"></i>
                        Dashboard
                    </a>
                </li>

                <!-- Entrada (Marcador y Admin) -->
                <?php if ($rol === "marcador" || $rol === "administrador"): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'entrada.php' ? 'active fw-semibold' : ''; ?>" href="entrada.php">
                        <i class="fas fa-car-side me-1"></i>
                        Entrada
                    </a>
                </li>
                <?php endif; ?>

                <!-- Salida (Cajero y Admin) -->
                <?php if ($rol === "cajero" || $rol === "administrador"): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'salidas.php' ? 'active fw-semibold' : ''; ?>" href="salidas.php">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Salida
                    </a>
                </li>
                <?php endif; ?>

            </ul>

            <!-- Cerrar Sesión -->
            <div class="dropdown">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../controllers/authController.php" onclick="return confirm('¿Seguro que deseas cerrar sesión?')">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Cerrar Sesión
                        </a>
                    </li>
            </div>

        </div>
    </div>
</nav>

<!-- Navbar Spacer -->
<div style="height: 76px;"></div>
<?php endif; ?>

<main class="main-content"><?php echo "\n"; ?>