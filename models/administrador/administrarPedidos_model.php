<?php
	require_once('../../db/conectar.php');

	class administrarPedidos_model{
		private $db;
		private $pedidos;
		private $pedidosRealizados;


		public function __construct(){
			$this->db = Conectar::conexion();
			$this->pedidos = array();
			$this->pedidosRealizados = array();
		}

		public function getPedidos(){
			$sql = "SELECT idPedido FROM Pedidos WHERE Estado = 'En proceso'";
				$consulta = $this->db->query($sql);
				
				while($filas = $consulta->fetch_assoc()){
					$this->pedidos[] = $filas;
				}
				
				return $this->pedidos;
		}

		public function getPedidosRealizados(){
			$sql = "SELECT idPedido FROM Pedidos WHERE Estado = 'Realizado'";
				$consulta = $this->db->query($sql);
				
				while($filas = $consulta->fetch_assoc()){
					$this->pedidosRealizados[] = $filas;
				}
				
				return $this->pedidosRealizados;
		}

		public function confirmarPedido($idPedido){
			$sql = "UPDATE Pedidos SET Estado = 'Realizado' WHERE Estado = 'En proceso' and idPedido = $idPedido";
			$this->db->query($sql);
		}


		function restarCantidadProducto($idProducto) {
			$sql = "SELECT Stock FROM Productos WHERE idProducto = " . $idProducto;
			$consulta = $this->db->query($sql);
			$fila = $consulta->fetch_assoc();
			$stock = $fila['Stock'];
		
			if ($stock == 1) {
				$sql = "UPDATE Productos SET Stock = Stock - 1, Estado = 'Inactivo' WHERE idProducto = " . $idProducto;
			} else {
				$sql = "UPDATE Productos SET Stock = Stock - 1 WHERE idProducto = " . $idProducto;
			}
		
			$this->db->query($sql);
		}



		public function pedidoLevantado($idPedido){
			//Restar el producto comprado
			$sql = "SELECT p.Nombre, p.Color, p.Talle, p.idProducto FROM Productos p
        	INNER JOIN Contiene c ON c.idProducto = p.idProducto
    		WHERE c.idPedido = " . $idPedido;
			$consulta = $this->db->query($sql);
			$productos = array(); 

			while ($filas = $consulta->fetch_assoc()) {
    			$productos[] = $filas;
    			$this->restarCantidadProducto($filas['idProducto']);
			}

			//Cambiar el estado del pedido
			$sql = "UPDATE Pedidos SET Estado = 'Retirado' WHERE Estado = 'Realizado' and idPedido =".$idPedido;
			$this->db->query($sql);
		}

		public function getProductosDelPedido($idPedido){
			$sql = "SELECT p.Nombre, p.Color, p.Talle FROM Productos p
			INNER JOIN Contiene c ON c.idProducto = p.idProducto
			WHERE c.idPedido = " . $idPedido;
			$consulta = $this->db->query($sql);
			$productos = array(); // Inicializar la variable $productos
	
			while($filas = $consulta->fetch_assoc()){
				$productos[] = $filas;
			}
	
			return $productos;
		}
	}
?>