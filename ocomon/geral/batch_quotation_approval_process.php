<?php session_start();
/*      Copyright 2023 Flávio Ribeiro

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

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;

$isAdmin = $_SESSION['s_nivel'] == 1;


$exception = "";
$now = date('Y-m-d H:i:s');
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['approver'] = $_SESSION['s_uid'];

$idPrefix = "id_";

// Função para remover o prefixo e converter para inteiro
$removePrefix = function($value) {
    return (int) str_replace((string)"id_", "", $value);
};

$data['tickets_to_approve'] = (isset($post['tickets_to_approve']) ? array_map('noHtml', $post['tickets_to_approve']) : []);
$data['tickets_to_approve'] = array_map($removePrefix, $data['tickets_to_approve']);

$data['approved'] = (isset($post['approve']) ? ($post['approve'] == "yes" ? 1 : 0) : 0);
$data['entry_appraise'] = (isset($post['entry_appraise']) ? noHtml($post['entry_appraise']) : "");


if (empty($data['tickets_to_approve'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_SOMETHING_GOT_WRONG'), '');
    
    echo json_encode($data);
    return false;
}

$ticketsCount = count($data['tickets_to_approve']);

if (!$data['approved'] && !isset($data['entry_appraise'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'), '', '');
    $data['field_id'] = 'entry_appraise';
    echo json_encode($data);
    return false;
}


$quotation_status = ($data['approved'] ? 2 : 3);
$newTicketStatus = getConfigValue($conn, 'TICKET_STATUS_QUOTATION_STATUS_' . $quotation_status);

$terms = (!$data['approved'] ? ', motivo_rejeicao = :motivo' : '');


$myManagedAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);

$data['cant_be_appraised'] = [];
$data['reason'] = [];
foreach ($data['tickets_to_approve'] as $ticket) {
    
    /**
     * Ver quais as consistências precisam ser checadas antes de realizar o update
     */
    
    $requesterInfo = getOpenerInfo($conn, $ticket);
    $requesterArea = $requesterInfo['AREA'];

    if (!in_array($requesterArea, array_column($myManagedAreas, 'sis_id'))) {
        $data['success'] = false;
        $data['cant_be_appraised'][] = $ticket;
        $data['reason'][$ticket] = TRANS('USER_NOT_MANAGER_FOR_THAT_AREA');
        continue;
    }


    
    $quotationData = getQuotationInfo($conn, $ticket);
    if (empty($quotationData)) {
        $data['success'] = false;
        $data['cant_be_appraised'][] = $ticket;
        $data['reason'][$ticket] = TRANS('TICKET_HAS_NO_QUOTATION');
        continue;
    }


    if ($quotationData['status'] != 1) {
        $data['success'] = false;
        $data['cant_be_appraised'][] = $ticket;
        $data['reason'][$ticket] = TRANS('QUOTATION_IS_NOT_READY_TO_APPRAISE');
        continue;
    }
    
    
    
    $sql = "UPDATE
                my_orcamentos
            SET
                status = :status,
                aprovador = :aprovador,
                data_aprovacao = :data_aprovacao 
                {$terms}
            WHERE
                numero = :numero
            ";
    try {
        
        $res = $conn->prepare($sql);
        $res->bindParam(':status', $quotation_status, \PDO::PARAM_INT);
        $res->bindParam(':aprovador', $data['approver'], \PDO::PARAM_INT);
        $res->bindParam(':data_aprovacao', $now, \PDO::PARAM_STR);
        
        if (!$data['approved']) {
            $res->bindParam(':motivo', $data['entry_appraise'], \PDO::PARAM_STR);
        }
        $res->bindParam(':numero', $ticket, \PDO::PARAM_INT);
        $res->execute();

        $ticketData = getTicketData($conn, $ticket);

        /**
         * Alterar o Status do chamado
         */
        if (!empty($newTicketStatus)) {
            
            $newTicketStatus = (int)$newTicketStatus;
            $operationType = ($ticketsCount > 1 ? 45 : 44);
            
            // $ticketData = getTicketData($conn, $ticket);

            $arrayBeforePost = [];
            $arrayBeforePost['status_cod'] = $ticketData['status'];

            $sql = "UPDATE ocorrencias SET `status` = :status_cod WHERE numero = :cod";
            try {
                $res = $conn->prepare($sql);
                $res->bindParam(':status_cod', $newTicketStatus, \PDO::PARAM_INT);
                $res->bindParam(':cod', $ticket, \PDO::PARAM_INT);
                $res->execute();

                $treater = $_SESSION['s_uid'];
                $stopTimeStage = insert_ticket_stage($conn, $ticket, 'stop', $newTicketStatus, $treater);
                $startTimeStage = insert_ticket_stage($conn, $ticket, 'start', $newTicketStatus, $treater);


                $afterPost = [];
                $afterPost['status'] = $newTicketStatus;
                $recordLog = recordLog($conn, $ticket, $arrayBeforePost, $afterPost, $operationType);

            } catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        }


        $entryText = (!empty($data['entry_appraise']) ? $data['entry_appraise'] : TRANS('ENTRY_BATCH_QUOTATION_APPRAISE'));

        /* Assentamento e notificação */
        $entryData = [
            'text' => $entryText,
            'created_at' => $now,
            'author' => $_SESSION['s_uid'],
            'privated' => 0,
            'type' => 46,
        ];
        $entryID = setTicketEntry($conn, $ticket, $entryData);
        if ($entryID && $_SESSION['s_uid'] != $ticketData['aberto_por']) {
            setUserTicketNotice($conn, 'assentamentos', $entryID);
        }


    } catch (PDOException $e) {
        $exception .= $e->getMessage();
    }

}

if (!empty($data['cant_be_appraised'])) {

    foreach ($data['cant_be_appraised'] as $ticket_number => $ticket) {
        $data['reason'][$ticket_number] = '<li>' . $ticket . ': ' . $data['reason'][$asset_id] . '</li>';
    }
    $data['reason'] = '<ul>' .implode('', $data['reason']) . '</ul>';

    $data['success'] = false;
    $data['message'] = message('danger', 'Ooops!', TRANS('QUOTATIONS_COULD_NOT_BE_APRAISED') . '<hr />' . $data['reason'] . '<hr />' . TRANS('MSG_CAN_BE_OTHERS_RESTRICTIONS'), '');
    echo json_encode($data);
    return false;
}



$data['message'] = ($data['approved'] ? TRANS('MSG_SUCCESS_BATCH_QUOTATION_APPROVED') : TRANS('MSG_SUCCESS_BATCH_QUOTATION_REJECTED'));
$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
echo json_encode($data);
return false;
