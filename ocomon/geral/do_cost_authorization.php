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


$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3, 1);
use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
$post = (isset($_POST) ? $_POST : []);

if (!isset($post['ticket'])) {
    return false;
}


/* Apenas gerentes de área devem autorizar chamados */
if (!$_SESSION['s_area_admin'] == '1') {
    return false;
    exit;
}

$data = [];
$exception = "";
$isAuthorizer = false;
$data['success'] = true;
$data['message'] = "";
$author = (isset($_SESSION['s_uid']) ? (int)$_SESSION['s_uid'] : '');

$data['ticket'] = (int) $post['ticket'];
$data['authorization_entry'] = (isset($post['authorization_entry']) && !empty($post['authorization_entry']) ? noHtml($post['authorization_entry']) : '');
$data['authorized'] = (isset($post['authorized']) && $post['authorized'] == "true" ? 1 : 0);


/* Se não informar o assentamento | comentário, não pode solicitar autorização */
if (empty($data['authorization_entry'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "authorization_entry";
    echo json_encode($data);
    return false;
}


$config = getConfig($conn);
$mailConfig = getMailConfig($conn);


/* Informações do chamado */
$sql = $QRY["ocorrencias_full_ini"] . " WHERE o.numero = {$data['ticket']} ";
$res = $conn->query($sql);
$ticketInfo = $res->fetch();



/* Confere se o chamado está com status compatível para o processo de autorização de atendimento */
if (!empty($ticketInfo['data_fechamento'])) {
    /* Não pode autorizar pois o chamado já está encerrado ou concluído */
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('ACTION_NOT_APPLICABLE_TO_DONE_TICKETS'), '');
    echo json_encode($data);
    return false;
}

/* Confere se o tipo precisa de autorização */
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
if ((!empty($ticketInfo['authorization_status']) && $ticketInfo['authorization_status'] != 1) || empty($ticketInfo['authorization_status'])) {
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
} else {
    foreach ($possibleAuthorizers as $possibleAuthorizer) {
        if ($possibleAuthorizer['user_id'] == $_SESSION['s_uid']) {
            $isAuthorizer = true;
            break;
        }
    }

    if (!$isAuthorizer) {
        $data['success'] = false;
        $data['message'] = message('warning', '', TRANS('NO_AUTHORIZATOR_FOUND'), '');
        echo json_encode($data);
        return false;
    }
}

/* Novo status que o chamado deve assumir - com base nas configurações do sistema */
if ($data['authorized']) {
    $data['new_status'] = (int)$config['status_cost_authorized'];
    $data['authorization_status'] = 2;
} else {
    $data['new_status'] = (int)$config['status_cost_refused'];
    $data['authorization_status'] = 3;
}

/* Tipo de assentamento: Autorização ou recusa do atendimento */
$data['entry_type'] = 20;

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
$afterPost['authorization_status'] = $data['authorization_status'];


$sql = "UPDATE 
            ocorrencias 
        SET 
            `status`= {$data['new_status']},
            `authorization_status`= {$data['authorization_status']},
            `authorization_author` = {$_SESSION['s_uid']}
        WHERE 
            numero = {$data['ticket']}";
try {
    $conn->exec($sql);

    $sql = "INSERT INTO assentamentos 
                (ocorrencia, assentamento, created_at, responsavel, tipo_assentamento) 
            values 
                ({$data['ticket']}, '{$data['authorization_entry']}', '".date('Y-m-d H:i:s')."', {$author}, {$data['entry_type']} )";
    try {
        $result = $conn->exec($sql);
        $notice_id = $conn->lastInsertId();
        
        setUserTicketNotice($conn, 'assentamentos', $notice_id);

        /* Função que grava o registro de alterações do chamado */
        $recordLog = recordLog($conn, $data['ticket'], $ticketInfo, $afterPost, 13);

    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
    }

    /* Gravação da data na tabela tickets_stages */
    $stopTimeStage = insert_ticket_stage($conn, $data['ticket'], 'stop', $data['new_status'], $author);
    $startTimeStage = insert_ticket_stage($conn, $data['ticket'], 'start', $data['new_status'], $author);



    /* Variáveis de ambiente para envio de e-mail */
    $VARS = getEnvVarsValues($conn, $data['ticket'], $ticketInfo);

    $mailTo = "";
    /** 
     * Email do operador (caso o chamado esteja na fila direta) 
     * ou da área (caso o chamado esteja na fila aberta)
    */
    $mailTo = ($ticketInfo['stat_painel_cod'] == 2 ? $ticketInfo['area_email'] : "");
    if (empty($mailTo)) {
        $operatorInfo = getUserInfo($conn, $ticketInfo['operador_cod']);
        $mailTo = $operatorInfo['email'];
    }

    $mailEvent = ($data['authorized'] ? 'request-authorized' : 'request-denied');
    
    if (!empty($mailTo)) {
        /** 
         * Enviar email para o operador (caso o chamado esteja na fila direta) 
         * ou para a área (caso o chamado esteja na fila aberta)
        */

        $mailSendMethod = 'send';
        if ($mailConfig['mail_queue']) {
            $mailSendMethod = 'queue';
        }

        $eventTemplate = getEventMailConfig($conn, $mailEvent);

        /* Disparo do e-mail (ou fila no banco) para o operador (caso o chamado esteja na fila direta) 
        ou para a área (caso o chamado esteja na fila aberta) */
        $mail = (new Email())->bootstrap(
            transvars($eventTemplate['msg_subject'], $VARS),
            transvars($eventTemplate['msg_body'], $VARS),
            $mailTo,
            $eventTemplate['msg_fromname'],
            $data['ticket']
        );

        if (!$mail->{$mailSendMethod}()) {
            $exception .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
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


$authorizationMsg = [
    0 => TRANS('MSG_SUCCESS_AUTHORIZATION_DENIED'),
    1 => TRANS('MSG_SUCCESS_AUTHORIZATION'),
];

$_SESSION['flash'] = message('success', '', $authorizationMsg[$data['authorized']] . $exception, '', '');
echo json_encode($data);
return true;