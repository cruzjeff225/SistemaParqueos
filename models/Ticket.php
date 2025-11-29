<?php

class Ticket
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crear ticket
    public function crear($numero, $usuarioId)
    {
        $numero = mysqli_real_escape_string($this->conn, $numero);
        $usuarioId = (int)$usuarioId;

        $query = "INSERT INTO tickets (numeroTicket, fechaHoraEntrada, idUsuario)
                  VALUES ('$numero', NOW(), $usuarioId)";

        return mysqli_query($this->conn, $query);
    }

    // Último ticket creado hoy
    public function ultimoTicketHoy()
    {
        $query = "SELECT numeroTicket 
                  FROM tickets
                  WHERE DATE(fechaHoraEntrada) = CURDATE()
                  ORDER BY idTicket DESC
                  LIMIT 1";

        $result = mysqli_query($this->conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result)['numeroTicket'];
        }
        return null;
    }

    // Listar tickets activos del día
    public function listarActivos()
    {
        $query = "SELECT t.numeroTicket, t.fechaHoraEntrada, u.nombreCompleto 
                  FROM tickets t
                  INNER JOIN Usuarios u ON t.idUsuario = u.idUsuario
                  WHERE DATE(t.fechaHoraEntrada) = CURDATE()
                  ORDER BY t.fechaHoraEntrada DESC";

        return mysqli_query($this->conn, $query);
    }
}
