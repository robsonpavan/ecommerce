<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Rota para acessar página de produtos
$app->get("/admin/products", function () {

    User::verifyLogin();

    $products = Product::listAll();

    $page = New PageAdmin();

    $page->setTpl("products", array(
        "products" => $products
    ));
});

//Rota para página de criação de produtos
$app->get("/admin/products/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();

    //Definindo qual página deverá ser desenhada 
    $page->setTpl("products-create");
});

//Rota pra salvar novos produtos
$app->post("/admin/products/create", function () {

    User::verifyLogin();

    $products = new Product();

    //Capturando os dados do formulário e inserindo no objeto products
    $products->setData($_POST);

    $products->save();

    //Encaminhando para página que lista os produtos
    header("Location: /admin/products");
    exit;
});

//Rota para editar o produto
$app->get("/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();

    $product->get((int) $idproduct);

    $page = New PageAdmin();

    //Passado as informações do produto carregado no objeto na linha 65 ($product->get((int)$idproduct);)
    $page->setTpl("products-update", array(
        "product" => $product->getValues()
    ));
});

//Rota para salvar as alterações no produto
$app->post("/admin/products/:idproduct", function($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int) $idproduct);
    $product->setData($_POST);
    $product->save();
    $product->setPhoto($_FILES["file"]);
    header('Location: /admin/products');
    exit;
});

//Rota para excluir os produtos]
$app->get("/admin/products/:idproduct/delete", function($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int) $idproduct);
    $product->delete();
    header('Location: /admin/products');
    exit;
});

