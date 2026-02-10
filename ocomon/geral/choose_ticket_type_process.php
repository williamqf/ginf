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
require_once __DIR__ . "/" . "../../includes/components/html2text-master/vendor/autoload.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();


/* Configurações globais */
$config = getConfig($conn);

$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);
/* Informações sobre a área solicitante - área do usuário logado */
// $areaInfo = getAreaInfo($conn, $_SESSION['s_area']);
$areaInfo = getAreaInfo($conn, $authorInfo['area_id']);

$pre_filters_string = "";

// /* Checa se há configuração de pré-filtros na área solicitante e caso não exista consulta também na configuração global */
// if (!empty($areaInfo['sis_cat_chain_at_opening'])) {
// 	$pre_filters_string = $areaInfo['sis_cat_chain_at_opening'];
// } elseif (!empty($config['conf_cat_chain_at_opening'])) {
// 	$pre_filters_string = $config['conf_cat_chain_at_opening'];
// }


/* Checa se há configuração de pré-filtros na área solicitante e caso não exista consulta também na configuração global */
if (!empty($areaInfo['use_own_config_cat_chain'] && $areaInfo['use_own_config_cat_chain'])) {
	$pre_filters_string = (!empty($areaInfo['sis_cat_chain_at_opening']) ? $areaInfo['sis_cat_chain_at_opening'] : "");
} elseif (!empty($config['conf_cat_chain_at_opening'])) {
	$pre_filters_string = $config['conf_cat_chain_at_opening'];
}


$totalPreFilters = 0;
if (!empty($pre_filters_string)) {
	$pre_filters_array = explode(',', $pre_filters_string);
	$totalPreFilters = count($pre_filters_array);
}




$post = $_POST;

$erro = false;
$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['info_messages'] = [];
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['field_id'] = "";

$data['issue_type'] = (isset($post['issue_type']) ? noHtml($post['issue_type']) : "");
$data['params'] = (isset($post['params']) ? noHtml($post['params']) : "");
// $data['params'] = (isset($post['params']) ? $post['params'] : "");

$paramPrefix = "amp;";
if (isset($data['params']) && !empty($data['params'])) {
	$data['params'] = str_replace($paramPrefix, "", $data['params']);
}

$data['profile_id'] = "";
$data['prob_descricao'] = "";
$data['area_to_open'] = "";


// var_dump($post);

/* Validações */
if ($totalPreFilters) {
    foreach ($pre_filters_array as $key => $filterSufix) {
        if (empty($post['filter_' . $filterSufix])) {
            $data['success'] = false; 
            $data['field_id'] = 'filter_' . $filterSufix;
            $data['message'] = message('warning', 'Ooops!', TRANS('MUST_FILL_ALL_CATEGORIES_FILTERS'),'');
            echo json_encode($data);
            return false;
        }
        // $data['breadcrumb'][] = getIssueCategoryNameBySufixAndId($conn, $filterSufix, $post['filter_' . $filterSufix]);
        // $data['params'] .= "&filter_" . $filterSufix . "=" . $post['filter_' . $filterSufix];
    }
}



if (empty($data['issue_type'])) {
    $data['success'] = false; 
    $data['field_id'] = "issue_type";
    $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
    echo json_encode($data);
    return false;
}

$areaToOpen = [];
$possibleAreasToOpen = [];
$arrayAreaDynamic = getAreaInDynamicMode($conn, $data['issue_type'], $_SESSION['s_area'], $_SESSION['s_uareas']);

$possibleAreasToOpen = $arrayAreaDynamic['common_areas_btw_issue_and_user'];
$areaToOpen = $arrayAreaDynamic['area_receiver'];
if ($arrayAreaDynamic['many_options']) {
    // $areaToOpen = $arrayAreaDynamic['common_areas_btw_issue_and_user'];
}

$areaToOpenInfo = getAreaInfo($conn, $areaToOpen[0]);
$data['area_to_open'] = $areaToOpenInfo['area_name'];


$script = "";
$hasScript = issueHasScript($conn, $data['issue_type']);
$enduser = issueHasEnduserScript($conn, $data['issue_type']);

if (($_SESSION['s_nivel'] < 3 && $hasScript) || ($enduser)) {
    $script = "<a onClick=\"popup('../../admin/geral/scripts_documentation.php?action=endview&prob=".$data['issue_type']."')\">".TRANS('TIPS')."</a>";
}



$sql = "SELECT 
            prob_descricao, 
            prob_profile_form,
            need_authorization 
        FROM 
            problemas 
        WHERE 
            prob_id = '" . $data['issue_type'] . "' ";
try {
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        
        $row = $res->fetch();
        
        $data['profile_id'] = ($row['prob_profile_form'] ?? getDefaultScreenProfile($conn));
        $data['prob_descricao'] = $row['prob_descricao'];
        $data['need_authorization'] = $row['need_authorization'];
        $mainIconClass = '';

        $beforeScriptIcon = '';
        $beforeAuthorizationIcon = '';
        if (!empty($row['prob_descricao'])) {
            // $row['prob_descricao'] = TRANS('TYPE_OF_ISSUE_INDICATED_TO') . ':&nbsp;' . $row['prob_descricao'];

            $row['prob_descricao'] = toHtml(TRANS('TYPE_OF_ISSUE_INDICATED_TO') . ':&nbsp;' . $row['prob_descricao']);
            
            $mainIconClass = 'far fa-lightbulb';
            $beforeScriptIcon = '<hr>';
            $beforeAuthorizationIcon = '<hr>';
        }

        $textNeedAuthorization = '';
        $needAuthorizationIcon = '';
        $scriptIcon = '';
        if (!empty($data['need_authorization']) && $data['need_authorization'] != 0) {
            $textNeedAuthorization = TRANS('MSG_ISSUE_TYPE_NEEDS_AUTHORIZATION');
            $mainIconClass = (!empty($mainIconClass) ? $mainIconClass : 'fas fa-info-circle');

            $needAuthorizationIcon = $beforeAuthorizationIcon . ($mainIconClass == 'fas fa-info-circle' ? '' : '<i class="fas fa-info-circle"></i>&nbsp;');
        }

        if (!empty($script)) {
            $mainIconClass = (!empty($mainIconClass) ? $mainIconClass : 'far fa-hand-point-right');
            $scriptIcon = $beforeScriptIcon . ($mainIconClass == 'far fa-hand-point-right' ? '' : '<i class="far fa-hand-point-right"></i>&nbsp;');
        }
        
        $data['info_messages'][] = $row['prob_descricao'];
        $data['info_messages'][] = $needAuthorizationIcon . $textNeedAuthorization;
        $data['info_messages'][] = $scriptIcon . $script;

        // $fullMessageText = implode("<hr/>", array_filter($data['info_messages']));
        $fullMessageText = implode("<br/>", array_filter($data['info_messages']));

        $data['description'] = '';
        if (!empty($fullMessageText)) {
            $data['description'] = message('info', '', $fullMessageText, '', '', true, $mainIconClass);
        }

    }
}
catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
}



echo json_encode($data);
