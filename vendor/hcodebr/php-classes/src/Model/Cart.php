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
    const SESSION_ERROR = "CartError";
    
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
            ":vlfreight"=>$this->getvlfreight(),
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
    
    //Método para adicionar um produto no carrinho
    public function addProduct (Product $product){
        
        $sql = new Sql();
        
        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct);", [
            ':idcart'=> $this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);
        
        //Atualizando os valores do carrinho
        $this->getCaulculateTotal();

    }//Fim do método addProduct
    
    //Método para remover um produto do carrinho
    public function removeProduct(Product $product, $all = false){
        
        $sql = new Sql();
        
        if ($all){
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",[
                ":idcart"=> $this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);
        }else{
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1;",[
                ":idcart"=> $this->getidcart(),
                ":idproduct"=>$product->getidproduct()
            ]);
        }        
        
        //Atualizando os valores do carrinho
        $this->getCaulculateTotal();

    }//Fim do método removeProduct
    
    //Método para listar os produtos do carrinho
    public function getProducts() {
        
        $sql = new Sql();
        
        $rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
                                FROM tb_cartsproducts a
                                INNER JOIN tb_products b ON a.idproduct = b.idproduct
                                WHERE a.idcart = :idcart AND a.dtremoved IS NULL
                                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                                ORDER BY b.desproduct;", 
                              [
                                ':idcart'=>$this->getidcart()
                              ]);
        return Product::checklist($rows);        
        
        
    }//Fim do método getProducts
    

    //Método para listar os produtos do carrinho
    public function getProductsTotals() {
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT SUM(a.vlprice) AS vlprice, SUM(a.vlwidth) AS vlwidth, SUM(a.vlheight) AS vlheight, SUM(a.vllength) AS vllength, SUM(a.vlweight) AS vlweight, COUNT(*) AS nrqtd
                                FROM tb_products a
                                INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
                                WHERE b.idcart = :idcart AND b.dtremoved IS NULL;", 
                              [
                                ':idcart'=>$this->getidcart()
                              ]);


        if (count($results) > 0) {
            return $results[0];
        } else {
            return [];
        }

    }//Fim do método getProductsTotals
    
    //Método para calcular o frete
    public function setFreight($nrzipcode){

        $nrzipcode = str_replace('-','',$nrzipcode);

        $totals = $this->getProductsTotals();

        if($totals['nrqtd'] > 0){

            //Validando altura e comprimento conforme os valores mínimos exigidos pelo correio par este serviço/formato
            if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if ($totals['vllength'] < 16) $totals['vllength'] = 16;

            //Função (http_build_query) utilizada para prepar a query para passar paâmetros via get
            //Passando os parâmetros necessários para consulta do frete via correios conforme manual de implementação do correio
            $qs = http_build_query([
                 'nCdEmpresa'=> '',
                 'sDsSenha'=> '',
                 'nCdServico'=> '40010', //código do tipo de serviço/envio código retirado do manual implementação do correio
                 'sCepOrigem'=> '82930080',
                 'sCepDestino'=> $nrzipcode,
                 'nVlPeso'=> $totals['vlweight'],
                 'nCdFormato'=> '1', //formato de entrega (1 – Formato caixa/pacote 2 – Formato rolo/prisma 3 - Envelope) retirado do manual de implementação do correio 
                 'nVlComprimento'=> $totals['vllength'],
                 'nVlAltura'=> $totals['vlheight'],
                 'nVlLargura'=> $totals['vlwidth'],
                 'nVlDiametro'=> '0',
                 'sCdMaoPropria'=> 'S',
                 'nVlValorDeclarado'=> $totals['vlprice'],
                 'sCdAvisoRecebimento'=> 'S'
            ]);

            // $qs = http_build_query([

			// 	'nCdEmpresa'=>'',
			// 	'sDsSenha'=>'',
			// 	'nCdServico'=>'40010',
			// 	'sCepOrigem'=>'09853120',
			// 	'sCepDestino'=>$nrzipcode,
			// 	'nVlPeso'=>$totals['vlweight'],
			// 	'nCdFormato'=>'1',
			// 	'nVlComprimento'=>$totals['vllength'],
			// 	'nVlAltura'=>$totals['vlheight'],
			// 	'nVlLargura'=>$totals['vlwidth'],
			// 	'nVlDiametro'=>'0',
			// 	'sCdMaoPropria'=>'S',
			// 	'nVlValorDeclarado'=>$totals['vlprice'],
			// 	'sCdAvisoRecebimento'=>'S'
			// ]);
           
            //simple_load_file é uma funcção utilizada para realizar a leitura de arquivos xml - esta função retorna um objeto
            //recebendo arquivo xml de resposta do webservice do correio
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
            
            //Capturando os objetos retornados na consulta ao webservice dos correios e atribuindo à variável $result
            $result = $xml->Servicos->cServico;

            //Verificando se houve algum erro na consulta e retornando o erro para o usuário
            if($result->MsgErro !== ''){
                Cart::setMsgError($result->MsgErro);
            } else{
                Cart::clearMsgError();
            }

            //Atribuindo os valores retornados pela consulta (Prazo de entrega e Valor do frete) e o cep no carrinho
            $this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);
            
            //Salvando as informações coletadas (Valore Frete, Prazo, CEP) no BD (TB_CartsProducts)
            $this->save();
            $teste = (array)$result;

            return $result;

        } else{

        }

    } //Fim do método setFreight

    //Método para converter valores para o formato do BD (substitui a virgula da casa decimal por ponto)
    public static function formatValueToDecimal($value):float {

        str_replace('.','',$value);
        return str_replace(',','.',$value);

    } //Fim do método formatValueToDecimal

    //Metodo para definir a sessão para passagem da mensagem de erro da consulta do frete nos correios
    public static function setMsgError($msg){

        $_SESSION[Cart::SESSION_ERROR] = $msg;

    }//Fim do método setMsgError

    //Metodo para pegar na sessão a mensagem de erro da consulta do frete nos correios
    public static function getMsgError()
	{
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
    	Cart::clearMsgError();
        return $msg;
               
    }//Fim do método getMsgError

    //Metodo para limpar a sessão com a mensagem de erro da consulta do frete nos correios
    public static function clearMsgError(){

        $_SESSION[Cart::SESSION_ERROR] = NULL;

    }//Fim do método clearMsgError

    //Método para atualizar o calculo fo frete quando houver mudanças no  carrinho
    public function updateFreight(){

        if ($this->getdeszipcode() != ''){
            
            $this->setFreight($this->getdeszipcode());

        }

    }//Fim do método updateFreight

    //Método extendido getValues para adicção da atualização dos valores no carrinho
    public function getValues(){

        $this->getCaulculateTotal();

        return parent::getValues();

    } //Fim do método extendido getValues

    //Método para adicionar o subtotal e o valor total no objeto cart 
    public function getCaulculateTotal(){

        //Atualizando o valor do frete
        $this->updateFreight();
        //Buscando os totais
        $totals = $this->getProductsTotals();
        //Atrinuindo aos subtotais o preço total dos produtos
        $this->setvlsubtotal($totals['vlprice']);
        //Somando o subtotao com o valor do frete
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());

    } //Fim do método getCaulculateTotal

} //Fim da classe