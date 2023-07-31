<?php
//cargo el modelo
require_once("../../models/detallesProducto_model.php");

//objeto de la clase
$dProducto = new detallesProducto_model();



session_start();
if (isset($_SESSION['usuario'])) {
    $idUsuario = $_SESSION['usuario'];
} else {
    $idUsuario = null;
}

//obtener idProducto del otro controlador
$detallesProducto = $_GET['detallesProducto'];

//cargar los datos del producto
$producto = $dProducto->getProducto($detallesProducto);

$nombreProducto = $dProducto->getNombreProducto($detallesProducto);
$colores = $dProducto->obtenerColores($nombreProducto);
$talles = $dProducto->obtenerTalles($nombreProducto);


if (isset($_POST['iniciarSesion'])) {
    header('Location: ../ingreso/inicioSesion_controller.php');
    exit();
}

//Agregar producto al carrito
if (isset($_POST['agregarAlCarrito'])) {
    $nombre = $_POST["nombreProducto"];
    $color = $_POST["selecColor"];
    $talle = $_POST["selecTalle"];
    $usuarioAprobado = $dProducto->getAprobacion($idUsuario);
    if ($usuarioAprobado) {
        $dProducto->AgregarProducto($idUsuario, $nombre, $color, $talle);
    } else {
        echo "<script>confirm('Aún no se a aceptado su registro');</script>";
    }
}

//Abrir carrito
if (isset($_POST['abrirCarrito'])) {
    $idPedidoExistente = $dProducto->verificarIdPedido($idUsuario);
    $usuarioAprobado = $dProducto->getAprobacion($idUsuario);
    if ($usuarioAprobado == 1) {
        if ($idPedidoExistente) {
            $idPedido = $dProducto->getIdPedido($idUsuario);
            session_start();
            $_SESSION['pedido'] = $idPedido;
            header('Location: ../carrito/carrito_controller.php');
            exit();
        } else {
            echo "<script>confirm('Agrega algun producto al carrito antes de abrirlo');</script>";
        }
    } else {
        echo "<script>confirm('Aún no se a aceptado su registro');</script>";
    }
}

if (isset($_POST['cerrar'])) {
    session_start();
    $_SESSION['usuario'] = '';
    header("Location: /totallook/");
    exit();
}

if (isset($_POST['volverInicio'])) {
    session_start();
    $_SESSION['usuario'] = $idUsuario;
    header('Location: /proyectoprogramacion');
    exit();
}

//cargo la vista
if ($idUsuario == null) {
    require_once("../../views/detallesProducto/detallesProductoNoRegistrado_view.php");
} else {
    $tipoUsuario = $dProducto->getTipoUsuario($idUsuario);
    if ($tipoUsuario == "Administrador") {
        require_once("../../views/detallesProducto/detallesProductoAdmin_view.php");
    } else if ($tipoUsuario == "Cliente") {
        require_once("../../views/detallesProducto/detallesProductoCliente_view.php");
    }
}
