<?php

namespace Hcode;

/**
 * Description of Model
 *
 * @author robsonp
 */

//Classe para realizar os Gets e Sets de froma dinâmica para todas as classes models
class Model {
   
    //Array para receber e armazenar todos os atributos do objeto
    //Ex. Para a calsse usuários (User) recebera os atributos de Id, login, seha, etc...
    private $values = [];

    //__call() é disparado ao invocar métodos inacessíveis em um contexto de objeto.
    //Ao chamar um método que "não existe" (não possui nome definido) num objeto, existindo método mágico __call configurado, 
    //O método __call atibuírá o nome utilizado para chamar o método que não existe para substituir o parâmetro $name e receberá os argumentos para execução do método no parâmetro $arguments
    //Possibilitando a criação de métodos dinâmicos
    public function __call($name, $arguments) {

        //Detectando se o método chamado é get ou set para isso é necessário utilizar as funções de string para ler o nome do método chamado e identificar o tipo
        //A função substr pega partes de uma string a partir da posição indicada até a quantidade de posições que você indicar
        //Abaixo está buscando, a partir da posição 0, 3 posições e armazenando na variável $method (assim identifica se éstá sendo executado um get ou set
        $method = substr($name, 0, 3);

        //O proximo passo é identificar o nome do atributo que o método get ou set pretence.
        //Ex. se o método chamado fosse getIdUsuario, no comando acima identificou que o método é get e agora deve identificar qual o atributo será retornado, no caso IdUsuario
        //A função strlen conta a quantidade de posições da string
        $fieldname = substr($name, 3, strlen($name));

        //Swicth com código para execução do get ou set conforme o metodo chamado
        switch ($method) {

            //Configuração do get para retornar o valor do atributo
            case "get":
                return $this->values[$fieldname];
                break;
            //Configurando o set para setar o atributo $arguments é o valor passado para o atributo
            case "set":
                $this->values[$fieldname] = $arguments[0];
                break;
            
        }
        
    }

    //Função para criar os sets de todos os atributos vindos do banco dinâmicamente
    public function setData($data = array()) {

        //Foreach para varrer todos os dados retornados do banco e executando o set do atributo (gerando o nome dinâmico do método e criando o atributo e seu valor no array $values
        foreach ($data as $key => $value) {

            //Para criar o set dinâmico é necessário concatenar o termo set com o nome do atributo para isso, (tudo que se cria dinâmicamente no PHP deve estar entre {} (cahves))
            //após o $this-> colocar entre chaves os "valores" (set e nomedo do atributo) concatenados
            //$key = nome do argumento e $value = valor atribuído ao argumento.
            $this->{"set" . $key}($value);
        }
        
    }

    //Metodo gest para retornar o valor de todos os atributos
    public function getValues() {

        //retornando o array value com os atributos setados
        return $this->values;
    
        
    }

    
}
