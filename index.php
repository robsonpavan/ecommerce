<?php

//Iniciando o uso de sessão
if (!isset($_SESSION)) {
    session_start();
}

//Configuração para trazer as dependencias
require_once("vendor/autoload.php");

//Definição das classes que deejo carregar
use \Slim\Slim;

//Configurado Slim para definição das rotas
$app = new Slim();

require_once ('site.php');
require_once ('functions.php');
require_once ('admin.php');
require_once ('admin-users.php');
require_once ('admin-categories.php');
require_once ('admin-products.php');

$app->config('debug', true);

$app->run();
?>