<?php

//Llamada al modelo
require_once("./models/productos_model.php");



//Creamos un objeto de la clase personas_model
$producto = new productos_model();

session_start();
if (isset($_SESSION['usuario'])) {
	$idUsuario = $_SESSION['usuario'];
} else {
	$idUsuario = null;
}

if (isset($_POST['registro'])) {
	header('Location: registrarse.php');
	exit();
}

if (isset($_POST['productos'])) {
	session_start();
	$_SESSION['usuario'] = $idUsuario;
	header('location: administrar_productos.php');
	exit();
}

if (isset($_POST['registros'])) {
	session_start();
	$_SESSION['usuario'] = $idUsuario;
	header('location: administrar_registros.php');
	exit();
}

if (isset($_POST['pedidos'])) {
	session_start();
	$_SESSION['usuario'] = $idUsuario;
	header('location: administrar_pedidos.php');
	exit();
}

if (isset($_POST['volverInicio'])) {
	session_start();
	$_SESSION['usuario'] = $idUsuario;
	header('Location: index.php');
	exit();
}

if (isset($_POST['iniciar'])) {
	header('Location: iniciar_sesion.php');
	exit();
}

if (isset($_POST['abrirProducto'])) {
	$idProducto = $_POST['detallesProducto'];
	session_start();
	$_SESSION['usuario'] = $idUsuario;
	$_SESSION['producto'] = $idProducto;
	header('Location: producto.php');
	exit();
}

if (isset($_POST['abrirCarrito'])) {
	$idPedidoExistente = $producto->verificarIdPedido($idUsuario);
	$usuarioAprobado = $producto->getAprobacion($idUsuario);
	if ($usuarioAprobado == 1) {
		if ($idPedidoExistente) {
			$idPedido = $producto->getIdPedido($idUsuario);
			session_start();
			$_SESSION['pedido'] = $idPedido;
			header('Location: carrito.php');
			exit();
		} else {
			echo "<script>confirm('Agrega un producto al carrito antes de abrirlo');</script>";
		}
	} else {
		echo "<script>confirm('Aún no se a aceptado su registro');</script>";
	}
}



if (isset($_POST['abrirPedidos'])) {
	$idPedidoExistente = $producto->verificarPedidoExistente($idUsuario);
	if ($idPedidoExistente) {
		session_start();
		$_SESSION['usuario'] = $idUsuario;
		header('Location: historial_pedidos.php');
		exit();
	} else {
		echo "<script>confirm('Realiza un pedido antes de ingresar al historial');</script>";
	}
}




//Mediante el objeto invocamos al metodo getPersonas y guardamos
//filtros
if (isset($_POST['buscador'])) {
	$buscar = $_POST['buscador'];
	$datos = $producto->filtrarProductos($buscar, null, null, null);
} else {
	$filtroAplicado = false; // Variable para verificar si se aplicó algún filtro
	$datos = array();
	$precio = null;
	$marca = null;
	$categoria = null;
	$tipo = null;

	if (isset($_POST['filtroPrecio'])) {
		$precio = $_POST['filtroPrecio'];
		$filtroAplicado = true;
	}

	if (isset($_POST['miSelectCategoria'])) {
		$categoria = $_POST['miSelectCategoria'];
		$filtroAplicado = true;
	}

	if (isset($_POST['miSelectMarca'])) {
		$marca = $_POST['miSelectMarca'];
		$filtroAplicado = true;
	}

	if (isset($_POST['miSelect'])) {
		$tipo = $_POST['miSelect'];
		$filtroAplicado = true;
	}

	if (!$filtroAplicado) {
		$datos = $producto->getProductos();
	} else {
		$datos = $producto->filtrarProductos($tipo, $marca, $categoria, $precio);
	}
}

if (isset($_POST['cerrar'])) {
	session_start();
	$_SESSION['usuario'] = '';
	header("Location: index.php");
	exit();
}

if ($idUsuario == null) {
	require_once("./views/productos/productos_view.php");
} else {
	$tipoUsuario = $producto->getTipoUsuario($idUsuario);
	$notificacion = $producto->getEstadoPedido($idUsuario);
	if ($tipoUsuario == "Administrador") {
		require_once("./views/productos/productosAdmin_view.php");
	} else if ($tipoUsuario == "Cliente") {
		if ($producto->getEstadoPedido($idUsuario)) {
			echo "<script>confirm('Tiene un pedido listo para levantar!')</script>";
		}
		require_once("./views/productos/productosClient_view.php");
	}
}
