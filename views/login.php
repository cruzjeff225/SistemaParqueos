<?php
require_once '../config/database.php';

// Login redirection if user is already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Parqueo</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alertify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../public/css/login.css">
</head>
<body class="login-page">
    
    <div class="login-container">
        <div class="login-card">
            
            <!-- Header -->
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-parking"></i>
                </div>
                <h4 class="title">Sistema de Parqueo</h4>
                <p class="subtitle">Inicia sesión para continuar</p>
            </div>

            <!-- Messagges Error/Success -->
            <div id="alert-container"></div>

            <!-- Login Form -->
            <form id="loginForm" autocomplete="off">
                
                <!-- Email -->
                <div class="form-group">
                    <label for="username">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="ejemplo@gmail.com"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                        <button 
                            class="btn-toggle-password" 
                            type="button" 
                            id="togglePassword"
                            tabindex="-1"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remenber -->
                <div class="form-check-custom">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordar sesión</label>
                </div>

                <!-- Botón Iniciar Sesión -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText">Iniciar Sesión</span>
                    <span id="btnSpinner" class="spinner d-none">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>

            </form>

            <!-- Footer -->
            <div class="login-footer">
                <small></small>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alertify JS -->
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    
    <!-- JS -->
    <script src="../public/js/login.js"></script>
    
</body>
</html>