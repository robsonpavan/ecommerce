<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hcode\Model;

/**
 * Description of User
 *
 * @author robsonp
 */

//Como a classe Sql encontra-se em outro namespace está sendo realizada a declaração de importação
use Hcode\DB\Sql;
use Hcode\Model;
use \Hcode\Mailer;
use Hcode\Model\User;

/**
 * Description of Cart
 *
 * @author robsonp
 */
class Cart extends Model{
    
    const SESSION = "Cart";
    
    //Método para verificar se precisa inseriri carrinho novo, se já exixtes, etc...
    public static function getFromSession(){
        
        $cart = new cart();
        
        //Verifica se o carrinho já está na sessão - isset($_SESSION[Cart::SESSION])
        //Verificar se dentro da sessão existe o id do carrinho e se ele é maior que zero
        //Será verdadeiro se a sessão existir com o carrinho e se o id do carrinho for maior que zero
        if(isset($_SESSION[Cart::SESSION]) && ((int)$_SESSION[Cart::SESSION]['idcart']>0) ){
            
            //carregando carrinho que já existe no BD e já foi carregado na sessão
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
            
        } else{ //Tentar recuperar o carrinho a partir do session id que fica no BD
            
            $cart->getFromSessionID();
            
            //Verificando se ele conseguiu recupera o carrinho
            if (!(int)$cart->getidcart() > 0){
                
                //Criando um novo carrinho
                $data = [
                  'dessessionid'=> session_id()
                ];
                
                //Testando se o usuário está logado
                if(User::checkLogin(false)){
                    
                    //Recuperando informações do usuário
                    $user = User::getFromSession();
                    //Inserindo no array as infromações do usuário
                    $data['iduser'] = $user->getiduser();
                }
                
                //Inserindo as informaçãoes no carrinho
                $cart->setData($data);
               
                //Salvando o carrinho no BD
                $cart->save();
                
                //Inserindo o carrionho na sessão
                $cart->setToSession();
                
            }    
                                
        }
         
        return $cart;
        
    }//Fim do método getFromSession

    
    //Método para inserir o carrinho na sessão (método não é estático por causa da necessidade de uso do "$this->"
    public function setToSession(){
        
        $_SESSION[Cart::SESSION] = $this->getValues();        
        
    }//Fim do método setToSession




    //Metodo para buscar no BD o carrinho a partir do sessionID
    public function getFromSessionID(){
        
        $sql = new Sql();
        
        //A funão session_id() retorna o id da sessão corrente
        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ":dessessionid"=> session_id()
        ]);
        
        if(count($results) > 0){
            //Armazenando no objeto o retorno do select realizado pela podecure
            $this->setData($results[0]);
        }
        
    }//Fim do método getFromSessionID
    

    public static function listAll(){
        
        $sql = new Sql();
        
        //Comado SQL para listar todas as categorias
        return $sql->select("SELECT * FROM tb_carts a ORDER BY a.iduser;");
        
    }//Fim do método listAll
    
    
    public function save(){
        
        $sql = new Sql();
        
        //Váriável results recebendo o select que executa uma procedures no BD que inseri as informações na tabela cart
        //Os gets são criados automáticamente pelo metodo setData da classe Model
        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", 
            array(
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getfreight(),
            ":nrdays"=>$this->getnrdays(),    
        ));
        
        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);       
        
    }//Fim do método save
    
    
    public function get(int $idcart){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ":idcart"=>$idcart
        ]);
        
        if(count($results) > 0){
            //Armazenando no objeto o retorno do select realizado pela podecure
            $this->setData($results[0]);
        }
        
    }//Fim do método get
    
    
    public function delete(){
        
        $sql = new Sql();
        
        //Deleta um carrinho.
        $sql->query("DELETE FROM tb_carts WHERE idcart = :idcart", [
            ":idcart"=> $this->getidcart()
        ]);
                
    }//Fim método delete
    
    
}
