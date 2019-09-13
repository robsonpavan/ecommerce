<?php

/**
 * Description of Pager
 *
 * @author robsonp
 */

namespace Hcode;

//Carregando micriframework para parte de templates
use Rain\Tpl;

class Pager {

    //Declarando arqgumentos da classe
    private $tpl;
    
    //Array criado para fazer o merge entre o array default e o que´pessado como parâmetro no metodo setData
    private $options = [];
    
    //O atributo default vai receber as configurações padrão
    //Na chave header - recebendo valor padrão para exibição do header padrão
    //Na chave footer - recebendo valor para exibição do footer padrão
    private $defaults = [
        "header"=>true, 
        "footer"=>true, 
        "data" => []
    ];

    //Metodo (mágico) construtor - A variável $opts é um array que vai receber as opções configuração específicas de cada rota configurada e o diretório tpl para diferenciar os templates do site e interface de administração
    public function __construct($opts = array(), $tpl_dir = "/views/") {

        //O array options está recebendo o merge entre os arrays default e opts, 
        //caso tenha algum parãmetro conflitante nos arrays as informações o opts irá sobrescrever as do default devido a ordem que estão setados na função array_merge
        $this->options = array_merge($this->defaults, $opts);

        //Configuração para os templates - Para funcionar os templates é necessário indicar a pasta pegar os arquivos de template html  e uma pasta para cache
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . $tpl_dir, //Pasta para pegar os arquivos de template html.  A variável de ambiente "$_SERVER["DOCUMENT_ROOT"]" trás local do diretório rootconfigurado no apache
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/", //Pasta para pegar os arquivos de cache.
            "debug" => false // set to false to improve the speed
        );

        //Carregando as configurações setas anteriormente
        Tpl::configure($config);

        //Instanciando o objeto Tpl para utilização de seus métodos
        $this->tpl = new Tpl;
        
        //Setando as variáveis que serão passadas de acordo com a rota
        $this->setData($this->options["data"]);
        
        //Testando se ao instanciar a classe foi passado parâmetro para desabilitar o header (por padrão é setado como treu)
        if ($this->options["header"] === true){
            //Desenhado a página (o template) na tela
            $this->tpl->draw("header");
        }    
    }

    //Metodo para passagem dos dados para construção da página
    private function setData($data = array()) {
        
        //Laço para passar os dados para o Tpl
        foreach ($this->options["data"] as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }
    
    //Metodo para setar o conteúdo do template, o parâmetro $name vai receber o nome do template a ser desenhado, 
    //o array data receberá o conteúdo da página e o parâmetro $returnHTML definirá se o html será retornado ou desenhado na tela por padrão será desenhado na tela
    public function setTpl($name, $data = array(), $returnHTML = false) {
        
        //Pegando os dados do array e passando para oassing para construção da página
        $this->setData($data);
        
        //Desenhado o corpo da página e retornando o html quando o parâmetro $returnHTML for true
        return $this->tpl->draw($name, $returnHTML);
    }

    //Metodo (mágico) destrutor executado automaticamente no final da execução da classe
    public function __destruct() {
        
        //Testando se ao instânciar a classe foi passado parâmetro para desabilitar o footer padrão
        if($this->options["footer"] === true){
            
            //Desenhado o rodapé do template na página
            $this->tpl->draw("footer");
        }
    }

}
