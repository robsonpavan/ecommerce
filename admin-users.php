<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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