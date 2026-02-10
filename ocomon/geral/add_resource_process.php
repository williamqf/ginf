<?php session_start();
/*  Copyright 2023 Flávio Ribeiro

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
$exception = "";
$data = [];
$data['success'] = true;
$entry = TRANS('ENTRY_RESOURCE_ALLOCATED');
$entryType = 18;


$post = $_POST;

if (!isset($post['numero']) || empty($post['numero'])) {
    exit;
}

$data['entry_schedule_for_resources'] = (isset($post['entry_schedule_for_resources']) && !empty($post['entry_schedule_for_resources']) ? noHtml($post['entry_schedule_for_resources']) : "");

$data['numero'] = (int) $post['numero'];
$data['resource_model'] = (isset($post['resource']) ? array_map('noHtml', $post['resource']) : "");

$data['resources'] = [];



function arrayHasDuplicates($array) {
    return count($array) !== count(array_unique($array));
}



if (!empty($data['resource_model'])) {

    if (arrayHasDuplicates($data['resource_model'])) {
        $data['success'] = FALSE; 
        $data['message'] = TRANS('MSG_RESOURCE_ALREADY_ALLOCATED');
        $data['message'] = message('warning', '', $data['message'], '', '');

        echo json_encode($data);
        return true;
    }

    $data['resource_model'] = array_unique($data['resource_model']);
    foreach ($data['resource_model'] as $key => $model) {
        
        if (!empty($model)) {
            
            $data['resources']['model'][] = $model;
            $data['resources']['amount'][] = (int) $post['amount'][$key];
            
            $modelData = getPriceFromAssetModel($conn, $model);
            if (!empty($modelData) && array_key_exists('comp_valor', $modelData)) {
                $data['resources']['unitary_price'][] = $modelData['comp_valor'] ?? 0;
            } else {
                $data['resources']['unitary_price'][] = 0;
            }
        }
    }
}


$data['author'] = $_SESSION['s_uid'];

// if ($row['status'] == 4 ) {
//     /* Já encerrado */
//     $return['message'] = TRANS('HNT_TICKET_CLOSED');
//     echo json_encode($return);
//     return true;
// }


// var_dump($data); exit;


if (!csrf_verify($post)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}


// Atualiza a tabela tickets_x_resources
$sql = "UPDATE tickets_x_resources SET is_current = 0 WHERE ticket = {$data['numero']}";
try {
    $conn->exec($sql);

    // Novos valores na tabela tickets_x_resources
    if (array_key_exists('model', $data['resources'])) {
        foreach ($data['resources']['model'] as $key => $model) {
            $sql = "INSERT INTO tickets_x_resources SET 
                    ticket = {$data['numero']},
                    model_id = {$model},
                    amount = {$data['resources']['amount'][$key]},
                    unitary_price = {$data['resources']['unitary_price'][$key]},
                    author = {$data['author']}
                    ";
            try {
                $conn->exec($sql);
            } catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        }
    }

    if (empty($exception)) {
            /* Tipo de assentamento: 18 - Alocação de recursos */
        $sql = "INSERT INTO assentamentos 
                (
                    ocorrencia, 
                    assentamento, 
                    created_at, 
                    responsavel, 
                    tipo_assentamento
                ) 
                    values 
                (
                    {$data['numero']},
                    '{$entry}', 
                    '".date('Y-m-d H:i:s')."', 
                    {$data['author']}, 
                    {$entryType} 
                )";

        try {
            $result = $conn->exec($sql);

            $notice_id = $conn->lastInsertId();
            $ticketData = getTicketData($conn, $data['numero'], ['aberto_por']);
            if ($_SESSION['s_uid'] != $ticketData['aberto_por']) {
                setUserTicketNotice($conn, 'assentamentos', $notice_id);
            }
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
}




$data['success'] = true; 
$data['message'] = TRANS('MSG_SUCCESS_RESOURCE_UPDATED');
$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

