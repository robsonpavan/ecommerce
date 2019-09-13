<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hcode;

/**
 * Description of PageAdmin
 *
 * @author robso
 */
class PageAdmin extends Pager{
    
    //Método construtor
    public function __construct($opts = array(), $tpl_dir = "/views/admin/") {
        //Chamando o método construto da classe pai, pois a única diferêncça entre as classes é o caminho da váriável $tpl_dir
        parent::__construct($opts, $tpl_dir);
    }
    
}
