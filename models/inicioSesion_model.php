<?php
require_once('../../db/conectar.php');

class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conectar::conexion();
    }


    public function validarUsuario($usuario, $clave)
    {
        $correo = mysqli_real_escape_string($this->db, $usuario);
        $contrasena = mysqli_real_escape_string($this->db, $clave);

        $query = "SELECT Correo FROM Usuarios WHERE Correo = ? AND Clave = SHA2(?, 256)";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('ss', $correo, $contrasena);
            $consulta->execute();
            $consulta->store_result();

            $numFilas = $consulta->num_rows;
            $consulta->close();

            return $numFilas;
        } else {
            // Error en la consulta preparada
            echo "Error en la consulta: " . $this->db->error;
            return false;
        }
    }




    public function obtenerId($usuario)
    {
        $correo = mysqli_real_escape_string($this->db, $usuario);
        $query = "SELECT idUsuario FROM usuarios WHERE correo = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('s', $correo);
            $consulta->execute();
            $consulta->store_result();

            if ($consulta->num_rows > 0) {
                $consulta->bind_result($idUsuario);
                $consulta->fetch();
                $consulta->free_result();
                $consulta->close();
                return $idUsuario;
            } else {
                $consulta->close();
                // No se encontraron resultados
                return null;
            }
        } else {
            // Error en la consulta preparada
            echo "Error en la consulta: " . $this->db->error;
            return null;
        }
    }
}
