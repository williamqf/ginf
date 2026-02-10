<?php
// require __DIR__ . "/" . "../../../api/ocomon_api/vendor/autoload.php";

if (version_compare(phpversion(), '8.1', '<' )) {
    require __DIR__ . "/../ddeboer_imap_older/vendor/autoload.php";
} else {
    require __DIR__ . "/../ddeboer_imap/vendor/autoload.php";
}

require __DIR__ . "/" . "../email-reply-parser/src/autoload.php";

require __DIR__ . "/" . "../ocomon_api_access/src/OcomonApi.php";
require __DIR__ . "/" . "../ocomon_api_access/src/Tickets.php";
require __DIR__ . "/" . "../config/config.php";

use EmailReplyParser\Parser\EmailParser;

use ocomon_api_access\OcomonApi\Tickets;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Message\Headers;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Body;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Date\Since;
use Ddeboer\Imap\Search\Flag\Unseen;
use Ddeboer\Imap\Search\Text\Subject;


if (ALLOW_OPEN_TICKET_BY_EMAIL != '1') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Settings: Opening tickets by email is not enabled'
    ]);
    return;
}

if (IMAP_PROVIDER && IMAP_PROVIDER == 'AZURE') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Settings: IMAP provider is Azure. Use other retriever script'
    ]);
    return;
}

$exception = "";
$tmp_dir = sys_get_temp_dir();
$tmp_dir = rtrim($tmp_dir, '/');

$cert = (MAIL_GET_CERT == '0' ? '/novalidate-cert' : '');

/**
 * @var \Ddeboer\Imap\Server $server
 * Definir essas configurações
 */
$server = new Server(
    MAIL_GET_IMAP_ADDRESS,
    MAIL_GET_PORT,
    '/imap/ssl' . $cert
);

/**
 * Dados para a API - Tickets
 */
$tickets = new Tickets(
    API_OCOMON_ADDRESS,
    API_USERNAME,
    API_APP,
    API_TOKEN
);

/**
 * @var \Ddeboer\Imap\Connection $connection
 */

try {
    $connection = $server->authenticate(MAIL_GET_ADDRESS, MAIL_GET_PASSWORD);
}
catch (Exception $e) {
    echo $e->getMessage();
    return;
}

$hasMailbox = $connection->hasMailbox(MAIL_GET_MAILBOX);

if ($hasMailbox) {
    $mailbox = $connection->getMailbox(MAIL_GET_MAILBOX);
} else {
    echo "Mailbox " . MAIL_GET_MAILBOX . " not found";
    return;
}


$today = new DateTimeImmutable();
$daysAgo = $today->sub(new DateInterval('P' . MAIL_GET_DAYS_SINCE . 'D'));

$search = new SearchExpression();
// $search->addCondition(new To('teste@gmail.com'));

if (MAIL_GET_SUBJECT_CONTAINS)
    $search->addCondition(new Subject(MAIL_GET_SUBJECT_CONTAINS));
if (MAIL_GET_BODY_CONTAINS)
    $search->addCondition(new Body(MAIL_GET_BODY_CONTAINS));

// $search->addCondition(new Unseen());
$search->addCondition(new Since($daysAgo));

$messages = $mailbox->getMessages($search, \SORTDATE, false);

/** @var \Ddebo\Imap\Message $message*/
foreach ($messages as $message) {

    $headers = (array)$message->getHeaders();
    $messageId = $message->getId();

    // Ensure message_id is correctly formatted
    if (strpos($messageId, '<') !== 0) {
        $messageId = '<' . $messageId . '>';
    }


    $objFrom = $message->getFrom();

    $domain = explode('@', $objFrom->getAddress())[1];
    $clientInfoFromDomain = getClientInfoFromDomain($conn, $domain);

    $findTicket = findTicketByEmailReferences($conn, $messageId);
    if ($findTicket) {
        echo "Mensagem ignorada - Já existe um chamado ({$findTicket['ticket']}) aberto por essa mensagem: " . $objFrom->getAddress() . " - " . $objFrom->getName() . " - " . $message->getSubject() . PHP_EOL;
        continue;
    }

    /* Em produção, alterar para false o terceiro parâmetro */
    $requesterInfo = getUserByEmail($conn, $objFrom->getAddress(), isLocalhost()); 

    if (!$requesterInfo && EMAIL_TICKETS_ONLY_FROM_REGISTERED) {
        echo "Mensagem ignorada - Remetente não cadastrado: " . $objFrom->getAddress() . " - " . $objFrom->getName() . " - " . $message->getSubject() . PHP_EOL;
        continue;
    }

    if ($requesterInfo && EMAIL_TICKETS_ONLY_FROM_REGISTERED && $requesterInfo['level'] > 3) {
        echo "Mensagem ignorada - Remetente desabilitado: " . $objFrom->getAddress() . " - " . $objFrom->getName() . " - " . $message->getSubject() . PHP_EOL;
        continue;
    }

    $files = [];
    $attachments = $message->getAttachments();
    /** @var \Ddebo\Imap\Message $attachment*/
    foreach ($attachments as $attachment) {
        $uniqueName = uniqid() . '_' . $attachment->getFilename();

        $tmp_path_and_name = $tmp_dir . '/' . $uniqueName;

        file_put_contents($tmp_path_and_name, $attachment->getDecodedContent());

        $files['name'][] = $attachment->getFilename();
        $files['tmp_name'][] = $tmp_path_and_name;
    }

    
    /* Só abrirá chamado se não for e-mail de resposta */
    if (!array_key_exists('in_reply_to', $headers) || empty($headers['in_reply_to'])) {
    
        // $dateObj = $message->getDate();
        // $dateObj->format('Y-m-d H:i:s');
        $description = "";
        $description .= $message->getSubject() . "\n";
        $description .= $message->getBodyText();
        $description = trim(toHtml($description));


        /* Checar se é uma mensagem automática */
        if (isAutoResponse($headers, $message->getSubject())) {
            echo "Mensagem ignorada - E-mail automático: " . $objFrom->getAddress() . " - " . $objFrom->getName() . " - " . $message->getSubject() . PHP_EOL;
            continue;
        }

        $client = API_TICKET_BY_MAIL_CLIENT;
        if ($clientInfoFromDomain && $clientInfoFromDomain['id']) {
            $client = $clientInfoFromDomain['id'];
        }
        $department = null;
        $phone = null;
        $requester = null;
        $contact = null;

        if ($requesterInfo) {
            $client = ($requesterInfo['user_client'] ? $requesterInfo['user_client'] : $client);
            $department = $requesterInfo['user_department'] ?? null;
            $phone = $requesterInfo['phone'] ?? null;
            $requester = $requesterInfo['user_id'] ?? null;
            $contact = $requesterInfo['name'] ?? null;
        }

        $ticketData = [];
        $ticketData['client'] = $client;
        $ticketData['requester'] = $requester;
        $ticketData['description'] = $description;
        $ticketData['contact'] = $contact ?? $objFrom->getName();
        $ticketData['contact_email'] = $objFrom->getAddress(); 
        $ticketData['department'] = $department;
        $ticketData['phone'] = $phone;
        $ticketData['channel'] = API_TICKET_BY_MAIL_CHANNEL;
        $ticketData['area'] = API_TICKET_BY_MAIL_AREA;
        $ticketData['status'] = API_TICKET_BY_MAIL_STATUS;
        $ticketData['input_tag'] = API_TICKET_BY_MAIL_TAG;
        $ticketData['files[]'] = $files;
        $ticketData = array_filter($ticketData);

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
                'started_from' => $objFrom->getAddress(),
                'original_subject' => $message->getSubject(),
            ]);

            /* Marcar como visualizada */
            if (MAIL_GET_MARK_SEEN && MAIL_GET_MARK_SEEN == '1') {
                $message->markAsSeen();
            }
            
            try {
                $newMailbox = $connection->getMailbox(MAIL_GET_MOVETO);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
                try {
                    $newMailbox = $connection->createMailbox(MAIL_GET_MOVETO);
                }
                catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage();
                    echo $exception;
                    return;
                }
            }
            $message->move($newMailbox);
            echo json_encode(['ticket' => $create->response()->ticket], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            // var_dump($create->response());
        } else {
            echo "No ticket created " . PHP_EOL;
            // var_dump($create->response());
        }
    } else {
        echo "Message is a reply - " . PHP_EOL;

        if (!array_key_exists('references', $headers)) {
            echo "Message reference not found in database " . PHP_EOL;
            return;
        }
        
        $full_references = $headers['references'];
        $array_references = explode(' ', $full_references);
        $references = $array_references[0];

        if (strpos($references, '<') !== 0) {
            $references = '<' . $references . '>';
        }

        // var_dump([
        //     'array_references' => $array_references,
        //     'references' => $references
        // ]);
            
        /* Buscar para ver se existe um chamado para a mensagem original */
        $findTicket = findTicketByEmailReferences($conn, $references);
        // var_dump([
        //     'findTicket' => $findTicket,
        // ]);

        if (!$findTicket) {
            echo "No ticket found for this message " . PHP_EOL;
            return;
        }
        echo "Ticket found: {$findTicket['ticket']} - " . PHP_EOL;

        // var_dump(['Chamado encontrado: ' => $findTicket['ticket']]);


        $messageParsed = (new EmailParser())->parse($message->getBodyText());
        $visibleText = \EmailReplyParser\EmailReplyParser::parseReply($message->getBodyText());

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
                    $message->markAsSeen();
                }
                /* Mover a mensagem */
                $message->move($connection->getMailbox(MAIL_GET_MOVETO));
            }
            return;
        }

        if (isset($comment->response()->id)) {
            echo "Comment created: {$comment->response()->id} " . PHP_EOL;
        } else {
            echo "Some unexpected error - No comment created " . PHP_EOL;
            return;
        }

        /* Marcar como visualizada */
        if (MAIL_GET_MARK_SEEN && MAIL_GET_MARK_SEEN == '1') {
            $message->markAsSeen();
        }
        /* Mover a mensagem */
        $message->move($connection->getMailbox(MAIL_GET_MOVETO));
    }

    echo "----------------------------------------------" . PHP_EOL;
}

if (!count((array)$messages)) {
    echo "Nenhuma mensagem encontrada" . PHP_EOL;
    return;
}

/* Garante a remoção das mensagens marcadas para exclusão */
$connection->expunge();