<?php
require_once '../config/database.php';
require_once '../models/user.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si la acción viene por GET (para logout)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    extLogout();
}

// Verify POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

// Get action
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Database connection
$conn = dbConnect();
$userModel = new User($conn);

// Execute action
switch ($action) {
    case 'login':
        extLogin($userModel);
        break;
    case 'logout':
        extLogout();
        break;

    default:
        replyJson(false, 'Acción no válida.');
}

// External login function
function extLogin($userModel) {
    // Get data from POST
    $email = isset($_POST['email']) ? clear($_POST['email']) : '';
    $password = isset($_POST['password']) ? clear($_POST['password']) : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        replyJson(false, 'Correo electrónico y contraseña son obligatorios.');
    }

    // Validate format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        replyJson(false, 'Formato de correo electrónico no válido.');
    }

    // Validate length password
    if (strlen($password) < 6) {
        replyJson(false, 'La contraseña debe tener al menos 6 caracteres.');
    }

    // Find user by email
    $user = $userModel->findByEmail($email);

    if (!$user) {
        // User not found
        replyJson(false, 'Correo electrónico o contraseña incorrectos.');
    }

    // Verify password with MD5
    $passwordMD5 = md5($password);
    if ($passwordMD5 !== $user['password']) {
        // Invalid password
        replyJson(false, 'Correo electrónico o contraseña incorrectos.');
    }

    // Verify if user is active
    if ($user['isActive'] != 1) {
        replyJson(false, 'La cuenta de usuario no está activa.');
    }

    // Login successful
    $_SESSION['user_id'] = $user['idUsuario'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nombre'] = $user['nombreCompleto'];
    $_SESSION['user_role'] = $user['nombreRol'];
    
    // Update last login
    $userModel->updateLastLogin($user['idUsuario']);

    // Reply success
    replyJson(true, 'Inicio de sesión exitoso.', ['redirect' => 'dashboard.php']);
}

// External logout function
function extLogout() {
    // Destroy session
    session_unset();
    session_destroy();
    
    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Redirect to login
    header('Location: ../views/login.php');
    exit;
}

// JSON reply helper
function replyJson($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}