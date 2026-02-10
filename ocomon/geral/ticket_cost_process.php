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

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;


if (!isset($_SESSION['s_nivel']) || $_SESSION['s_nivel'] > 2) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

$conn = ConnectPDO::getInstance();
$post = (isset($_POST) ? $_POST : []);

if (!isset($post['ticket'])) {
    return false;
}


// var_dump($post); exit;


$data = [];
$exception = "";
$mailNotification = "";
$data['success'] = true;
$data['message'] = "";
$author = (int)$_SESSION['s_uid'];
$current_ticket_cost = 0;

$data['ticket'] = (isset($post['ticket']) && !empty($post['ticket']) ? (int)$post['ticket'] : '');
$data['ticket_cost'] = (isset($post['ticket_cost']) && !empty($post['ticket_cost']) ? noHtml($post['ticket_cost']) : '');
$data['set_cost_entry'] = (isset($post['set_cost_entry']) && !empty($post['set_cost_entry']) ? noHtml($post['set_cost_entry']) : '');
$data['operation_type'] = 23;
$data['entry_type'] = 34;


if (empty($data['ticket'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "ticket";
    echo json_encode($data);
    return false;
}

if (empty($data['ticket_cost'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "ticket_cost";
    echo json_encode($data);
    return false;
}

if (empty($data['set_cost_entry'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "set_cost_entry";
    echo json_encode($data);
    return false;
}


$config = getConfig($conn);
$tickets_cost_field = (!empty($config['tickets_cost_field']) ? $config['tickets_cost_field'] : "");
if (empty($tickets_cost_field)) {
    return false;
}
$status_cost_updated = $config['status_cost_updated'] ?? 1;



$mailConfig = getMailConfig($conn);
$ticketInfo = getTicketData($conn, $data['ticket']);
if (!empty($ticketInfo['data_fechamento'])) {
    return false;
}

$cost_field_info = getTicketCustomFields($conn, $data['ticket'], $tickets_cost_field);
$current_ticket_cost = '0,00';
$current_ticket_cost = $cost_field_info['field_value'];

if ($current_ticket_cost == $data['ticket_cost']) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('PRICE_WAS_NOT_CHANGED'), '');
    $data['field_id'] = "ticket_cost";
    echo json_encode($data);
    return false;
}


$changeAuthorizationStatus = false;
/* Array para a funcao recordLog */
$arrayBeforePost = [];
$arrayBeforePost['status_cod'] = $ticketInfo['status'];
$arrayBeforePost['authorization_status'] = $ticketInfo['authorization_status'];
$arrayBeforePost['authorization_author'] = $ticketInfo['authorization_author'];


// var_dump([
//     'ticket' => $data['ticket'],
//     'ticketInfo' => $ticketInfo,
//     'ticket_cost' => $data['ticket_cost'],
//     'set_cost_entry' => $data['set_cost_entry'],
//     'tickets_cost_field' => $tickets_cost_field,
//     'cost_field_info' => $cost_field_info,
//     'current_ticket_cost' => $current_ticket_cost
// ]);
// exit;





$updateCustomField = updateOrSetTicketCustomFieldValue($conn, $data['ticket'], $tickets_cost_field, $data['ticket_cost']);

$treaterInfo = getTicketTreater($conn, $data['ticket']);
$treater = (!empty($treaterInfo) ? $treaterInfo['user_id'] : $author);


$changedStatus = false;
$afterPost = [];
if ($ticketInfo['authorization_status']) {
    /* Já tinha status de autorização - Remover */
    $changedStatus = true;
    $afterPost['status'] = $status_cost_updated;
    $afterPost['authorization_status'] = '0';
    $afterPost['authorization_author'] = '0';
    $operationType = $data['operation_type'];
    $sql = "UPDATE
                ocorrencias
            SET
                status = {$status_cost_updated},
                authorization_status = null,
                authorization_author = null
            WHERE
                numero = {$data['ticket']}
    ";
    $conn->exec($sql);
} 

$data['message'] = TRANS('MSG_COST_UPDATED');


/* Inserir assentamento e notificação*/
$entryData = [
    'text' => $data['set_cost_entry'],
    'author' => $author,
    'type' => $data['entry_type']
];
$entryID = setTicketEntry($conn, $data['ticket'], $entryData);

if ($entryID) {
    setUserTicketNotice($conn, 'assentamentos', $entryID);
}


//Checa se já existe algum registro de log - caso não existir grava o estado atual
$firstLog = firstLog($conn, $data['ticket'],'NULL', 1);



if ($changedStatus) {
    /* Função que grava o registro de alterações do chamado */
    $recordLog = recordLog($conn, $data['ticket'], $arrayBeforePost, $afterPost, $data['operation_type']);

    /* Gravação da data na tabela tickets_stages */
    $stopTimeStage = insert_ticket_stage($conn, $data['ticket'], 'stop', $afterPost['status'], $treater);
    $startTimeStage = insert_ticket_stage($conn, $data['ticket'], 'start', $afterPost['status'], $treater);
}


/* Informações sobre a área destino */
$infoAreaTo = ($ticketInfo['sistema'] != '-1' ? getAreaInfo($conn, $ticketInfo['sistema']) : []);


/* Variáveis de ambiente para envio de e-mail */
$VARS = getEnvVarsValues($conn, $data['ticket']);



$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
echo json_encode($data);
return true;