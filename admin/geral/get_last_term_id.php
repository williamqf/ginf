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

$html = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";


$data['user_id'] = (isset($post['user_id']) ? noHtml($post['user_id']) : "");

if (empty($data['user_id'])) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERR_GET_DATA'),'');
    echo json_encode($data);
    return false;
}

$userInfo = getUserInfo($conn, $data['user_id']);
if (empty($userInfo)) {
    $data['success'] = false;
    echo json_encode([]);
    return false;
}
$isAdmin = $_SESSION['s_nivel'] == 1;

$isAreaAdmin = false;
$managebleAreas = [];
if (isset($_SESSION['s_uid'])) {
	$managebleAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);

    if (!empty($managebleAreas)) {
        $managebleAreas = array_column($managebleAreas, 'sis_id');
        $isAreaAdmin = in_array($userInfo['area_id'], $managebleAreas);
    } else {
        $isAreaAdmin = false;
    }
}


if (!$isAdmin && !$isAreaAdmin && $_SESSION['s_uid'] != $post['user_id']) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERR_GET_DATA'),'');
    echo json_encode($data);
    return false;
}




$termUpdated = isUserTermUpdated($conn, $data['user_id']);
if (!$termUpdated) {
    $data['success'] = false;
    $data['message'] = message('danger', 'Ooops!', TRANS('TERM_NEED_TO_BE_GENERATED'),'');
    echo json_encode($data);
    return false;
}


$commitmentTermId = getUserLastCommitmentTermId($conn, $data['user_id']);

$data['last_term_id'] = $commitmentTermId;

echo json_encode($data);
return true;