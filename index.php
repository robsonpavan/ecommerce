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
use \Hcode\Model\Category;

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

    //Metodo estático para verificar (testar) o login do usuário (se ele está logado)
    User::verifyLogin();

    //Método estático para buscar todos os usuários existentes no banco de dados
    $users = User::listAll();

    //Instanciando o objeto PageAdmin carrregando o header e footer
    $page = new PageAdmin();

    //Definindo qual página deverá ser desenhada 
    //(Como não foram passados argumentos ao instânciar o objeto PageAdmin carregará com as configurações padrão e carregará no construct o header e no destruct o footer
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
    
    //Buscando as informações do usuário existentes no BD
    $user->get((int)$iduser);
    
    //Excluindo o usuário carregado no passo anterior
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
    
    //Buscando as informações do usuário existentes no BD
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


//Rota perdia senha forgot
$app->get("/admin/forgot", function(){
            
    //como a tela de login não tem o mesmo header e footer padrão das demais é necessário passar alguns parâmetros no momento do instanciamento para desabilita-los
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    //Carregando a ágina de login -executando setTPL
    $page->setTpl("forgot");
        
});

//Rota para enviar o e-mail
$app->post("/admin/forgot", function (){

    //Método da estático classe User para receber o e-mail do usuário passado pela pági a do esqueci a senha (forgot)
    $user = User::getForgot($_POST["email"]);
    
    header("Location: /admin/forgot/sent");
    exit;
    
});

//Rota para página que envia e-mail para resetar a senha
$app->get("/admin/forgot/sent", function (){
    
    //como a tela de login não tem o mesmo header e footer padrão das demais é necessário passar alguns parâmetros no momento do instanciamento para desabilita-los
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    //Carregando a ágina de login -executando setTPL
    $page->setTpl("forgot-sent");
    
});

//Rota para acessar página para resetar a senha
$app->get("/admin/forgot/reset", function (){
    
    $user = User::validForgotDecrypt($_GET["code"]); 
    
    //como a tela de login não tem o mesmo header e footer padrão das demais é necessário passar alguns parâmetros no momento do instanciamento para desabilita-los
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    //Carregando a página de login -executando setTPL e passando os parâmetros solicitados pela página de reset
    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]        
    ));
    
});

//Rota para chamar o metodos que altera a senha e redireciona para a página de confirmação de alteração de senha
$app->post("/admin/forgot/reset", function (){
    
    //Validando o código de recovery (idrecovery)
    $forgot = User::validForgotDecrypt($_POST["code"]); 
    //Registrando no BD que a alteração da senha foi realizada
    User::setForgotUsed($forgot["idrecovery"]);
    
    $user = new User();
    //Buscando as informaçoes do usuário
    $user->get((int)$forgot["iduser"]);
    //Criptografando a senha função password_hash(senha, Padrão de criptografia, custo poder computacional empregado para gerar o hash
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost"=>12
        ]);
    
    //Chamando método para alterar a senha
    $user->setPassword($password);
    
    //como a tela de login não tem o mesmo header e footer padrão das demais é necessário passar alguns parâmetros no momento do instanciamento para desabilita-los
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    //Carregando a página informando que o a senha foi alterada com sucesso
    $page->setTpl("forgot-reset-success");
    
});

//Rota para ac essar template de categorias
$app->get("/admin/categories", function(){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
   
    $categories = Category::listAll();
    
    $page = new PageAdmin();
    
    $page->setTpl("categories",[
        'categories'=>$categories
    ]);
    
});

//Rota para cadastrar categorias
$app->get("/admin/categories/create", function(){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
            
    $page = new PageAdmin();
    
    $page->setTpl("categories-create");
    
});



//Rota efetuar o cadastro da categoria no BD
$app->post("/admin/categories/create", function(){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
            
    $category = new Category();

    //Criandos os sets e gets com os dados digitados pelo usuário no formulário
    $category->setData($_POST);
    
    //Salvando a categoria
    $category->save();
    
    header("Location: /admin/categories");
    exit;
    
});

//Rota para excluir uma categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
    
    $category = new Category();
    
    //Carregando objeto para certificar-se que a categoria ainda existe no BD
    $category->get((int)$idcategory);
    
    //Deletando a categoria
    $category->delete();
    
    header("Location: /admin/categories");
    exit;
    
});


//Rota para editar uma categoria
$app->get("/admin/categories/:idcategory", function ($idcategory){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
 
    $category = new Category();
    
    //Carregando o objeto selecionado para edição. è feito cast do id para inteiro pois tudo que é carregado via url é convertido para texto
    $category->get((int)$idcategory);
    
    $page = new PageAdmin();
    
    //Carregando a página de update
    $page->setTpl("categories-update", [
        "category"=>$category->getValues()
    ]);
    
});


//Rota para efetuar a alteração da categoria
//Rota para editar uma categoria
$app->post("/admin/categories/:idcategory", function ($idcategory){
    
    //Metodo estático para verificar (testar) o login do uauário
    User::verifyLogin();
 
    $category = new Category();
    
    //Carregando o objeto selecionado para edição. è feito cast do id para inteiro pois tudo que é carregado via url é convertido para texto
    $category->get((int)$idcategory);
    
    //Alterando o objeto carregado com as informações vindas do formulário
    $category->setData($_POST);
    
    //Salvando as alterações no BD
    $category->save();
    
    header("Location: /admin/categories");
    exit;
    
});

//Rota para acesso às categorias acessadas via site
$app->get("/categories/:idcategory", function ($idcategory){
    
    $category = new Category();
    
    //Carregando o objeto selecionado para edição. è feito cast do id para inteiro pois tudo que é carregado via url é convertido para texto
    $category->get((int)$idcategory);  
        
    $page = new Pager();
    
    //Carregando a página da categoria, e passando as informações referentes a categoria selecionada
    $page->setTpl("category", [
        'category'=>$category->getValues(),
        'products'=>[]
    ]);
    
    
});


$app->run();
?>