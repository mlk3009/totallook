<?php
require_once('./db/conectar.php');

class pedido_model
{
    private $db;
    private $producto;

    public function __construct()
    {
        $this->db = Conectar::conexion();
        $this->producto = array();
    }

    public function getIdPedido($idUsuario)
    {
        $query = "SELECT idPedido FROM pedidos WHERE idUsuario = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idUsuario);
            $consulta->execute();
            $consulta->store_result();

            if ($consulta->num_rows > 0) {
                $consulta->bind_result($idPedido);
                $consulta->fetch();
                $consulta->free_result();
                $consulta->close();
                return $idPedido;
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


    public function getPedidosList($idUsuario)
    {
        $sql = " 
        SELECT p.idPedido, pr.Nombre, pr.Precio, pr.Talle, pr.Color, p.Estado
        FROM Pedidos p
        JOIN Usuarios u ON p.idUsuario = u.idUsuario
        JOIN Contiene c ON p.idPedido = c.idPedido
        JOIN Productos pr ON c.idProducto = pr.idProducto
        WHERE p.idUsuario = '$idUsuario' AND p.Estado IN ('En Proceso', 'Realizado', 'Retirado', 'Cancelado')
        ORDER BY p.idPedido";

        $consulta = $this->db->query($sql);
        while ($filas = $consulta->fetch_assoc()) {
            $this->producto[] = $filas;
        }
        return $this->producto;
    }

    public function bajaPedido($idPedido)
    {
        $sql = "UPDATE Pedidos SET Estado = 'Cancelado' WHERE idPedido = '$idPedido' and Estado in ('En Proceso', 'Realizado')";
        $resultado = $this->db->query($sql);
        return $resultado;
    }
}
