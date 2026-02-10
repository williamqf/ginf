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

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);


if (!isset($_POST['numero'])) {
    exit();
}

$post = $_POST;

$exception = "";
$sent = false;
$numero = (int) $post['numero'];
$user = (int) $_SESSION['s_uid'];
$now = date("Y-m-d H:i:s");
$data['success'] = true;
$data['message'] = "";


$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");
$data['entry'] = (isset($post['entry']) && !empty($post['entry']) ? noHtml($post['entry']) : "");
$data['status'] = (isset($post['status']) && !empty($post['status']) ? (int)$post['status'] : "");

if (empty($data['entry'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "entryRequestFeedback";
    echo json_encode($data);
    return false;
}

if (empty($data['status'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "statusRequestFeedback";
    echo json_encode($data);
    return false;
}


$row = getTicketData($conn, $numero);

if (!count($row)) {
    $data['success'] = false;
    $data['message'] = message('danger', '', TRANS('NO_RECORDS_FOUND'), '');
    echo json_encode($data);
    return false;
}


$config = getConfig($conn);

$isRequester = $_SESSION['s_uid'] == $row['aberto_por'];
$isResponsible = isTicketTreater($conn, $numero, $_SESSION['s_uid']);
$isClosed = ($row['status'] == 4 ? true : false);
$isDone = ($row['status'] == $config['conf_status_done'] ? true : false);
$isScheduled = ($row['oco_scheduled'] == 1 ? true : false);

$statusAnswered = $config['stat_out_inactivity'];
$statusToMonitor = $config['stats_to_close_by_inactivity'];
$arrayStatusToMonitor = [];
if (!empty($statusToMonitor)) {
    $arrayStatusToMonitor = explode(',', (string)$statusToMonitor);
    $alreadyRequestedFeedback = (in_array($row['status'], $arrayStatusToMonitor) ? true : false);

    $alreadyAnswered = ($row['status'] == $statusAnswered ? true : false);

    $allowRequestFeedback = (!$isClosed && !$isDone && !$isScheduled && !$isRequester && $isResponsible && !$alreadyRequestedFeedback && !$alreadyAnswered);
}

if (!$allowRequestFeedback) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_NOT_ALLOW_REQUEST_FEEDBACK'), '');
    echo json_encode($data);
    return false;
}


/* Para pegar o estado da ocorrência antes da atualização e permitir a gravação do log de modificações com recordLog() */
$arrayBeforePost = [];
$arrayBeforePost['status_cod'] = $row['status'];

/* Configurações de e-mail */
$rowconfmail = getMailConfig($conn);
/* E-mail de quem abriu o chamado */
$openerEmail = getOpenerEmail($conn, $numero);

//Checa se já existe algum registro de log - caso não existir grava o estado atual
$firstLog = firstLog($conn, $numero, 'NULL', 1);


if (!csrf_verify($post, $data['csrf_session_key'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}



if (!empty($row['data_atendimento'])) {
    $sql = "UPDATE 
                ocorrencias 
            SET 
                `status` = {$data['status']}, 
                operador = {$user} 
            WHERE 
                numero = '{$numero}'";
} else {
    $sql = "UPDATE 
                ocorrencias 
            SET 
                `status` = 2, 
                operador = {$user}, 
                data_atendimento = '{$now}' 
            WHERE 
                numero = '{$numero}'";
}

try {
    $result = $conn->exec($sql);

    /* Tipo de assentamento: 2 - Edição para atendimento */
    $sql = "INSERT INTO assentamentos 
                (ocorrencia, assentamento, created_at, responsavel, tipo_assentamento) 
            values 
                ({$numero}, '" . $data['entry'] . "', '{$now}', {$user}, 31 )";

    try {
        $result = $conn->exec($sql);

        $notice_id = $conn->lastInsertId();
        if ($_SESSION['s_uid'] != $row['aberto_por']) {
            setUserTicketNotice($conn, 'assentamentos', $notice_id);
        }

    } catch (Exception $e) {
        $exception .= '<hr>' .$e->getMessage();
    }



} catch (Exception $e) {
    $exception .= '<hr>' .$e->getMessage();
    $data['success'] = false;
    $data['message'] = message('danger', '', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '');
    echo json_encode($data);
    return false;
}

/* Gravação da data na tabela tickets_stages */
$stopTimeStage = insert_ticket_stage($conn, $numero, 'stop', $data['status'], $user, $now);
$startTimeStage = insert_ticket_stage($conn, $numero, 'start', $data['status'], $user, $now);



/* Array para a função recordLog */
$afterPost = [];
// $afterPost['operador'] = $user;
$afterPost['status'] = $data['status'];

/* Função que grava o registro de alterações do chamado */
$recordLog = recordLog($conn, $numero, $arrayBeforePost, $afterPost, 22);


/* Variáveis de ambiente para os e-mails */
$vars = array();
$vars = getEnvVarsValues($conn, $numero);
$mailSendMethod = 'send';
if ($rowconfmail['mail_queue']) {
    $mailSendMethod = 'queue';
}

$event = "request-feedback";
$eventTemplate = getEventMailConfig($conn, $event);

$recipient = "";
if (!empty($row['contato_email'])) {
    $recipient = $row['contato_email'];
} else {
    $recipient = $openerEmail;
}

/* Disparo do e-mail (ou fila no banco) para a área de atendimento */
$mail = (new Email())->bootstrap(
    transvars($eventTemplate['msg_subject'], $vars),
    transvars($eventTemplate['msg_body'], $vars),
    $recipient,
    $eventTemplate['msg_fromname'],
    $numero
);

if (!$mail->{$mailSendMethod}()) {
    $exception .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
}


$_SESSION['flash'] = message('success', '', TRANS('MSG_SUCCESS_REQUEST_FEEDBACK') . $exception, '', '');
echo json_encode($data);
return true;


