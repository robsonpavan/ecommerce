<?php

namespace Hcode\Model;

/**
 * Description of User
 *
 * @author robsonp
 */

//Como a classe Sql encontra-se em outro namespace está sendo realizada a declaração de importação
use Hcode\DB\Sql;
use Hcode\Model;

class User extends Model {
    
    //Constante definida para configurar o nome da sessão
    const SESSION = "User";

    //Metodo para realizar o login - Busca o login no banco, se encontrar criptografa a senha digitada e compara os hashs
    public static function login ($login, $password){
        
        //Instanciando objeto Sql para consulta ao banco de dados (para acessar o BD)
        $sql = new Sql();
        
        //Executando a consulta no banco utilizando array para o bind da variável para evitar sql injection
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));
        
        //Testando se o login pesquisado foi encontrado no banco de dados
        //Utilizado o metodo count para ver quanto elemento existem no array, se for 0 é poque não encontrou o login e é necessário dispara exception
        if(count($results) === 0 ){
            
            //Necessário inserir a "\" (contrabarra) antes da declaração da Exception porque ela se encontra no namespace principal do PHP 
            //(não foi criada uma exception própria neste namespace por isso deve-se indicar o caminho onde se encontra Exception nesse caso o namespace a padrão)
            throw new \Exception("Usuário inexistente ou senha inválida.");
            
        }
        
        //Capturando os dados do usuário (login e senha) para validação da senha
        $data = $results[0];
        
        //A funcção password_verify compara uma senha no formato de string com um hash e retorna true se igual ou false de diferente
        //Realizando teste se a senha digitada está correta
        if (password_verify($password, $data["despassword"]) === true ){
            
            //Por ser um método estático está sendo gerada ima instância da própria classe Users
            $user = new User();
            
            //Chamando método setData que receber as informações trazidas do Banco de dados e monta os nomes de métodos dinâmicamente 
            //para que não seja necessário criar várias chamadas de metodo para cada um dos atributos do objeto
            $user->setData($data);
              
            //Criando sessão para receber os dados de login. O nome da sessão está sendo passado por meio da constante SESSION criada no objeto User.
            //Utilizando o método get values para atribuir os valores do usuário na sessão
            $_SESSION[User::SESSION] = $user->getValues();
            
            return ($user);
            
        } else {
            
            throw new \Exception("Usuário inexistente ou senha inválida.");
            
        }
        
    }   
    
    //Método para verificar se o logins e senha informados conferem (se o usuário está logado ou não)
    public static function verifyLogin($inadmin = true){
        
        //If pra testar se o usuário está logado ou não
        if(
            !isset($_SESSION[User::SESSION]) //Verificando se existe a Session com a constante session ou não (se a sessão não foi criada/  definida)
            ||
            !$_SESSION[User::SESSION] //Verificando se a sessão, apesar de existe, não está vazia
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 //Verificar o Id do usuário, converte o id carregado na sessão para inteiro,
            ||                                           // se o id for vazio transforma em zero em seguida basta testar se o id é maior que zero para verificar se há um id válido ou não           
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //Verificando se o usuário se logou para acessar a área de administração (se o usuário é administrador logado na área de administração)
                                                                   //Verifica se o atributo inadmin é true ou não, se for false o usuário não tem privilégios administrativos
        ){
            //Redirecionando para teala de login
            header("Location: /admin/login");
            //Encerrando a execução
            exit;
        }
        
    }
    
    //Metodo para realizar o logoff
    public static function logout(){
        
        //Tornando se sessão User nulla
        $_SESSION[User::SESSION] = null;
        
    }
    
    
}
