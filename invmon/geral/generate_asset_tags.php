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

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);
$exception = "";
$now = date('Y-m-d H:i:s');
$data = [];
$data['success'] = true;

$maxAmountEachTime = getConfigValue($conn, 'MAX_AMOUNT_BATCH_ASSETS_RECORD');
if (!isset($maxAmountEachTime)) {
    setConfigValue($conn, 'MAX_AMOUNT_BATCH_ASSETS_RECORD', 500);
    $maxAmountEachTime = getConfigValue($conn, 'MAX_AMOUNT_BATCH_ASSETS_RECORD');
}



$post = $_POST;

// $data['asset_unit'] = (isset($post['asset_unit']) ? noHtml($post['asset_unit']) : "");
// if (empty($data['asset_unit'])) {
//     $data = [];
//     $data['success'] = false; 
//     $data['field_id'] = 'asset_unit';
//     $data['message'] = message('warning', 'Ooops!', TRANS('MSG_NEED_UNIT_TO_GENERATE_TAGS'),'');
//     echo json_encode($data);
//     return false;
// }


/* Validar se o asset_amount é um número */
if (!isset($post['asset_amount']) || empty($post['asset_amount']) || !filter_var($post['asset_amount'], FILTER_VALIDATE_INT) || $post['asset_amount'] <= 0) {
    $data['success'] = false; 
    $data['field_id'] = 'asset_amount';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERROR_WRONG_VALUE'),'');
    echo json_encode($data);
    return false;
}
$data['asset_amount'] = ($post['asset_amount'] > $maxAmountEachTime ? $maxAmountEachTime : (int)$post['asset_amount']);

$data["tags_prefix"] = (isset($post['tags_prefix']) && !empty($post['tags_prefix']) ? noHtml($post['tags_prefix']) : "");
$data["tags_prefix"] = (!empty($data["tags_prefix"]) ? generate_slug($data["tags_prefix"]) : "");


$tags = generateRandomArray(6, $data['asset_amount'], $data["tags_prefix"]);


$data = [];
$data['tags'] = implode(",", $tags);
$data['tags'] = str_replace(',', ', ', $data['tags']);

$data['success'] = true; 
$data['message'] = TRANS('TAGS_SUCCESSFULLY_GENERATED');
$data['message'] = message('success', '', $data['message'], '');
// $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

