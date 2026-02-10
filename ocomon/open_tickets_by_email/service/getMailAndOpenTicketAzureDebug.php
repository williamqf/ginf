<?php
require __DIR__ . "/" . "../php-imap/vendor/autoload.php";

require __DIR__ . "/" . "../email-reply-parser/src/autoload.php";

require __DIR__ . "/" . "../ocomon_api_access/src/OcomonApi.php";
require __DIR__ . "/" . "../ocomon_api_access/src/Tickets.php";
require __DIR__ . "/" . "../config/config.php";

use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use EmailReplyParser\Parser\EmailParser;
use ocomon_api_access\OcomonApi\Tickets;


if (ALLOW_OPEN_TICKET_BY_EMAIL != '1') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Settings: Opening tickets by email is not enabled'
    ]);
    return;
}

if (IMAP_PROVIDER && IMAP_PROVIDER != 'AZURE') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Settings: IMAP provider is not Azure'
    ]);
    return;
}

$exception = "";
$tmp_dir = sys_get_temp_dir();
$tmp_dir = rtrim($tmp_dir, '/');

$cert = (MAIL_GET_CERT == '0' ? false : true);

$url = "https://login.microsoftonline.com/".IMAP_OAUTH_TENANT_ID."/oauth2/v2.0/token";

$param_post_curl = [
    'client_id' => IMAP_OAUTH_CLIENT_ID,
    'client_secret' => IMAP_OAUTH_CLIENT_SECRET,
    'refresh_token' => IMAP_OAUTH_REFRESH_TOKEN,
    'grant_type' => 'refresh_token'
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param_post_curl));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//ONLY USE CURLOPT_SSL_VERIFYPEER AT FALSE IF YOU ARE IN LOCALHOST !!!
if (isLocalhost()) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // NOT IN LOCALHOST ? ERASE IT !
}

$oResult = curl_exec($ch);

echo ("Obtendo autorização.... \n");

if (!empty($oResult)) {

    echo ("Conectando à caixa de entrada... \n");

    //The token is a JSON object
    $array_php_resul = json_decode($oResult, true);

    if (isset($array_php_resul["access_token"])) {

        $access_token = $array_php_resul["access_token"];

        //$cm = new ClientManager($options = ["options" => ["debug" => true]]);                     
        $cm = new ClientManager();
        $client = $cm->make([
            'host'          => MAIL_GET_IMAP_ADDRESS, //'outlook.office365.com',
            'port'          => MAIL_GET_PORT, //993
            'encryption'    => 'ssl',
            'validate_cert' => $cert,
            'username'      => MAIL_GET_ADDRESS,
            'password'      => $access_token,
            'protocol'      => 'imap',
            'authentication' => "oauth"
        ]);

        try {
            //Connect to the IMAP Server
            $client->connect();

            echo "<br/>Conectado! Protocolo IMAP (".MAIL_GET_IMAP_ADDRESS.") em " . date("d/m/Y H:i:s") . "<br/><br/>\n";

            /**
             * Dados para a API - Tickets
             */
            $tickets = new Tickets(
                API_OCOMON_ADDRESS,
                API_USERNAME,
                API_APP,
                API_TOKEN
            );


            // Obter a caixa de entrada
            $folder = $client->getFolder(MAIL_GET_MAILBOX, "/");
            if (!$folder) {
                $folder = $client->createFolder($folder = MAIL_GET_MAILBOX, $expunge = true);
            }
            if (!$folder) {
                echo "Folder " . MAIL_GET_MAILBOX . " not found";
                return;
            }

            $folderToMove = $client->getFolder(MAIL_GET_MOVETO);
            if (!$folderToMove) {
                $folderToMove = $client->createFolder(MAIL_GET_MOVETO, $expunge = true);
            }

            if (!$folderToMove) {
                echo "Folder " . MAIL_GET_MOVETO . " not found";
                return;
            }

            
            /** @var \Webklex\PHPIMAP\Query\WhereQuery $query */
            /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
            $filter = [];
            $filterSubject = (MAIL_GET_SUBJECT_CONTAINS ? ["subject" => MAIL_GET_SUBJECT_CONTAINS] : null);
            $filterBody = (MAIL_GET_BODY_CONTAINS ? ["text" => MAIL_GET_BODY_CONTAINS] : null);

            if ($filterSubject) {
                $filter[] = $filterSubject;
            }
            if ($filterBody) {
                $filter[] = $filterBody;
            }

            if (!empty(array_filter($filter))) {
                $messages = $folder->query()->where($filter)->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->limit(10, 1)->get();
                // $messages = $folder->query()->where($filter)->where($filter2)->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->limit(10, 1)->get();


                $count = $folder->query()->where($filter)->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->count();
                // $count = $folder->query()->where($filter)->where($filter2)->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->count();

            } else {
                $messages = $folder->query()->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->limit(10, 1)->get();
                $count = $folder->query()->unseen()->since(\Carbon\Carbon::now()->subDays(MAIL_GET_DAYS_SINCE))->count();
            }

            if ($count < 1) {
                echo "Nenhuma mensagem encontrada no endereço [" . MAIL_GET_ADDRESS . "] de acordo com os critérios de pesquisa." . PHP_EOL;
                return;
            }
            echo "Foram encontradas " . $count . " mensagens de acordo com os critérios de pesquisa<br/>\n";


            // Iterar sobre as mensagens
            /** @var \Webklex\PHPIMAP\Query\WhereQuery $query */
            /** @var \Webklex\PHPIMAP\Message $message */
            $counter = 0;

            $messagesIds = [];
            $duplicatedMessages = [];
            foreach ($messages as $message) {
                
                $header = $message->getHeader();

                $messageId = $message->getMessageId();
                // Ensure message_id is correctly formatted
                if (strpos($messageId, '<') !== 0) {
                    $messageId = '<' . $messageId . '>';
                }

                /* Ignorar mensagens que já foram abertas */
                if (in_array($messageId, $messagesIds)) {
                    $duplicatedMessages[] = $messageId;
                    continue;
                }
                $messagesIds[] = $messageId;

                $counter++;
                echo "<br/>-----------------------------------<br/>\n";
                echo "Mensagem número " . $counter . "<br/>\n";
                echo "-----------------------------------<br/>\n";


                $domain = $message->getFrom()[0]->host;
                $clientInfoFromDomain = getClientInfoFromDomain($conn, $domain);

                $findTicket = findTicketByEmailReferences($conn, $messageId);
                if ($findTicket) {
                    echo "Mensagem ignorada - Já existe um chamado ({$findTicket['ticket']}) aberto por essa mensagem: " . $message->getFrom()[0]->mail . " - " . $message->getFrom()[0]->personal . " - " . $message->getSubject() . PHP_EOL;

                    /* Apenas para depuração - remover o 'continue'*/
                    // continue;

                    /* Marcar como visualizada */
                    try {
                        $message->setFlag('Seen');
                        echo "<br/>Message marked as read <br/>" . PHP_EOL;
                    } catch (\Exception $e) {
                        echo "<br/>Erro ao marcar a mensagem como lida: " . $e->getMessage() . "<br/>" . PHP_EOL;
                    }

                    /* Mover a mensagem */
                    try {
                        $message->move(MAIL_GET_MOVETO);
                        echo "<br/>Message moved <br/>" . PHP_EOL;
                    } catch (\Exception $e) {
                        echo "<br/>Erro ao mover a mensagem: " . $e->getMessage() . "<br/>" . PHP_EOL;
                    }

                    continue;
                }


                /* Em produção, alterar para false o terceiro parâmetro */
                $requesterInfo = getUserByEmail($conn, $message->getFrom()[0]->mail, isLocalhost()); 

                if (!$requesterInfo && EMAIL_TICKETS_ONLY_FROM_REGISTERED) {
                    echo "Mensagem ignorada - Remetente não cadastrado: " . $message->getFrom()[0]->mail . " - " . $message->getFrom()[0]->personal . " - " . $message->getSubject() . PHP_EOL;
                    continue;
                }

                if ($requesterInfo && EMAIL_TICKETS_ONLY_FROM_REGISTERED && $requesterInfo['level'] > 3) {
                    echo "Mensagem ignorada - Remetente desabilitado: " . $message->getFrom()[0]->mail . " - " . $message->getFrom()[0]->personal . " - " . $message->getSubject() . PHP_EOL;
                    continue;
                }


                $files = [];
                $attachments = $message->getAttachments();
                /** @var \Webklex\PHPIMAP\Attachment $attachment */
                foreach ($attachments as $attachment) {

                    $uniqueName = uniqid() . '_' . $attachment->getName();
            
                    $tmp_path_and_name = $tmp_dir . '/' . $uniqueName;
            
                    file_put_contents($tmp_path_and_name, $attachment->getContent());
            
                    $files['name'][] = $attachment->getName();
                    $files['tmp_name'][] = $tmp_path_and_name;
                }


                echo "<b>Subject:</b> " . $message->getSubject() . "<br/>\n";
                echo "<b>From:</b> " . $message->getFrom()[0]->mail . "<br/>\n";
                echo "<b>Name:</b> " . $message->getFrom()[0]->personal . "<br/>\n";
                echo "<b>Domain:</b> " . $message->getFrom()[0]->host . "<br/>\n";
                echo "<b>Date:</b> " . $message->getDate() . "<br/>\n";
                echo "<b>Message ID:</b> " . $message->getMessageId() . "<br/>\n";
                echo "<b>Text Body:</b> " . $message->getTextBody() . "<br/>\n";
                echo "<b>HTML Body:</b> " . $message->getHTMLBody() . "<br/>\n";
                echo "-----------------------------------<br/>\n";

                /* Apenas para depuração (exibição das mensagens) - para abrir os chamados será necessário remover o 'continue' */
                continue;

                /* Só abrirá chamado se não for e-mail de resposta */
                if (!$header->in_reply_to[0] || empty($header->in_reply_to[0])) {
                    /* Não é resposta de mensagem */

                    // echo "<br/>Não é resposta! <br/>\n";

                    $description = "";
                    $description .= $message->getSubject() . "\n";
                    // $description .= $message->getTextBody();
                    $description .= $message->getHTMLBody();
                    // $description .= $message->getHtmlBody();
                    $description = trim(toHtml($description));
            
                    /* Checar se é uma mensagem automática */
                    // if (isAutoResponse((array)$header, $message->getSubject())) {
                    //     echo "Mensagem ignorada - E-mail automático: " . $message->getFrom()[0]->mail . " - " . $message->getFrom()[0]->personal . " - " . $message->getSubject() . PHP_EOL;
                    //     continue;
                    // }


            
                    $ticketClient = API_TICKET_BY_MAIL_CLIENT;
                    if ($clientInfoFromDomain && $clientInfoFromDomain['id']) {
                        $ticketClient = $clientInfoFromDomain['id'];
                    }
                    $department = null;
                    $phone = null;
                    $requester = null;
                    $contact = null;
            
                    if ($requesterInfo) {
                        $ticketClient = ($requesterInfo['user_client'] ? $requesterInfo['user_client'] : $ticketClient);
                        $department = $requesterInfo['user_department'] ?? null;
                        $phone = $requesterInfo['phone'] ?? null;
                        $requester = $requesterInfo['user_id'] ?? null;
                        $contact = $requesterInfo['name'] ?? null;
                    }
            
                    $ticketData = [];
                    $ticketData['client'] = $ticketClient;
                    $ticketData['requester'] = $requester;
                    $ticketData['description'] = $description;
                    $ticketData['contact'] = $contact ?? $message->getFrom()[0]->personal;
                    $ticketData['contact_email'] = $message->getFrom()[0]->mail; 
                    $ticketData['department'] = $department;
                    $ticketData['phone'] = $phone;
                    $ticketData['channel'] = API_TICKET_BY_MAIL_CHANNEL;
                    $ticketData['area'] = API_TICKET_BY_MAIL_AREA;
                    $ticketData['status'] = API_TICKET_BY_MAIL_STATUS;
                    $ticketData['input_tag'] = API_TICKET_BY_MAIL_TAG;
                    $ticketData['files[]'] = $files;
                    $ticketData = array_filter($ticketData);
                    
                    var_dump($ticketData);

                    continue; //depuração apenas - deve ser removido para o prosseguimento do script

                    /**
                     * Abertura do chamado
                     */
                    $create = $tickets->create($ticketData);

                    /* Se nao ocorrer erro, então movo a mensagem */
                    if (!empty($create->response()->ticket)) {
            
                        $exception = "";
                        echo "Ticket created: {$create->response()->ticket->numero} " . PHP_EOL;
                        /* Gravar o id da mensagem na tabela tickets_email_references */
                        setTicketEmailReferences($conn, [
                            'ticket' => $create->response()->ticket->numero,
                            'references_to' => $messageId,
                            'started_from' => $message->getFrom()[0]->mail,
                            'original_subject' => $message->getSubject(),
                        ]);
            
                        /* Marcar como visualizada */
                        if (MAIL_GET_MARK_SEEN && MAIL_GET_MARK_SEEN == '1') {
                            $message->setFlag('Seen');
                        }
                        
                        try {
                            $message->move(MAIL_GET_MOVETO);
                            echo "<br/>Message moved <br/>" . PHP_EOL;
                        }
                        catch (Exception $e) {
                            $exception .= "<hr>" . $e->getMessage();
                            echo "Message could not be moved " . PHP_EOL;
                            echo $exception;
                        }
                        
                        echo json_encode(['ticket' => $create->response()->ticket], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        var_dump($create->response()); //debug
                    } else {
                        echo "No ticket created " . PHP_EOL;
                        var_dump($create->response());
                    }                    

                } else {
                    // echo "<br/>Message is a reply - " . PHP_EOL;
                    if (isset($header->references[0]) && !empty($header->references[0])) {
                        
                        $references = $header->references[0];
            
                        if (strpos($references, '<') !== 0) {
                            $references = '<' . $references . '>';
                        }
                    }

                    /* Buscar para ver se existe um chamado para a mensagem original */
                    $findTicket = findTicketByEmailReferences($conn, $references);
                    // var_dump([
                    //     'findTicket' => $findTicket,
                    // ]);

                    if (!$findTicket) {
                        echo "No ticket found for this message " . PHP_EOL;
                        continue;
                    }
                    echo "Ticket found: {$findTicket['ticket']} - " . PHP_EOL;

                    $messageParsed = (new EmailParser())->parse($message->getTextBody());
                    $visibleText = \EmailReplyParser\EmailReplyParser::parseReply($message->getTextBody());

                    // $description = toHtml($message->getBodyText());
                    $description = $visibleText;
                    /**
                     * Comentário no chamado
                     */
                    
                    $entryData = [];
                    $entryData['author']  = null;
                    if ($requesterInfo) {
                        $entryData['author'] = $requesterInfo['user_id'];
                    }
                    $entryData['comment'] = $description;
                    $entryData['comment_type'] = 33; /* código reservado */
                    $entryData['ticket'] = $findTicket['ticket'];
                    $entryData['files[]'] = $files;
                    $entryData = array_filter($entryData);
                    $entryData['asset_privated'] = 0;

                    $comment = $tickets->comment($entryData);

                    if (!$comment->response()) {
                        echo "No comment created " . PHP_EOL;
                        return;
                    }
                    
                    if (isset($comment->response()->errors)) {
                        echo "No comment created - Comment error: {$comment->response()->errors->message} " . PHP_EOL;

                        if (isset($comment->response()->code) && $comment->response()->code == 428) {
                            /* Marcar como visualizada */
                            if (MAIL_GET_MARK_SEEN && MAIL_GET_MARK_SEEN == '1') {
                                $message->setFlag('Seen');
                            }
                            /* Mover a mensagem */
                            $message->move(MAIL_GET_MOVETO);
                        }
                        continue;
                    }

                    if (isset($comment->response()->id)) {
                        echo "Comment created: {$comment->response()->id} " . PHP_EOL;
                    } else {
                        echo "Some unexpected error - No comment created " . PHP_EOL;
                        return;
                    }

                    /* Marcar como visualizada */
                    if (MAIL_GET_MARK_SEEN && MAIL_GET_MARK_SEEN == '1') {
                        $message->setFlag('Seen');
                    }
                    /* Mover a mensagem */
                    $message->move(MAIL_GET_MOVETO);

                }

                echo "----------------------------------------------" . PHP_EOL;
                
            }
            // $client->disconnect();

        } catch (Exception $e) {
            echo 'Exception : ',  $e->getMessage(), "\n";
        }
    } else {
        echo ('Error : ' . $array_php_resul["error_description"]);
    }
}