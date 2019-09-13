<?php 

//Configuração para trazer as dependencias
require_once("vendor/autoload.php");

//Definição das classes que deejo carregar
use \Slim\Slim;
use \Hcode\Pager;
use \Hcode\PageAdmin;

//Configurado Slim para definição das rotas
$app = new Slim();

$app->config('debug', true);

//Configuração da rota '/'
$app->get('/', function() {
    //Carregando o Header - executando o construct
    $page = new Pager();
    //Carregando o Index -executando setTPL
    $page->setTpl("index");
    //Ao final do comado carrega o Footer pois o destruct roda automáricamente no final - executando o destruct
});

$app->get('/admin', function() {
    //Carregando o Header - executando o construct
    $page = new PageAdmin();
    //Carregando o Index -executando setTPL
    $page->setTpl("index");
    //Ao final do comado carrega o Footer pois o destruct roda automáricamente no final - executando o destruct
});


$app->run();

 ?>