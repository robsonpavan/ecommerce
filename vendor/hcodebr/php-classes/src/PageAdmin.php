<?php

namespace Hcode;

/**
 * Description of PageAdmin
 *
 * @author robsonp
 */
class PageAdmin extends Pager{
    
    //Método construtor
    public function __construct($opts = array(), $tpl_dir = "/views/admin/") {
      
        //Chamando o método construto da classe pai, pois a única diferêncça entre as classes é o caminho da váriável $tpl_dir
        parent::__construct($opts, $tpl_dir);
        
    }
    
}
