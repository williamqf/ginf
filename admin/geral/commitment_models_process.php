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



$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";
/* Não podem ser excluídos e também não podem ser vinculados a clientes e unidades */
$system_defaults_ids = [1,2];

$data['commitment_type'] = (isset($post['commitment_type']) && !empty($post['commitment_type']) ? intval($post['commitment_type']) : "");
$data['client'] = (isset($post['client']) && !empty($post['client']) ? intval($post['client']) : "");
$data['unit'] = (isset($post['unit']) && !empty($post['unit']) ? intval($post['unit']) : "");

/* Se for informada a unidade, o respetivo cliente será assumido */
if (!empty($data['unit'])) {
    $unitInfo = getUnits($conn, null, $data['unit']);
    $data['client'] = $unitInfo['inst_client'];
}


// $data['html_content'] = (isset($post['html_content']) && !empty($post['html_content']) ? noHtml($post['html_content']) : "");
$data['html_content'] = (isset($post['html_content']) && !empty($post['html_content']) ? $post['html_content'] : "");



/* Validações */

if ($data['action'] == "new") {

    if (empty($data['commitment_type'])) {
        $data['success'] = false; 
        $data['field_id'] = "commitment_type";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }
    
    /* Não pode ter o campo cliente e o campo unidade vazios ao mesmo tempo */
    if (empty($data['client']) && empty($data['unit'])) {
        $data['success'] = false; 
        $data['field_id'] = "client";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_SELECT_AT_LEAST_ONE_CLIENT_OR_UNIT'),'');
        echo json_encode($data);
        return false;
    }

    $paramUnit = (!empty($data['unit']) ? $data['unit'] : null);
    $paramClient = (!empty($data['client']) ? $data['client'] : null);

    $findCommitmentModel = getCommitmentModels($conn, null, $paramUnit, $paramClient, $data['commitment_type']);

    if (!empty($findCommitmentModel)) {
        $data['success'] = false; 
        $data['field_id'] = "client";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_COMMITMENT_MODEL_ALREADY_FOR_THIS_CLIENT_OR_UNIT'),'');
        echo json_encode($data);
        return false;
    }

    if (empty($data['html_content'])) {
        $data['success'] = false; 
        $data['field_id'] = "html_content";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }


    $sql = "INSERT INTO
            commitment_models (
                `type`,
                html_content,
                client_id,
                unit_id
            ) VALUES (
                {$data['commitment_type']},
                '{$data['html_content']}',
                " . dbField($data['client'],'int') . ",
                " . dbField($data['unit'],'int') . "
            )";


    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');
        $_SESSION['flash'] = message('success', '', $data['message'], '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $data['success'] = false; 
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $data['message'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
} elseif ($data['action'] == 'edit') {

    if (empty($data['cod'])) {
        $data['success'] = false; 
        $data['field_id'] = "cod";
        $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');
        echo json_encode($data);
        return false;
    }

    if (empty($data['commitment_type'])) {
        $data['success'] = false; 
        $data['field_id'] = "commitment_type";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }


    /* Não pode ter o campo cliente e o campo unidade vazios ao mesmo tempo caso não sejam os registro de sistema */
    if (empty($data['client']) && empty($data['unit']) && !in_array($data['cod'], $system_defaults_ids)) {
        $data['success'] = false; 
        $data['field_id'] = "client";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_SELECT_AT_LEAST_ONE_CLIENT_OR_UNIT'),'');
        echo json_encode($data);
        return false;
    }

    $paramUnit = (!empty($data['unit']) ? $data['unit'] : null);
    $paramClient = (!empty($data['client']) ? $data['client'] : null);

    $findCommitmentModel = getCommitmentModels($conn, null, $paramUnit, $paramClient, $data['commitment_type']);

    if (!empty($findCommitmentModel) && $findCommitmentModel[0]['id'] != $data['cod']) {
        
        $data['success'] = false; 
        $data['field_id'] = "client";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_COMMITMENT_MODEL_ALREADY_FOR_THIS_CLIENT_OR_UNIT'),'');
        echo json_encode($data);
        return false;
    }

    if (empty($data['html_content'])) {
        $data['success'] = false; 
        $data['field_id'] = "html_content";
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }


    $sql = "UPDATE commitment_models SET 
            `type` = {$data['commitment_type']},
            `html_content` = '{$data['html_content']}',
            `client_id` = " . dbField($data['client'],'int') . ",
            `unit_id` = " . dbField($data['unit'],'int') . "

            WHERE id = '" . $data['cod'] . "'";

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

    /* Confere se não é formulário padrão do sistema */
    if (in_array($data['cod'], $system_defaults_ids)) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL_SYSTEM_REGISTER');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }


    $sql = "DELETE FROM commitment_models WHERE id = '" . $data['cod'] . "'";
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
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE') . $exception;
        $_SESSION['flash'] = message('danger', '', $data['message'], '');
        echo json_encode($data);
        return false;
    }

}

echo json_encode($data);