<?php
require_once('./db/conectar.php');

class administrarProductos_model
{

	//Declaramos atributos privados
	private $db;
	private $productos;

	//Declaramos un constructor que sirve para incializar los atributos
	public function __construct()
	{

		//Asignamos al atributo db el valor del metodo conexion() de la clase Conectar
		//Conectar es la clase y conexion es el metodo
		$this->db = Conectar::conexion();
		//Determinamos que el atributo personas será un array
		$this->productos = array();
	}

	//Cargar Productos
	function insertProductos($nombre, $categoria, $precio, $stock, $color, $talle, $directorio_destino, $nombre_fichero)
	{

		$tmp_name = $_FILES[$nombre_fichero]['tmp_name'];

		if (is_dir($directorio_destino) && is_uploaded_file($tmp_name)) {

			$img_file = $_FILES[$nombre_fichero]['name'];
			$img_type = $_FILES[$nombre_fichero]['type'];

			if (((strpos($img_type, "gif") || strpos($img_type, "jpeg") || strpos($img_type, "jpg")) || strpos($img_type, "png"))) {
				if (move_uploaded_file($tmp_name, $directorio_destino . '/' . $img_file)) {

					$ruta = "/proyectoprogramacion/imagenes/imagenesProductos/" . $img_file;



					$query = "INSERT INTO Productos(nombre,idCategoria,precio,stock,color,talle, Imagen, Estado) VALUE ('$nombre','$categoria',$precio,$stock,'$color','$talle','$ruta', 'Activo')";

					if (!$resultado = ($this->db->query($query))) {
						print "<script>alert(\"No se pudo realizar el alta.\");window.location.href = window.location.href;</script>";
						exit;
					} else {
						print "<script>alert(\"Datos guardados en la DB.\");window.location.href = window.location.href;</script>";
					}
					return true;
				}
			}
		}
		return false;
	}



	//Eliminar productos (completar)
	public function deleteProductos($Nombre)
	{

		$query = "UPDATE Productos SET Estado = 'Inactivo' WHERE Nombre = '$Nombre'";

		if ($this->db->query($query)) {
			header("Location: " . $_SERVER['PHP_SELF']);
			exit;
			return true;
		} else {
			return false;
		}
	}

	public function addProductos($Nombre)
	{

		$query = "UPDATE Productos SET Estado = 'Activo' WHERE Nombre = '$Nombre'";

		if ($this->db->query($query)) {
			header("Location: " . $_SERVER['PHP_SELF']);
			exit;
			return true;
		} else {
			return false;
		}
	}

	public function obtenerColores()
	{
		$query = "SELECT DISTINCT Color FROM Productos";
		$consulta = $this->db->query($query);

		if ($consulta->num_rows > 0) {
			$colores = array();
			while ($row = $consulta->fetch_assoc()) {
				$colores[] = $row['Color'];
			}
			return $colores;
		} else {
			return false;
		}
	}

	//Editar producto (completar)
	public function updateProductos($id, $nombre)
	{

		$query = "UPDATE Personas SET Nombre = '$nombre' WHERE id = $id";
		if ($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}

	public function getProductosList()
	{
		$query = "SELECT Productos.Imagen, MIN(idProducto) AS idProducto, MIN(Nombre) AS Nombre, Categoria.Categoria, MIN(Precio) AS Precio, MIN(Estado) AS Estado, MIN(nuevoPrecio) AS nuevoPrecio
			FROM Productos
			JOIN Categoria ON Productos.idCategoria = Categoria.idCategoria
			GROUP BY Productos.Imagen, Categoria.Categoria;";
		$consulta = $this->db->query($query);

		while ($filas = $consulta->fetch_assoc()) {
			$this->productos[] = $filas;
		}

		return $this->productos;
	}

	public function getModProductosList($Nombre)
	{
		$query = "SELECT Productos.Imagen, idProducto, Nombre, Categoria.Categoria, Precio, Estado, Stock, Color, Talle, Productos.idCategoria
			FROM Productos
			JOIN Categoria ON Productos.idCategoria = Categoria.idCategoria
			WHERE Nombre = '$Nombre';";
		$consulta = $this->db->query($query);

		while ($filas = $consulta->fetch_assoc()) {
			$this->productos[] = $filas;
		}

		return $this->productos;
	}

	// Ver bien que cosas se van a poder modificar
	public function modificarProductos($modstock, $modcolor, $modtalle, $idProducto)
	{

		$query = "UPDATE Productos SET Stock = '$modstock', Color = '$modcolor', Talle = '$modtalle' WHERE idProducto = $idProducto";

		if ($this->db->query($query)) {

			header("Location: " . $_SERVER['PHP_SELF']);
			exit;

			return true;
		} else {
			return false;
		}
	}


	public function modificarPrecio($precio, $nombre)
	{

		$query = "UPDATE Productos SET Precio = '$precio' WHERE Nombre = '$nombre'";

		if ($this->db->query($query)) {

			header("Location: " . $_SERVER['PHP_SELF']);
			exit;

			return true;
		} else {
			return false;
		}
	}




	public function agregarOtroProducto($addstock, $addcolor, $addtalle, $nombre, $idcategoria, $precio, $estado, $imagen)
	{
		echo "Valores recibidos:<br>";
		echo "addstock: " . $addstock . "<br>";
		echo "addcolor: " . $addcolor . "<br>";
		echo "addtalle: " . $addtalle . "<br>";
		echo "nombre: " . $nombre . "<br>";
		echo "idcategoria: " . $idcategoria . "<br>";
		echo "precio: " . $precio . "<br>";
		echo "estado: " . $estado . "<br>";
		echo "imagen: " . $imagen . "<br>";

		// Resto del código
		$query = "INSERT INTO Productos (Nombre, idCategoria, Precio, Stock, Estado, Talle, Color, Imagen, descuento, nuevoPrecio)
			VALUES ('$nombre', '$idcategoria', '$precio', '$addstock', '$estado', '$addtalle', '$addcolor', '$imagen', '', '');";

		if ($this->db->query($query)) {
			header("Location: " . $_SERVER['PHP_SELF']);
			exit;
			return true;
		} else {
			return false;
		}
	}

	public function enDescuentos($descuento, $nombre)
	{
		$descuento = $this->db->real_escape_string($descuento);
		$nombre = $this->db->real_escape_string($nombre);

		$query = "UPDATE Productos SET descuento = '$descuento' WHERE Nombre = '$nombre'";

		if ($this->db->query($query)) {
			header("Location: " . $_SERVER['PHP_SELF']);
			exit;
			return true;
		} else {
			return false;
		}
	}

	public function setNuevoPrecio($descuento, $nombre)
	{
		$descuento = $this->db->real_escape_string($descuento);
		$nombre = $this->db->real_escape_string($nombre);

		// Obtener el precio actual del producto
		$query = "SELECT Precio FROM Productos WHERE Nombre = '$nombre'";
		$result = $this->db->query($query);

		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$precioActual = $row['Precio'];

			$nuevoPrecio = $precioActual * (1 - ($descuento / 100));

			$updateQuery = "UPDATE Productos SET nuevoPrecio = '$nuevoPrecio' WHERE Nombre = '$nombre'";
			if ($this->db->query($updateQuery)) {
				return true;
				header("Location: " . $_SERVER['PHP_SELF']);
				exit;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
