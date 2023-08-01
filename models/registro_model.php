<?php
require_once('../../db/conectar.php');

class RegistroUsuarioModel {
    private $db;

    public function __construct(){
        $this->db = Conectar::conexion();
    }


    public function RegistroUsuario($usuario, $correo, $telefono, $celular,  $clave) {
        $contraseña = $this->db->real_escape_string(hash('sha256', $clave));
        $sql = "INSERT INTO Usuarios (Nombre, Correo, Telefono, Celular, Clave, Tipo, Aceptado) VALUES ('$usuario', '$correo', $telefono, $celular, '$contraseña', 'Cliente', FALSE); ";
        $result = mysqli_query($this->db, $sql);
        $filasAfectadas = mysqli_affected_rows($this->db);
        return $filasAfectadas;
    }

public function getIdUsuario($correo) {
    $query = "SELECT idUsuario FROM usuarios WHERE Correo = ?";
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
