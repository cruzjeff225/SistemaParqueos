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

    // Listar tickets activos del día (MODIFICADA para cobro)
    public function listarActivos()
    {
        // Añadimos 't.idTicket' y filtramos por 'estado'
        $query = "SELECT t.idTicket, t.numeroTicket, t.fechaHoraEntrada, u.nombreCompleto 
                  FROM tickets t
                  INNER JOIN Usuarios u ON t.idUsuario = u.idUsuario
                  WHERE t.estado = 'pendiente'
                  ORDER BY t.fechaHoraEntrada DESC";

        return mysqli_query($this->conn, $query);
    }

    // ----------------------------------------------------
    // NUEVOS METODOS PARA COBRO
    // ----------------------------------------------------

    // Obtener info para cobro (por número de ticket, solo si está pendiente)
    public function obtenerPorNumeroPendiente($numeroTicket)
    {
        $numeroTicket = mysqli_real_escape_string($this->conn, $numeroTicket);
        
        $query = "SELECT idTicket, numeroTicket, fechaHoraEntrada, idUsuario 
                  FROM tickets 
                  WHERE numeroTicket = '$numeroTicket' AND estado = 'pendiente'";

        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) === 1) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }

    // Obtener todos los datos de un ticket por su ID (para el recibo final)
    public function obtenerPorId($idTicket)
    {
        $idTicket = (int)$idTicket;
        
        $query = "SELECT * FROM tickets WHERE idTicket = $idTicket";
        
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) === 1) {
            return mysqli_fetch_assoc($result);
        }
        return false;
    }


    // Actualiza todos los campos de cobro y marca como pagado
    public function actualizarCobro($idTicket, $fechaHoraSalida, $tiempoTotal, $costoTotal, $montoRecibido, $cambio)
    {
        // Sanitizar y asegurar tipos
        $idTicket = (int)$idTicket;
        $fechaHoraSalida = mysqli_real_escape_string($this->conn, $fechaHoraSalida);
        $tiempoTotal = mysqli_real_escape_string($this->conn, $tiempoTotal);
        $costoTotal = (float)$costoTotal;
        $montoRecibido = (float)$montoRecibido;
        $cambio = (float)$cambio;

        $sql = "UPDATE tickets SET 
                fechaHoraSalida = '$fechaHoraSalida', 
                tiempoTotal = '$tiempoTotal', 
                costoTotal = $costoTotal, 
                montoRecibido = $montoRecibido, 
                cambio = $cambio, 
                estado = 'pagado' 
                WHERE idTicket = $idTicket AND estado = 'pendiente'";

        // El chequeo de 'estado = pendiente' evita doble cobro.
        return mysqli_query($this->conn, $sql);
    }
}