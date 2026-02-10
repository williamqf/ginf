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

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

$config = getConfig($conn);
$costField = $config['tickets_cost_field'];

/* Variáveis de sessão para a atualização dos gráficos de acordo com o filtro */
$_SESSION['dash_filter_areas'] = "";
$_SESSION['dash_filter_clients'] = "";
$_SESSION['requester_areas'] = "";

$isAdmin = $_SESSION['s_nivel'] == 1;

$allAreasInfo = getAreas($conn, 0, 1, null);
$arrayAllAreas = [];
foreach ($allAreasInfo as $sigleArea) {
    $arrayAllAreas[] = $sigleArea['sis_id'];
}
$allAreas = implode(",", $arrayAllAreas);

$u_areas = ($isAdmin ? $allAreas : $_SESSION['s_uareas']);
$array_uareas = explode(",", $u_areas);



?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" /> -->
    <link rel="stylesheet" type="text/css" href="../../includes/css/loading.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="../../includes/components/jquery/dynamic-seo-tag-cloud/jquery.tagcloud.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>

    <style>
        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .pointer {
            cursor: pointer;
        }

        .dropdown-header {
            cursor: pointer !important;
            background: teal !important;
            color: white !important;
        }

        .dash-form-filter {
            display: none;
            background-color: white;
        }

        .filter-handler {
            cursor: pointer;
        }

        .filter-handler:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f0b0";
            font-weight: 900;
            font-size: 16px;
        }

        .filter-handler-invert {
            -webkit-transform: rotate(180deg);
            transform: rotate(180deg);
            right: 15px;
        }

        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
            margin-left: 10px;
            margin-right: 10px;
        }

        .chart-container-fullspace {
            position: relative;
            /* height: 300px; */
            height: 50vh;
            width: 100%;
            margin-left: 10px;
            margin-right: 10px;
        }

        .side-cards {
            max-width: calc(16.7%);
            min-width: calc(16.7%);
            height: 180%;
            float: right;
        }


        .icon-toogle {
            position: absolute;
            top: 0;
            right: 5px;
            z-index: 1;
            cursor: pointer;
        }

        .icon-show-graph {
            position: absolute;
            top: 0;
            left: 5px;
            z-index: 1;
            cursor: pointer;
        }

        .icon-expand:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f065"; */
            content: "\f30b";
            font-weight: 900;
            font-size: 16px;
        }

        .icon-collapse:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f066"; */
            content: "\f30a";
            font-weight: 900;
            font-size: 16px;
        }

        .icon-view-graph:before {
            font-family: "Font Awesome\ 5 Free";
            /* content: "\f065"; */
            content: "\f06e";
            font-weight: 900;
            font-size: 12px;
        }

        .icon-toogle-card:before {
            font-family: "Font Awesome\ 5 Free";
            content: "\f362";
            font-weight: 900;
            font-size: 12px;
        }

        .flex-container {
            display: flex;
            position: relative;
        }

        .flex-child {
            display: flex;
            max-width: calc(100%);
            flex: 1;
            position: relative;
        }

        .flex-cloudtag {
            display: flex;
            min-width: calc(100%);
            max-width: calc(100%);
            flex: 1;
            position: relative;
        }

        .cloud-container {
            flex: 1;
            min-width: calc(100%);
            max-width: calc(100%);
        }

        .flex-child-child {
            max-width: calc(100%/2);
            flex: 1;
            padding-right: 5px;
            padding-bottom: 5px;
            position: relative;
        }
        .flex-child-child-child {
            max-width: calc(100%/3);
            flex: 1;
            padding-right: 5px;
            padding-bottom: 5px;
            position: relative;
        }
        .flex-child-child-child-child {
            max-width: calc(100%/4);
            flex: 1;
            padding-right: 5px;
            padding-bottom: 5px;
            position: relative;
        }

        .flex-child-child-fullspace {
            max-width: calc(100%);
            flex: 1;
            padding-right: 5px;
            padding-bottom: 5px;
            position: relative;
        }


        .modal-graph {
            min-height: 90vh;
        }

        .modal-1000 {
            max-width: 1000px;
            margin: 30px auto;
        }


        @media only screen and (max-width: 768px) {

            .flex-container,
            .flex-child,
            .flex-child-child,
            .flex-child-child-child,
            .flex-child-child-child-child,
            .side-cards {
                display: block;
                max-width: 100%;
                min-width: 100%;
            }

            .icon-toogle {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container">
    <?php
        if (!$costField) {
            echo message('danger', 'Oooops!' , TRANS('COST_FIELD_NOT_DEFINED'), '', '', true);
            exit;
        }
    ?>
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>

    
    <div class="container-fluid">

        <div class="dash-form-filter" id="dash-form-filter">
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form" onSubmit="return false;">


                <div class="form-group row my-4">
                    <h5 class="w-100 mt-2 ml-5 p-4"><i class="fas fa-filter text-secondary"></i>&nbsp;<?= firstLetterUp(TRANS('CONTENT_FILTER')); ?></h5>


                    <label for="client" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">

                        <select class="form-control bs-select " id="client" name="client[]" multiple="multiple">
                            <?php
                            $clients = getClients($conn);
                            foreach ($clients as $client) {  
                                ?>
                                    <option value="<?= $client['id']; ?>" ><?= $client['nickname']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>


                    <label for="area" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('AREA'); ?></label>
                    <div class="form-group col-md-10">

                        <select class="form-control sel2 " id="area" name="area[]" multiple="multiple">
                            <optgroup label="<?= TRANS('SERVICE_AREAS'); ?>" data-icon="fas fa-headset">
                            <?php
                            $areas = getAreas($conn, 0, 1, 1);
                            foreach ($areas as $rowArea) {  

                                if (in_array($rowArea['sis_id'], $array_uareas)) {
                                ?>
                                    <option value="<?= $rowArea['sis_id']; ?>" ><?= $rowArea['sistema']; ?></option>
                                <?php
                                }
                            }
                            ?>
                            </optgroup>
                            <optgroup label="<?= TRANS('REQUESTER_AREAS'); ?>" data-icon="fas fa-user">
                            <?php
                            $areas = getAreas($conn, 0, 1, 0);
                            foreach ($areas as $rowArea) {  

                                if (in_array($rowArea['sis_id'], $array_uareas)) {
                                ?>
                                    <option value="<?= $rowArea['sis_id']; ?>" ><?= $rowArea['sistema']; ?></option>
                                <?php
                                }
                            }
                            ?>
                            </optgroup>
                        </select>
                    </div>

                    <label class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('REQUESTER_AREAS'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $yesChecked = "";
                        $noChecked = "checked"
                        ?>
                        <input type="radio" id="requester_areas" name="requester_areas" value="yes" <?= $yesChecked; ?> />
                        <label for="requester_areas"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="requester_areas_no" name="requester_areas" value="no" <?= $noChecked; ?> />
                        <label for="requester_areas_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <label class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_RENDER_CUSTOM_FIELDS'); ?>"><?= TRANS('RENDER_CUSTOM_FIELDS'); ?></label>
                    <div class="form-group col-md-4 switch-field">
                        <?php
                        $yesChecked = (isset($_SESSION['render_custom_fields']) && $_SESSION['render_custom_fields'] == "1" ? "checked" : "");
                        $noChecked = ((isset($_SESSION['render_custom_fields']) && $_SESSION['render_custom_fields'] == "0") || !isset($_SESSION['render_custom_fields']) ? "checked" : "");
                        ?>
                        <input type="radio" id="render_custom_fields" name="render_custom_fields" value="yes" <?= $yesChecked; ?> />
                        <label for="render_custom_fields"><?= TRANS('YES'); ?></label>
                        <input type="radio" id="render_custom_fields_no" name="render_custom_fields" value="no" <?= $noChecked; ?> />
                        <label for="render_custom_fields_no"><?= TRANS('NOT'); ?></label>
                    </div>

                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>

                    <input type="hidden" name="app_from" value="dashboard" id="app_from"/>
                    <div class="form-group col-12 col-md-2 ">
                        <button type="submit" id="idSearch" class="btn btn-primary btn-block"><?= TRANS('BT_FILTER'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" id="idReset" class="btn btn-secondary btn-block text-nowrap"><?= TRANS('COL_DEFAULT'); ?></button>
                    </div>
                </div>


                <!-- Cores para o gráfico de avaliações dos atendimentos -->
                <input type="hidden" name="color-great" class="color-great" id="color-great"/>
                <input type="hidden" name="color-good" class="color-good" id="color-good"/>
                <input type="hidden" name="color-regular" class="color-regular" id="color-regular"/>
                <input type="hidden" name="color-bad" class="color-bad" id="color-bad"/>
                <input type="hidden" name="color-not-rated" class="color-not-rated" id="color-not-rated"/>


            </form>
        </div>
        <div class='toogle-form-filter' id="toogle-form-filter">
            <!-- <div class="filter-handler btn btn-oc-teal btn-block" id="filter-handler"></div> -->
            <div class="filter-handler btn ocomon-color-5-lightest ocomon-color-3-bg btn-block" id="filter-handler"></div>
        </div>

        <div class="modal" tabindex="-1" id="modalDefault">
            <!-- <div class="modal-dialog modal-xl"> -->
            <div class="modal-dialog modal-1000">
                <div class="modal-content">
                    <div id="divShowGraph" class="modal-graph p-3">
                        <!-- <canvas id="canvasModal"></canvas> -->
                    </div>
                </div>
            </div>
        </div>


        <!-- Cards do topo - Fixos -->
        <div id="top-cards-2" class="top-cards mt-2">
            <div class="row no-gutters">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-info">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_TODAY'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-file-invoice-dollar"></i>&nbsp;<?= TRANS('TOTAL'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostTodayNotRejected" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-success">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-user-check"></i>&nbsp;<?= TRANS('STATUS_AUTHORIZED'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostMonthAuthorized" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-oc-orange">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-user-clock"></i>&nbsp;<?= TRANS('STATUS_WAITING_AUTHORIZATION'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostMonthWaitingAuth" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-question-circle"></i>&nbsp;<?= TRANS('WITHOUT_AUTHORIZATION_REQUEST'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostMonthNullAuth" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-user-times"></i>&nbsp;<?= TRANS('STATUS_REFUSED'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostMonthRejected" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-header bg-success">
                            <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                            <h6 class="text-center text-white text-nowrap"><i class="fas fa-file-invoice-dollar"></i>&nbsp;<?= TRANS('TOTAL'); ?></h6>
                            <h5 class="text-center text-white"><span id="totalCostMonthNotRejected" class="badge badge-light">0</span></h5>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        

        <!-- Cards Laterais - Carregados dinamicamente -->
        <div class="side-cards" id="side-cards">
            <div class="row">

            <?php
                
                $issues = getIssuesToCostcards($conn);
                $jsonIssues = json_encode($issues);
                $cardIdPrefix = 'issue_';
                
                foreach ($issues as $issue) {
                    ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-oc-teal">
                                <small><span class="badge badge-warning mb-2"><?= TRANS('CARDS_IN_THIS_MONTH'); ?></span></small>
                                <h6 class="text-center text-white text-nowrap"><i class="fas fa-ellipsis-v"></i>&nbsp;<?= $issue['problema'] ?></h6>
                                <h5 class="text-center text-white"><span id="<?= $cardIdPrefix . $issue['prob_id']; ?>" class="badge badge-light">0</span></h5>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ?>
            </div>
        </div>        

        <!-- ------------------------------------------------------------- --
            Seção de gráficos
        --------------------------------------------------------------------->

        <!-- Gráfico principal - Fixo - Maior que os demais -->
        <div class="flex-container">
            <div class="icon-toogle" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('INCREASE_OR_DECREASE_VIEW_PANEL'); ?>">
                <span class="icon-expand text-secondary" id="toogle-side-cards"></span>
            </div>
            <div class="flex-cloudtag">
                <div class="flex-child-child-fullspace">
                    <div class="icon-show-graph" id="header_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light ">
                            <div class="chart-container-fullspace" id="container-header">
                                <canvas id="graph_header"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Gráficos fixos -->
        <div class="flex-container">
            <div class="flex-child">
                <div class="flex-child-child">
                    <div class="icon-show-graph" id="first_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container" id="container01">
                                <canvas id="graph_01"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-child-child">
                    <div class="icon-show-graph" id="second_graph" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                        <span class="icon-view-graph text-oc-teal"></span>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="chart-container" id="container02">
                                <canvas id="graph_02"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?php
            /* Gráficos carregados dinamicamente em função dos tipos de solicitações marcados como card_in_costdash */
            if (count($issues)) {

                $chartPerLine = 3; /* 2, 3 ou 4 apenas (por linha) */
                $containerClasses = [
                    2 => 'flex-child-child',
                    3 => 'flex-child-child-child',
                    4 => 'flex-child-child-child-child'
                ];
                /* Separo o array de solicitações em arrays com o número máximo de gráficos por linha */
                $issues_chunked = array_chunk($issues, $chartPerLine);
                /* Não alterar esses valores */
                $showChartIDPrefix = 'show_';
                $chartContainerIDPrefix = 'container_';
                $canvasIDPrefix = 'canvas_';

                // Criação das divs para receber os gráficos com os tipos de solicitações
                $totalFlexContainers = (count($issues) % $chartPerLine == 0) ? (count($issues) / $chartPerLine) : (intdiv(count($issues), $chartPerLine)) + 1;
                $totalFlexContainers = (count($issues) < $chartPerLine) ? 1 : $totalFlexContainers;

                for ($i = 0; $i < $totalFlexContainers; $i++) {
                    ?>
                        <div class="flex-container">
                            <div class="flex-child">
                            <?php
                                foreach($issues_chunked[$i] as $issue) {
                                    /* criação dos containers para os gráficos */
                                    ?>
                                        <div class="<?= $containerClasses[$chartPerLine]; ?>">
                                            <div class="icon-show-graph" id="<?= $showChartIDPrefix . $issue['prob_id']; ?>" data-toggle="popover" data-trigger="hover" data-placement="left" title="<?= TRANS('SHOW_CHART'); ?>">
                                                <span class="icon-view-graph text-oc-teal"></span>
                                            </div>
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <div class="chart-container" id="<?= $chartContainerIDPrefix . $issue['prob_id']; ?>">
                                                        <canvas id="<?= $canvasIDPrefix . $issue['prob_id']; ?>"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                }
                            ?>
                            </div>
                        </div>
                    <?php
                }
            }
        ?>



        <?php
        /* Se o isolamento de visibilidade entre áreas estiver habilitado e não for user admin */
        $area = null;
        if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
            $area = $_SESSION['s_uareas'];
        ?>
            <div class="flex-container">
                <div class="flex-child">
                    <small class="mt-4 text-secondary">(<?= TRANS('SHOWN_ONLY_YOUR_AREAS_DATA'); ?>)</small>
                </div>
            </div>
        <?php
        }
        $startDate = date("Y-m-01");
        $endDate = date("Y-m-d");
        ?>
        <!-- Para a consulta de tags individuais na nuvem de tags -->
        <input type="hidden" name="startDate" id="startDate" value="<?= $startDate; ?>">
        <input type="hidden" name="endDate" id="endDate" value="<?= $endDate; ?>">
        <!-- <input type="hidden" name="area" id="area" value="<?= $area; ?>"> -->



        <script src="../../includes/javascript/funcoes-3.0.js"></script>
        <script src="../../includes/components/jquery/jquery.js"></script>
        <script src="../../includes/components/jquery/jquery-flip/dist/jquery.flip.js"></script>
        <script src="../../includes/components/bootstrap/js/popper.min.js"></script>
        <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
        <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
        <script src="../../includes/components/jquery/dynamic-seo-tag-cloud/jquery.tagcloud.js" type="text/javascript" charset="utf-8"></script>
        <script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>



        <script src="ajax/cost_x_area_months.js"></script>
        <script src="ajax/cost_x_issues_months.js"></script>
        <script src="ajax/cost_full_months.js"></script>
        <!-- Script que carregará os gráficos de forma dinâmica -->
        <script src="ajax/get_issues_cost_datacharts.js"></script>
        <script>
            $(function() {

                let jsonIssues = JSON.parse('<?= $jsonIssues; ?>');

                $.fn.selectpicker.Constructor.BootstrapVersion = '4';
                $('.sel2').selectpicker({
                    /* placeholder */
                    title: "<?= TRANS('OCO_SEL_ANY', '', 1); ?>",
                    liveSearch: true,
                    liveSearchNormalize: true,
                    liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                    noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                    style: "",
                    styleBase: "form-control input-select-multi",
                }).on('loaded.bs.select', enableBoostrapSelectOptgroup);
                
                $('.bs-select').selectpicker({
                    /* placeholder */
                    title: "<?= TRANS('OCO_SEL_ANY', '', 1); ?>",
                    actionsBox: true,
                    deselectAllText: "<?= TRANS('DESELECT_ALL', '', 1); ?>",
                    selectAllText: "<?= TRANS('SELECT_ALL', '', 1); ?>",
                    liveSearch: true,
                    liveSearchNormalize: true,
                    liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                    noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                    style: "",
                    styleBase: "form-control input-select-multi",
                });


                $('#toogle-form-filter').on('click', function() {

                    if ($('#dash-form-filter').css('display') == 'none') {
                        // $('#filter-handler').addClass('filter-handler-invert');
                        $('#dash-form-filter').slideDown();
                        $('#app_from').focus();
                    } else {
                        // $('#filter-handler').removeClass('filter-handler-invert');
                        $('#dash-form-filter').slideUp();
                        $('#toogle-form-filter').focusout();
                        $('#app_from').focus();
                    }
                });



                /* Filtro de pesquisa */
                $('#idSearch').on('click', function() {

                    loadFullData(jsonIssues);
                });

                $("#idReset").click(function(e) {

                    e.preventDefault();
                    $("#form").trigger('reset');

                    $('.sel2').selectpicker('render');
                    $('.bs-select').selectpicker('render');
                });


                /* Primeiro carregamento dos gráficos */
                cost_x_area_months('graph_01');
                cost_x_issues_months('graph_02');
                cost_full_months('graph_header');

                /* Iteração para carregar os gráficos de tipos de solicitações marcadas como card_in_costdash */
                for (let i in jsonIssues) {
                    /* Função que renderiza o gráfico dinamicamente */
                    get_issues_cost_datacharts(jsonIssues[i].prob_id);
                    
                    /* Evento de clique para a visualização de cada gráfico em modal */
                    $('#' + 'show_' + jsonIssues[i].prob_id).off().on('click', function() {
                        showGraphInModal(get_issues_cost_datacharts, jsonIssues[i].prob_id); 
                    });
                }
                

                $(".flip").flip({
                    trigger: 'manual'
                });


                $(function() {
                    $('[data-toggle="popover"]').popover({
                        html: true
                    })
                });
                $('.popover-dismiss').popover({
                    trigger: 'focus'
                });

                $('#first_graph').off().on('click', function() {
                    showGraphInModal(cost_x_area_months); 
                });
                $('#second_graph').off().on('click', function() {
                    showGraphInModal(cost_x_issues_months);
                });
                $('#header_graph').off().on('click', function() {
                    showGraphInModal(cost_full_months);
                });


                $('#toogle-side-cards').on('click', function() {
                    $('#side-cards').toggle('slow');
                    if ($('#toogle-side-cards').hasClass('icon-collapse')) {
                        $('#toogle-side-cards').addClass('icon-expand');
                        $('#toogle-side-cards').removeClass('icon-collapse');
                    } else {
                        $('#toogle-side-cards').addClass('icon-collapse');
                        $('#toogle-side-cards').removeClass('icon-expand');
                    }
                });

                $('.icon-toogle-card').on('click', function() {
                    if ($(this).parents().eq(2).hasClass('frente')) {
                        $(this).parents().eq(2).addClass('costas');
                        $(this).parents().eq(2).removeClass('frente');
                        $(this).parents().eq(2).flip(true);
                    } else {
                        $(this).parents().eq(2).addClass('frente');
                        $(this).parents().eq(2).removeClass('costas');
                        $(this).parents().eq(2).flip(false);
                    }
                });


                getTopCardsData_2();
                getSideCardsData_2();

                setInterval(function() {

                    loadFullData(jsonIssues);

                    // getTopCardsData_2();
                    // getSideCardsData_2();
                }, 120000); //a cada 2 minutos

            });

            function showGraphInModal(funcao, issueID = null) {

                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $('.canvas-modal').remove();

                var fieldHTML = '<canvas class="canvas-modal" id="canvasModal"></canvas>';

                $('#divShowGraph').append(fieldHTML);

                if (issueID != null) {
                    funcao(issueID, 'canvasModal');
                } else {
                    funcao('canvasModal');
                }

                $('#modalDefault').modal();
            }

            function getTopCardsData_2() {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });
                $.ajax({
                    url: 'get_top_cards_data_2.php',
                    method: 'POST',
                    data: $('#form').serialize(),
                    dataType: 'json',

                }).done(function(data) {

                    $('#totalCostTodayNotRejected').empty();
                    $('#totalCostTodayNotRejected').html(data.totalCostTodayNotRejected);
                    $('#totalCostTodayNotRejected').addClass('pointer');
                    $('#totalCostTodayNotRejected').off('click');
                    $('#totalCostTodayNotRejected').on('click', function(e) {
                        cardsAjaxList(data.totalCostTodayNotRejectedFilter, e);
                    });


                    $('#totalCostMonthAuthorized').empty();
                    $('#totalCostMonthAuthorized').html(data.totalCostMonthAuthorized);
                    $('#totalCostMonthAuthorized').addClass('pointer');
                    $('#totalCostMonthAuthorized').off('click');
                    $('#totalCostMonthAuthorized').on('click', function(e) {
                        cardsAjaxList(data.totalCostMonthAuthorizedFilter, e);
                    });
                    
                    
                    $('#totalCostMonthWaitingAuth').empty();
                    $('#totalCostMonthWaitingAuth').html(data.totalCostMonthWaitingAuth);
                    $('#totalCostMonthWaitingAuth').addClass('pointer');
                    $('#totalCostMonthWaitingAuth').off('click');
                    $('#totalCostMonthWaitingAuth').on('click', function(e) {
                        cardsAjaxList(data.totalCostMonthWaitingAuthFilter, e);
                    });

                    $('#totalCostMonthNullAuth').empty();
                    $('#totalCostMonthNullAuth').html(data.totalCostMonthNullAuth);
                    $('#totalCostMonthNullAuth').addClass('pointer');
                    $('#totalCostMonthNullAuth').off('click');
                    $('#totalCostMonthNullAuth').on('click', function(e) {
                        cardsAjaxList(data.totalCostMonthNullAuthFilter, e);
                    });

                    $('#totalCostMonthRejected').empty();
                    $('#totalCostMonthRejected').html(data.totalCostMonthRejected);
                    $('#totalCostMonthRejected').addClass('pointer');
                    $('#totalCostMonthRejected').off('click');
                    $('#totalCostMonthRejected').on('click', function(e) {
                        cardsAjaxList(data.totalCostMonthRejectedFilter, e);
                    });

                    $('#totalCostMonthNotRejected').empty();
                    $('#totalCostMonthNotRejected').html(data.totalCostMonthNotRejected);
                    $('#totalCostMonthNotRejected').addClass('pointer');
                    $('#totalCostMonthNotRejected').off('click');
                    $('#totalCostMonthNotRejected').on('click', function(e) {
                        cardsAjaxList(data.totalCostMonthNotRejectedFilter, e);
                    });
                    

                }).fail(function(data) {
                    // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
                    // console.log(data);
                });
                return false;
            }

            function getSideCardsData_2() {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });
                $.ajax({
                    url: 'get_side_cards_data_2.php',
                    method: 'POST',
                    data: $('#form').serialize(),
                    dataType: 'json',

                }).done(function(data) {

                    /* Itera sobre a resposta para adicionar as informações nos cards de tipos de solicitações - apenas os marcados como card_in_costdash */
                    for (var i in data) {
                        $('#' + data[i].id).empty();
                        $('#' + data[i].id).html(data[i].total);
                        $('#' + data[i].id).addClass('pointer');
                        $('#' + data[i].id).off('click');

                        $('#' + data[i].id).on('click', function(e) {
                            cardsAjaxList(data[$(this).attr('id')].totalCostByIssueFilter, e);
                        });
                    }

                }).fail(function(data) {
                    // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
                    // console.log(data);
                });
                return false;
            }

            function cardsAjaxList(arrayKeyData, e) {

                var data = {};
                $.each(arrayKeyData, function(key, value) {
                    // data[key] = encodeURIComponent(value);
                    data[key] = value;
                });

                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });

                $(document).ajaxStop(function() {
                    loading.hide();
                });

                popup_alerta_wide('./get_card_tickets.php?' + $.param(data));
            }





            function loadFullData(jsonIssues) {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });
                $.ajax({
                    url: 'update_dashboard_session.php',
                    method: 'POST',
                    data: $('#form').serialize(),
                }).done(function(data) {

                    let canvasHeader = '<canvas id="graph_header"></canvas>'
                    $('#container-header').empty().append(canvasHeader);
                    cost_full_months('graph_header');

                    let canvas01 = '<canvas id="graph_01"></canvas>'
                    $('#container01').empty().append(canvas01);
                    cost_x_area_months('graph_01'); //scheduled_tickets_x_workers

                    let canvas02 = '<canvas id="graph_02"></canvas>'
                    $('#container02').empty().append(canvas02);
                    cost_x_issues_months('graph_02');


                    /* Atualização das informações dos gráficos de tipos de solicitações - são carregados dinamicamente */
                    for (let i in jsonIssues) {
                        /* Limpa o canvas para poder carregar os gráficos com as informações resultantes do filtro */
                        let canvas = '<canvas id="canvas_' + jsonIssues[i].prob_id + '"></canvas>';
                        $('#' + 'container_' + jsonIssues[i].prob_id).empty().append(canvas);

                        /* Renderiza o gráfico dinamicamente - um para cada tipo de solicitação marcada como card_in_costdash */
                        get_issues_cost_datacharts(jsonIssues[i].prob_id);

                        /* Adiciona o evento de clique para a visualização do gráfico em modal */
                        $('#' + 'show_' + jsonIssues[i].prob_id).off().on('click', function() {
                            showGraphInModal(get_issues_cost_datacharts, jsonIssues[i].prob_id); 
                        });
                    }

                    getTopCardsData_2();
                    getSideCardsData_2();
                });
            };







            /* Função para habilitar a seleção de todos os itens de um optgroup ao clicar no label */
            function enableBoostrapSelectOptgroup() {

                let that = $(this).data('selectpicker'),
                    inner = that.$menu.children('.inner');

                // remove default event
                inner.off('click', '.divider, .dropdown-header');
                // add new event
                inner.on('click', '.divider, .dropdown-header', function(e) {
                    // original functionality
                    e.preventDefault();
                    e.stopPropagation();
                    if (that.options.liveSearch) {
                        that.$searchbox.trigger('focus');
                    } else {
                        that.$button.trigger('focus');
                    }

                    // extended functionality
                    let position0 = that.isVirtual() ? that.selectpicker.view.position0 : 0,
                        clickedData = that.selectpicker.current.data[$(this).index() + position0];

                    // copied parts from changeAll function
                    let selected = null;
                    for (let i = 0, data = that.selectpicker.current.data, len = data.length; i < len; i++) {
                        let element = data[i];
                        if (element.type === 'option' && element.optID === clickedData.optID) {
                            if (selected === null) {
                                selected = !element.selected;
                            }
                            element.option.selected = selected;
                        }
                    }
                    that.setOptionStatus();
                    that.$element.triggerNative('change');
                });
            }
        </script>
</body>

</html>
