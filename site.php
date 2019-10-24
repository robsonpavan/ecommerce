<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Hcode\Pager;
use \Hcode\Model\Category;
use Hcode\Model\Product;

//Configuração da rota '/'
$app->get('/', function() {
    
    //Instanciando objeto para carregar os produtos a partir do banco de dados
    $products = Product::listAll();
    
    //Carregando o Header - executando o construct
    $page = new Pager();
    //Carregando o Index -executando setTPL
    $page->setTpl("index", array(
        "products"=> Product::checklist($products)
    ));
    //Ao final do comado carrega o Footer pois o destruct roda automáricamente no final - executando o destruct
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
        'products'=> Product::checklist($category->getProducts())
    ]);
       
});

