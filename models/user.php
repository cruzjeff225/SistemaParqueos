<?php

class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Search user by email
    public function findByEmail($email)
    {
        $email = mysqli_real_escape_string($this->conn, $email);
        // Query to find user by email
        $query = "SELECT u.*, r.nombreRol FROM Usuarios u INNER JOIN Roles r On u.rolId = r.idRol WHERE u.email = '$email' LIMIT 1";
        $result = mysqli_query($this->conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    // Update last login timestamp
    public function updateLastLogin($userId)
    {
        $userId = (int)$userId;
        $query = "UPDATE Usuarios SET ultimoAcceso = NOW() WHERE idUsuario = $userId";
        return mysqli_query($this->conn, $query);
    }

    // Get user by ID
    public function getById($userId)
    {
        $userId = (int)$userId;
        $query = "SELECT * FROM Usuarios WHERE idUsuario = $userId LIMIT 1";
        $result = mysqli_query($this->conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
}
