<?php
require_once('./db/conectar.php');

class home_model
{
    private $db;
    private $flayers;
    private $categorias;

    public function __construct()
    {
        $this->db = Conectar::conexion();
        $this->flayers = array();
        $this->categorias = array(); // Corregido el error aquí, duplicado en la inicialización
    }

    public function getFlayers()
    {
        $query = "SELECT * FROM flayers";
        $consulta = $this->db->query($query);

        while ($filas = $consulta->fetch_assoc()) {
            $this->flayers[] = $filas;
        }

        return $this->flayers;
    }

    public function getCategorias()
    {
        $query = "SELECT * FROM categoria";
        $consulta = $this->db->query($query);

        while ($filas = $consulta->fetch_assoc()) {
            $this->categorias[] = $filas;
        }

        return $this->categorias;
    }
}
