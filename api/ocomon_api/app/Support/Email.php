<?php

namespace OcomonApi\Support;

require_once __DIR__ . "/" . "../Boot/Config.php";
require_once __DIR__ . "/" . "../Boot/Helpers.php";

use CoffeeCode\DataLayer\Connect;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * OcoMonAPI | Class Email
 */
class Email
{
    /** @var \stdClass */
    private $data;

    /** @var PHPMailer */
    private $mail;

    /** @var Message */
    private $message;

    /**
     * Email constructor.
     */
    public function __construct(?bool $smtpAuth = null, ?string $host = null, ?string $port = null, ?string $user = null, ?string $pass = null, ?string $secure = null)
    {
        $this->mail = new PHPMailer(true);
        $this->data = new \stdClass();
        $this->message = new Message();

        /**
         * Enable SMTP debugging
         * 0 = off (for production use)
         * 1 = client messages
         * 2 = client and server messages
         */
        $this->mail->SMTPDebug = 0;
        //setup
        $this->mail->isSMTP();
        $this->mail->setLanguage(CONF_MAIL_OPTION_LANG);
        $this->mail->isHTML(CONF_MAIL_OPTION_HTML);
        $this->mail->SMTPAuth = (isset($smtpAuth) ? $smtpAuth : CONF_MAIL_OPTION_AUTH);

        $secure = (isset($secure) ? noHtml($secure) : CONF_MAIL_OPTION_SECURE);

        if (!empty($secure)) {
            $this->mail->SMTPSecure = $secure;
        } else {
            /* Insecure */
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
        
        $this->mail->CharSet = CONF_MAIL_OPTION_CHARSET;

        //auth
        $this->mail->Host = (isset($host) ? noHtml($host) : CONF_MAIL_HOST);
        $this->mail->Port = (isset($port) ? noHtml($port) : CONF_MAIL_PORT);
        $this->mail->Username = (isset($user) ? noHtml($user) : CONF_MAIL_USER);
        $this->mail->Password = (isset($pass) ? noHtml($pass) : CONF_MAIL_PASS);
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $recipient
     * @param string $recipientName
     * @return Email
     */
    public function bootstrap(string $subject, string $body, string $recipient, string $recipientName, ?int $ticket = null): Email
    {
        $this->data->subject = $subject;
        $this->data->body = $body;
        $this->data->recipient_email = $recipient;
        $this->data->recipient_name = $recipientName;

        $this->data->ticket = ($ticket ? $ticket : null);

        if (!empty($this->data->ticket)) {
            $stmt = Connect::getInstance()->prepare(
                "SELECT 
                    `references_to`, 
                    `started_from`, 
                    `original_subject` 
                FROM 
                    tickets_email_references 
                WHERE 
                    ticket = :ticket"
            );

            try {
                $stmt->bindValue(":ticket", $this->data->ticket, \PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch();
                    $this->data->references = $row->references_to;
                    $this->data->started_from = $row->started_from;
                    $this->data->original_subject = $row->original_subject;
                }
            } catch (\PDOException $e) {
                $this->data->references = null;
                $this->data->started_from = null;
                $this->data->original_subject = null;
            }
        }

        return $this;
    }


    /**
     * @param string $filePath
     * @param string $fileName
     * @return Email
     */
    public function attach(string $filePath, string $fileName): Email
    {
        $this->data->attach[$filePath] = $fileName;
        return $this;
    }
    

    /**
     * @param string $from
     * @param string $fromName
     * @return bool
     */
    public function send(string $from = CONF_MAIL_SENDER['address'], string $fromName = CONF_MAIL_SENDER["name"]): bool
    {
        
        /* Configuração sobre habilitação de envio de emails */
        if (CONF_MAIL_SEND == 0) {
            return true;
        }

        if (empty($this->data)) {
            $this->message->error("Erro ao enviar, favor verifique os dados");
            return false;
        }

        if (!is_email($this->data->recipient_email)) {
            $this->message->error("O e-mail de destinatário não é válido");
            return false;
        }

        if (!is_email($from)) {
            $this->message->error("O e-mail de remetente não é válido");
            return false;
        }

        try {
            $this->mail->Subject = $this->data->subject;
            $this->mail->msgHTML($this->data->body);
            $this->mail->ClearAddresses(); 
            $this->mail->addAddress($this->data->recipient_email, $this->data->recipient_name);
            $this->mail->setFrom($from, $fromName);

            if (!empty($this->data->references) && $this->data->recipient_email == htmlspecialchars_decode($this->data->started_from, ENT_QUOTES)) {
                $this->mail->Subject = "Re: " . htmlspecialchars_decode($this->data->original_subject, ENT_QUOTES);
                $this->data->references = htmlspecialchars_decode($this->data->references, ENT_QUOTES);

                $this->mail->addCustomHeader('In-Reply-To', $this->data->references);
                $this->mail->addCustomHeader('References', $this->data->references);
            }

            if (!empty($this->data->attach)) {
                foreach ($this->data->attach as $path => $name) {
                    $this->mail->addAttachment($path, $name);
                }
            }

            // var_dump($this->data); exit;
            $this->mail->send();
            // $messageID = $this->mail->getLastMessageID();
            $this->setTicketMessageIdIfNotExists($this->mail->getLastMessageID());

            return true;
        } catch (Exception $e) {
            $this->message->error($e->getMessage());
            // exit; //Apenas para depuração
            return false;
        }
    }


    /**
     * Grava os emails em uma fila no banco de dados para serem enviados em outro momento
     * @param string $from
     * @param string $fromName
     * @return bool
     */
    public function queue(string $from = CONF_MAIL_SENDER['address'], string $fromName = CONF_MAIL_SENDER["name"]): bool
    {
        try {
            $stmt = Connect::getInstance()->prepare(
                    "INSERT INTO 
                        mail_queue (
                            ticket,
                            subject,
                            body,
                            from_email,
                            from_name,
                            recipient_email,
                            recipient_name
                        )
                    VALUES (
                        :ticket,
                        :subject,
                        :body,
                        :from_email,
                        :from_name,
                        :recipient_email,
                        :recipient_name
                    )"
            );

            $stmt->bindValue(":ticket", $this->data->ticket, \PDO::PARAM_INT);
            // $stmt->bindValue(":references_to", $this->data->references, \PDO::PARAM_STR);
            $stmt->bindValue(":subject", $this->data->subject, \PDO::PARAM_STR);
            $stmt->bindValue(":body", $this->data->body, \PDO::PARAM_STR);
            $stmt->bindValue(":from_email", $from, \PDO::PARAM_STR);
            $stmt->bindValue(":from_name", $fromName, \PDO::PARAM_STR);
            $stmt->bindValue(":recipient_email", $this->data->recipient_email, \PDO::PARAM_STR);
            $stmt->bindValue(":recipient_name", $this->data->recipient_name, \PDO::PARAM_STR);

            $stmt->execute();
            return true;
        }
        catch (Exception $e) {
            $this->message->error($e->getMessage());
            // return false;
            // echo $e->errorMessage();  //PHPMailer error messages
            return false;
        }
    }


    /**
     * Envia os e-mails da fila no banco de dados
     * @param int $perSecond : envios por segundo - pode variar de acordo com o provedor do serviço de envio
     * @return void
     */
    public function sendQueue(int $perSecond = 5): void
    {
        $stmt = Connect::getInstance()->query(
            "SELECT 
                m.*, 
                t.references_to as `references`
            FROM
                mail_queue m
                LEFT JOIN tickets_email_references t ON
                m.ticket = t.ticket
                WHERE sent_at IS NULL"
        );
        if ($stmt->rowCount()) {
            foreach ($stmt->fetchAll() as $send) {
                $email = $this->bootstrap(
                    $send->subject,
                    $send->body,
                    $send->recipient_email,
                    $send->recipient_name,
                    $send->ticket
                );

                if ($email->send($send->from_email, $send->from_name)) {
                    usleep(1000000 / $perSecond);
                    Connect::getInstance()->exec(
                        "UPDATE mail_queue SET sent_at = NOW() WHERE id = {$send->id} "
                    );
                }
            }
        }
    }


    /**
     * Tenta enviar o e-mail ignorando se a configuração de envios está habilitada
     * @param string $from
     * @param string $fromName
     * @return bool
     */
    public function sendTest(string $from = CONF_MAIL_SENDER['address'], string $fromName = CONF_MAIL_SENDER["name"]): bool
    {

        if (empty($this->data)) {
            $this->message->error("Erro ao enviar, favor verifique os dados");
            return false;
        }

        if (!is_email($this->data->recipient_email)) {
            $this->message->error("O e-mail de destinatário não é válido");
            return false;
        }

        if (!is_email($from)) {
            $this->message->error("O e-mail de remetente não é válido");
            return false;
        }

        try {
            $this->mail->Subject = $this->data->subject;
            $this->mail->msgHTML($this->data->body);
            $this->mail->addAddress($this->data->recipient_email, $this->data->recipient_name);
            $this->mail->setFrom($from, $fromName);

            if (!empty($this->data->attach)) {
                foreach ($this->data->attach as $path => $name) {
                    $this->mail->addAttachment($path, $name);
                }
            }

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            $this->message->error($e->getMessage());
            // exit; //Apenas para depuração
            return false;
        }
    }



    private function ticketHasMessageId(): bool
    {
        // echo "ticketHasMessageId" . PHP_EOL;
        // var_dump($this->data);
        
        if (empty($this->data->ticket)) {
            return false;
        }
        
        $stmt = Connect::getInstance()->prepare(
            "SELECT 
                ticket
            FROM
                tickets_email_references
            WHERE
                ticket = :ticket
            "
        );

        $stmt->bindValue(":ticket", $this->data->ticket, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function setTicketMessageIdIfNotExists(string $messageId): bool
    {
        // echo "setTicketMessageIdIfNotExists" . PHP_EOL;
        // var_dump(['messageId' => $messageId]);
        // var_dump($this->data);
        
        if (empty($this->data->ticket)) {
            return false;
        }

        if (empty($messageId)) {
            return false;
        }
        
        if (!$this->ticketHasMessageId()) {
            $contactEmail = $this->getTicketContactEmail();

            if (empty($contactEmail)) {
                return false;
            }
            
            $stmt = Connect::getInstance()->prepare(
                "INSERT INTO
                    tickets_email_references
                        (ticket, references_to, started_from, original_subject)
                VALUES
                    (:ticket, :references_to, :started_from, :subject)
                "
            );

            $stmt->bindValue(":ticket", $this->data->ticket, \PDO::PARAM_INT);
            $stmt->bindValue(":references_to", htmlspecialchars($messageId, ENT_QUOTES), \PDO::PARAM_STR);
            $stmt->bindValue(":started_from", $contactEmail, \PDO::PARAM_STR);
            $stmt->bindValue(":subject", $this->data->subject, \PDO::PARAM_STR);
            return $stmt->execute();
        }
        return false;
    }



    private function getTicketContactEmail(): string
    {
        // echo "getTicketContactEmail" . PHP_EOL;
        // var_dump($this->data);
        
        if (empty($this->data->ticket)) {
            return "";
        }

        $stmt = Connect::getInstance()->prepare(
            "SELECT 
                contato_email
            FROM
                ocorrencias
            WHERE
                numero = :ticket
            "
        );

        $stmt->bindValue(":ticket", $this->data->ticket, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }


    /**
     * @return PHPMailer
     */
    public function mail(): PHPMailer
    {
        return $this->mail;
    }

    /**
     * @return Message
     */
    public function message(): Message
    {
        return $this->message;
    }

}