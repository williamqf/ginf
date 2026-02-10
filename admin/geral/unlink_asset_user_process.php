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
$now = date('Y-m-d H:i:s');
$data = [];
$data['success'] = true;
$had_removed = false;
$fromAssetDetails = false;

$mailConfig = getMailConfig($conn);

$post = $_POST;

$data['asset_id'] = (isset($post['asset_id']) ? (int)$post['asset_id'] : "");
$data['asset_new_department'] = (isset($post['asset_new_department']) ? (int)$post['asset_new_department'] : "");
$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");


if (empty($data['asset_id']) || empty($data['asset_new_department'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');

    echo json_encode($data);
    return false;
}


$alocatedTo = getUserFromAssetId($conn, $data['asset_id']);

if (empty($alocatedTo)) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');

    echo json_encode($data);
    return false;
}

$data['user_id'] = $alocatedTo['user_id'];
$userInfo = getUserInfo($conn, $data['user_id']);

if ($alocatedTo['user_id'] == $_SESSION['s_uid'] && $_SESSION['s_nivel'] > 1) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ONLY_ADMIN_CAN_AUTO_REMOVE_ASSET'),'');

    echo json_encode($data);
    return false;
}

$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);
$authorDepartment = $authorInfo['user_department'];
if (!empty($authorDepartment)) {
    $authorDepartment = getDepartments($conn, null, $authorDepartment)['local'];
}
$data['author'] = $_SESSION['s_uid'];


// if (!csrf_verify($post, $data['csrf_session_key'])) {
//     $data['success'] = false; 
//     $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');

//     echo json_encode($data);
//     return false;
// }




/* Remoção do vínculo */
$sql = "UPDATE 
            users_x_assets 
        SET 
            is_current = 0,
            author_id = :author_id
        WHERE 
            user_id = :user_id AND 
            asset_id = :asset_id 
        ";
try {
    $res = $conn->prepare($sql);
    $res->bindParam(':user_id', $data['user_id']);
    $res->bindParam(':asset_id', $data['asset_id']);
    $res->bindParam(':author_id', $data['author']);
    $res->execute();


    /* Atualizar o departamento do ativo */
    $updateRemovedAssetDepartment = updateAssetDepartamentAndHistory($conn, $data['asset_id'], $data['author'], $data['asset_new_department']);


    /* Atualizar a tabela pivot para facilitar a geração de relatórios */
    /* Considerando o contexto deste script, o termpo não está atualizado, não está gerado, não está assinado */
    $updatePivotTable = insertOrUpdateUsersTermsPivotTable($conn, $data['user_id'], false, false, null);



} catch (\PDOException $e) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
    echo json_encode($data);
    return false;
}



/* Notificação para o usuário */
$sentNotification = false;
if ($data['author'] != $data['user_id']) {
    $sentNotification = setUserNotification($conn, $data['user_id'], 1, TRANS('NOTIFICATION_ASSET_UNLINKED_FROM_USER'), $data['author']);
}



/* Processos para envio do e-mail para o usuário */
$envVars = [];
/* Injeto os valores das variáveis específicas para esse evento */
$envVars['%usuario%'] = $userInfo['nome'];
$envVars['%autor%'] = $authorInfo['nome'];
$envVars['%autor_departamento%'] = $authorDepartment;
$envVars['%data%'] = dateScreen($now);

$event = "alocate-asset-to-user";
$eventTemplate = getEventMailConfig($conn, $event);

/* Disparo do e-mail (ou fila no banco) para cada operador */
$mailSendMethod = 'send';
if ($mailConfig['mail_queue']) {
    $mailSendMethod = 'queue';
}
$mail = (new Email())->bootstrap(
    transvars($eventTemplate['msg_subject'], $envVars),
    transvars($eventTemplate['msg_body'], $envVars),
    $userInfo['email'],
    $eventTemplate['msg_fromname']
);

if (!$mail->{$mailSendMethod}()) {
    $mailNotification .= "<hr>" . TRANS('EMAIL_NOT_SENT') . "<hr>" . $mail->message()->getText();
}



$data['success'] = true; 
$data['message'] = TRANS('MSG_SUCCESSFULLY_ASSOCIATED');
$_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');

echo json_encode($data);
// dump($return);
return true;

