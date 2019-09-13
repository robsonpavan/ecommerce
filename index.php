<?php 

//Iniciando o uso de sessão
if (!isset($_SESSION)){
    session_start();
}

//Configuração para trazer as dependencias
require_once("vendor/autoload.php");

//Definição das classes que deejo carregar
use \Slim\Slim;
use \Hcode\Pager;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

//Criando rota para interface de administração
$app->get('/admin', function() {
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
    
    //Carregando o Header - executando o construct
    $page = new PageAdmin();
    //Carregando o Index -executando setTPL
    $page->setTpl("index");
    //Ao final do comado carrega o Footer pois o destruct roda automáricamente no final - executando o destruct
});

//Criando rota para acesso a página de login
$app->get('/admin/login', function() {
    //Ao instanciar o objeto page é executado o construct da classe PageAdmin que extende a classe Pager, 
    //como a tela de login não tem o mesmo header e footer padrão das demais é necessário passar alguns parâmetros no momento do instanciamento para desabilita-los
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    //Carregando o Index -executando setTPL
    $page->setTpl("login");
    //Ao final do comado carrega o Footer pois o destruct roda automáricamente no final - executando o destruct
});

//Rota que será acionada pela página de logi (post do formulário) para realização do login no site administrativo
$app->post('/admin/login', function() {
    
    //Executando método estático login da classe User para realizar a autenticação do usuário (estão sendo passados o login e a senha capturados no formulário de login
    //A váriável $_POST["login"] captura o login do usuário e o parâemtro login passado na variável é o nome do campo que recebe o login (nome do campo input da página html)
    User::login($_POST["login"], $_POST["password"]);
    
    //Redirecionando para página principal da interface de administração (index.html) caso a autenticação tenha sido bem sucedida
    header("Location: /admin");
    exit; //Parando a execução
        

});


$app->get("/admin/logout", function (){
    
    //Executando o método estático para realizar logout
    User::logout();
    //Redirecionando para página de login
    header("Location: /admin/login");
    //Parando a execução para que a próxima página seja carregada
    exit;
 });

$app->run();

 ?>