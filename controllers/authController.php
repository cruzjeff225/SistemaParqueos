<?php
require_once '../config/database.php';
require_once '../models/user.php';

// Verify POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    replyJson(false, 'Método no permitido.');
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

    // Verify password
    if (!password_verify($password, $user['password'])) {
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

    // Define route based on role
    $routes = [
        'administrador' => 'dashboard.php',
        'cajero'        => 'dashboard.php',
        'marcador'      => 'dashboard.php'
    ];

    $redirectTo = isset($routes[$user['rolId']]) ? $routes[$user['rolId']] : 'login.php';

    // Reply success
    replyJson(true, 'Inicio de sesión exitoso.', ['redirect' => $redirectTo]);
}

// External logout function
function extLogout() {
    // Destroy session
    session_unset();
    session_destroy();

    // Reply success
    replyJson(true, 'Cierre de sesión exitoso.', ['redirect' => 'login.php']);
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

