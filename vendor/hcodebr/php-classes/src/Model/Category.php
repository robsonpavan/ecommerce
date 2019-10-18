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
        
    }//Fim método delete
    
    
}//Fim da Classe
