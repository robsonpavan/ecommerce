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
use \Hcode\Mailer;

class User extends Model {
    
    //Constante definida para configurar o nome da sessão
    const SESSION = "User";
    //Constantes criadas para criptografar a cgave de recovery gerada para troca de senha (forgot)
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";

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
            //Utilizando o método get values para atribuir os valores do usuário na sessão.
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
    
    public static function listAll(){
        
        $sql = new Sql();
        
        //Comado SQL unindo 2 tabelas para pegar as informações existentes nas duas tabelas user e person (o campo idperson existe nas duas taelas)
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson;");
        
    }
    
    
    //Metotodo para gravar os dados de um novo usuário no BD
    public function save(){
              
        $sql = new Sql();
        
        //Váriável results recebendo o select que executa uma procedures no BD que inseri as informações nas tabelas person e user
        //Os gets são criados automáticamente pelo metodo setData da classe Model
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
            array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        
        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);
        
    }
    
    public function get($iduser){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser =  :iduser", array(
            ":iduser"=>$iduser            
        ));
        
        $this->setData($results[0]);
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        //Váriável results recebendo o select que executa uma procedures no BD que inseri as informações nas tabelas person e user
        //Os gets são criados automáticamente pelo metodo setData da classe Model
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
            array(
                ":iduser"=> $this->getiduser(),
                ":desperson"=>$this->getdesperson(),
                ":deslogin"=>$this->getdeslogin(),
                ":despassword"=>$this->getdespassword(),
                ":desemail"=>$this->getdesemail(),
                ":nrphone"=>$this->getnrphone(),
                ":inadmin"=>$this->getinadmin()
            ));

        
        //Armazenando no objeto o retorno do select realizado pela podecure
        $this->setData($results[0]);
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=> $this->getiduser()
        ));
        
    }
    
    //Metodo que recebe o e-mail da página esqueci a senha e verifica se ele existe no BD
    public static function getForgot($email){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING (idperson) WHERE a.desemail = :email;", array(
            ":email"=>$email
        ));
        
        //Testando se o e-,ail indormado foi encontrado
        if(count($results) === 0){
            
            //E-mail não foi encontrado retornando mensagem de erro
            throw new \Exception("Não foi possível recuperar a senha.");
            
        } else{
            
            $data = $results[0];
            
            //Criando registro de recuper~ção de senha para o usuários
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));
            
            //Testar se o procedimento de registro de recuperação de senha
            if(count($results2) === 0){
                
                throw new \Exception("Não foi possível recuperar a senha.");
                
            } else {
                
                $dataRecovery = $results2[0];
                
                //Encriptando o idRecover para enviar via e-mail para o usuário como link recadastrar a senha
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
		
                //Transformando para base64
                $code = base64_encode($code);
                               
                //Preparando link a ser enviado por e-mail para que o usuário possa alterar sua senha
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                
                $mailer = new Mailer($data["desemail"], $data["desperson"],"Redefinie senha na Robson Store", "forgot", array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                ));
                
                $mailer->send();
                
                //return $link;   
                return $data;
                
                
            }
            
        }
        
    }
    
    
    public static function validForgotDecrypt($code){
        
        $code = base64_decode($code); //Desfazendo a codificação da base64
	
        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV)); //Decriptando o ID recovery
        
        $sql = new Sql();
	
        //Verificando no BD se o idrecovery é válido, se ele ainda não foi utilizado e se está dentro do intervalo de tempo de 1 hora para alteração da senha
        $results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a 
            INNER JOIN tb_users b USING(iduser) 
            INNER JOIN tb_persons c USING(idperson)
            WHERE
                a.idrecovery = :idrecovery
                AND
                a.dtrecovery IS NULL
                AND
                DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
            ", array(
                ":idrecovery" => $idrecovery
        ));
    
        if (count($results) === 0 ){
            throw new \Exception("Não foi possível recuperar a senha.");
        } else{
            
            return $results[0];
            
        }
        
    }//Fim método validForgotDecrypt
    
    public static function setForgotUsed($idrecovery){
                
        $sql = new Sql();
        
        //Inserindo no BD a data quando foi realizado o reset da senha
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            "idrecovery"=>$idrecovery
        ));
                
    }//Fim do método setForgatUsed
    
    
    public function setPassword($password){
        
        $sql =new Sql();
        
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            "password"=>$password,
            "iduser"=> $this->getiduser()
        ));
        
        
    }//Fim do método setPassowrd
    
}
