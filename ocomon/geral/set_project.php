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
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
$post = (isset($_POST) ? $_POST : []);

if (!isset($post['numero']) && !isset($post['project_id'])) {
    return false;
}



$data = [];
$exception = "";
$data['success'] = true;
$data['message'] = "";
$user = (isset($_SESSION['s_uid']) ? (int)$_SESSION['s_uid'] : '');

$data['numero'] = (isset($post['numero']) && !empty($post['numero']) ? (int)$post['numero'] : '');
$data['project_id'] = (isset($post['project_id']) && !empty($post['project_id']) ? (int)$post['project_id'] : '');
$data['name'] = (isset($post['name']) && !empty($post['name']) ? noHtml($post['name']) : '');
$data['description'] = (isset($post['description']) && !empty($post['description']) ? noHtml($post['description']) : '');

$data['action'] = (isset($post['action']) && !empty($post['action']) ? $post['action'] : '');

if (empty($data['action'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "action";
    echo json_encode($data);
    return false;
}


if (empty($data['name'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "project_name";
    echo json_encode($data);
    return false;
}

if (empty($data['description'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_EMPTY_DATA'), '');
    $data['field_id'] = "project_description";
    echo json_encode($data);
    return false;
}

/* Checa se de fato pode-se definir um projeto com base no número do chamado informado */
if ($data['action'] == 'new') {
    $sql = "SELECT * FROM 
            ocodeps
        WHERE
            (dep_pai = {$data['numero']} OR dep_filho = {$data['numero']}) AND
            proj_id IS NULL
    ";
    $res = $conn->query($sql);
    if (!$res->rowCount()) {
        $data['success'] = false;
        $data['message'] = message('warning', '', TRANS('MSG_DATA_NOT_QUALIFIED_TO_PROJECT') . "<hr />" . TRANS('MSG_HELPER_NOT_QUALIFIED_TO_PROJECT'), '');
        echo json_encode($data);
        return false;
    }
}



if ($data['action'] == "new") {
    /* Gravação das definições do projeto */
    $sql = "INSERT 
        INTO
            projects
        SET
            name = '{$data['name']}',
            description = '{$data['description']}'
        ";
    try {
        $conn->exec($sql);
        $data['project_id'] = $conn->lastInsertId();
        $data['success'] = true;

        $storeParent = [];
        $storeSons = [];
        $relations = [];
        $firstOfAll = getFirstFather($conn, $data['numero'], $storeParent);
        if ($firstOfAll) {
            $relations = getTicketDownRelations($conn, $firstOfAll, $storeSons);
        }

        $ticketNumbers = array_keys($relations);
        foreach ($ticketNumbers as $ticket) {
            /* Atualizar a tabela ocodeps com o id do projeto criado */
            $sql = "UPDATE
                        ocodeps
                    SET
                        proj_id = {$data['project_id']}
                    WHERE
                        dep_pai = {$ticket} OR dep_filho = {$ticket}";
            try {
                $conn->exec($sql);
            } catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
                $data['success'] = false;
                $data['message'] = message('danger', '', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '');
                echo json_encode($data);
                return false;
            }
        }

    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false;
        $data['message'] = message('danger', '', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '');
        echo json_encode($data);
        return false;
    }
} elseif ($data['action'] == "update") {
    /* Gravação das definições do projeto */
    $sql = "UPDATE
                projects
            SET
                name = '{$data['name']}',
                description = '{$data['description']}'
            WHERE
                id = {$data['project_id']}
            ";
    try {
        $conn->exec($sql);
        $data['success'] = true;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        $data['success'] = false;
        $data['message'] = message('danger', '', TRANS('MSG_ERR_DATA_UPDATE') . $exception, '');
        echo json_encode($data);
        return false;
    }
}


$msgKey = "MSG_SUCCESS_PROJECT_CREATED";
if ($data['action'] == 'update') {
    $msgKey = "MSG_SUCCESS_PROJECT_UPDATED";
}


$_SESSION['flash'] = message('success', '', TRANS($msgKey) . $exception, '', '');
echo json_encode($data);
return true;