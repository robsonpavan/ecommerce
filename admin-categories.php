<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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
