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
 * Description of Product
 *
 * @author robsonp
 */
class Product extends Model {

    public static function listAll() {

        $sql = new Sql();

        //Comado SQL para listar todas as categorias
        return $sql->select("SELECT * FROM tb_products a ORDER BY a.desproduct;");
    }

//Fim do método listAll

    public function save() {

        $sql = new Sql();

        //Váriável results recebendo o select que executa uma procedures no BD que inseri as informações na tabela categories
        //Os gets são criados automáticamente pelo metodo setData da classe Model
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()
        ));
        $this->setData($results[0]);
    }

//Fim do método save

    public function get($idproduct) {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct" => $idproduct
        ]);

        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);
    }

//Fim do método get

    public function delete() {

        $sql = new Sql();

        //Deleta a categoria. Busca o ID da categoria no objeto carregado durante a execução do método anterior (get)
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct" => $this->getidproduct()
        ]);
    }

//Fim método delete
    //Método para verificar se existem fotos
    public function checkPhotos() {

        //Verificando se o arquivo da foto existe
        if (file_exists(
                        $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                        "res" . DIRECTORY_SEPARATOR .
                        "site" . DIRECTORY_SEPARATOR .
                        "img" . DIRECTORY_SEPARATOR .
                        "products" . DIRECTORY_SEPARATOR .
                        $this->getidproduct() . ".jpg"
                )) {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            $url = "/res/site/img/product.jpg";
        }
        return $this->setdesphoto($url);
    }

//Fim do método checkPhotos
    //"Reescrita" do método getValues para tratar as fotos  dos produtos
    public function getValues() {

        $this->checkPhotos();

        $values = parent::getValues();

        return $values;
    }

//Fim fuction getValues
    //Metodo para fazer upload do arquivo da foto
    public function setPhoto($file) {

        //Identificando o tipo de arquivo ****
        //Verificando a extensão
        $extension = explode('.', $file['name']);
        $extension = end($extension);
        switch ($extension) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
        }

        //Definido caminho e nome para imagem
        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                "res" . DIRECTORY_SEPARATOR .
                "site" . DIRECTORY_SEPARATOR .
                "img" . DIRECTORY_SEPARATOR .
                "products" . DIRECTORY_SEPARATOR .
                $this->getidproduct() . ".jpg";
        imagejpeg($image, $dist);
        imagedestroy($image);
        $this->checkPhoto();
    }

//Fim do método setPhotos
}

//Fim da classe


