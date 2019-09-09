<?php 

require_once("vendor/autoload.php");

//Configurado Slim para definição das rotas
$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	//echo "OK";
    $sql = new Hcode\DB\Sql();
    
    $result = $sql->select("SELECT * FROM tb_users");

    echo json_encode($result);
    
});

$app->run();

 ?>