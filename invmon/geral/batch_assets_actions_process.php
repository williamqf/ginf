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
// var_dump($post);

$data['action'] = (isset($post['action']) ? noHtml($post['action']) : "");
if (empty($data['action'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_SOMETHING_GOT_WRONG'), '');
    
    echo json_encode($data);
    return false;
}

$data['text_assets_ids'] = (isset($post['assets_ids']) ? noHtml($post['assets_ids']) : "");
if (empty($data['text_assets_ids'])) {
    $data['success'] = false;
    $data['message'] = message('warning', '', TRANS('MSG_SOMETHING_GOT_WRONG'), '');

    echo json_encode($data);
    return false;
}

$data['assets_ids'] = explode(',', $data['text_assets_ids']);
$data['assets_ids'] = array_filter($data['assets_ids']);
$data['assets_ids'] = array_map('intval', $data['assets_ids']);


/* Para manter a compatibilidade com versões antigas */
$table = "equipxpieces";
$sqlTest = "SELECT * FROM {$table}";
try {
    $conn->query($sqlTest);
}
catch (Exception $e) {
    $table = "equipXpieces";
}


if ($data['action'] == "delete") {

    /* Só permite exclusão se for admin */
    if ($_SESSION['s_nivel'] != 1) {
        $data['success'] = false; 
        $data['message'] = message('danger', 'Ooops!', TRANS('PERMISSIONS_NOT_SUFFICIENT') . $exception, '');
        echo json_encode($data);
        return false;
    }



    $data['cant_be_deleted'] = [];
    $data['reason'] = [];

    foreach ($data['assets_ids'] as $asset_id) {
        /* Verificações e ações necessárias para a remoção dos ativos */

        /* Busco os dados do ativo */
        $assetInfo = getEquipmentInfo($conn, null, null, $asset_id);

        if (empty($assetInfo)) {
            $data['success'] = false; 
            $data['message'] = message('warning', '', TRANS('MSG_SOMETHING_GOT_WRONG'), '');
            echo json_encode($data);
            return false;
        }

        $tag = $assetInfo['comp_inv'];
        $unit = $assetInfo['comp_inst'];
        $unit_name = getUnits($conn, null, (int)$unit)['inst_nome'];


        /* Checa se está vinculado a algum usuário */
        $sql = "SELECT id FROM users_x_assets WHERE asset_id = '{$asset_id}' AND is_current = 1 ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['cant_be_deleted'][$asset_id] = $tag . " - " . $unit_name;
            $data['reason'][$asset_id] = TRANS('CANT_DELETE_DUE_ALLOCATION');
            $data['success'] = false; 
            
            continue;
        }


        /* Checa se há componentes avulsos associados */
        $sql = "SELECT * FROM {$table} WHERE eqp_equip_inv = '{$tag}' AND eqp_equip_inst = '{$unit}' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            
            $data['cant_be_deleted'][$asset_id] = $tag . " - " . $unit_name;
            $data['reason'][$asset_id] = TRANS('CANT_DELETE_DUE_LEGACY_AGREGATION');
            $data['success'] = false; 
            
            continue;
        }

        /* Checa se há chamados associados */
        $sql = "SELECT * FROM ocorrencias WHERE equipamento = '{$tag}' AND instituicao = '{$unit}' ";
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['cant_be_deleted'][$asset_id] = $tag . " - " . $unit_name;
            $data['reason'][$asset_id] = TRANS('CANT_DELETE_DUE_TICKETS');
            $data['success'] = false; 
            
            continue;
        }


        /* Checa se há ativos agregados */
        $hasAggregated = getAssetSpecs($conn, $asset_id, true);
        if (!empty($hasAggregated)) {
            $data['cant_be_deleted'][$asset_id] = $tag . " - " . $unit_name;
            $data['reason'][$asset_id] = TRANS('CANT_DELETE_DUE_AGGREGATION');
            $data['success'] = false; 
            
            continue;
        }

        /* Checa se o ativo possui um ativo pai */
        $hasParent = getAssetParentId($conn, $asset_id);
        if (!empty($hasParent)) {
            $data['cant_be_deleted'][$asset_id] = $tag . " - " . $unit_name;
            $data['reason'][$asset_id] = TRANS('CANT_DELETE_DUE_AGGREGATED_PARENT');
            $data['success'] = false; 
            
            continue;
        }
    }

    if (!empty($data['cant_be_deleted'])) {

        foreach ($data['cant_be_deleted'] as $asset_id => $asset_name) {
            $data['reason'][$asset_id] = '<li>' . $asset_name . ': ' . $data['reason'][$asset_id] . '</li>';
        }
        $data['reason'] = '<ul>' .implode('', $data['reason']) . '</ul>';

        $data['success'] = false;
        $data['message'] = message('danger', 'Ooops!', TRANS('MSG_NO_DELETION_DUE_RESTRICTIONS') . '<hr />' . $data['reason'] . '<hr />' . TRANS('MSG_CAN_BE_OTHERS_RESTRICTIONS'), '');
        echo json_encode($data);
        return false;
    }

    /* Guardar o log com a lista de ativos excluídos e o usuário responsável */
    $author = $_SESSION['s_uid'];
    $ipAddress = getClientIP();
    $actionType = "DELETE_ASSET";
    $actionDetails = "";
    foreach ($data['assets_ids'] as $asset_id) {
        $assetInfo = getEquipmentInfo($conn, null, null, $asset_id);
        $tag = $assetInfo['comp_inv'];
        $unit = $assetInfo['comp_inst'];
        $unit_name = getUnits($conn, null, (int)$unit)['inst_nome'];
        
        $textAssetInfo = implode(", ", $assetInfo);
        $actionDetails .= "<li>{$tag} - {$unit_name} - ID: {$asset_id} - Record: {$textAssetInfo}</li>\n";
    }

    $logged = recordUserLog($conn, $author, $actionType, $actionDetails, $ipAddress);
    if (!$logged) {
        $exception .= "<hr>" . $conn->errorInfo()[2];
    }


    /* Sem restrições para excluir os ativos */
    foreach ($data['assets_ids'] as $asset_id) {
        
        $assetInfo = getEquipmentInfo($conn, null, null, $asset_id);
        $tag = $assetInfo['comp_inv'];
        $unit = $assetInfo['comp_inst'];
        $unit_name = getUnits($conn, null, (int)$unit)['inst_nome'];
        
        
        $sql = "DELETE FROM equipamentos WHERE comp_cod = {$asset_id} ";
        try {
            $conn->exec($sql);
            $data['success'] = true; 
            
            /* Remover do historico de localizacao (hist_inv, hist_inst) */
            $sql = "DELETE FROM historico WHERE hist_inv = '{$tag}' AND hist_inst = '{$unit}' ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
            
            /* Remover do historico de alteração de hardware (hwa_inst, hwa_inv) */
            $sql = "DELETE FROM hw_alter WHERE hwa_inv = '{$tag}' AND hwa_inst = '{$unit}' ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
            
            /* Remover do hw_sw (hws_hw_inst, hws_hw_cod) também */
            $sql = "DELETE FROM hw_sw WHERE hws_hw_cod = '{$tag}' AND hws_hw_inst = '{$unit}' ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }


            /* Remover campos customizados relacionados ao ativo */
            $sql = "DELETE FROM assets_x_cfields WHERE asset_id = '{$asset_id}' ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
                
            
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>";
            $data['success'] = false; 
            $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
            $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
            echo json_encode($data);
            return false;
        }
    }

    $data['message'] = TRANS('OK_BATCH_DELETE');
    $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
    echo json_encode($data);
    return false;
}


// var_dump($data);

echo json_encode($data);
return false;