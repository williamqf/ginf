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
$data['except_resource'] = (isset($post['except_resource']) && $post['except_resource'] == 1 ? 1 : null);

$data['asset_tag'] = (isset($post['asset_tag']) ? noHtml($post['asset_tag']) : "");
$user_id = (isset($post['user_id']) ? (int)$post['user_id'] : $_SESSION['s_uid']);
$userInfo = getUserInfo($conn, $user_id);

if (empty($userInfo['user_client'])) {
    echo json_encode([]);
    return false;
}
$userClient = $userInfo['user_client'];

/* Configuração que limita a exibição a apenas unidades do mesmo cliente do usuário */
$allowOnlyOpsGetAssetsBtwClients = false;
$allowUserGetAssetsBtwClients = false;
$allowUserGetAssetsBtwClients = getConfigValue($conn, 'ALLOW_USER_GET_ASSETS_BTW_CLIENTS') ?? 0;

if ($allowUserGetAssetsBtwClients) {
    $allowOnlyOpsGetAssetsBtwClients = getConfigValue($conn, 'ALLOW_ONLY_OPS_GET_ASSETS_BTW_CLIENTS') ?? 0;
}

if ($allowOnlyOpsGetAssetsBtwClients) {
    $user_level = getUserLevel($conn, $user_id);
}

// if (isset($post['fromAnyClient']) && $post['fromAnyClient'] == 1) {
//     $allowUserGetAssetsBtwClients = false;
// }


if (empty($data['asset_tag']) || empty($user_id)) {
    $data['success'] = false;
    
    echo json_encode([]);
    return false;
}

if ($allowUserGetAssetsBtwClients) {
    if ($allowOnlyOpsGetAssetsBtwClients) {
        if ($userInfo['nivel'] < 3) {
            $units = getUnitsByAssetTag($conn, $data['asset_tag'], null, $data['except_resource']);
        } else {
            $units = getUnitsByAssetTag($conn, $data['asset_tag'], $userClient, $data['except_resource']);
        }
    } else {
        $units = getUnitsByAssetTag($conn, $data['asset_tag'], null, $data['except_resource']);
    }
} else {
    $units = getUnitsByAssetTag($conn, $data['asset_tag'], $userClient, $data['except_resource']);
}

$data = $units;

echo json_encode($data);
return true;