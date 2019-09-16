<?php

//Iniciando o uso de sessão
if (!isset($_SESSION)) {
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
        "header" => false,
        "footer" => false
    ]);
    //Carregando a ágina de login -executando setTPL
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

//Rota para página de logout
$app->get("/admin/logout", function () {

    //Executando o método estático para realizar logout
    User::logout();
    //Redirecionando para página de login
    header("Location: /admin/login");
    //Parando a execução para que a próxima página seja carregada
    exit;
});

//Rota para tela que listará todos os usuários
$app->get("/admin/users", function () {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();

    //Método estático para buscar todos os usuários existentes no banco de dados
    $users = User::listAll();

    //Instanciando o objeto PageAdmin carrregando o header e footer
    $page = new PageAdmin();

    //Definindo qual página deverá ser desenhada 
    //(Como não foram passados argumentos ao instânciar o objeto PageAdmin carregará com as configurações padrão e carregará no construct o headre e no destruct o footer
    //Esta sendo passado como parâmetro um array com os dados retornados do banco de dados para que a clsse PageAdmin inclua no template (necessário ajustar o template para exibir as variáveis
    $page->setTpl("users", array(
        "users" => $users
    ));
});


//Rota para abrir a tela de createuser para cadastrar um um novo usuário
$app->get("/admin/users/create", function () {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();

    //Instanciando o objeto PageAdmin carrregando o header e footer
    $page = new PageAdmin();

    //Definindo qual página deverá ser desenhada 
    //(Como não foram passados argumentos ao instânciar o objeto PageAdmin carregará com as configurações padrão e carregará no construct o headre e no destruct o footer
    $page->setTpl("users-create");
});

//Rota para deletar um usuário. No momento de configurar a rota, se o caminho da página possuir um "padrão" (uma variável) semelhante ao de outra rota 
//a rota que tiver uma descrição após a váriável deve vir primeiro. Exemplo: A rota delete tem que estar antes da de salvar alteração porque senão a rota delete nuca será executata
$app->get("/admin/users/:iduser/delete", function($iduser) {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int)$iduser);
    
    $user->delete();
    
    //Encaminhando para página que lista os usuário após inserção do novo usuário
    header("Location: /admin/users");
    exit;
    
});

//Rota para update de usuários. Nessa rota é definido um padrão (inserindo :iduser) para receber o ID do usuário que será atualizado, 
//o valor passado no padrão :iduser será recebido na variável instanciada como parâmetro na função
$app->get("/admin/users/:iduser", function ($iduser) {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();

    $user = new User();
    
    $user->get((int)$iduser);
    
    //Instanciando o objeto PageAdmin carrregando o header e footer
    $page = new PageAdmin();

    //Definindo qual página deverá ser desenhada 
    //(Como não foram passados argumentos ao instânciar o objeto PageAdmin carregará com as configurações padrão e carregará no construct o headre e no destruct o footer
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

//Rota para salvar os dados do novo usuário (metodos para realizar o insert do usuário). 
//Essa rota tem o mesmo nome que uma outra acessada via get, o metodo de acesso definirá qual rota será seguida (post essa rota get a outra)
$app->post("/admin/users/create", function() {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();

    $user = new User();

    //Transformando o true ou false do checkbox em 1 ou 0 para armazenar no BD
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    //Ajuste no código para que o login com novos usuários funcione enquanto a criptografia de senha não é implementada
    //$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
      //  "cost" => 12
   // ]);
   
    $user->setData($_POST);

    $user->save();
    
    //Encaminhando para página que lista os usuário após inserção do novo usuário
    header("Location: /admin/users");
    exit;
    
});


//Rota para salvar a edição dos dados alterados do usuário.
$app->post("/admin/users/:iduser", function($iduser) {

    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
    
    $user = new User();
    
    //Carregando as informações existentes do usuários no BD
    $user->get((int)$iduser);
    
    //Transformando o true ou false do checkbox em 1 ou 0 para armazenar no BD
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    
    //Buscando as informações passadas pelo form (HTML) via post
    $user->setData($_POST);
    
    $user->update();
    
    //Encaminhando para página que lista os usuário após inserção do novo usuário
    header("Location: /admin/users");
    exit;
});




$app->run();
?>