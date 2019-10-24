<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Função para formatar preço
function formatPrice(float $vlprice){
    
    return number_format($vlprice, 2, ",", ".");
    
}
