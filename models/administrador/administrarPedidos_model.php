<?php
require_once('./db/conectar.php');

class administrarPedidos_model
{
	private $db;
	private $pedidos;
	private $pedidosRealizados;


	public function __construct()
	{
		$this->db = Conectar::conexion();
		$this->pedidos = array();
		$this->pedidosRealizados = array();
	}

	public function getPedidos()
	{
		$query = "SELECT idPedido FROM Pedidos WHERE Estado = 'En proceso'";
		$consulta = $this->db->query($query);

		while ($filas = $consulta->fetch_assoc()) {
			$this->pedidos[] = $filas;
		}

		return $this->pedidos;
	}

	public function getPedidosRealizados()
	{
		$query = "SELECT idPedido FROM Pedidos WHERE Estado = 'Realizado'";
		$consulta = $this->db->query($query);

		while ($filas = $consulta->fetch_assoc()) {
			$this->pedidosRealizados[] = $filas;
		}

		return $this->pedidosRealizados;
	}

	public function confirmarPedido($idPedido)
	{
		$query = "UPDATE Pedidos SET Estado = 'Realizado' WHERE Estado = 'En proceso' and idPedido = ?";
		$consulta = $this->db->prepare($query);

		if ($consulta) {
			$consulta->bind_param("i", $idPedido);
			$consulta->execute();
		}
	}



	function restarCantidadProducto($idProducto)
	{
		$query = "SELECT Stock FROM Productos WHERE idProducto = ?";
		$consulta = $this->db->prepare($query);

		if ($consulta) {
			$consulta->bind_param("i", $idProducto);
			$consulta->execute();
			$result = $consulta->get_result();
			$fila = $result->fetch_assoc();
			$stock = $fila['Stock'];

			if ($stock == 1) {
				$query = "UPDATE Productos SET Stock = Stock - 1, Estado = 'Inactivo' WHERE idProducto = ?";
			} else {
				$query = "UPDATE Productos SET Stock = Stock - 1 WHERE idProducto = ?";
			}

			$consulta = $this->db->prepare($query);

			if ($consulta) {
				$consulta->bind_param("i", $idProducto);
				$consulta->execute();
				$consulta->close();
			}
		}
	}




	public function pedidoLevantado($idPedido)
	{
		//Restar el producto comprado
		$query = "SELECT p.Nombre, p.Color, p.Talle, p.idProducto FROM Productos p
        	INNER JOIN Contiene c ON c.idProducto = p.idProducto
    		WHERE c.idPedido = " . $idPedido;
		$consulta = $this->db->query($query);
		$productos = array();

		while ($filas = $consulta->fetch_assoc()) {
			$productos[] = $filas;
			$this->restarCantidadProducto($filas['idProducto']);
		}

		//Cambiar el estado del pedido
		$query = "UPDATE Pedidos SET Estado = 'Retirado' WHERE Estado = 'Realizado' and idPedido = ?";
		$consulta = $this->db->prepare($query);

		if ($consulta) {
			$consulta->bind_param("i", $idPedido);
			$consulta->execute();
		}
	}

	public function getProductosDelPedido($idPedido)
	{
		$query = "SELECT p.Nombre, p.Color, p.Talle FROM Productos p
			INNER JOIN Contiene c ON c.idProducto = p.idProducto
			WHERE c.idPedido = " . $idPedido;
		$consulta = $this->db->query($query);
		$productos = array(); // Inicializar la variable $productos

		while ($filas = $consulta->fetch_assoc()) {
			$productos[] = $filas;
		}

		return $productos;
	}
}
