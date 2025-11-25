<?php

// Login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define ('DB_HOST', 'localhost');
define ('DB_USER', 'root');
define ('DB_PASS', 'root');
define ('DB_NAME', 'parkingsystem');

// Create database connection
function dbConnect() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if (!$conn) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utfmb4");
    return $conn;
}

// Funtions helpers
function clear($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}