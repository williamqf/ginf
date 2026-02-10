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


if (!isset($post['user']) || $post['user'] == '') {
    $data['']['error'] = TRANS('MSG_SOMETHING_GOT_WRONG');
    echo json_encode($data);
    return false;
}

/* Se for somente abertura, somente recebe as informações do próprio usuário */
if ($_SESSION['s_nivel'] == 3 && $post['user'] != $_SESSION['s_uid']) {

    $author_department = getUserDepartment($conn, $_SESSION['s_uid']);
    $requester_department = getUserDepartment($conn, $post['user']);

    if (empty($author_department) || $author_department != $requester_department) {
        $post['user'] = $_SESSION['s_uid'];
    }
    
    // $post['user'] = $_SESSION['s_uid'];
}

$userInfo = [];

$userInfo = getUserInfo($conn, (int)$post['user']);
unset($userInfo['password']);
unset($userInfo['hash']);


$userClient = (!empty($userInfo) && !empty($userInfo['user_client']) ? $userInfo['user_client'] : "");
$userDepartment = (!empty($userInfo) && !empty($userInfo['user_department']) ? $userInfo['user_department'] : "");


$unitInfo = [];
$units = [];
if (!empty($userDepartment)) {
    /* Buscando a unidade com base no departamento do usuário */
    $departmentInfo = getDepartments($conn, null, $userDepartment);
    if (!empty($departmentInfo) && !empty($departmentInfo['loc_unit'])) {
        $unitInfo = getUnits($conn, null , $departmentInfo['loc_unit']);
    }
}

$unit = (!empty($unitInfo) && !empty($unitInfo['inst_cod']) ? $unitInfo : []);

$unitByDepartment = (!empty($unit) ? " <small>(" . $unitInfo['inst_nome'] . ")</small>" : "");

if (empty($unit)) {
    /* Buscar pelo cliente do usuário então */
    if (!empty($userClient)) {
        $units = getUnits($conn, null , null, $userClient);
    }

    if (count($units) == 1) {
        $unit = $units[0];
    }
}

$info['unit'] = $unit;
$info['user'] = $userInfo;

$html = "";
$html .= TRANS('USER_INFO') . "<br /><hr />";
$html .= TRANS('COL_PHONE') . ": <strong>" . $info['user']['fone'] . "</strong><br />";
$html .= TRANS('COL_EMAIL') . ": <strong>" . $info['user']['email'] . "</strong><br />";
$html .= TRANS('CLIENT') . ": <strong>" . $info['user']['nickname'] . "</strong><br />";
// $html .= TRANS('COL_UNIT') . ": " . $unit['inst_nome'] . "<br />";
$html .= TRANS('DEPARTMENT') . ": <strong>" . $info['user']['department'] . $unitByDepartment . "</strong><br />";
$html .= TRANS('REQUESTER_AREA') . ": <strong>" . $info['user']['area_nome'] . "</strong><br />";


$info['html'] = message('info', '', $html, '', '', true, '');

$data = $info;

echo json_encode($data);

return true;