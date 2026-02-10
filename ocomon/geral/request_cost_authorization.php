<?php session_start();
/*                        Copyright 2023 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 3 of the License, or
        (at your option) any later version.
        OCOMON is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with Foobar; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


if ((!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0)) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";


$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);
use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
$post = (isset($_POST) ? $_POST : []);

if (!isset($post['ticket'])) {
    return false;
}


$data = [];
$exception = "";
$data['success'] = true;
$data['message'] = "";
$author = (isset($_SESSION['s_uid']) ? (int)$_SESSION['s_uid'] : '');

$data['ticket'] = (int) $post['ticket'];
$data['request_authorization_entry'] = (isset($post['request_authorization_entry']) && !empty($post['request_authorization_entry']) ? noHtml($post['request_authorization_entry']) : '');


/* Se não informar o assentamento | comentário, não pode solicitar autorização */
if (empty($data['request_authorization_entry'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "request_authorization_entry";
    echo json_encode($data);
    return false;
}


$config = getConfig($conn);
$mailConfig = getMailConfig($conn);


/* Informações do chamado */
$sql = $QRY["ocorrencias_full_ini"] . " WHERE o.numero = {$data['ticket']} ";
$res = $conn->query($sql);
$ticketInfo = $res->fetch();


// $authorizationTypes = [
//     1 => TRANS('STATUS_WAITING_AUTHORIZATION'),
//     2 => TRANS('STATUS_AUTHORIZED'),
//     3 => TRANS('STATUS_REFUSED'),
// ];




/* Confere se o chamado está com status compatível para o processo de solicitação de autorização de atendimento */
if (!empty($ticketInf['data_fechamento'])) {
    /* Não pode solicitar autorizaçao pois o chamado já está encerrado ou concluído */
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('ACTION_NOT_APPLICABLE_TO_DONE_TICKETS'), '');
    echo json_encode($data);
    return false;
}

/* Confere se o tipo de solicitação precisa de autorização */
if (!$ticketInfo['need_authorization']) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('ISSUE_TYPE_DOES_NOT_NEED_AUTHORIZATION'), '');
    echo json_encode($data);
    return false;
}

$has_cost = false;
$cost_field_info = [];
$tickets_cost_field = (!empty($config['tickets_cost_field']) ? $config['tickets_cost_field'] : "");
if (!empty($tickets_cost_field)) {
    $cost_field_info = getTicketCustomFields($conn, $data['ticket'], $tickets_cost_field);
    if (!empty($cost_field_info['field_value']) && priceDB($cost_field_info['field_value']) > 0) {
        $has_cost = true;
    }
}

/* Se o chamado não tiver custo associado, não pode solicitar autorização */
if (!$has_cost) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_NEED_TO_DEFINE_TICKET_COST'), '');
    echo json_encode($data);
    return false;
}

// Se já tiver status de autorização não pode solicitar novamente
if (!empty($ticketInfo['authorization_status'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('REQUEST_AUTHORIZATION_ALREADY_EXISTS'), '');
    echo json_encode($data);
    return false;
}


/* Área solicitante */
$requesterArea = $ticketInfo['area_solicitante_cod'];

/* Listagem de autorizadores possíveis */
$possibleAuthorizers = getAreaAdmins($conn, $requesterArea, priceDB($cost_field_info['field_value']));

/* Se não existirem autorizadores, não pode solicitar autorização */
if (empty($possibleAuthorizers)) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('NO_AUTHORIZATOR_FOUND'), '');
    echo json_encode($data);
    return false;
}

/* Novo status que o chamado deve assumir - com base nas configurações do sistema */
$data['new_status'] = (int)$config['status_waiting_cost_auth'];

/* Tipo de assentamento: Solicitação de autorização de atendimento */
$data['entry_type'] = 19;

// $data['msg_style'] = ($data['approved'] ? 'success' : 'info');




// var_dump($data); exit;

// if (!csrf_verify($post)) {
//     $data['success'] = false; 
//     $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

//     echo json_encode($data);
//     return false;
// }


/* Array para a função recordLog */
$afterPost = [];
$afterPost['status'] = $data['new_status'];
$afterPost['authorization_status'] = 1;


$sql = "UPDATE 
            ocorrencias 
        SET 
            `status`= {$data['new_status']},
            `authorization_status`= 1 
        WHERE 
            numero = {$data['ticket']}";
try {
    $conn->exec($sql);

    $sql = "INSERT INTO assentamentos 
                (ocorrencia, assentamento, created_at, responsavel, tipo_assentamento) 
            values 
                ({$data['ticket']}, '{$data['request_authorization_entry']}', '".date('Y-m-d H:i:s')."', {$author}, {$data['entry_type']} )";
    try {
        $result = $conn->exec($sql);

        $notice_id = $conn->lastInsertId();
        if ($_SESSION['s_uid'] != $ticketInfo['aberto_por_cod']) {
            setUserTicketNotice($conn, 'assentamentos', $notice_id);
        }

        /* Função que grava o registro de alterações do chamado */
        $recordLog = recordLog($conn, $data['ticket'], $ticketInfo, $afterPost, 10);

    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
    }

    /* Gravação da data na tabela tickets_stages */
    $stopTimeStage = insert_ticket_stage($conn, $data['ticket'], 'stop', $data['new_status'], $author);
    $startTimeStage = insert_ticket_stage($conn, $data['ticket'], 'start', $data['new_status'], $author);



    /* Variáveis de ambiente para envio de e-mail */
    $VARS = getEnvVarsValues($conn, $data['ticket'], $ticketInfo);

    $ticketNumberSeparator = "%tkt%{$data['ticket']}";
    foreach ($possibleAuthorizers as $authorizer) {
    

        if ($authorizer['user_id'] == $_SESSION['s_uid']) {
            continue;
        }
        
        
        if ($authorizer['user_id'] != $ticketInfo['aberto_por_cod']) {
            /* Todos os possíveis autorizadores receberão também uma notificação na plataforma */
            setUserNotification($conn, $authorizer['user_id'], 1, $data['request_authorization_entry'] . ': ' . $ticketNumberSeparator, $author);
        }
        


        if (!empty($authorizer['email'])) {
            /** 
             * Enviar email para o autorizador
            */

            $mailSendMethod = 'send';
            if ($mailConfig['mail_queue']) {
                $mailSendMethod = 'queue';
            }

            $event = "request-authorization";
            $eventTemplate = getEventMailConfig($conn, $event);

            /* Disparo do e-mail (ou fila no banco) para o autorizador */
            $mail = (new Email())->bootstrap(
                transvars($eventTemplate['msg_subject'], $VARS),
                transvars($eventTemplate['msg_body'], $VARS),
                $authorizer['email'],
                $eventTemplate['msg_fromname'],
                $data['ticket']
            );

            if (!$mail->{$mailSendMethod}()) {
                $exception .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
            }
        }
    }


}
catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['success'] = false;
    $data['message'] = message('danger', '', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '');
    echo json_encode($data);
    return false;
}


$_SESSION['flash'] = message('success', '', TRANS('MSG_SUCCESS_REQUEST_AUTHORIZATION') . $exception, '', '');
echo json_encode($data);
return true;