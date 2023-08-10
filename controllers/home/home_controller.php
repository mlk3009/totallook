<?php
require_once("./models/home_model.php");

$flayersModel = new home_model(); // Cambiado el nombre de la instancia para que sea más descriptivo

session_start();
if (isset($_SESSION['usuario'])) {
    $idUsuario = $_SESSION['usuario'];
} else {
    $idUsuario = null;
}

if (isset($_POST['iniciar'])) {
    header('Location: iniciar_sesion.php');
    exit();
}

$datos = $flayersModel->getFlayers(); // Obtenemos los flayers desde el modelo

require_once("./views/home/home_view.php");
