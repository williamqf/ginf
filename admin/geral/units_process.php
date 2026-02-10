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

// var_dump($post); exit;

$exception = "";
$screenNotification = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");
$data['field_id'] = "";
$data['cep_mask'] = "^\d{5}-\d{3}$";

$data['unit_name'] = (isset($post['unit_name']) ? noHtml($post['unit_name']) : "");
$data['unit_client'] = (isset($post['unit_client']) && !empty($post['unit_client']) ? noHtml($post['unit_client']) : "");
$data['unit_status'] = (isset($post['unit_status']) ? ($post['unit_status'] == "yes" ? 1 : 0) : 1);

$data['unit_cep'] = (isset($post['unit_cep']) && !empty($post['unit_cep']) ? noHtml($post['unit_cep']) : "");
$data['unit_street'] = (isset($post['unit_street']) && !empty($post['unit_street']) ? noHtml($post['unit_street']) : "");
$data['unit_neighborhood'] = (isset($post['unit_neighborhood']) && !empty($post['unit_neighborhood']) ? noHtml($post['unit_neighborhood']) : "");
$data['unit_city'] = (isset($post['unit_city']) && !empty($post['unit_city']) ? noHtml($post['unit_city']) : "");
$data['unit_state'] = (isset($post['unit_state']) && !empty($post['unit_state']) ? noHtml($post['unit_state']) : "");
$data['unit_address_number'] = (isset($post['unit_address_number']) && !empty($post['unit_address_number']) ? noHtml($post['unit_address_number']) : "");
$data['unit_address_complement'] = (isset($post['unit_address_complement']) && !empty($post['unit_address_complement']) ? noHtml($post['unit_address_complement']) : "");
$data['unit_obs'] = (isset($post['unit_obs']) && !empty($post['unit_obs']) ? noHtml($post['unit_obs']) : "");

if (!empty($data['unit_cep']) && !preg_match('/' . $data['cep_mask'] . '/i', (string)$data['unit_cep'])) {
    $data['success'] = false; 
    $data['field_id'] = "unit_cep";

    $data['message'] = message('warning', 'Ooops!', TRANS('BAD_FIELD_FORMAT'),'');
    echo json_encode($data);
    return false;
}

$data['unit_cep'] = (!empty($data['unit_cep']) ? str_replace("-", "", $data['unit_cep']) : "");


/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['unit_name'])) {
        $data['success'] = false; 
        $data['field_id'] = "unit_name";
    }

    if ($data['success'] == false) {
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }
}

if ($data['action'] == 'new') {

    /* verifica se um registro com esse nome já existe para o mesmo cliente */
    $sql = "SELECT inst_cod FROM instituicao WHERE inst_nome = '" . $data['unit_name'] . "' AND inst_client = '" . $data['unit_client'] ."' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "unit_name";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post, $data['csrf_session_key'])) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "INSERT INTO instituicao 
        (
            inst_nome, 
            inst_status,
            inst_client,
            addr_cep,
            addr_street,
            addr_neighborhood,
            addr_city,
            addr_uf,
            addr_number,
            addr_complement,
            observation
        ) 
        VALUES 
        (
            '" . $data['unit_name'] . "', 
            '" . $data['unit_status'] . "', 
            " . dbField($data['unit_client']) . ", 
            " . dbField($data['unit_cep'], 'text') . ", 
            " . dbField($data['unit_street'], 'text') . ", 
            " . dbField($data['unit_neighborhood'], 'text') . ", 
            " . dbField($data['unit_city'], 'text') . ", 
            " . dbField($data['unit_state'], 'text') . ", 
            " . dbField($data['unit_address_number'], 'text') . ", 
            " . dbField($data['unit_address_complement'], 'text') . ", 
            " . dbField($data['unit_obs'], 'text') . " 
        )";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD') . $exception . "<hr>" . $sql;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {

    /* verifica se um registro com esse nome já existe para outro código do mesmo cliente */
    $sql = "SELECT inst_cod FROM instituicao WHERE inst_nome = '" . $data['unit_name'] . "' AND inst_client = '" . $data['unit_client'] . "'  AND inst_cod <> '" . $data['cod'] . "'";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "unit_name";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }

    if (!csrf_verify($post, $data['csrf_session_key'])) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE instituicao SET 
				inst_nome =  '" . $data['unit_name'] . "' ,
                inst_status = '" . $data['unit_status'] . "', 
                inst_client = " . dbField($data['unit_client']) . ",
                addr_cep = " . dbField($data['unit_cep'], 'text') . ",
                addr_street = " . dbField($data['unit_street'], 'text') . ",
                addr_neighborhood = " . dbField($data['unit_neighborhood'], 'text') . ",
                addr_city = " . dbField($data['unit_city'], 'text') . ",
                addr_uf = " . dbField($data['unit_state'], 'text') . ",
                addr_number = " . dbField($data['unit_address_number'], 'text') . ",
                addr_complement = " . dbField($data['unit_address_complement'], 'text') . ",
                observation = " . dbField($data['unit_obs'], 'text') . " 
            WHERE inst_cod = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE') . $exception;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'delete') {


    $sqlFindPrevention = "SELECT comp_cod FROM equipamentos WHERE comp_inst = '" . $data['cod'] . "' ";
    $resFindPrevention = $conn->query($sqlFindPrevention);
    $foundPrevention = $resFindPrevention->rowCount();

    if ($foundPrevention) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    $sqlFindPrevention = "SELECT numero FROM ocorrencias WHERE instituicao = '" . $data['cod'] . "' ";
    $resFindPrevention = $conn->query($sqlFindPrevention);
    $foundPrevention = $resFindPrevention->rowCount();

    if ($foundPrevention) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

    $sqlFindPrevention = "SELECT base_unit FROM clients WHERE base_unit = '" . $data['cod'] . "' ";
    $resFindPrevention = $conn->query($sqlFindPrevention);
    $foundPrevention = $resFindPrevention->rowCount();

    if ($foundPrevention) {
        $data['success'] = false; 
        $data['message'] = TRANS('CANT_DEL_BASE_UNIT');
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }



    $sql = "DELETE FROM instituicao WHERE inst_cod = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    
}

echo json_encode($data);