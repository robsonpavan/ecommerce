<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hcode\Model;

//Como a classe Sql encontra-se em outro namespace está sendo realizada a declaração de importação
use Hcode\DB\Sql;
use Hcode\Model;
use \Hcode\Mailer;

/**
 * Description of Category
 *
 * @author robsonp
 */
class Category extends Model {
    
    public static function listAll(){
        
        $sql = new Sql();
        
        //Comado SQL para listar todas as categorias
        return $sql->select("SELECT * FROM tb_categories a ORDER BY a.descategory;");
        
    }//Fim do método listAll
    
    
    public function save(){
        
        $sql = new Sql();
        
        //Váriável results recebendo o select que executa uma procedures no BD que inseri as informações na tabela categories
        //Os gets são criados automáticamente pelo metodo setData da classe Model
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", 
            array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));
        
        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);
        
        //Atualizando arquivo html com a relação das categorias
        Category::updateFile();
        
    }//Fim do método save
    
    
    public function get($idcategory){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=>$idcategory
        ]);
        
        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);
        
    }//Fim do método get
    
    
    public function delete(){
        
        $sql = new Sql();
        
        //Deleta a categoria. Busca o ID da categoria no objeto carregado durante a execução do método anterior (get)
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=> $this->getidcategory()
        ]);
        
        //Atualizando arquivo html com a relação das categorias
        Category::updateFile();
        
    }//Fim método delete
    
    
    public static function updateFile(){
        
        $categories = Category::listAll();
        
        $html = [];
        
        //Criando estrutura html para ser inserida no arquivo
        foreach ($categories as $row){
            array_push($html, '<li><a href="/categories/'.$row["idcategory"].'">'.$row["descategory"].'</a></li>');
        }
        
        //Escrevendo no arquivo html de categorias. Necessário informar o camonho absoluto do arquivo, para isso serão usdas das variáveis globais de ambiente. 
        //O conteúdo do array $html tem que ser convetido para string para ser inserido no arquivo
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html));
        
    }//Fim do método updateFile
    
    //Método para trazer todos os produtos
    public function getProducts($related = true){
        
        $sql = new Sql();
        
        if ($related === true){
            
            return $sql->select("
                            SELECT * FROM tb_products WHERE idproduct IN (
                                SELECT a.idproduct
                                FROM tb_products a
                                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                                WHERE b.idcategory = :idcategory
                            );", [
                                ':idcategory'=> $this->getidcategory()
                            ]);    
            
        } else{
            
            return $sql->select("
                            SELECT * FROM tb_products WHERE idproduct NOT IN (
                                SELECT a.idproduct
                                FROM tb_products a
                                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                                WHERE b.idcategory = :idcategory
                            );", [
                                ':idcategory'=> $this->getidcategory()
                            ]); 
            
        }
        
        
    }//Fim do método getProducts
    
    //Método para adicionar um produto a uma categoria
    public function addProduct (Product $product){
        
        $sql = new Sql();
        
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
            ':idcategory'=> $this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
        
    }//Fim do método addProduct
    
    //Método para remove um produto de uma categoria
    public function removeProduct (Product $product){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
            ':idcategory'=> $this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
        
    }//Fim do método removeProduct
    
    
}//Fim da Classe
