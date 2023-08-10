<?php
require_once('./db/conectar.php');

class detallesProducto_model
{
    private $db;
    private $producto;

    public function __construct()
    {
        $this->db = Conectar::conexion();
        $this->producto = array();
    }

    public function setOpciones($a)
    {
        $idProductoSeleccionado = $a;
        $opciones = array();

        // Realizar la consulta SQL para obtener los datos de las opciones
        $query = "SELECT color FROM productos WHERE idProducto = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idProductoSeleccionado);
            $consulta->execute();
            $resultado = $consulta->get_result();

            // Recorrer los resultados y almacenarlos en el array de opciones
            while ($fila = $resultado->fetch_assoc()) {
                $opciones[] = $fila;
            }

            // Liberar el resultado
            $consulta->close();
        } else {
            echo 'Error en la consulta: ' . $this->db->error;
        }

        return $opciones;
    }



    public function getTipoUsuario($idUsuario)
    {
        $query = "SELECT Tipo FROM Usuarios WHERE idUsuario = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idUsuario);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();
                $tipoUsuario = $fila['Tipo'];
                $consulta->close();
                return $tipoUsuario;
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


    public function getProducto($a)
    {
        $idProductoSeleccionado = $a;
        $sql = "SELECT Imagen, MIN(idProducto) AS idProducto, MIN(Stock) AS Stock, MIN(Nombre) AS Nombre, MIN(Precio) AS Precio, MIN(Color) AS Color FROM Productos WHERE idProducto = " . $idProductoSeleccionado;
        $consulta = $this->db->query($sql);

        if ($consulta && $consulta->num_rows > 0) {
            $resultado = $consulta->fetch_assoc();
            $this->producto = $resultado;
        }

        return $this->producto;
    }

    public function getIdPedido($idUsuario)
    {
        $query = "SELECT idPedido FROM pedidos WHERE idUsuario = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idUsuario);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();
                $idPedido = $fila['idPedido'];
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


    function verificarIdPedido($idUsuario)
    {
        // Verificar si hay un idPedido asociado al idUsuario
        $query = "SELECT idPedido FROM Pedidos WHERE idUsuario = ? AND Estado = 'Pendiente'";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idUsuario);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                // El idUsuario tiene un idPedido existente
                $consulta->close();
                return true;
            } else {
                // El idUsuario no tiene un idPedido existente
                $consulta->close();
                return false;
            }
        } else {
            // Error en la consulta preparada
            echo "Error en la consulta: " . $this->db->error;
            return false;
        }
    }


    public function getAprobacion($idUsuario)
    {
        $sql = "SELECT Aceptado FROM Usuarios WHERE idUsuario = ?";
        $consulta = $this->db->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();
        $aprobacion = $fila['Aceptado'];
        return $aprobacion;
    }


    function agregarProducto($idUsuario, $nombre, $color, $talle)
    {
        // Verificar si hay un pedido pendiente para el usuario
        $query = "SELECT idPedido, Estado FROM Pedidos WHERE idUsuario = $idUsuario and (Estado = 'Iniciado' OR Estado = 'Pendiente')";
        $resultado = $this->db->query($query);

        if ($resultado->num_rows > 0) {
            // El usuario tiene un pedido existente
            $row = $resultado->fetch_assoc();
            $idPedido = $row["idPedido"];
            $estadoPedido = $row["Estado"];

            //Validar que el producto exista
            $query = "SELECT Estado FROM Productos WHERE Nombre = '$nombre' AND Color = '$color' AND Talle = '$talle'";
            $result = $this->db->query($query);

            if ($result->num_rows > 0) {
                //Validar que quede stock
                $sql = "SELECT Estado FROM Productos WHERE Nombre = '$nombre' AND Color = '$color' AND Talle = '$talle'";
                $result = $this->db->query($sql);
                $row = $result->fetch_assoc();
                $estadoProducto = $row['Estado'];

                if ($estadoProducto != "Inactivo") {
                    if ($estadoPedido === "Iniciado") {
                        $query = "INSERT INTO Contiene (idPedido, idProducto) VALUES ($idPedido, (SELECT idProducto FROM Productos WHERE Nombre = '$nombre' AND Color = '$color' AND Talle = '$talle'))";

                        if ($this->db->query($query) === TRUE) {
                            $sql = "UPDATE Pedidos SET Estado = 'Pendiente' WHERE Estado = 'Iniciado' and idUsuario = $idUsuario";
                            $this->db->query($sql);
                            echo "<script>confirm('El producto ha sido agregado correctamente al carrito');</script>";
                        } else {
                            echo "Error al agregar el producto a la tabla Contiene: " . $this->db->error;
                        }
                    } else if ($estadoPedido === "Pendiente") {
                        // Verificar si el producto ya existe en la tabla "Contiene"
                        $query = "SELECT idProducto FROM Contiene WHERE idPedido = $idPedido AND idProducto IN (SELECT idProducto FROM Productos WHERE Nombre = '$nombre' AND Color = '$color' AND Talle = '$talle')";
                        $resultado = $this->db->query($query);

                        if ($resultado->num_rows > 0) {
                            echo "<script>confirm('El producto ya se encuentra en el carrito.');</script>";
                        } else {
                            $query = "INSERT INTO Contiene (idPedido, idProducto) VALUES ($idPedido, (SELECT idProducto FROM Productos WHERE Nombre = '$nombre' AND Color = '$color' AND Talle = '$talle'))";

                            if ($this->db->query($query) === TRUE) {
                                echo "<script>confirm('El producto ha sido agregado correctamente al carrito');</script>";
                            } else {
                                echo "Error al agregar el producto a la tabla Contiene: " . $this->db->error;
                            }
                        }
                    }
                } else {
                    echo "<script>confirm('Actualmente no se encuentra stock del producto seleccionado');</script>";
                }
            } else {
                echo "<script>confirm('Actualmente no se encuentra stock del producto seleccionado');</script>";
            }
        }
    }


    public function getNombreProducto($idProductoSeleccionado)
    {
        $query = "SELECT nombre FROM productos WHERE idproducto = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('i', $idProductoSeleccionado);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();
                $nombreProducto = $fila['nombre'];
                $consulta->close();
                return $nombreProducto;
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


    public function obtenerColores($nombreProducto)
    {
        $query = "SELECT DISTINCT Color FROM productos WHERE nombre = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('s', $nombreProducto);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                $colores = array();
                while ($row = $resultado->fetch_assoc()) {
                    $colores[] = $row['Color'];
                }
                $consulta->close();
                return $colores;
            } else {
                $consulta->close();
                return array(); // Devolver un array vacío en lugar de false
            }
        } else {
            // Error en la consulta preparada
            echo "Error en la consulta: " . $this->db->error;
            return array(); // Devolver un array vacío en caso de error
        }
    }


    public function obtenerTalles($nombreProducto)
    {
        $query = "SELECT DISTINCT Talle FROM productos WHERE nombre = ?";
        $consulta = $this->db->prepare($query);

        if ($consulta) {
            $consulta->bind_param('s', $nombreProducto);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado->num_rows > 0) {
                $talles = array();
                while ($row = $resultado->fetch_assoc()) {
                    $talles[] = $row['Talle'];
                }
                $consulta->close();
                return $talles;
            } else {
                $consulta->close();
                return array(); // Devolver un array vacío en lugar de false
            }
        } else {
            // Error en la consulta preparada
            echo "Error en la consulta: " . $this->db->error;
            return array(); // Devolver un array vacío en caso de error
        }
    }
}
