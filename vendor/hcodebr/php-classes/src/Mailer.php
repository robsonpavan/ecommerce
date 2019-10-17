<?php

namespace Hcode;

/**
 * Description of Mailer
 *
 * @author robsonp
 * 
 * Classe para configurar o envio de e-mails
 * 
 */
use Rain\Tpl;

class Mailer {

    //Contante com usuário utilizado para enviar o e-mail
    const USERNAME = "robsonpavansistemas@gmail.com";
    const PASSWORD = "su3ht@m260885A";
    const NAME_FROM = "Robson Store";

    private $mail;

    //Metodo contrutor recebendo o endereço de e-mail, nome do destinátário, assunto , nome do template que será utilizado para enviar o e-mail e array com os dados (as informações)do e-mail 
    public function __construct($toAddress, $toName, $subject, $tplName, $data = array()) {

        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views/email/", //Pasta para pegar os arquivos de template html.  A variável de ambiente "$_SERVER["DOCUMENT_ROOT"]" trás local do diretório rootconfigurado no apache
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/", //Pasta para pegar os arquivos de cache.
            "debug" => false // set to false to improve the speed
        );

        //Carregando as configurações setas anteriormente
        Tpl::configure($config);

        //Instanciando o objeto Tpl para utilização de seus métodos
        $tpl = new Tpl;

        //Passar os dados para o template
        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);

        //Create a new PHPMailer instance
        $this->mail = new \PHPMailer;
        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();
        //Enable SMTP debugging
        $this->mail->SMTPDebug = 0;
        //Set the hostname of the mail server
        $this->mail->Host = 'smtp.gmail.com';

        $this->mail->Port = 587;
        //Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = 'tls';
        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Mailer::USERNAME;
        //Password to use for SMTP authentication
        $this->mail->Password = Mailer::PASSWORD;
        //Set who the message is to be sent from
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
        //Set an alternative reply-to address
        //$this->mail->addReplyTo(Mailer::USERNAME, Mailer::NAME_FROM);
        //Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);
        //Set the subject line
        $this->mail->Subject = $subject;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);
        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'This is a plain-text message body';
        //Attach an image file
        //$this->mail->addAttachment('images/phpmailer_mini.png');
    }

    public function send() {

        //send the message, check for errors
        if (!$this->mail->send()) {
            echo "Mailer Error: " . $this->mail->ErrorInfo;
        } else {
            echo "Message sent!";
            //Section 2: IMAP
            //Uncomment these to save your message in the 'Sent Mail' folder.
            #if (save_mail($this->mail)) {
            #    echo "Message saved!";
            #}
        }
    }

}
