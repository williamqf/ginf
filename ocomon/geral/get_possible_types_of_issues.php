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



$pre_filters_string = "";
$pre_filters_array = [];

/* Configurações globais */
$config = getConfig($conn);

$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);
/* Informações sobre a área solicitante - área do usuário logado */
// $areaInfo = getAreaInfo($conn, $_SESSION['s_area']);
$areaInfo = getAreaInfo($conn, $authorInfo['area_id']);

/* Checa se há configuração de pré-filtros na área solicitante e caso não exista consulta também na configuração global */
if (!empty($areaInfo['use_own_config_cat_chain'] && $areaInfo['use_own_config_cat_chain'])) {
	$pre_filters_string = (!empty($areaInfo['sis_cat_chain_at_opening']) ? $areaInfo['sis_cat_chain_at_opening'] : "");
} elseif (!empty($config['conf_cat_chain_at_opening'])) {
	$pre_filters_string = $config['conf_cat_chain_at_opening'];
}


$totalPreFilters = 0;
if (!empty($pre_filters_string)) {
	$pre_filters_array = explode(',', (string)$pre_filters_string);
	// $totalPreFilters = count($pre_filters_array);
}


$post = $_POST;

$returnCategories = false;

$categories = (isset($post['categories']) ? json_decode($post['categories'], true) : []);
$output = (isset($post['output']) ? noHtml($post['output']) : 'issues');
$categorieSufix = (isset($post['categorieSufix']) ? (int)$post['categorieSufix'] : '');
$position = (isset($post['position']) ? (int)$post['position'] : 0);

// Primeiro combo de categorias - nenhuma categoria selecionada
if ($position == 0 && $output != 'issues') {
    $categories = [];
}


// if ($output == 'issues') {
//     var_dump([
//         'post categories' => $post['categories'],
//         'json_decoded categories' => json_decode($post['categories'], true),
//         'categories' => $categories
//     ]);
// }

// var_dump($pre_filters_array);
// var_dump($post);
$typesOfIssues = getIssuesByArea4($conn, false, null, 0, $_SESSION['s_uareas'], null, $categories, $pre_filters_array);

// var_dump($typesOfIssues);

if ($output != 'issues' && !empty($categorieSufix)) {
    $categoriesUsed = getUsedCatsFromPossibleIssues($categorieSufix, $typesOfIssues);
    $data = $categoriesUsed;
} else {
    $data = $typesOfIssues;
}

echo json_encode($data);