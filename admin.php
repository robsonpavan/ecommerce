<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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


