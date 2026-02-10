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


$maxFileSize = 10 * 1024;


$post = $_POST;
$files = $_FILES;

/* Validar se o asset_amount é um número */
if (!isset($post['asset_amount']) || empty($post['asset_amount']) || !filter_var($post['asset_amount'], FILTER_VALIDATE_INT) || $post['asset_amount'] <= 0) {
    $data['success'] = false; 
    $data['field_id'] = 'asset_amount';
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ERROR_WRONG_VALUE'),'');
    echo json_encode($data);
    return false;
}
$data['asset_amount'] = ($post['asset_amount'] > $maxAmountEachTime ? $maxAmountEachTime : (int)$post['asset_amount']);


$has_file = isset($files['txt_file']) && !empty($files['txt_file']['name']);


if (!$has_file) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('CHECK_FILLED_DATA'),'');

    echo json_encode($data);
    return false;
}

$file_type = $files['txt_file']['type'];
$file_tmp_name = $files['txt_file']['tmp_name'];
$file_size = $files['txt_file']['size'];


/* Apenas arquivos de texto são permitidos */
$allowedTypes = "text\/(plain|csv)"; 

if (!preg_match("/^" . $allowedTypes . "$/i", $file_type)) {
    $data = [];
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FILETYPE_NOT_ALLOWED'),'');

    echo json_encode($data);
    return false;
}

if ($file_size > $maxFileSize) {
    $data = [];
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FILE_TOO_HEAVY'),'');

    echo json_encode($data);
    return false;
}


$data['fileContent'] = file_get_contents($files['txt_file']['tmp_name']);

$tags = explode(",", $data['fileContent']);
$tags = array_map("trim", $tags);
$tags = array_map("noHtml", $tags);
$tags = array_unique($tags);

if (count($tags) != $data['asset_amount']) {
    $data = [];
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('NUMBER_OF_TAGS_DOESNT_MATCH_FILE'),'');
    echo json_encode($data);
    return false;
}






$data = [];
$data['fileContent'] = implode(",", $tags);
$data['fileContent'] = str_replace(' ', '', $data['fileContent']);

$data['success'] = true; 
$data['message'] = TRANS('TXT_FILE_SUCCESSFULLY_LOADED');
$data['message'] = message('success', '', $data['message'], '');
// $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

