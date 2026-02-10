<?php
/* Copyright 2023 Flávio Ribeiro

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
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();


$config = getConfig($conn);
$configExt = getConfigValues($conn);

$isAdmin = $_SESSION['s_nivel'] == 1;
$files = array();
$files = getDirFileNames('../../includes/languages/');

if (!defined('ALLOWED_LANGUAGES')) {
    $langLabels = [
        'pt_BR.php' => TRANS('LANG_PT_BR'),
        'en.php' => TRANS('LANG_EN'),
        'es_ES.php' => TRANS('LANG_ES_ES')
    ];
} else {
    $langLabels = ALLOWED_LANGUAGES;
}

array_multisort($langLabels, SORT_LOCALE_STRING);



$searchStatusTermOptions = [
    // '0' => TRANS('OCO_SEL_ANY'),
    '1' => TRANS('NO_ASSETS_LINKED'),
    '2' => TRANS('WITH_ASSETS_LINKED'),
    '3' => TRANS('TERM_OUTDATED'),
    '4' => TRANS('TERM_SIGNED'),
    '5' => TRANS('SIGNING_PENDING'),
];
/* Para recuperar as chaves após a ordenação */
$searchStatusTermOptionsKeys = [
    // TRANS('OCO_SEL_ANY') => '0',
    TRANS('NO_ASSETS_LINKED') => '1',
    TRANS('WITH_ASSETS_LINKED') => '2',
    TRANS('TERM_OUTDATED') => '3',
    TRANS('TERM_SIGNED') => '4',
    TRANS('SIGNING_PENDING') => '5'
];
array_multisort($searchStatusTermOptions, SORT_LOCALE_STRING);
/* Recuperando as chaves originais mesmo após a ordenação */
$arrayTmp = [];
foreach ($searchStatusTermOptions as $key => $value) {
    $arrayTmp[$searchStatusTermOptionsKeys[$value]] = $value;
}
$searchStatusTermOptions = $arrayTmp;

$jsonTermStatusOptions = json_encode($searchStatusTermOptions);


$sqlUserLang = "SELECT upref_lang FROM uprefs WHERE upref_uid = " . $_SESSION['s_uid'] . "";
$execUserLang = $conn->query($sqlUserLang);
$rowUL = $execUserLang->fetch();
$hasUL = $execUserLang->rowcount();

$areaAdmin = 0;
$user_id = "";
// $localAuth = AUTH_TYPE == "SYSTEM";
$localAuth = (isset($configExt['AUTH_TYPE']) && !empty($configExt['AUTH_TYPE']) && $configExt['AUTH_TYPE'] != 'SYSTEM' ? false : true); 

$allowAddAssets = $_SESSION['s_nivel'] == 1 ? true : false;



if (isset($_GET['action']) && $_GET['action'] == 'profile') {
    $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);
    $user_id = $_SESSION['s_uid'];
    $_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];
} else {
    if (isset($_SESSION['s_area_admin']) && $_SESSION['s_area_admin'] == '1' && $_SESSION['s_nivel'] != '1') {
        $areaAdmin = 1;
    }

    if ($areaAdmin) {
        $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);
    } else {
        $auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

        if (!$config['conf_updated_issues']) {
            redirect('update_issues_areas.php');
            exit;
        }
    }

    $_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];

}

/* Departamentos para definição de ativos removidos */
$departments = getDepartments($conn);


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/jquery/datetimepicker/jquery.datetimepicker.css" />

    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <style>
        
        li.area_admins {
			line-height: 1.5em;
		}

		td.admins {
			min-width: 15%;
		}

        tr {
            height: 40px;
        }
        .container-switch {
			position: relative;
		}

		.switch-next-checkbox {
			position: absolute;
			top: 0;
			left: 140px;
			z-index: 1;
		}
        
        .chart-container {
            position: relative;
            /* height: 100%; */
            max-width: 100%;
            margin-left: 10px;
            margin-right: 10px;
            margin-bottom: 30px;
        }

        .bt-assets:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f0c1";
            font-weight: 900;
            font-size: 16px;
        }
        .bt-download:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f019";
            font-weight: 900;
            font-size: 16px;
        }
        .bt-generate:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f1c1";
            font-weight: 900;
            font-size: 16px;
        }

        .bt-signature:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f5b7";
            font-weight: 900;
            font-size: 16px;
        }

        .bt-sign:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f573";
            font-weight: 900;
            font-size: 16px;
        }

        .bt-view:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f06e";
            font-weight: 900;
            font-size: 16px;
        }

        .canvas-signature {
			background-color: white;
		}

        .dataTables_filter select {
            border: 1px solid gray;
            border-radius: 4px;
            background-color: white;
            height: 25px;

            float: auto !important;
        }

        .term_status_custom {
            border: 1px solid gray;
            border-radius: 4px;
            background-color: white;
            height: 25px;

        }

    </style>

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>


    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    <div id="divResult"></div>
    <input type="hidden" name="label_close" id="label_close" value="<?= TRANS('BT_CLOSE'); ?>">
	<input type="hidden" name="label_return" id="label_return" value="<?= TRANS('TXT_RETURN'); ?>">


    <div class="container-fluid" id="container_users">
        <h5 class="my-4"><i class="fas fa-user-friends text-secondary"></i>&nbsp;<?= TRANS('MNL_USUARIOS'); ?></h5>
        <div id="div_flash"></div>
        <div class="modal" id="modalIframe" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetailsIframe" style="position:relative">
                        <iframe id="iframe-content"  frameborder="1" style="position:absolute;top:0px;width:100%;height:100vh;"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>
        
        <?= alertRequiredModule('fileinfo'); ?>


        <?php
        if ((isset($_GET['action']) && $_GET['action'] == "profile") && empty($_POST['submit'])) {
            $signature_info = getUserSignatureFileInfo($conn, $_SESSION['s_uid']);
            $idxTitleModalSignature = 'DEFINE_YOUR_SIGNATURE';
            $idxHelperSignature = 'HELPER_SIGNATURE';

            if (!empty($signature_info)) {
                $idxTitleModalSignature = 'REDEFINE_YOUR_SIGNATURE';
                $idxHelperSignature = 'HELPER_REDEFINE_SIGNATURE';
            }
            ?>
            <div class="modal" id="modal_signature" tabindex="-1" style="z-index:9001!important">
                <div class="modal-dialog "> <!-- modal-lg -->
                    <div class="modal-content">
                        <div id="divResultSignature"></div>
                        <div class="modal-header text-center bg-light">
                            <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-signature"></i>&nbsp;<?= TRANS($idxTitleModalSignature); ?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="row mx-2 mt-4" id="divDetailsSignature" >
                            <div class="form-group col-md-12" id="signature_pad">
                                <?= message('info', '', TRANS($idxHelperSignature), '', '', true); ?>
                                <form name="form_signature" id="form_signature" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                                    <?= csrf_input('signature-pad'); ?>
                                    <div class="form-group row my-4">
                                        <div class="form-group col-md-12">
                                            <div class="canvas-container">
                                                <canvas class="canvas-signature border" id="canvas_signature" width="400px" height="200px"></canvas>
                                                <input type="hidden" name="data_signature" id="data_signature" value="" />
                                            </div>	
                                        </div>	
                                        <div class="form-group col-md-12">
                                            <button class="btn btn-primary" id="save-svg"><?= TRANS('SAVE'); ?></button>
                                            <button class="btn btn-primary" id="undo"><?= TRANS('UNDO'); ?></button>
                                            <button class="btn btn-primary" id="clear"><?= TRANS('CLEAR'); ?></button>
                                        </div>		
                                        
                                        <?php
                                            $idxCreateSignature = 'CREATE_OR_LOAD_SIGNATURE_FILE';
                                            $signature_info = getUserSignatureFileInfo($conn, $_SESSION['s_uid']);
                                        ?>

                                        <div class="w-100"></div>
                                        
                                        <div class="form-group col-md-12">
                                            <div class="field_wrapper" id="field_wrapper">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <i class="fa fa-signature"></i></a>
                                                        </div>
                                                    </div>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="signature_file" id="signature_file" aria-describedby="signature_file" lang="br">
                                                        <label class="custom-file-label text-truncate" for="signature_file"><?= TRANS('CHOOSE_FILE'); ?></label>
                                                    </div>
                                                    <input type="hidden" name="text_choose_file" id="text_choose_file" value="<?= TRANS('CHOOSE_FILE'); ?>">
                                                </div>
                                                <small class="form-text text-muted"><?= TRANS('HELPER_SIGNATURE_FILE'); ?></small>
                                            </div>
                                        </div>	
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <!-- Modal para assinatura do termo de compromisso -->
            <div class="modal" id="modal_sign_term" tabindex="-1" style="z-index:9001!important">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div id="divDetailsSignTerm" style="position:relative">
                            <iframe id="iframeSignTerm"  frameborder="1" style="position:absolute;top:0px;width:100%;height:100vh;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <?php
        }
        
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $user_id = (!empty($user_id) ? $user_id : (isset($_GET['cod']) ? (int)$_GET['cod'] : ""));

        /* Termo de compromisso */
        if (!empty($user_id)) {

            $userInfo = getUserInfo($conn, $user_id);
            $userClient = (!empty($userInfo['user_client']) ? $userInfo['user_client'] : "");
            $userLevel = $userInfo['nivel'];
            $units = "";
            if (!empty($userClient)) {
                $units = getUnits($conn, 1, null, $userClient);
            }

            $commitmentTermId = getUserLastCommitmentTermId($conn, $user_id);
            $termUpdated = isUserTermUpdated($conn, $user_id);

            $termSigned = ($termUpdated ? isLastUserTermSigned($conn, $user_id) : false);

            $disableGenerate = ($termUpdated ? ' disabled' : '');
            $disableDownload = (!$termUpdated ? ' disabled' : '');

            $user_assets = getAssetsFromUser($conn, $user_id);
            $assets_info = [];
            if (!empty($user_assets)) {
                foreach ($user_assets as $asset) {
                    $assets_info[] = getAssetBasicInfo($conn, $asset['asset_id']);
                }

                /* Copiar o índice created_at, do array user_assets para o array assets_info */
                foreach ($assets_info as $key => $asset) {
                    $asset['created_at'] = $user_assets[$key]['created_at'];
                    $assets_info[$key] = $asset;
                }
            }

            $assets_info = arraySortByColumn($assets_info, 'tipo_nome', SORT_ASC);
        }


        $query = "SELECT u.*, n.*,s.*, cl.*, l.* FROM usuarios u 
                    LEFT JOIN sistemas AS s ON u.AREA = s.sis_id
                    LEFT JOIN nivel AS n ON n.nivel_cod = u.nivel
                    LEFT JOIN clients AS cl ON cl.id = u.user_client 
                    LEFT JOIN localizacao AS l ON l.loc_id = u.user_department 
                WHERE
                    u.user_id > 0 ";


        if ($areaAdmin) {

            $userManageableAreas = getManagedAreasByUser($conn, $_SESSION['s_uid']);
            $csvAreas = "";
            foreach ($userManageableAreas as $mArea) {
                if (strlen((string)$csvAreas) > 0) 
                    $csvAreas .= ',';
                $csvAreas .= $mArea['sis_id'];
            }
            // $query .= " AND s.sis_id = " . $_SESSION['s_area'] . " ";
            $query .= " AND s.sis_id IN ({$csvAreas}) ";
        }

        // if (isset($_GET['cod'])) {
        if (!empty($user_id)) {
            $query .= " AND u.user_id = '" . $user_id . "' ";
        }
        $query .= "ORDER BY u.nome";
        $resultado = $conn->query($query);
        $registros = $resultado->rowCount();

        if ($allowAddAssets && !empty($userInfo['user_client']) && !empty($userInfo['user_department']) && $userLevel <= 3) {
            ?>
            <!-- Modal para vincular ativos ao usuário -->
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form_assets">
                <?= csrf_input('csrf_assets'); ?>
            <div class="modal fade" id="modalAssets" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="myModalAssets" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div id="divResultAssets"></div>
                        <div class="modal-header text-center bg-light">

                            <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-link"></i>&nbsp;<?= TRANS('ASSETS_TO_USER_ASSOCIATION'); ?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        

                        <?php
                            
                            $idx = 0;
                            if (!empty($assets_info)) {
                                // Recursos já alocados para o chamado
                                foreach ($assets_info as $asset) {
                                    $marginTop = ($idx == 0) ? 'mt-4' : '';
                                    ?>
                                        <div class="row mx-2 <?= $marginTop; ?> old_assets_row">
                                            <div class="form-group col-md-2 <?= $asset['comp_cod']; ?>">
                                                <div class="field_wrapper_specs">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">
                                                                <a href="javascript:void(0);" class="remove_button_assets" data-random="<?= $asset['comp_cod']; ?>" title="<?= TRANS('REMOVE'); ?>"><i class="fa fa-trash text-danger"></i></a>
                                                            </div>
                                                        </div>
                                                        <input type="text" name="assetTag_update[]" class="form-control" id="assetTag<?= $asset['comp_cod']; ?>" value="<?= $asset['comp_inv']; ?>" readonly/>
                                                    </div>
                                                    <small class="form-text text-muted"><?= TRANS('ASSET_TAG_TAG'); ?></small>
                                                </div>
                                            </div>

                                            
                                            <div class="form-group col-md-4 <?= $asset['comp_cod']; ?>">
                                                <div class="field_wrapper_specs" >
                                                    
                                                    <select class="form-control bs-select"  name="asset_unit_update[]" id="asset_unit<?= $asset['comp_cod'];?>" readonly>
                                                        <option data-subtext="<?= $asset['cliente']; ?>" value="<?= $asset['inst_cod']; ?>"><?= $asset['inst_nome']; ?></option>
                                                    </select>

                                                    <small class="form-text text-muted"><?= TRANS('REGISTRATION_UNIT'); ?></small>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2 <?= $asset['comp_cod']; ?>">
                                                <div class="field_wrapper_specs" >
                                                    
                                                    <input type="text" name="asset_department[]" class="form-control" id="asset_department<?= $asset['comp_cod']; ?>" value="<?= $asset['local']; ?>" disabled/>

                                                    <small class="form-text text-muted"><?= TRANS('DEPARTMENT'); ?></small>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-4 <?= $asset['comp_cod']; ?>">
                                                <div class="field_wrapper_specs" >
                                                    

                                                    <?php
                                                        $asset_description = $asset['tipo_nome'] . '&nbsp;' . $asset['fab_nome'] . '&nbsp;' . $asset['marc_nome'];
                                                    ?>

                                                    <input type="text" name="asset_desc[]" class="form-control" id="asset_desc<?= $asset['comp_cod']; ?>" value="<?= $asset_description; ?>" disabled/>

                                                    <small class="form-text text-muted"><?= TRANS('ASSET_TYPE'); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                    $idx++;
                                }
                            }
                            $marginTop = ($idx == 0) ? 'mt-4' : '';
                        ?>
                        <!-- Novos recursos que podem ser alocados -->
                        <div class="row mx-2 <?= $marginTop; ?>">

                            <div class="form-group col-md-2">
                                <div class="field_wrapper_specs" id="field_wrapper_specs">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <a href="javascript:void(0);" class="add_button_assets" title="<?= TRANS('ADD'); ?>"><i class="fa fa-plus"></i></a>
                                            </div>
                                        </div>
                                        <input type="text" name="assetTag[]" id="assetTag" class="form-control" />
                                        
                                    </div>
                                    <small class="form-text text-muted"><?= TRANS('ASSET_TAG_TAG'); ?></small>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-4">
                                <div class="field_wrapper_specs" >
                                    <select class="form-control bs-select"  name="asset_unit[]" id="assetTag_asset_unit">
                                        <option value=""><?= TRANS('REGISTRATION_UNIT'); ?></option>
                                    </select>
                                    <small class="form-text text-muted"><?= TRANS('REGISTRATION_UNIT'); ?></small>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <div class="field_wrapper_specs" >
                                    <input type="text" class="form-control "  name="asset_department[]" id="assetTag_asset_department" disabled />
                                    <small class="form-text text-muted"><?= TRANS('DEPARTMENT'); ?></small>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="field_wrapper_specs" >
                                    <input type="text" class="form-control "  name="asset_desc[]" id="assetTag_asset_desc" disabled />
                                    <small class="form-text text-muted"><?= TRANS('ASSET_TYPE'); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Receberá cada um dos ativos alocados para o usuário -->
                        <div id="new_assets_row" class="row mx-2 mt-1 new_assets_row"></div>

                        <!-- Seleção a unidade que será utilizada para a geração do termo -->
                        <div class="row mx-2 <?= $marginTop; ?>">
                            <div class="form-group col-md-12">
                                <div class="field_wrapper_specs" >
                                    <select class="form-control bs-select"  name="term_unit" id="term_unit">
                                        <option value=""><?= TRANS('REGISTRATION_UNIT'); ?></option>
                                        <?php
                                            foreach ($units as $unit) {
                                                ?>
                                                    <option value="<?= $unit['inst_cod']; ?>"
                                                    <?= (isset($userInfo['term_unit']) && $userInfo['term_unit'] == $unit['inst_cod'] ? 'selected' : ''); ?>
                                                    ><?= $unit['inst_nome']; ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <small class="form-text text-muted"><?= TRANS('TEXT_CHOOSE_UNIT_TO_GENERATE_TERM'); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Seleção do departamento para os ativos que forem desalocados/removidos do usuário -->
                        <div class="row mx-2 <?= $marginTop; ?>">
                            <div class="form-group col-md-12">
                                <div class="field_wrapper_specs" >
                                    <select class="form-control bs-select"  name="department_for_removed" id="department_for_removed" disabled>
                                        <option value=""><?= TRANS('TEXT_CHOOSE_DEPARTMENT_FOR_REMOVED'); ?></option>
                                        <?php
                                            foreach ($departments as $department) {
                                                
                                                $department_info = [];
                                                $department_info[] = $department['unidade'];
                                                $department_info[] = $department['nickname'];
                                                $department_subtext = implode(' - ', array_filter($department_info));
                                                ?>
                                                    <option data-subtext="<?= $department_subtext; ?>" value="<?= $department['loc_id']; ?>"><?= $department['local']; ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <small class="form-text text-muted"><?= TRANS('TEXT_CHOOSE_DEPARTMENT_FOR_REMOVED'); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-end bg-light">
                            <button id="confirmAsset" class="btn btn-primary"><?= TRANS('BT_OK'); ?></button>
                            <button id="cancelAsset" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CANCEL'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="user_id" id="user_id" value="<?= $user_id; ?>">
            </form>

            <?php
        }




        if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

        ?>
            <!-- Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?= TRANS('CONFIRM_REMOVE'); ?> <span class="j_param_id"></span>?
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                            <button type="button" id="deleteButton" class="btn"><?= TRANS('BT_OK'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="deleteTmpModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="modalTitle"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?= TRANS('CONFIRM_REMOVE'); ?> <span class="j_param_id"></span>?
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                            <button type="button" id="deleteTmpButton" class="btn"><?= TRANS('BT_OK'); ?></button>
                        </div>
                    </div>
                </div>
            </div>


            <button class="btn btn-sm btn-primary" id="idBtIncluir" name="new"><?= TRANS("ACT_NEW"); ?></button><br /><br />
            <?php
            if ($registros == 0) {
                echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
            } else {

            ?>
                <input type="hidden" name="term_status" id="term_status" value="">
                <input type="hidden" name="jsonTermStatusOptions" id="jsonTermStatusOptions" value="<?= $jsonTermStatusOptions; ?>">


                <table id="table_users" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                    <thead>
                        <tr class="header">
                            <td class="line name"><?= TRANS('COL_NAME'); ?></td>
                            <td class="line login"><?= TRANS('OPT_LOGIN_NAME'); ?></td>
                            <td class="line client_name"><?= TRANS('CLIENT_NAME'); ?></td>
                            <td class="line admin"><?= TRANS('MANAGER'); ?></td>
                            <td class="line area"><?= TRANS('AREA'); ?></td>
                            <td class="line email"><?= TRANS('COL_EMAIL'); ?></td>
                            <td class="line level"><?= TRANS('LEVEL'); ?></td>
                            <td class="line last_logon"><?= TRANS('LAST_LOGON'); ?></td>
                            <td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
                            <td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
                        </tr>
                    </thead>
                </table>
                <div class="chart-container">
                    <canvas id="canvasChart1"></canvas>
                </div>


                <?php
                if (!$areaAdmin) {
                ?>
                    <h6 class="my-4"><?= TRANS('WAITING_CONFIRMATION'); ?></h6>
                    <table id="table_users_tmp" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                        <thead>
                            <tr class="header">
                                <td class="line name"><?= TRANS('COL_NAME'); ?></td>
                                <td class="line login"><?= TRANS('COL_LOGIN'); ?></td>
                                <td class="line email"><?= TRANS('COL_EMAIL'); ?></td>
                                <td class="line email"><?= TRANS('DATE'); ?></td>
                                <td class="line editar"><?= TRANS('BT_OK'); ?></td>
                                <td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>
                            </tr>
                        </thead>
                    </table>
            <?php
                }
            }
        } else
		if ((isset($_GET['action'])  && ($_GET['action'] == "new")) && !isset($_POST['submit'])) {

            ?>
            <h6><?= TRANS('NEW_RECORD'); ?></h6>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>
                <div class="form-group row my-4">

                    <label for="login_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control" id="login_name" name="login_name" required />
                    </div>

                    <label for="fullname" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="fullname" name="fullname" required />
                    </div>

                    


                    <label for="password" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password" name="password" required />
                    </div>

                    <label for="password2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RETYPE_PASS'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password2" name="password2" required />
                    </div>

                    
                    <label for="level" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LEVEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control" name="level" id="level" required>
                            <option value=""><?= TRANS('SEL_LEVEL'); ?></option>
                            <?php
                            if ($areaAdmin) {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod = '" . $_SESSION['s_nivel'] . "' ORDER BY nivel_nome ";
                            } else {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod <> 5 ORDER BY nivel_nome";
                            }
                            $res = $conn->query($sql);
                            foreach ($res->fetchall() as $row) {
                            ?>
                                <option value='<?= $row['nivel_cod']; ?>'><?= $row['nivel_nome']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    

                    <input type="hidden" name="user_client_db" id="user_client_db" value="">
                    <label for="user_client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT_NAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="user_client" name="user_client">
                            <option id="user_client_sel_level" value=""><?= TRANS('SEL_LEVEL_FIRST'); ?></option>
                        </select>
                    </div>


                    <input type="hidden" name="user_department_db" id="user_department_db" value="">
                    <label for="user_department" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control bs-select" id="user_department" name="user_department" required>
                            <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        </select>
                    </div>

                    <div class="w-100"></div>


                    <label for="subscribe_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="subscribe_date" name="subscribe_date" value="<?= date("d/m/Y H:i:s"); ?>" required readonly />
                    </div>

                    <label for="hire_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('HIRE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="hire_date" name="hire_date" value="" />
                    </div>

                    <label for="email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="email" class="form-control " id="email" name="email" required />
                    </div>

                    <label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="tel" class="form-control " id="phone" name="phone" required />
                    </div>

                    <label for="primary_area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PRIMARY_AREA'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="primary_area" name="primary_area" required>
                            <option id="sel_areas" value=""><?= TRANS('LOADING'); ?></option>
                        </select>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('AREA_MANAGER'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <input type="radio" id="area_admin" name="area_admin" value="yes" />
                        <label for="area_admin"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="area_admin_no" name="area_admin" value="no" checked />
                        <label for="area_admin_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <!-- Valor máximo para aprovação de custos de chamados -->
                    <label for="max_cost_authorizing" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CAN_AUTHORIZE_TO'); ?></label>
                    <div class="form-group col-md-4 ">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <?= TRANS('CURRENCY'); ?>
                                </div>
                            </div>
                            <input type="text" class="form-control " id="max_cost_authorizing" name="max_cost_authorizing" value="0,00" disabled/>
                        </div>
                    </div>

                    <!-- Seção para definição se o usuário pode encaminhar e receber chamados encaminhados -->
                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_ROUTE'); ?>"><?= TRANS('CAN_ROUTE'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <input type="radio" id="can_route" name="can_route" value="yes" checked />
                        <label for="can_route"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_route_no" name="can_route" value="no"  />
                        <label for="can_route_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_GET_ROUTED'); ?>"><?= TRANS('CAN_GET_ROUTED'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        
                        <input type="radio" id="can_get_routed" name="can_get_routed" value="yes" checked />
                        <label for="can_get_routed"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_get_routed_no" name="can_get_routed" value="no"  />
                        <label for="can_get_routed_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <label for="bgcolor" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_USER_BGCOLOR'); ?>"><?= TRANS('COL_BG_COLOR'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="color" class="form-control " id="bgcolor" name="bgcolor" value="<?= (isset($row['user_bgcolor']) ? $row['user_bgcolor'] : "#3A4D56"); ?>" />
                    </div>
                    <label for="textcolor" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_USER_TEXTCOLOR'); ?>"><?= TRANS('FONT_COLOR'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="color" class="form-control " id="textcolor" name="textcolor" value="<?= (isset($row['user_textcolor']) ? $row['user_textcolor'] : "#FFFFFF"); ?>" />
                    </div>


                </div>

                <div class="form-group row my-4" id="div_secondary_areas"></div>
                <div class="form-group row my-4" id="div_manageble_areas"></div>

                <div class="form-group row my-4">
                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="action" id="action" value="new">
                        <input type="hidden" name="isAdmin" id="isAdmin" value="<?= $isAdmin; ?>">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                </div>
            </form>
        <?php
        } else

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && empty($_POST['submit'])) {

            $row = $resultado->fetch();
            $onlyOpen = $row['nivel'] == 3;
            $userInfo = getUserInfo($conn, $user_id);

            $editable = (!$isAdmin ? ' disabled' : '');

            if ($allowAddAssets) {
                $btDisable = (!empty($userInfo['user_client']) && !empty($userInfo['user_department']) ? '' : ' disabled');
                $btDisable = ($userLevel > 3 ? ' disabled' : $btDisable);
                ?>
                <button class="btn btn-primary bt-assets" id="add-asset" <?= $btDisable; ?> data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MSG_NEED_TO_SET_USER_CLIENT'); ?>">&nbsp;<?= TRANS('BT_ASSOCIATE_ASSETS_TO_USER'); ?></button>
                    
                <?php
            }
            
            ?>
            <!-- <h6><?= TRANS('BT_EDIT'); ?></h6> -->
            
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>
                <div class="form-group row my-4">


                    <label for="login_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control " id="login_name" name="login_name" value="<?= (isset($row['login']) ? $row['login'] : ""); ?>" required readonly />
                    </div>

                    <label for="fullname" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="fullname" name="fullname" value="<?= (isset($row['nome']) ? $row['nome'] : ""); ?>" required />
                    </div>
                    
                    <div class="w-100"></div>

                    <label for="password" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password" name="password" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
                    </div>

                    <label for="password2" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RETYPE_PASS'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="password" class="form-control " id="password2" name="password2" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
                    </div>

                    

                    <label for="level" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LEVEL'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control" name="level" id="level" required>
                            <option value=""><?= TRANS('SEL_LEVEL'); ?></option>
                            <?php
                            if ($areaAdmin) {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod in (" . $_SESSION['s_nivel'] . " , 5) ORDER BY nivel_nome ";
                            } else {
                                $sql = "SELECT * FROM nivel WHERE nivel_cod NOT IN (4) ORDER BY nivel_nome";
                            }
                            $res = $conn->query($sql);
                            foreach ($res->fetchall() as $rowLevel) {
                            ?>
                                <option value='<?= $rowLevel['nivel_cod']; ?>' <?= ($rowLevel['nivel_cod'] == $row['nivel'] ? 'selected' : ''); ?>><?= $rowLevel['nivel_nome']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    
                    <input type="hidden" name="user_client_db" id="user_client_db" value="<?= $userInfo['user_client']; ?>">
                    <label for="user_client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT_NAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="user_client" name="user_client" required>
                            <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        </select>
                    </div>
                            
                    <input type="hidden" name="user_department_db" id="user_department_db" value="<?= $userInfo['user_department']; ?>">
                    <label for="user_department" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control bs-select" id="user_department" name="user_department" required>
                            <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        </select>
                    </div>



                    <div class="w-100"></div>

                    <label for="subscribe_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="subscribe_date" name="subscribe_date" value="<?= (isset($row['data_inc']) ? dateScreen($row['data_inc'], 1) : ""); ?>" required readonly />
                    </div>

                    <label for="hire_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('HIRE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="hire_date" name="hire_date" value="<?= (isset($row['data_admis']) ? dateScreen($row['data_admis'], 1) : ""); ?>" />
                    </div>

                    <label for="email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="email" class="form-control " id="email" name="email" value="<?= (isset($row['email']) ? $row['email'] : ""); ?>" required />
                    </div>

                    <label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="tel" class="form-control " id="phone" name="phone" value="<?= (isset($row['fone']) ? $row['fone'] : ""); ?>" required />
                    </div>

                    <input type="hidden" name="primary_area_db" id="primary_area_db" value="<?= $row['AREA']; ?>">
                    <label for="primary_area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PRIMARY_AREA'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="primary_area" name="primary_area" required>
                            <option id="sel_areas" value=""><?= TRANS('LOADING'); ?></option>

                        </select>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AREA_ADMINS_USERS'); ?>"><?= TRANS('AREA_MANAGER'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $disabled = ($areaAdmin ? ' disabled' : '');
                        $yesChecked = ($row['user_admin'] == 1 ? "checked" : "");
                        $noChecked = ($row['user_admin'] == 0 ? "checked" : "");
                        ?>
                        <input type="radio" id="area_admin" name="area_admin" value="yes" <?= $yesChecked; ?> <?= $disabled; ?> />
                        <label for="area_admin"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="area_admin_no" name="area_admin" value="no" <?= $noChecked; ?> <?= $disabled; ?> />
                        <label for="area_admin_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <!-- Valor máximo para aprovação de custos de chamados -->
                    <?php
                        $disabled = ($areaAdmin ? ' disabled' : '');
                        $value = ($row['max_cost_authorizing'] > 0 ? priceScreen($row['max_cost_authorizing']) : priceScreen(0));
                    ?>
                    <label for="max_cost_authorizing" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CAN_AUTHORIZE_TO'); ?></label>
                    <div class="form-group col-md-4 ">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <?= TRANS('CURRENCY'); ?>
                                </div>
                            </div>
                            <input type="text" class="form-control " id="max_cost_authorizing" name="max_cost_authorizing" value="<?= $value; ?>" <?= $disabled; ?>/>
                        </div>
                    </div>


                    <!-- Seção para definição se o usuário pode encaminhar e receber chamados encaminhados -->
                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_ROUTE'); ?>"><?= TRANS('CAN_ROUTE'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $disabled = ($row['nivel'] != 2 ? ' disabled' : '');
                        $yesChecked = ($row['can_route'] == 1 ? "checked" : "");
                        $noChecked = ($row['can_route'] == 0 || $row['can_route'] == '' ? "checked" : "");
                        ?>
                        <input type="radio" id="can_route" name="can_route" value="yes" <?= $yesChecked; ?> <?= $editable; ?> />
                        <label for="can_route"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_route_no" name="can_route" value="no" <?= $noChecked; ?> <?= $editable; ?> />
                        <label for="can_route_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_GET_ROUTED'); ?>"><?= TRANS('CAN_GET_ROUTED'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $disabled = ($row['nivel'] != 2 && $row['nivel'] != 1 ? ' disabled' : '');
                        $yesChecked = ($row['can_get_routed'] == 1 ? "checked" : "");
                        $noChecked = ($row['can_get_routed'] == 0 || $row['can_get_routed'] == '' ? "checked" : "");
                        ?>
                        <input type="radio" id="can_get_routed" name="can_get_routed" value="yes" <?= $yesChecked; ?> <?= $disabled; ?> />
                        <label for="can_get_routed"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_get_routed_no" name="can_get_routed" value="no" <?= $noChecked; ?> <?= $disabled; ?> />
                        <label for="can_get_routed_no"><?= TRANS('NOT'); ?></label>
                    </div>


                    <label for="bgcolor" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_USER_BGCOLOR'); ?>"><?= TRANS('COL_BG_COLOR'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="color" class="form-control " id="bgcolor" name="bgcolor" value="<?= (isset($row['user_bgcolor']) ? $row['user_bgcolor'] : ""); ?>" />
                    </div>
                    <label for="textcolor" class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_USER_TEXTCOLOR'); ?>"><?= TRANS('FONT_COLOR'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="color" class="form-control " id="textcolor" name="textcolor" value="<?= (isset($row['user_textcolor']) ? $row['user_textcolor'] : ""); ?>" />
                    </div>

                </div>

                

                <?php
                    if (!empty($assets_info)) {

                        $textTermSigned = '&nbsp;<strong>(' .TRANS('TERM_OUTDATED') . ')</strong>';
                        if ($termUpdated) {
                            $iconSigned = ($termSigned ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>');
                            $textTermSigned = '<strong>(' . ($termSigned ? TRANS('TERM_SIGNED') : TRANS('TERM_NOT_SIGNED')) . ' ' . $iconSigned . ')</strong>';
                        }
                        
                        ?>
                        <div class="form-group row my-4">
                            <div class="h6 w-100 my-4 border-top p-4"><?= TRANS('TTL_ASSETS_ASSOCIATED_WITH_USER'); ?>&nbsp;<?= $textTermSigned; ?></div>
                            
                            <table class="table table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th><?= TRANS('ASSET_TYPE'); ?></th>
                                        <th><?= TRANS('ASSET_TAG_TAG'); ?></th>
                                        <th><?= TRANS('REGISTRATION_UNIT'); ?></th>
                                        <th><?= TRANS('LOCALIZATION'); ?></th>
                                        <th><?= TRANS('ALOCATION_DATE'); ?></th>
                                    </tr>
                                </thead>
                        <?php
                        
                        $descriptionKeys = ['tipo_nome', 'fab_nome', 'marc_nome'];
                        
                        foreach ($assets_info as $asset) {
                            
                            $descriptionArray = [];
                            foreach ($descriptionKeys as $key) {
                                $descriptionArray[] = $asset[$key];
                            }
                            $asset_description = implode(" ", array_filter($descriptionArray));


                            $asset_unit_client = $asset['inst_nome'] . ' (' . $asset['cliente'] . ')';

                            $local_unit = "";
                            $unitFromLocalization = getUnitFromDepartment($conn, $asset['loc_id']);
                            if (!empty($unitFromLocalization)) {
                                $local_unit = '&nbsp;(' . $unitFromLocalization['inst_nome'] . ')';
                            }
                            
                            ?>
                                <tr>
                                    <td><?= $asset_description; ?></td>
                                    <td class="td-tag" data-tag="<?= $asset['comp_cod']; ?>"><?= $asset['comp_inv']; ?></td>
                                    <td><?= $asset_unit_client; ?></td>
                                    <td><?= $asset['local'] . $local_unit; ?></td>
                                    <td><?= dateScreen($asset['created_at']); ?></td>
                                </tr>
                            <?php
                        }
                        ?>
                            </table>
                            
                            <div class="form-group col-md-4 d-none d-md-block"></div>
                            <div class="form-group col-12 col-md-4 ">
                                <?php
                                    if (!empty($commitmentTermId)) {
                                        ?>
                                            <button id="downloadTerm" name="downloadTerm" type="button" class="btn btn-primary btn-block bt-download" <?= $disableDownload; ?>>&nbsp;<?= TRANS('DOWNLOAD_TERM_OF_COMMITMENT'); ?></button>
                                        <?php
                                    }
                                ?>

                            </div>
                            <div class="form-group col-12 col-md-4 ">
                                <button id="generateTerm" name="generateTerm" type="button" class="btn btn-primary btn-block bt-generate" <?= $disableGenerate; ?>>&nbsp;<?= TRANS('GENERATE_TERM_OF_COMMITMENT'); ?></button>
                            </div>
                        <?php
                        ?>
                        </div>
                        <?php
                    }
                ?>



                <div class="form-group row my-4" id="div_secondary_areas"></div>
                <div class="form-group row my-4" id="div_manageble_areas"></div>


                <div class="form-group row my-4">

                    <!-- <input type="hidden" name="cod" id="cod" value="<?= (int)$_GET['cod']; ?>"> -->
                    <input type="hidden" name="cod" id="cod" value="<?= $user_id; ?>">
                    <input type="hidden" name="area" id="idArea" value="<?= $row['sis_id']; ?>">
                    <input type="hidden" name="action" id="action" value="edit">
                    <input type="hidden" name="isAdmin" id="isAdmin" value="<?= $isAdmin; ?>">


                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">
                        <button type="submit" id="idSubmit" name="submit" value="edit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" id="close_details" class="btn btn-secondary btn-block"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>

                </div>
            </form>
        <?php
        } else 
        if ((isset($_GET['action']) && $_GET['action'] == "profile") && empty($_POST['submit'])) {

            $row = $resultado->fetch();
            $editable = (!$isAdmin ? ' disabled' : '');
            // $termSigned = ($termUpdated ? isLastUserTermSigned($conn, $user_id) : false);
        ?>
            <h6><?= TRANS('MY_PROFILE'); ?></h6>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <?= csrf_input(); ?>
                <div class="form-group row my-4">


                    <label for="client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control " id="client" name="client" value="<?= (isset($row['nickname']) ? $row['nickname'] : ""); ?>" readonly />
                    </div>


                    <label for="user_department_profile" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control " id="user_department_profile" name="user_department_profile" value="<?= (isset($row['local']) ? $row['local'] : ""); ?>" readonly />
                    </div>

                    <div class="w-100"></div>

                    <label for="login_name" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_LOGIN'); ?></label>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control " id="login_name" name="login_name" value="<?= (isset($row['login']) ? $row['login'] : ""); ?>" readonly />
                    </div>

                    <label for="change_pass" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PASSWORD'); ?></label>
                    <div class="form-group col-md-4 ">
                        <?php
                        $enableChangePass = (!$localAuth ? " disabled" : "");
                        ?>
                        <button class="btn btn-sm btn-primary" id="change_pass" name="change_pass" <?= $enableChangePass; ?>><?= TRANS('BT_ALTER'); ?></button>
                    </div>

                    <div class="w-100"></div>

                    <label for="fullname" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('FULLNAME'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="fullname" name="fullname" value="<?= (isset($row['nome']) ? $row['nome'] : ""); ?>" required />
                    </div>


                    <label for="level" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LEVEL'); ?></label>
                    <div class="form-group col-md-4">

                        <div class="input-group">
                            <?php
                                $textChange = '<hr>' . TRANS('CLICK_TO_CHANGE');
                                $changeLevel = '';
                                /* Indicador do tipo de navegação */
                                if ($_SESSION['s_nivel_real'] == 1) {
                                    $changeLevel = ($_SESSION['s_nivel'] == 1 ? '<span id="change_level" title="'.TRANS('MSG_ADMIN_LEVEL_NAVIGATION') . $textChange . '" data-toggle="popover" data-content="" data-placement="left" data-trigger="hover"><i class="fa fa-user-cog"></i></span>' : '&nbsp;&nbsp;<span id="change_level" title="'.TRANS('MSG_OPERATOR_LEVEL_NAVIGATION') . $textChange . '" data-toggle="popover" data-content="" data-placement="left" data-trigger="hover"><i class="fa fa-user-edit"></i></span>');
                                }
                            ?>

                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <?= $changeLevel; ?>
                                </div>
                            </div>

                            <select class="form-control" name="level" id="level" required <?= $editable; ?>>
                                <option value=""><?= TRANS('SEL_LEVEL'); ?></option>
                                <?php
                                if ($areaAdmin) {
                                    $sql = "SELECT * FROM nivel WHERE nivel_cod in (" . $_SESSION['s_nivel'] . " , 5) ORDER BY nivel_nome ";
                                } else {
                                    $sql = "SELECT * FROM nivel WHERE nivel_cod NOT IN (4) ORDER BY nivel_nome";
                                }
                                $res = $conn->query($sql);
                                foreach ($res->fetchall() as $rowLevel) {
                                ?>
                                    <option value='<?= $rowLevel['nivel_cod']; ?>' <?= ($rowLevel['nivel_cod'] == $row['nivel'] ? 'selected' : ''); ?>><?= $rowLevel['nivel_nome']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>


                    <label for="subscribe_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_SUBSCRIBE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="subscribe_date" name="subscribe_date" value="<?= (isset($row['data_inc']) ? dateScreen($row['data_inc'], 1) : ""); ?>" required readonly />
                    </div>

                    <label for="hire_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('HIRE_DATE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="text" class="form-control " id="hire_date" name="hire_date" value="<?= (isset($row['data_admis']) ? dateScreen($row['data_admis'], 1) : ""); ?>" <?= $editable; ?> />
                    </div>

                    <label for="email" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_EMAIL'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="email" class="form-control " id="email" name="email" value="<?= (isset($row['email']) ? $row['email'] : ""); ?>" required />
                    </div>

                    <label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
                    <div class="form-group col-md-4 ">
                        <input type="tel" class="form-control " id="phone" name="phone" value="<?= (isset($row['fone']) ? $row['fone'] : ""); ?>" required />
                    </div>

                    <label for="primary_area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('PRIMARY_AREA'); ?></label>
                    <div class="form-group col-md-4 ">
                        <select class="form-control" id="primary_area" name="primary_area" <?= $editable; ?>>
                            <option id="sel_areas" value=""><?= TRANS('LOADING'); ?></option>

                        </select>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AREA_ADMINS_USERS'); ?>"><?= TRANS('AREA_MANAGER'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        // $disabled = ' disabled';
                        $yesChecked = ($row['user_admin'] == 1 ? "checked" : "");
                        $noChecked = ($row['user_admin'] == 0 ? "checked" : "");

                        $enableAreaAdminField = (!$row['user_admin'] == 1 ? ' disabled' : '');
                        ?>
                        <input type="radio" id="area_admin" name="area_admin" value="yes" <?= $yesChecked; ?> <?= $enableAreaAdminField; ?> />
                        <label for="area_admin"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="area_admin_no" name="area_admin" value="no" <?= $noChecked; ?> <?= $enableAreaAdminField; ?> />
                        <label for="area_admin_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <!-- Valor máximo para aprovação de custos de chamados -->
                    <?php
                        $value = ($row['max_cost_authorizing'] > 0 ? priceScreen($row['max_cost_authorizing']) : priceScreen(0));
                    ?>
                    <label for="max_cost_authorizing" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CAN_AUTHORIZE_TO'); ?></label>
                    <div class="form-group col-md-4 ">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <?= TRANS('CURRENCY'); ?>
                                </div>
                            </div>
                            <input type="text" class="form-control " id="max_cost_authorizing" name="max_cost_authorizing" value="<?= $value; ?>" <?= $editable; ?>/>
                        </div>
                    </div>

                    <!-- Seção para definição se o usuário pode encaminhar e receber chamados encaminhados -->
                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_ROUTE'); ?>"><?= TRANS('CAN_ROUTE'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        // $disabled = ($_SESSION['s_nivel'] != 1 ? ' disabled' : '');
                        $yesChecked = ($row['can_route'] == 1 ? "checked" : "");
                        $noChecked = ($row['can_route'] == 0 || $row['can_route'] == '' ? "checked" : "");
                        ?>
                        <input type="radio" id="can_route" name="can_route" value="yes" <?= $yesChecked; ?> <?= $editable; ?> />
                        <label for="can_route"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_route_no" name="can_route" value="no" <?= $noChecked; ?> <?= $editable; ?> />
                        <label for="can_route_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_CAN_GET_ROUTED'); ?>"><?= TRANS('CAN_GET_ROUTED'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $disabled = ($row['nivel'] != 2 && $row['nivel'] != 1 ? ' disabled' : '');
                        $yesChecked = ($row['can_get_routed'] == 1 ? "checked" : "");
                        $noChecked = ($row['can_get_routed'] == 0 || $row['can_get_routed'] == '' ? "checked" : "");
                        ?>
                        <input type="radio" id="can_get_routed" name="can_get_routed" value="yes" <?= $yesChecked; ?> <?= $editable; ?> />
                        <label for="can_get_routed"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="can_get_routed_no" name="can_get_routed" value="no" <?= $noChecked; ?> <?= $editable; ?> />
                        <label for="can_get_routed_no"><?= TRANS('NOT'); ?></label>
                    </div>


                    <label for="lang" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('MNL_LANG'); ?></label>
                    <div class="form-group col-md-4">
                        <select class="form-control" id="lang" name="lang">
                            <option value=""><?= TRANS('SYSTEM_DEFAULT'); ?></option>
                            <?php

                            foreach ($langLabels as $key => $label) {
                                if (in_array($key, $files)) {
                                    echo '<option value="' . $key . '"';
                                    echo ($rowUL && $key == $rowUL['upref_lang'] ? ' selected' : '') . '>' . $label;
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="w-100"></div>
                    

                    <label class="col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('SIGNATURE'); ?>"><?= TRANS('SIGNATURE'); ?></label>
                    <div class="form-group col-md-4">

                        <?php
                            $idxCreateSignature = 'CREATE_OR_LOAD_SIGNATURE_FILE';
                            if (!empty($signature_info)) {
                                $idxCreateSignature = 'CREATE_OR_LOAD_NEW_SIGNATURE_FILE';
                                ?>
                                    <img src="<?= $signature_info['signature_src']; ?>" width="100" />
                                <?php
                            } else {
                                echo message('info', '', TRANS('YOU_DONT_HAVE_SIGNATURE_FILE'), '', '', true);
                            }
                        ?>
                        
                    </div>
                    
                    <!-- Seção referente à assinatura do usuário -->
                    <div class="form-group col-md-2"></div>
                    <div class="form-group col-md-4">
                        <button class="btn btn-primary bt-draw bt-signature">&nbsp;<?= TRANS($idxCreateSignature); ?></button>
                    </div>
                </div>

                <?php
                    if (!empty($assets_info)) {
                    
                        $textTermSigned = '&nbsp;<strong>(' .TRANS('TERM_OUTDATED') . ')</strong>';
                        if ($termUpdated) {
                            $iconSigned = ($termSigned ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>');
                            $textTermSigned = '<strong>(' . ($termSigned ? TRANS('TERM_SIGNED') : TRANS('TERM_NOT_SIGNED')) . ' ' . $iconSigned . ')</strong>';
                        }
                    
                    
                    ?>
                        <div class="form-group row my-4">
                            <div class="h6 w-100 my-4 border-top p-4"><?= TRANS('TTL_ASSETS_ASSOCIATED_WITH_USER'); ?>&nbsp;<?= $textTermSigned; ?></div>
                            
                            
                            <table class="table table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th><?= TRANS('ASSET_TYPE'); ?></th>
                                        <th><?= TRANS('ASSET_TAG_TAG'); ?></th>
                                        <th><?= TRANS('REGISTRATION_UNIT'); ?></th>
                                        <th><?= TRANS('LOCALIZATION'); ?></th>
                                        <th><?= TRANS('ALOCATION_DATE'); ?></th>
                                    </tr>
                                </thead>
                        <?php
                        foreach ($assets_info as $asset) {
                            
                            $asset_description = $asset['tipo_nome'] . '&nbsp;' . $asset['fab_nome'] . '&nbsp;' . $asset['marc_nome'];

                            $asset_unit_client = $asset['inst_nome'] . ' (' . $asset['cliente'] . ')';
                            
                            $local_unit = "";
                            $unitFromLocalization = getUnitFromDepartment($conn, $asset['loc_id']);
                            if (!empty($unitFromLocalization)) {
                                $local_unit = '&nbsp;(' . $unitFromLocalization['inst_nome'] . ')';
                            }
                            ?>
                                <tr>
                                    <td><?= $asset_description; ?></td>
                                    <td><?= $asset['comp_inv']; ?></td>
                                    <td><?= $asset_unit_client; ?></td>
                                    <td><?= $asset['local'] . $local_unit; ?></td>
                                    <td><?= dateScreen($asset['created_at']); ?></td>
                                </tr>
                            <?php
                        }
                        ?>
                            </table>

                            
                            
                            <?php
                                if (!empty($commitmentTermId)) {
                                    $buttonSignOrViewText = ($termSigned ? 'VIEW_TERM_OF_COMMITMENT' : 'SIGN_TERM_OF_COMMITMENT');
                                    $buttonSignOrViewClass = ($termSigned ? 'btn-primary bt-view' : 'btn-oc-orange text-white  bt-sign');
                                    ?>
                                    <div class="form-group col-md-4 d-none d-md-block"></div>
                                    <div class="form-group col-12 col-md-4 ">
                                        <button id="signTerm" name="signTerm" type="button" class="btn btn-block <?= $buttonSignOrViewClass; ?>" <?= $disableDownload; ?>>&nbsp;<?= TRANS($buttonSignOrViewText); ?></button>
                                    </div>
                                    <div class="form-group col-12 col-md-4 ">
                                        <button id="downloadTerm" name="downloadTerm" type="button" class="btn btn-primary btn-block bt-download" <?= $disableDownload; ?>>&nbsp;<?= TRANS('DOWNLOAD_TERM_OF_COMMITMENT'); ?></button>
                                    </div>
                                    <?php
                                }
                        ?>
                        </div>
                    <?php
                    }
                ?>





                <div class="form-group row my-4" id="div_secondary_areas"></div>
                <div class="form-group row my-4" id="div_manageble_areas"></div>


                <div class="form-group row my-4">

                    <input type="hidden" name="cod" id="cod" value="<?= $user_id; ?>">
                    <input type="hidden" name="password" id="password" value="">
                    <input type="hidden" name="password2" id="password2" value="">
                    <input type="hidden" name="area" id="idArea" value="<?= $row['sis_id']; ?>">
                    <input type="hidden" name="action" id="action" value="profile">
                    <input type="hidden" name="isAdmin" id="isAdmin" value="<?= $isAdmin; ?>">


                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">
                        <button type="submit" id="idSubmit" name="submit" value="profile" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>

                </div>
            </form>
        <?php
        }
        ?>
    </div>

    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/plentz-jquery-maskmoney/dist/jquery.maskMoney.min.js"></script>

    <script src="../../includes/components/jquery/datetimepicker/build/jquery.datetimepicker.full.min.js"></script>

    <script src="../../includes/components/jquery/MHS/jquery.md5.min.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
    <script src="../../includes/components/signature_pad/dist/signature_pad.umd.min.js"></script>


    <script src="./ajax/user_x_level.js"></script>
    <script type="text/javascript">
        $(function() {

            /* Atualizar a tabela users_terms_pivot com os dados de termos já existentes
            - A atualização só ocorrerá na primeira execução
             */
            updateUsersTermsInfo();
            
            
            if ($('#canvasChart1').length)
                showTotalGraph();

            $(function() {
                $('[data-toggle="popover"]').popover({
                    html: true
                });
            });

            $('.popover-dismiss').popover({
                trigger: 'focus'
            });


            /* Trazer os parâmetros do banco a partir da opção que será criada para internacionaliação */
            $('#max_cost_authorizing').maskMoney({
                prefix: 'R$ ',
                thousands: '.',
                decimal: ',',
                allowZero: false,
                affixesStay: false
            });


            costInputControl();
            $('.bt-draw').on('click', function(e) {
                e.preventDefault();
                openSignaturePad();
            });
            
            $('#signTerm').on('click', function(e) {
                e.preventDefault();
                openSignTerm();
            });


            $('.td-tag').css('cursor', 'pointer').on('click', function(e) {
                e.preventDefault();
                console.log($(this).attr('data-tag'));
                loadInIframe('../../invmon/geral/asset_show', 'asset_id=' + $(this).attr('data-tag'));
            });

            $('#modalIframe').on('hidden.bs.modal', function (e) {
                $("#iframe-content").attr('src','');
            });

            /* Identificar se a janela está sendo carregada em uma popup (iframe dentro de uma modal) */
			if (isInIframe()) {
				if (!isMainIframe()) {
					$('#close_details').text($('#label_close').val()).on("click", function() {
						window.parent.closeIframe();
					});
				} else {
					$('#close_details').text($('#label_return').val()).on("click", function() {
						window.history.back();
					});
				}
			} else {
				$('#close_details').text($('#label_return').val()).on("click", function() {
					window.history.back();
				});
			}


            if ($('#canvas_signature').length > 0) {
                const canvas = document.querySelector("#canvas_signature");
                const signaturePad = new SignaturePad(canvas);

                // console.log(signaturePad);
                
                $('#save-svg').on('click', function(e){
                    e.preventDefault();

                    let input_file = $('#signature_file').val();
                    var data = '';

                    if (!signaturePad.isEmpty()) {
                        // $('#data_signature').val(signaturePad.toDataURL('image/png'));
                        $('#data_signature').val(signaturePad.toDataURL("image/svg+xml"));
                    }
                    createSignatureProcess();
                });

                $('#signature_file').on('change', function() {
                    let fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').addClass("selected").html(fileName);
                });

                document.getElementById('clear').addEventListener('click', function (e) {
                    e.preventDefault();
                    signaturePad.clear();
                    $('#signature_file').val('');
                    $('#signature_file').next('.custom-file-label').addClass("selected").html($('#text_choose_file').val());
                });

                document.getElementById('undo').addEventListener('click', function (e) {
                    e.preventDefault();
                    var data = signaturePad.toData();
                    if (data) {
                        data.pop(); // remove the last dot or line
                        signaturePad.fromData(data);
                    }
                });

                $('#modal_signature').on('hidden.bs.modal', function(e){
                    e.preventDefault();
                    signaturePad.clear();
                    $('#signature_file').val('');
                    $('#signature_file').next('.custom-file-label').addClass("selected").html($('#text_choose_file').val());
                });
            }
            


            $.fn.selectpicker.Constructor.BootstrapVersion = '4';
			$('#user_client').selectpicker({
				/* placeholder */
				// noneSelectedText: 'teste',
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});


            $('.bs-select').selectpicker({
				/* placeholder */
				noneSelectedText: "<?= TRANS('SEL_SELECT', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
                showSubtext: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});

            /* Idioma global para os calendários */
            $.datetimepicker.setLocale('pt-BR');

            $('#hire_date').datetimepicker({
                timepicker: false,
                format: 'd/m/Y',
                lazyInit: true
            });



            if ($('#change_pass').length > 0) {
                $('#change_pass').on('click', function(e) {
                    e.preventDefault();
                    $('#divDetails').html('');
                    $("#divDetails").load('../../includes/common/change_pass.php');
                    $('#modal').modal();
                });
            }

            if ($('#change_level').length > 0) {
                $('#change_level').on('click', function() {
                    toggleUserLevel();
                }).css({ cursor: "pointer"});
            }

            loadClients();
            loadDepartments($('#user_client_db').val());
            controlRoutingRadio();


            $('#user_client').on('change', function() {
                loadDepartments($(this).val());
            });

            $('.add_button_assets').on('click', function() {
				loadNewAssetRow();
			});

            $('.new_assets_row, .old_assets_row').on('click', '.remove_button_assets', function(e) {
                e.preventDefault();
				dataRandom = $(this).attr('data-random');
				$("."+dataRandom).remove();
            });

            $('.old_assets_row').on('click', '.remove_button_assets', function(e) {
                e.preventDefault();
				$('#department_for_removed').prop('disabled', false).selectpicker('refresh');
            });

            $('#add-asset').on('click', function() {
                $('#modalAssets').modal();
            });

            $('#modalAssets').on('hidden.bs.modal', function(){
                location.reload();
            });


            $('#assetTag').on('change', function() {
                loadUnitsByAssetTag();

                loadAssetBasicInfo('assetTag');
            });

            $('#assetTag_asset_unit').on('change', function() {
                loadAssetBasicInfo('assetTag', $('#assetTag').val(), $(this).val());
            });

            $('#generateTerm').on('click', function(e) {
                e.preventDefault();
                generateTerm();
            });

            $('#downloadTerm').on('click', function(e) {
                e.preventDefault();
                
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });
                
                $.ajax({
                    url: './get_last_term_id.php',
                    method: 'POST',
                    data: {
                        user_id: $('#cod').val(),
                    },
                    dataType: 'json',
                }).done(function(data) {

                    if (!data.success) {
                        $('#divResult').html(data.message);
                    } else {
                        let termId = data.last_term_id;
                        redirect('../../includes/functions/download_user_files.php?file_id=' + termId + '&user_id=' + $('#cod').val());
                    }
                });
            });

            


            if ($('#new_assets_row').length > 0) {
                /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
                var obs = $.initialize(".after-dom-ready", function() {
					$('.bs-select').selectpicker({
                        /* placeholder */
                        noneSelectedText: "<?= TRANS('SEL_SELECT', '', 1); ?>",
                        liveSearch: true,
                        liveSearchNormalize: true,
                        showSubtext: true,
                        liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                        noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                        style: "",
                        styleBase: "form-control ",
                    });

                    $('.asset_tag_class').on('change', function() {

						var myId = $(this).attr('id');
                        // console.log('meu ID: ' + myId);
						loadUnitsByAssetTag(myId);
                        loadAssetBasicInfo(myId);
					});

                    $('.asset_unit_class').on('change', function() {
                        let randomId = $(this).attr('data-base-random');
                        loadAssetBasicInfo(randomId, $('#' + randomId).val(), $(this).val());
                    });

                }, {
                    target: document.getElementById('new_assets_row')
                }); /* o target limita o scopo do observer */

            }

            $('#confirmAsset').on('click', function(e) {
                e.preventDefault();
                addAssetsProcess();
            });



            var obsCustomSearchField = $.initialize(".term_status_custom", function() {
                $('#term_status_custom').on('change', function() {
                    $('#term_status').val($(this).val());
                    dataTable.ajax.reload(null, false);
                });
            }, {
                target: document.getElementById('container_users')
            }); 







            $.ajax({
                url: 'get_possible_areas.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'level': $('#level').val()
                },
                success: function(data) {

                    var area = (typeof($('#idArea') !== 'undefined') ? $('#idArea').val() : "");
                    $('#sel_areas').text('<?= TRANS('SEL_AREA'); ?>');
                    if ($('#level').val() == "") {
                        $('#sel_areas').text('<?= TRANS('SEL_LEVEL_FIRST'); ?>');
                    } else {
                        $.each(data, function(key, data) {
                            $('#primary_area').append('<option value="' + data.sis_id + '"' + (data.sis_id == area ? 'selected' : '') + '>' + data.sistema + '</option>');
                        });
                    }
                }
            });

            $.ajax({
                url: 'get_secondary_areas.php',
                type: 'POST',
                data: {
                    // 'primary_area': $('#primary_area').val(), 
                    'primary_area': (typeof($('#idArea') !== 'undefined') ? $('#idArea').val() : ""),
                    'level': $('#level').val(),
                    'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                    'action': $('#action').val(),
                    'setAdmin': $('#area_admin').is(':checked')
                },
                success: function(data) {
                    $('#div_secondary_areas').html(data);
                }
            });

            /* Vou ter que utilizar o observer para poder realizar esse controle */
            if ($('#div_secondary_areas').length > 0) {
                var obsClient = $.initialize(".switch-next-checkbox, .container-switch", function() {
                    
                    $(function() {
						$('[data-toggle="popover"]').popover({
							html: true
						});
					});

					$('.popover-dismiss').popover({
						trigger: 'focus'
					});

                    controlSetAreaAdmin()

                    $.each($('.switch-next-checkbox'), function(index, el) {
                        // controlSetAreaAdmin()
                        var group_parent = $(this).parent(); 
                        var enabled = group_parent.find('input:first').is(':checked') && $('#area_admin').is(':checked');

                        if (enabled) {
                            $(this).prop('disabled', false);
                        } else {
                            $(this).prop('disabled', true);
                        }
                    })

                    $('.container-switch').on('click', 'input', function() {

						var group_parent = $(this).parents(); //object
						var last_checkbox_id = group_parent.find('input:last').attr('id');
						var last_checkbox_name = group_parent.find('input:last').attr('name');

						if ($(this).val() == "no") {
							$('input[name="'+last_checkbox_name +'"]').prop('checked', false).prop('disabled', true);
							
						} else if ($('#area_admin').is(':checked')) {
							$('input[name="'+last_checkbox_name +'"]').prop('disabled', false);
						}
					});

					$('input[name^="setAdmin"]').on('change', function() {
						controlInputAreaAdmin($(this));
					})


                }, {
                    target: document.getElementById('div_secondary_areas')
                }); /* o target limita o scopo do observer */
            }


            $.ajax({
                url: 'get_areas_to_set_admin.php',
                type: 'POST',
                data: {
                    'primary_area': (typeof($('#idArea') !== 'undefined') ? $('#idArea').val() : ""),
                    'level': $('#level').val(),
                    'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                    'action': $('#action').val(),
                    'setAdmin': $('#area_admin').is(':checked')
                },
                success: function(data) {
                    $('#div_manageble_areas').html(data);
                }
            });

            $('#level').on("change", function() {

                departmentSelectionControl("");
                loadClients();
                controlRoutingRadio();
                $.ajax({
                    url: 'get_possible_areas.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'level': $(this).val()
                    },
                    success: function(data) {

                        $('#primary_area').empty();

                        if ($('#level').val() == "") {
                            $('#primary_area').append('<option value="" selected id="sel_areas">' + '<?= TRANS('SEL_LEVEL_FIRST'); ?>' + '</option>');
                        } else {
                            $('#primary_area').append('<option value="" selected id="sel_areas">' + '<?= TRANS('SEL_AREA'); ?>' + '</option>');
                            $.each(data, function(key, data) {
                                if (data.sis_id == $('#primary_area_db').val()) {
                                    $('#primary_area').append('<option value="' + data.sis_id + '" selected>' + data.sistema + '</option>');
                                } else {
                                    $('#primary_area').append('<option value="' + data.sis_id + '">' + data.sistema + '</option>');
                                }
                            });
                        }
                    }
                });
            });


            $('#primary_area, #level, [name="area_admin"]').on("change", function() {
                $.ajax({
                    url: 'get_secondary_areas.php',
                    type: 'POST',
                    data: {
                        'primary_area': $('#primary_area').val(),
                        'level': $('#level').val(),
                        'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                        'setAdmin': $('#area_admin').is(':checked')
                    },
                    success: function(data) {
                        $('#div_secondary_areas').html(data);
                    }
                });

                $.ajax({
                    url: 'get_areas_to_set_admin.php',
                    type: 'POST',
                    data: {
                        'primary_area': $('#primary_area').val(),
                        'level': $('#level').val(),
                        'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                        'setAdmin': $('#area_admin').is(':checked')
                    },
                    success: function(data) {
                        $('#div_manageble_areas').html(data);
                    }
                });
            });

            $('[name="area_admin"]').on('change', function() {
                costInputControl();
                
                $.ajax({
                    url: 'get_areas_to_set_admin.php',
                    type: 'POST',
                    data: {
                        'primary_area': $('#primary_area').val(),
                        'level': $('#level').val(),
                        'cod': (typeof $('#cod') !== 'undefined' ? $('#cod').val() : ""),
                        'setAdmin': ($(this).val() == "yes" ? "true": false)
                    },
                    success: function(data) {
                        $('#div_manageble_areas').html(data);
                    }
                });
                
			});


            /* Datatables DOM reference: https://datatables.net/reference/option/dom#Styling */
            var dataTable = $('#table_users').DataTable({
                "processing": true,
                "serverSide": true,
                deferRender: true,
                dom: "<'row'<'#custom_filter.col-sm-12 col-md-4'><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columnDefs: [{
                    searchable: false,
                    orderable: false,
                    targets: ['editar', 'remover']
                }, ],
                "ajax": {
                    url: "users-grid-data.php", // json datasource
                    type: "post", // method  , by default get

                    "data": function (d) {
                        d.term_status = $('#term_status').val();
                        d.term_status_custom = $('#term_status_custom').val();
                    },
                    error: function() { // error handling
                        $(".users-grid-error").html("");
                        $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                        $("#users-grid_processing").css("display", "none");
                    }
                },
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });

            dataTable.on('draw.dt', function () {
                createCustomElementInDatatablesDiv();
            });


            $('#term_status').on('change', function() {
                dataTable.ajax.reload(null, false);
            });


            if ($('#table_users_tmp').length) {
                var dataTableTmp = $('#table_users_tmp').DataTable({
                    "processing": true,
                    "serverSide": true,
                    deferRender: true,
                    columnDefs: [{
                        searchable: false,
                        orderable: false,
                        targets: ['editar', 'remover']
                    }, ],
                    "ajax": {
                        url: "userstmp_grid_data.php", // json datasource
                        type: "post", // method  , by default get
                        data: {
                            "areaAdmin": '<?= $areaAdmin ?>'
                        },
                        error: function() { // error handling
                            $(".users-grid-error").html("");
                            $("#users-grid").append('<tbody class="users-grid-error"><tr><th colspan="3">Informações indisponíveis no momento</th></tr></tbody>');
                            $("#users-grid_processing").css("display", "none");
                        }
                    },
                    "language": {
                        "url": "../../includes/components/datatables/datatables.pt-br.json"
                    }
                });
            }

            $('input, select, textarea').on('change', function() {
                $(this).removeClass('is-invalid');
            });

            $('#idSubmit').on('click', function(e) {
                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                let password = ($('#password').val() != "" ? $.MD5($('#password').val()) : "");
                let password2 = ($('#password2').val() != "" ? $.MD5($('#password2').val()) : "");

                let form = $('#form').serialize();
                form = removeParam('password', form);
                form = removeParam('password2', form);
                form += "&password=" + password + "&password2=" + password2;

                $("#idSubmit").prop("disabled", true);
                $.ajax({
                    url: './users_process.php',
                    method: 'POST',
                    // data: $('#form').serialize(),
                    data: form,
                    dataType: 'json',
                }).done(function(response) {

                    // console.log(response);
                    if (!response.success) {
                        $('#divResult').html(response.message);
                        $('input, select, textarea').removeClass('is-invalid');
                        if (response.field_id != "") {
                            $('#' + response.field_id).focus().addClass('is-invalid');
                        }
                        $("#idSubmit").prop("disabled", false);
                    } else {
                        $('#divResult').html('');
                        $('input, select, textarea').removeClass('is-invalid');
                        $("#idSubmit").prop("disabled", false);

                        if (response.profile) {
                            window.top.location.reload(true);
                            return true;
                        } else {
                            
                            if (isInIframe() && !isMainIframe()) {
                                window.parent.closeIframe('refresh');
                            } else {
                                $('#divResult').html('');
                                $('input, select, textarea').removeClass('is-invalid');
                                $("#idSubmit").prop("disabled", false);
                                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                                $(location).prop('href', url);
                            }
                        }
                        // $(location).prop('href', url);
                        return false;
                    }
                });
                return false;
            });

            $('#idBtIncluir').on("click", function() {
                $('#idLoad').css('display', 'block');
                var url = '<?= $_SERVER['PHP_SELF'] ?>?action=new';
                $(location).prop('href', url);
            });

            $('#bt-cancel').on('click', function() {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
            });
        });


        function loadClients() {
            $.ajax({
                url: 'get_clients_by_user_level.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    'level': $('#level').val(),
                    'clientDb': $('#user_client_db').val()
                },
                success: function(data) {

                    var clientDb = (typeof($('#user_client_db') !== 'undefined') ? $('#user_client_db').val() : "");
                    $('#user_client').empty();

                    if ($('#level').val() == "") {
                        $('#user_client').append('<option value=""><?= TRANS("SEL_LEVEL_FIRST"); ?></option>');
                        $('#user_client').selectpicker('refresh');
                        $('#user_client').selectpicker('val', "");
                    }
                    else {
                        
                        if (Object.keys(data).length > 1) {
                            $('#user_client').append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                        }
                        
                        $.each(data, function(key, data) {
                            $('#user_client').append('<option value="' + data.id + '"' + (data.id == clientDb ? 'selected' : '') + '>' + data.nickname + '</option>');
                        });
                        
                        $('#user_client').selectpicker('refresh');
                        
                        if (Object.keys(data).length == 1) {
                            $('#user_client').selectpicker('val', data[0].id);

                            loadDepartments(data[0].id);
                        } else
                        if (clientDb != "") {

                            var found = false;

                            for (i in data) {
                                if (data[i].id == clientDb) {
                                    found = true;
                                    $('#user_client').selectpicker('val', clientDb);
                                    loadDepartments(clientDb);
                                    break;
                                }
                            }
                            
                            if (!found) {
                                $('#user_client').selectpicker('val', "");
                                loadDepartments("");
                            }
                            
                        } else
                        {
                            $('#user_client').selectpicker('val', ""); 
                            loadDepartments("");
                        }
                    }
                }
            });
        }


        function loadDepartments(elementValue) {

			if ($("#user_department").length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './get_departments.php',
					method: 'POST',
					dataType: 'json',
					data: {
						client: elementValue,
                        origin: 'users'
					},
				}).done(function(data) {


                    var departmentDb = (typeof($('#user_department_db') !== 'undefined') ? $('#user_department_db').val() : "");

                    // console.log(data);
					$('#user_department').empty();
					if (Object.keys(data).length > 1) {
						$('#user_department').append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
					}
					$.each(data, function(key, data) {

						let unit = "";
						if (data.unidade != null) {
							unit = ' (' + data.unidade + ')';
						}
						$('#user_department').append('<option data-subtext="' + unit + '" value="' + data.loc_id + '">' + data.local + '</option>');
					});
					
                    
                    // if (departmentDb != "") {
                    //     var found = false;

                    //     for (i in data) {
                    //         if (data[i].loc_id == departmentDb) {
                    //             found = true;
                    //             $('#user_department').selectpicker('val', departmentDb);
                    //             break;
                    //         }
                    //     }
                        
                    //     if (!found) {
                    //         $('#user_department').selectpicker('val', "");
                    //     }
                    // }
                    
                    $('#user_department').selectpicker('refresh');
					$('#user_department').selectpicker('val', $("#user_department_db").val());

                    departmentSelectionControl(elementValue);
				});
			}
		}
		
        
        
        
        function loadUnitsByAssetTag(elementID = "assetTag") {
			// if ($('#asset_unit').length > 0) {
			if ($('#' + elementID).length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './get_units_by_asset_tag.php',
					method: 'POST',
					data: {
						asset_tag: $('#'+elementID).val(),
                        user_id: $('#cod').val(),
                        except_resource: 1
					},
					dataType: 'json',
				}).done(function(data) {
					$('#divResultAssets').html('');
                    
                    let html = '';
                    $('#'+elementID+'_asset_unit').empty().html(html);
                    $('#'+elementID+'_asset_unit').selectpicker('refresh');

                    if (data.length > 1) {
                        html += '<option value=""><?= TRANS("SEL_SELECT"); ?></option>';
                        for (i in data) {
							html += '<option data-subtext="' + data[i].cliente + '" value="' + data[i].inst_cod + '">' + data[i].inst_nome + '</option>';
						}
                        $('#'+elementID+'_asset_unit').empty().html(html);
					    $('#'+elementID+'_asset_unit').selectpicker('refresh');
                    } else

					if (data.length == 1) {
                        /* Nesse caso, já traz o elemento selecionado */
                        for (i in data) {
							html += '<option data-subtext="' + data[i].cliente + '" value="' + data[i].inst_cod + '">' + data[i].inst_nome + '</option>';
						}
                            
                        $('#'+elementID+'_asset_unit').empty().html(html);
                        $('#'+elementID+'_asset_unit').selectpicker('refresh');
                        $('#'+elementID+'_asset_unit').selectpicker('val', data[0].inst_cod);

                        loadAssetBasicInfo(elementID, $('#'+elementID).val(), data[0].inst_cod);
					} else {
                        
                        var html_message = '<div class="d-flex justify-content-center">';
                        html_message += '<div class="d-flex justify-content-center my-3" style="max-width: 100%; position: fixed; top: 1%; z-index:1030 !important;">';
                        html_message += '<div class="alert alert-warning alert-dismissible fade show w-100" role="alert">';
                        html_message += '<i class="fas fa-exclamation-circle"></i> ';
                        html_message += '<strong>Ooops!</strong> ';
                        html_message += '<?= TRANS('MSG_NO_ASSETS_FOUND_WITH_TAG_AND_CLIENT'); ?>';
                        html_message += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                        html_message += '<span aria-hidden="true">&times;</span>';
                        html_message += '</button>';
                        html_message += '</div></div></div>';
                        
                        $('#divResultAssets').html(html_message);
                    }
				});
				return false;
			}
		}


        function loadAssetBasicInfo (elementID, assetTag, assetUnit) {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            if (assetTag == "") {
                $('#'+elementID+'_asset_department').val('');
                $('#'+elementID+'_asset_desc').val('');
            }

            $.ajax({
                url: './get_asset_basic_info.php',
                method: 'POST',
                data: {
                    asset_tag: assetTag,
                    asset_unit: assetUnit,
                    except_resource: 1
                },
                dataType: 'json',
            }).done(function(data) {

                if (!jQuery.isEmptyObject(data)) {
                    $('#'+elementID+'_asset_department').val(data.local);
                
                    let assetDesc = data.tipo_nome + ' ' + data.fab_nome + ' ' + data.marc_nome;
                
                    $('#'+elementID+'_asset_desc').val(assetDesc);
                } else {
                    $('#'+elementID+'_asset_department').val('');
                    $('#'+elementID+'_asset_desc').val('');
                }
            });
		};

        
        function loadNewAssetRow() {
			// if ($('#type').length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './render_new_asset_row.php',
					method: 'POST',
					data: {
						random: Math.random().toString(16).substr(2, 8)
					},
					// dataType: 'json',
				}).done(function(data) {
					$('#new_assets_row').append(data);
				});
				return false;
			// }
		}



        function addAssetsProcess(numero) {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './add_user_assets_process.php',
                method: 'POST',
                dataType: 'json',
                data: $('#form_assets').serialize(),
            }).done(function(response) {
                if (!response.success) {

                    if (response.field_id != "") {
                        $('#' + response.field_id).focus().addClass('is-invalid');
                    }
                    $('#divResultAssets').html(response.message);
                } else {
                    $('#modalAssets').modal('hide');
                    location.reload();
                }
            });
            return false;
        }



        function generateTerm() {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './generate_user_term.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    user_id: $('#cod').val(),
                },
            }).done(function(response) {
                if (!response.success) {

                    if (response.field_id != "") {
                        $('#' + response.field_id).focus().addClass('is-invalid');
                    }
                    $('#divResult').html(response.message);
                } else {
                    // $('#divResult').html(response.message);
                    location.reload();
                }
            });
            return false;
        }


        function generateTermControl () {
                // e.preventDefault();
                
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });
                
                $.ajax({
                    url: './is_user_term_updated.php',
                    method: 'POST',
                    data: {
                        // user_id: $('#user_id').val(),
                        user_id: $('#cod').val(),
                    },
                    dataType: 'json',
                }).done(function(data) {

                    if (data.is_term_updated) {
                        $('#generateTerm').prop('disabled', true);
                    } else {
                        $('#generateTerm').prop('disabled', false);
                    }
                });
            };


        function confirmDeleteModal(id) {
            $('#deleteModal').modal();
            $('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
        }

        function deleteData(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './users_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'delete'
                },
                dataType: 'json',
            }).done(function(response) {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
                return false;
            });
            return false;
            // $('#deleteModal').modal('hide'); // now close modal
        }



        function controlInputAreaAdmin(el) {
			if (el.is(':checked')) {
				$.each($('.switch-next-checkbox'), function(index, el) {
					$('input[name^="areaAdmin"]').prop('checked', false);
				});
				// $(el).prop('checked', true).prop('disabled', true);
			}
		}


        function controlSetAreaAdmin () {
			$.each($('.switch-next-checkbox'), function(index, el) {
				var group_parent = $(this).parent(); 

				/* Radio: ID da opção SIM */
				var first_checkbox_id = group_parent.find('input:first').attr('id');

				/* Se a opção está marcada como SIM */
				var enabled = group_parent.find('input:first').is(':checked');
				
				/* checkbox "gerente" */
				var last_checkbox_id = $(this).find('input:last').attr('id');
				var last_checkbox_name = $(this).find('input:last').attr('name');

                // console.log('first_checkbox_id: ' + $('#'+first_checkbox_id).val())
                // console.log('first_checkbox_id: ' + first_checkbox_id)
                // console.log('last_checkbox_name: ' + last_checkbox_name)
                // console.log('last_checkbox_id: ' + last_checkbox_id)
				

				if (!enabled && $('#area_admin').is(':checked')) {
					$('#' + last_checkbox_id).prop('checked', false).prop('disabled', true);
				} else {
					$('#' + last_checkbox_id).prop('disabled', false);
				}
			});
		}



        function confirmDeleteModalTmp(id) {
            $('#deleteTmpModal').modal();
            $('#deleteTmpButton').html('<a class="btn btn-danger" onclick="deleteTmpData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
        }

        function deleteTmpData(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './new_user_confirm_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'delete',
                },
                dataType: 'json',
            }).done(function(response) {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
                return false;
            });
            return false;
            // $('#deleteModal').modal('hide'); // now close modal
        }

        function confirmUser(id) {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './new_user_confirm_process.php',
                method: 'POST',
                data: {
                    cod: id,
                    action: 'adminconfirm',
                },
                dataType: 'json',
            }).done(function(response) {
                var url = '<?= $_SERVER['PHP_SELF'] ?>';
                $(location).prop('href', url);
                return false;
            });
            return false;
            // $('#deleteModal').modal('hide'); // now close modal
        }

        function toggleUserLevel() {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../../admin/geral/toggleUserLevel.php',
                method: 'POST',
                dataType: 'json',
                // data: {
                // 	prob_id: val,
                // },
            }).done(function(response) {
                window.top.location.reload(true);
                // console.log(response);
            });
        }

        function controlRoutingRadio() {
            let isOperator = $('#level').val() == '2';
            let isAdmin = $('#level').val() == '1';
            let action = $('#action').val();


            // console.log('isAdmin: ' + $('#isAdmin').val())

            if (!isOperator && !isAdmin) {
                $('#can_get_routed').prop('checked', false).prop('disabled', true);
				$('#can_get_routed_no').prop('checked', true).prop('disabled', true);
                
                $('#can_route').prop('checked', false).prop('disabled', true);
				$('#can_route_no').prop('checked', true).prop('disabled', true);
                
            } else if (isOperator) {

                if (action == 'new') {
                    $('#can_get_routed').prop('checked', true);
                    $('#can_get_routed_no').prop('checked', false);

                    $('#can_route').prop('checked', true);
				    $('#can_route_no').prop('checked', false);
                }


                if ($('#isAdmin').val() == 1) {
                    $('#can_get_routed').prop('disabled', false);
                    $('#can_get_routed_no').prop('disabled', false);

                    $('#can_route').prop('disabled', false);
                    $('#can_route_no').prop('disabled', false);
                } else {
                    $('#can_get_routed').prop('disabled', true);
                    $('#can_get_routed_no').prop('disabled', true);

                    $('#can_route').prop('disabled', true);
                    $('#can_route_no').prop('disabled', true);
                }
                
            } else if (isAdmin) {
                
                if ($('#isAdmin').val() == 1) {
                    $('#can_get_routed').prop('disabled', false);
                    $('#can_get_routed_no').prop('disabled', false);

                    $('#can_route').prop('checked', true).prop('disabled', true);
				    $('#can_route_no').prop('checked', false).prop('disabled', true);
                } else {
                    $('#can_get_routed').prop('disabled', true);
                    $('#can_get_routed_no').prop('disabled', true);

                    $('#can_route').prop('disabled', true);
                    $('#can_route_no').prop('disabled', true);
                }
                
                // $('#can_get_routed').prop('disabled', false);
                // $('#can_get_routed_no').prop('disabled', false);
                
                // $('#can_route').prop('checked', true).prop('disabled', true);
				// $('#can_route_no').prop('checked', false).prop('disabled', true);
            }
        }

        function enableCostInput() {
            $('#max_cost_authorizing').prop('disabled', false);
        }

        function disableCostInput() {
            $('#max_cost_authorizing').prop('disabled', true).val('0,00');
        }

        function costInputControl() {
            if ($('#area_admin').length > 0) {
                if ($('#area_admin').is(':checked')) {
                    enableCostInput();
                } else {
                    disableCostInput();
                }
            }
        }
        
        function updateUsersTermsInfo() {
            $.ajax({
                url: './users_terms_update_process.php',
				dataType: 'json',
            });
            return false;
        }



        function createSignatureProcess() {
			
			var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

			// var form = $('form').get(0);
            var form = $('#form_signature').get(0);

            $.ajax({
                url: './create_signature_process.php',
                method: 'POST',
                data: new FormData(form),
				dataType: 'json',
				cache: false,
				processData: false,
				contentType: false,
            }).done(function(response) {
                if (!response.success) {

					// console.log(response);
                    $('#divResultSignature').html(response.message);
                } else {
					// console.log(response);
                    $('#divResultSignature').html(response.message);
                    location.reload();
                }
            });
            return false;
        }


        function createCustomElementInDatatablesDiv() {
            let div = document.getElementById('custom_filter');
            /* Se a div tiver valor, os elementos já foram criados, não precisa prosseguir*/
            if (div.innerHTML != '') {
                return false;
            }
            
            const sel = document.createElement("select");
             /* Definir o nome do select */
            sel.name = "term_status_custom";
            /* Definir o id do select */
            sel.id = "term_status_custom";
            /* Definir a classe do select */
            sel.className = "term_status_custom";

            const opt0 = document.createElement("option");
            opt0.value = "";
            opt0.text = "<?= TRANS('TERMS_ANY_STATUS', '', 1); ?>";
            sel.add(opt0, null);


            let jsonKeyValues = JSON.parse('<?= $jsonTermStatusOptions; ?>');
            // console.log(jsonKeyValues);

            /* Transformar o objeto jsonKeyValues em um array */
            let arrayJsonKeyValues = Object.keys(jsonKeyValues).map(key => [key, jsonKeyValues[key]]);
            /* Ordenar o array pelo valor */
            arrayJsonKeyValues.sort((a, b) => a[1].localeCompare(b[1]));
            
            // console.log(arrayJsonKeyValues);

            /* Criar os options com base no array */
            for (const [key, value] of arrayJsonKeyValues) {
                const opt = document.createElement("option");
                opt.value = key;
                opt.text = value;
                sel.appendChild(opt);
            }

            /* Criar os options com base no json chave:valor */
            // for (const key in jsonKeyValues) {
                
            //     if (jsonKeyValues.hasOwnProperty(key)) {
            //         // console.log(`${key} -> ${jsonKeyValues[key]}`)
            //         const opt = document.createElement("option");
            //         opt.value = key;
            //         opt.text = jsonKeyValues[key];
            //         sel.appendChild(opt);
            //     }
            // }
            div.appendChild(sel);
        }


        function sortObjectByValue(obj) {
            const sortedKeys = Object.keys(obj).sort((a, b) => obj[a].localeCompare(obj[b]));
            const sortedObj = {};

            for (let key of sortedKeys) {
                sortedObj[key] = obj[key];
            }

            return sortedObj;
        }
        
        
        
        function departmentSelectionControl(referencedElementValue) {
            $('#user_department').prop('disabled', false).selectpicker('refresh');

            if (referencedElementValue == '') {
                $('#user_department').val('').prop('disabled', true).selectpicker('refresh');
            }
        }
        
        
        function openSignaturePad() {
            $('#modal_signature').modal();
        }

        function openSignTerm() {
            let location = './sign_term.php';
            $("#iframeSignTerm").attr('src',location);
            $('#modal_sign_term').modal();
        }



        // function reloadTablesData(tablesObj) {
        //     for (const key in tablesObj) {
        //         if (tablesObj.hasOwnProperty(key)) {
        //             const table = tablesObj[key];
        //             if (table && typeof table.ajax !== 'undefined' && typeof table.ajax.reload === 'function') {
        //                 table.ajax.reload(null, false);
        //             }
        //         }
        //     }
        // }

        function reloadUsersData() {
            $('#table_users').DataTable().ajax.reload(null, false);
        }


        function getFlashMessage() {
            $.ajax({
                url: './get_flash_message.php',
                method: 'POST',
            }).done(function(response) {
                if (response.length > 0) {
                    $('#div_flash').html(response);
                }
            })
        }

        function loadInIframe(pageBase, params) {
            let url = pageBase + '.php?' + params;
            $("#iframe-content").attr('src',url)
            $('#modalIframe').modal();
        }

        function closeIframe(refresh) {
            $('#modalIframe').modal('hide');
            $("#iframe-content").attr('src','');

            if (refresh === 'refresh') {
                reloadUsersData();
                getFlashMessage();
            }
        }

        function isInIframe() {
			return (window.location !== window.parent.location) ? true : false;
		}

		function isMainIframe() {
			var iframeParent = window.parent.document.getElementById('iframeMain');
			return (iframeParent) ? true : false;
		}
    </script>
</body>

</html>
