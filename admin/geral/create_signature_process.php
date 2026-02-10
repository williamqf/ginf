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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);
$exception = "";
$now = date('Y-m-d H:i:s');
$data = [];
$data['success'] = true;
$maxFileSize = 150 * 1024;

$post = $_POST;
$files = $_FILES;

$signature_draw_data = isset($post['data_signature']) && !empty($post['data_signature']);
$signature_file_data = isset($files['signature_file']) && !empty($files['signature_file']['name']);


if (!$signature_draw_data && !$signature_file_data) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('CHECK_FILLED_DATA'),'');

    echo json_encode($data);
    return false;
}


if ($signature_draw_data) {
    $data['data_signature'] = explode(',', $post['data_signature']); 
    $data['file_type'] = $data['data_signature'][0];
    $data['file'] = base64_decode($data['data_signature'][1]);
} else {
    $data['file_type'] = 'data:' . $files['signature_file']['type'] . ';base64';
    $data['file'] = file_get_contents($files['signature_file']['tmp_name']);
}

$data['author'] = $_SESSION['s_uid'];
$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");





$tmp_file_prefix = 'oc_';
$tmp_dir = sys_get_temp_dir();
$tmp_path_and_name = tempnam($tmp_dir, $tmp_file_prefix);

file_put_contents($tmp_path_and_name, $data['file']);

$file_size = filesize($tmp_path_and_name);
$mime_type = mime_content_type("{$tmp_path_and_name}");

/* Apenas imagens são permitidas */
$allowedTypes = "image\/(pjpeg|jpeg|png|gif|x-ms-bmp|svg\+xml)"; 

if (!preg_match("/^" . $allowedTypes . "$/i", $mime_type)) {
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


if (!csrf_verify($post, $data['csrf_session_key'])) {
    $data = [];
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

    echo json_encode($data);
    return false;
}


$sql = "INSERT
        INTO
            users_x_signatures
            (
                user_id,
                signature_file,
                file_type,
                file_size,
                created_at
            )
        VALUES 
        (
            :user_id,
            :signature_file,
            :file_type,
            :file_size,
            :created_at
        )
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $data['author'], PDO::PARAM_INT);
    $stmt->bindParam(':signature_file', $data['file'], PDO::PARAM_LOB);
    $stmt->bindParam(':file_type', $data['file_type'], PDO::PARAM_STR);
    $stmt->bindParam(':file_size', $file_size, PDO::PARAM_INT);
    $stmt->bindParam(':created_at', $now, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    $data = [];
    $exception .= $e->getMessage();
    $data['success'] = false; 
    $data['message'] = message('danger', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED') . $exception,'');

    echo json_encode($data);
    return false;
}


$data = [];
$data['success'] = true; 
$data['message'] = TRANS('MSG_SIGNATURE_SUCCESS_SAVED');
$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

