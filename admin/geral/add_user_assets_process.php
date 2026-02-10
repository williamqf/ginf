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

/* A chamada está vindo diretamente da tela de detalhes do ativo */
if (isset($post['action']) && $post['action'] == 'assign_from_asset_details') {
    $fromAssetDetails = true;
}

$data['csrf_session_key'] = (isset($post['csrf_session_key']) ? $post['csrf_session_key'] : "");


if (!$fromAssetDetails) {
    
    
    $data['asset_tags_update'] = (isset($post['assetTag_update']) && !empty($post['assetTag_update']) ? array_map('noHtml', $post['assetTag_update']) : []);
    $data['asset_units_update'] = (isset($post['asset_unit_update']) && !empty($post['asset_unit_update']) ? array_map('intval', $post['asset_unit_update']) : []);

    $data['asset_tags'] = (isset($post['assetTag']) && !empty($post['assetTag']) ? array_map('noHtml', $post['assetTag']) : []);
    $data['asset_units'] = (isset($post['asset_unit']) && !empty($post['asset_unit']) ? array_map('intval', $post['asset_unit']) : []);

    $data['term_unit'] = (isset($post['term_unit']) && !empty($post['term_unit']) ? (int)$post['term_unit'] : "");
    $data['department_for_removed'] = (isset($post['department_for_removed']) && !empty($post['department_for_removed']) ? (int)$post['department_for_removed'] : "");


    if (count($data['asset_tags']) != count($data['asset_units'])) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('CHECK_ASSET_USER_ASSOCIATION'),'');

        echo json_encode($data);
        return false;
    }
}


$data['user'] = (isset($post['user_id']) && !empty($post['user_id']) ? intval($post['user_id']) : "");
if (empty($data['user'])) {
    $data['success'] = false; 
    $data['message'] = message('warning', 'Ooops!', TRANS('SOME_ERROR_DONT_PROCEED'),'');

    echo json_encode($data);
    return false;
}


$userInfo = getUserInfo($conn, $data['user']);
$userDepartment = $userInfo['user_department'];
$data['department'] = $userDepartment;


$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);
$authorDepartment = $authorInfo['user_department'];

if (!empty($authorDepartment)) {
    $authorDepartment = getDepartments($conn, null, $authorDepartment)['local'];
}

$data['author'] = $_SESSION['s_uid'];


function arrayHasDuplicates($array) {
    return count($array) !== count(array_unique($array));
}







if (!$fromAssetDetails) {
    /* Ativos já vinculados */
    $assets_old = [];
    $i = 0;
    foreach ($data['asset_tags_update'] as $key => $tag) {
        $assets_old[$i][] = $tag;
        $assets_old[$i][] = $data['asset_units_update'][$i];
        $i++;
    }

    $old_asset_ids = [];
    foreach ($assets_old as $key => $asset) {
        $old_asset_ids[] = getAssetIdFromTag($conn, $asset[1], $asset[0]);
    }

    /* Remove valores nulos */
    $old_asset_ids = array_filter($old_asset_ids, static function($var){return $var !== null;});

    $textOldAssetIds = implode(',', $old_asset_ids);

    /* Novos ativos */
    $assets_new = [];
    $i = 0;
    foreach ($data['asset_tags'] as $key => $tag) {
        $assets_new[$i][] = $tag;
        $assets_new[$i][] = $data['asset_units'][$i];
        $i++;
    }

    $new_asset_ids = [];
    foreach ($assets_new as $key => $asset) {
        $new_asset_ids[] = getAssetIdFromTag($conn, $asset[1], $asset[0]);
    }

    /* Remove valores nulos */
    $new_asset_ids = array_filter($new_asset_ids, static function($var){return $var !== null;});

    if (arrayHasDuplicates(array_merge($new_asset_ids, $old_asset_ids))) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_DUPLICATE_RECORD'),'');

        echo json_encode($data);
        return false;
    }

    $textNewAssetIds = implode(',', $new_asset_ids);


    if ($new_asset_ids) {
        /**
        * Checagem se algum dos ativos já está vinculado à algum OUTRO usuário
        */
        $sql = "SELECT * FROM users_x_assets WHERE asset_id IN ({$textNewAssetIds}) AND user_id <> :user_id AND is_current = 1"; //
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':user_id', $data['user']);
            $res->execute();
            if ($res->rowCount()) {
                $data['success'] = false; 
                $data['message'] = message('warning', 'Ooops!', TRANS('MSG_AT_LEAST_ONE_ASSET_ALREADY_ASSOCIATED'),'');
    
                echo json_encode($data);
                return false;
            }
        } catch (\PDOException $e) {
            $data['success'] = false; 
            $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
            echo json_encode($data);
            return false;
        }
    
    
        /**
         * Checar se algum dos ativos é filho de outro - neste caso não deve permitir a vinculação direta
         */
        foreach ($new_asset_ids as $assetId) {
            if (getAssetParentId($conn, $assetId)) {
                $data['success'] = false; 
                $data['message'] = message('warning', 'Ooops!', TRANS('MSG_ONLY_PARENTS_CAN_BE_VINCULATED'),'');
                echo json_encode($data);
                return false;
            }
        }
    }

    /* Checagem para saber se houve remoção de ativos da listagem do usuário */
    $user_assets_before = getAssetsFromUser($conn, $data['user']);
    $count_user_assets_before = count($user_assets_before);
    $array_assets_before_ids = [];
    $removed_ids = [];

    if ($count_user_assets_before > count($old_asset_ids)) {
        $had_removed = true;
        $array_assets_before_ids = array_column($user_assets_before, 'asset_id'); 

        /* Quais ids foram removidos */
        $removed_ids = array_diff($array_assets_before_ids, $old_asset_ids);
    }

    if ($had_removed && empty($data['department_for_removed'])) {
        $data['success'] = false; 
        $data['field_id'] = 'department_for_removed';
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_NEED_TO_INFORM_NEW_DEPARTMENT_FOR_REMOVED'),'');

        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post, $data['csrf_session_key'])) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }


    /**
     * Se o olds_asset_ids estiver vazio, posso realizar um update da tabela marcando eventuais ativos como is_current = 0
     */
    if (empty($old_asset_ids)) {
        $sql = "UPDATE users_x_assets SET is_current = 0 WHERE user_id = :user_id";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':user_id', $data['user']);
            $res->execute();
        } catch (\PDOException $e) {
            $data['success'] = false; 
            $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
            echo json_encode($data);
            return false;
        }
    } else {
        /**
         * Atualizo os registros que não foram passados via post, isso significa que foram desvinculados
         */
        $sql = "UPDATE users_x_assets SET is_current = 0 WHERE user_id = :user_id AND asset_id NOT IN ({$textOldAssetIds})";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':user_id', $data['user']);
            $res->execute();
            if ($res->rowCount()) {
            
            }
        } catch (\PDOException $e) {
            $data['success'] = false; 
            $data['message'] = message('warning', 'Ooops!', $e->getMessage() . '<hr />' . $sql,'');
            echo json_encode($data);
            return false;
        }
    }




} else {

    $new_asset_ids = [];
    $new_asset_ids[] = $post['asset_id'];

    if (!csrf_verify($post, $data['csrf_session_key'])) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
        
        echo json_encode($data);
        return false;
    }

}





/* Inserção */
foreach ($new_asset_ids as $asset_id) {
    $sql = "INSERT INTO users_x_assets 
            (
                user_id, 
                asset_id,
                author_id
            ) 
            VALUES 
            (
                :user_id, 
                :asset_id,
                :author_id
            )";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user_id', $data['user']);
        $res->bindParam(':asset_id', $asset_id);
        $res->bindParam(':author_id', $data['author']);
        $res->execute();

    } catch (\PDOException $e) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }
}

/* Atualizar o departamento e o histórico de localização do ativo. Atualiza o localização do ativo a partir do departamento do usuário */
if (!empty($userDepartment)) {
    $updateAssetDepartment = updateUserAssetsDepartment($conn, $data['user'], $data['author'], $userDepartment);
    if (!$updateAssetDepartment) {
        $exception .= '<hr />' .TRANS('MSG_ERROR_IN_LOGGING_NEW_DEPARTMENT');
    }
}


if (!$fromAssetDetails) {

    /* Atualizar a localização dos ativos removidos */
    if (count($removed_ids) > 0) {
        foreach ($removed_ids as $removed_id) {
            // $updateAssetRemovedDepartment = updateUserAssetsDepartment($conn, $data['user'], $data['author'], $data['department_for_removed']);
            $updateRemovedAssetDepartment = updateAssetDepartamentAndHistory($conn, $removed_id, $data['author'], $data['department_for_removed']);
        }
    }

    /* Checa se há mudança no term_unit em que o usuário está vinculado */
    if ($userInfo['term_unit'] != $data['term_unit']) {
        /* Atualização do term_unit na tabela usuarios */
        $sql = "UPDATE usuarios 
                SET 
                    term_unit = ". dbField($data['term_unit']) ." ,
                    term_unit_updated_at = '{$now}'
                WHERE user_id = :user_id";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':user_id', $data['user']);
            $res->execute();
        } catch (\PDOException $e) {
            $exception = $e->getMessage();
        }
    }
}





/* Atualizar a tabela pivot para facilitar a geração de relatórios */
/* Considerando o contexto deste script, o termpo não está atualizado, não está gerado, não está assinado */
$updatePivotTable = insertOrUpdateUsersTermsPivotTable($conn, $data['user'], false, false, null);


/* Notificação para o usuário */
$sentNotification = false;
if ($data['author'] != $data['user']) {
    $sentNotification = setUserNotification($conn, $data['user'], 1, TRANS('NOTIFICATION_ASSET_SIGNED_TO_USER'), $data['author']);
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

