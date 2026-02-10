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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

$sess_client = (isset($_SESSION['s_rep_filters']['client']) ? $_SESSION['s_rep_filters']['client'] : '');
$sess_units = (isset($_SESSION['s_rep_filters']['units']) && !empty($_SESSION['s_rep_filters']['units']) ? implode(',', $_SESSION['s_rep_filters']['units']) : '');
$sess_units = json_encode($sess_units);
$sess_area = (isset($_SESSION['s_rep_filters']['area']) ? $_SESSION['s_rep_filters']['area'] : '-1');
$sess_issue = (isset($_SESSION['s_rep_filters']['issue']) ? $_SESSION['s_rep_filters']['issue'] : '-1');
$sess_resource = (isset($_SESSION['s_rep_filters']['resource']) ? $_SESSION['s_rep_filters']['resource'] : '-1');
$sess_d_ini = (isset($_SESSION['s_rep_filters']['d_ini']) ? $_SESSION['s_rep_filters']['d_ini'] : date('01/m/Y'));
$sess_d_fim = (isset($_SESSION['s_rep_filters']['d_fim']) ? $_SESSION['s_rep_filters']['d_fim'] : date('d/m/Y'));
$sess_state = (isset($_SESSION['s_rep_filters']['state']) ? $_SESSION['s_rep_filters']['state'] : 1);

$filter_areas = "";
$areas_names = "";
if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
    /* Visibilidade isolada entre áreas para usuários não admin */
    $u_areas = $_SESSION['s_uareas'];
    $filter_areas = " AND s.sis_id IN ({$u_areas}) ";

    $array_areas_names = getUserAreasNames($conn, $u_areas);

    foreach ($array_areas_names as $area_name) {
        if (strlen((string)$areas_names))
            $areas_names .= ", ";
        $areas_names .= $area_name;
    }
}

$labelsByStates = [
    1 => TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE'),
    2 => TRANS('STATE_OPEN_IN_SEARCH_RANGE'),
    3 => TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME'),
    4 => TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'),
    5 => TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE')
];

$jsonChart1 = 0;
$jsonChart2 = 0;
$jsonChart3 = 0;
$jsonChart4 = 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/jquery/datetimepicker/jquery.datetimepicker.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />


    <style>
        .chart-container {
            position: relative;
            /* height: 100%; */
            max-width: 100%;
            margin-left: 10px;
            margin-right: 10px;
            margin-bottom: 30px;
        }

        li {
            /* list-style: none; */
            margin-left: 16px;
            line-height: 1.5em;
        }
    </style>

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>


    <div class="container">
        <h5 class="my-4"><i class="fas fa-plus-square text-secondary"></i>&nbsp;<?= TRANS('ALLOCABLE_RESOURCES'); ?></h5>
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails">
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
            echo $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }


        if (!isset($_POST['action'])) {

        ?>
            <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                <div class="form-group row my-4">
                    <label for="client" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="client" name="client">
                            <option value="" selected><?= TRANS('ANY_OR_EMPTY'); ?></option>
                            <?php
                                $clients = getClients($conn);
                                foreach ($clients as $client) {
                                    ?>
                                    <option value="<?= $client['id']; ?>"
                                    <?= ($client['id'] == $sess_client ? ' selected' : ''); ?>
                                    ><?= $client['nickname']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="units" class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="units" name="units[]" multiple="multiple">
                            
                        </select>
                    </div>


                    <label for="area" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select-sel-any" id="area" name="area">
                            <option value=""><?= TRANS('OCO_SEL_ANY'); ?></option>
                            <?php
                            $sql = "SELECT * FROM sistemas s WHERE s.sis_atende = 1 {$filter_areas} AND s.sis_status NOT IN (0) ORDER BY sistema";
                            $resultado = $conn->query($sql);
                            foreach ($resultado->fetchAll() as $rowArea) {
                                print "<option value='" . $rowArea['sis_id'] . "'";
                                echo ($rowArea['sis_id'] == $sess_area ? ' selected' : '');
                                print ">" . $rowArea['sistema'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    
                    <label for="issue_type" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('ISSUE_TYPE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select-sel-any" id="issue_type" name="issue_type">
                            <option value=""><?= TRANS('OCO_SEL_ANY'); ?></option>
                            <?php
                                $issues = getIssuesByArea($conn, true);
                                foreach ($issues as $issue) {
                                    ?>
                                    <option value="<?= $issue['prob_id']; ?>"
                                    <?= ($issue['prob_id'] == $sess_issue ? ' selected' : ''); ?>
                                    ><?= $issue['problema']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="resource" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('RESOURCE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select-sel-any" id="resource" name="resource">
                            <option value=""><?= TRANS('OCO_SEL_ANY'); ?></option>
                            <?php
                                $resources = getAssetsModels($conn, null, null, null, 1, ['t.tipo_nome']);
                                foreach ($resources as $resource) {
                                    $fullType = $resource['tipo'] . ' - ' . $resource['fabricante'] . ' - ' . $resource['modelo'];
                                    ?>
                                    <option data-subtext="<?= $resource['cat_name']; ?>" data-model="<?= $resource['codigo']; ?>" value="<?= $resource['codigo']; ?>" 
                                    <?= ($resource['codigo'] == $sess_resource ? ' selected' : ''); ?>
                                    ><?= $fullType; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="d_ini" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('START_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_ini" name="d_ini" value="<?= $sess_d_ini; ?>" autocomplete="off" required />
                    </div>

                    <label for="d_fim" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('END_DATE'); ?></label>
                    <div class="form-group col-md-10">
                        <input type="text" class="form-control " id="d_fim" name="d_fim" value="<?= $sess_d_fim; ?>" autocomplete="off" required />
                    </div>


                    <label for="state" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('STATE'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select" id="state" name="state">
                            <option value="1" <?= ($sess_state == 1 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="2"<?= ($sess_state == 2 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE'); ?></option>
                            <option value="3"<?= ($sess_state == 3 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME'); ?></option>
                            <option value="4"<?= ($sess_state == 4 ? ' selected': ''); ?>><?= TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'); ?></option>
                            <option value="5"<?= ($sess_state == 5 ? ' selected': ''); ?>><?= TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE'); ?></option>
                        </select>
                    </div>

                    <div class="row w-100"></div>
                    <div class="form-group col-md-8 d-none d-md-block">
                    </div>
                    <div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="session_units" id="session_units" value="<?= $sess_units; ?>">
                        <input type="hidden" name="action" value="search">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_SEARCH'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                    

                </div>
            </form>
            <?php
        } else {
            /* Formulário submetido */

            $hora_inicio = ' 00:00:00';
            $hora_fim = ' 23:59:59';

            $criteria = "";
            
            /* Tratando os retorno sobre o filtro utilizado - Cliente */
            $client = (isset($_POST['client']) && !empty($_POST['client']) ? (int)$_POST['client'] : "");
            $_SESSION['s_rep_filters']['client'] = $client;
            $clientName = (!empty($client) ? getClients($conn, $client)['nickname']: "");
            if (!empty($clientName)) {
                $criteria .= '<li>' . TRANS('FILTERED_CLIENT') . ":<b> {$clientName}</b>" . '</li>';
            }


            /* Tratando os retorno sobre o filtro utilizado - Unidades */
            $units = (isset($_POST['units']) && !empty($_POST['units']) ? array_map('intval', $_POST['units']) : "");
            // $_SESSION['s_rep_filters']['units'] = $units;

            $criterText = "";
            if (!empty($units)) {
                $unitsIDs = implode(',', $units);
                $criterText = "";
                $sqlCriter = "SELECT inst_nome FROM instituicao WHERE inst_cod in ({$unitsIDs}) ORDER BY inst_nome";
                $resCriter = $conn->query($sqlCriter);
                foreach ($resCriter->fetchAll() as $rowCriter) {
                    if (strlen((string)$criterText)) $criterText .= ", ";
                    $criterText .= $rowCriter['inst_nome'];
                }
            }
            $unitsNames = $criterText;
            if (!empty($unitsNames)) {
                $criteria .= '<li>' . TRANS('FILTERED_UNIT') . ":<b> {$unitsNames}</b>" . '</li>';
            }



            /* Tratando os retorno sobre o filtro utilizado - Area */
            $area = (isset($_POST['area']) && !empty($_POST['area']) ? (int)$_POST['area'] : "");
            $_SESSION['s_rep_filters']['area'] = $area;
            $areaName = (!empty($area) ? getAreaInfo($conn, $area)['area_name']: "");
            if (!empty($areaName)) {
                $criteria .= '<li>' . TRANS('FILTERED_AREA') . ":<b> {$areaName}</b>" . '</li>';
            }


            /* Tratando os retorno sobre o filtro utilizado - Tipo de solicitação */
            $issueType = (isset($_POST['issue_type']) && !empty($_POST['issue_type']) ? (int)$_POST['issue_type'] : "");
            $_SESSION['s_rep_filters']['issue'] = $issueType;
            $issueTypeName = (!empty($issueType) ? getIssueById($conn, $issueType)['problema']: "");
            if (!empty($issueTypeName)) {
                $criteria .= '<li>' . TRANS('FILTERED_ISSUE_TYPE') . ":<b> {$issueTypeName}</b>" . '</li>';
            }


            /* Tratando os retorno sobre o filtro utilizado - Tipo de recurso alocado | alocável */
            $resource = (isset($_POST['resource']) && !empty($_POST['resource']) ? (int)$_POST['resource'] : "");
            $_SESSION['s_rep_filters']['resource'] = $resource;
            $resourceInfo = (!empty($resource) ? getAssetsModels($conn, $resource): "");
            $resourceName = (!empty($resourceInfo) ? $resourceInfo['tipo'] . ' ' . $resourceInfo['fabricante'] . ' ' . $resourceInfo['modelo'] : "");
            if (!empty($resourceName)) {
                $criteria .= '<li>' . TRANS('FILTERED_RESOURCE') . ":<b> {$resourceName}</b>" . '</li>';
            }

            

            /* Tratando os retorno sobre o filtro utilizado - Situação quanto ao status dos chamados no período */
            $state = (isset($_POST['state']) && !empty($_POST['state']) ? (int)$_POST['state'] : "");
            $_SESSION['s_rep_filters']['state'] = $state;
            $stateName = (!empty($state) ? $labelsByStates[$state]: "");
            $criteria .= '<li>' . TRANS('FILTERED_STATE') . ":<b> {$stateName}</b>" . '</li>';
            


            if ((!isset($_POST['d_ini'])) || (!isset($_POST['d_fim']))) {
                $_SESSION['flash'] = message('info', '', TRANS('MSG_ALERT_PERIOD'), '');
                redirect($_SERVER['PHP_SELF']);
            } else {

                $d_ini = $_POST['d_ini'] . $hora_inicio;
                $d_ini = dateDB($d_ini);

                $d_fim = $_POST['d_fim'] . $hora_fim;
                $d_fim = dateDB($d_fim);

                if ($d_ini <= $d_fim) {

                    $_SESSION['s_rep_filters']['d_ini'] = $_POST['d_ini'];
                    $_SESSION['s_rep_filters']['d_fim'] = $_POST['d_fim'];

                    $titleAboutRange = TRANS('TTL_PERIOD_FROM') . "&nbsp;" . dateScreen($d_ini, 1) . "&nbsp;" . TRANS('DATE_TO') . "&nbsp;" . dateScreen($d_fim, 1);


                    $clausulesByStates = [
                        1 => " AND o.data_abertura >= '{$d_ini}' AND o.data_abertura <= '{$d_fim}' AND 
                        o.data_fechamento IS NOT NULL AND o.data_fechamento >= '{$d_ini}' AND o.data_fechamento <= '{$d_fim}' ",
                        2 => " AND o.data_abertura >= '{$d_ini}' AND o.data_abertura <= '{$d_fim}' AND 
                        (o.data_fechamento >= '{$d_fim}' OR o.data_fechamento IS NULL) ",
                        3 => " AND o.data_abertura >= '{$d_ini}' AND o.data_abertura <= '{$d_fim}' AND 
                        o.data_fechamento IS NOT NULL ",
                        4 => " AND o.data_fechamento >= '{$d_ini}' AND o.data_fechamento <= '{$d_fim}' ",
                        5 => " AND o.data_abertura >= '{$d_ini}' AND o.data_abertura <= '{$d_fim}' "
                    ];
                    

                    $clientPost = (isset($_POST['client']) && !empty($_POST['client']) ? (int)$_POST['client'] : "");
                    $clientTerm = (!empty($clientPost) ? " AND o.client = {$clientPost} " : "");

                    $unitsPost = (isset($_POST['units']) && !empty($_POST['units']) ? implode(',', array_map('intval', $_POST['units'])) : "");
                    $unitsTerm = (!empty($unitsPost) ? " AND o.instituicao IN ({$unitsPost}) " : "");
                    
                    $areaPost = (isset($_POST['area']) && !empty($_POST['area']) ? (int)$_POST['area'] : "");
                    $areaTerm = (!empty($areaPost) ? " AND o.sistema = {$areaPost} " : "");

                    $issueTypePost = (isset($_POST['issue_type']) && !empty($_POST['issue_type']) ? (int)$_POST['issue_type'] : "");
                    $issueTypeTerm = (!empty($issueTypePost) ? " AND o.problema = {$issueTypePost} " : "");

                    $resourcePost = (isset($_POST['resource']) && !empty($_POST['resource']) ? (int)$_POST['resource'] : "");
                    $resourceTerm = (!empty($resourcePost) ? " AND txr.model_id = {$resourcePost} " : "");


                    /* Consulta base para a geração de tabelas e gráficos */
                    $sqlParts = [];
                    $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                    CONCAT(te.tipo_nome, ' ', f.fab_nome, ' ', mc.marc_nome) as tipo,
                                                    SUM(txr.amount) as quantidade ";

                    $sqlParts['FROM_COLUMNS'] = "FROM
                                                    tickets_x_resources txr,
                                                    sistemas s,
                                                    problemas p,
                                                    tipo_equip te,
                                                    marcas_comp mc,
                                                    fabricantes f,
                                                    assets_categories ac,
                                                    ocorrencias o
                                                        LEFT JOIN clients cl ON cl.id = o.client
                                                        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao ";

                    $sqlParts['WHERE_DEFAULT'] = "WHERE 
                                            o.numero = txr.ticket AND
                                            o.sistema = s.sis_id AND
                                            o.problema = p.prob_id AND
                                            txr.model_id = mc.marc_cod AND
                                            txr.is_current = 1 AND
                                            mc.marc_tipo = te.tipo_cod AND
                                            mc.marc_manufacturer = f.fab_cod AND
                                            te.tipo_categoria = ac.id AND
                                            te.can_be_product = 1 ";
                                            // ac.cat_is_product = 1 ";

                    $sqlParts['WHERE_CUSTOM'] = "{$clientTerm}
                                                    {$unitsTerm}
                                                    {$areaTerm}
                                                    {$issueTypeTerm}
                                                    {$resourceTerm}
                                                {$clausulesByStates[$_POST['state']]} ";

                    $sqlParts['GROUP_BY'] = "GROUP BY tipo ";
                    $sqlParts['ORDER_BY'] = "ORDER BY quantidade DESC";

                    $sqlTable1 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];

                    try {
                        $resultado = $conn->query($sqlTable1);
                        $linhasTable1 = $resultado->rowCount();

                        if ($linhasTable1) {
                        ?>
                            <p><?= $titleAboutRange; ?><span class="px-2 new_search" style="float:right" ><?= TRANS('NEW_SEARCH'); ?></span></p>
                            <small class="text-muted"><?= $criteria; ?></small>
                            <h5 class="my-4"><i class="fas fa-map-marker text-secondary"></i>&nbsp;<?= TRANS('TTL_GROUP_BY_RESOURCES'); ?></h5>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <!-- table-hover -->
                                    <!-- <caption><?= $criteria; ?></caption> -->
                                    <thead>
                                        <tr class="header table-borderless">
                                            <td class="line"><?= mb_strtoupper(TRANS('RESOURCE')); ?></td>
                                            <td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $dataChart1 = [];
                                        $dataChart2 = [];
                                        $dataChart3 = [];
                                        
                                        $total = 0;
                                        foreach ($resultado->fetchall() as $row) {
                                            $dataChart1[] = $row;
                                            ?>
                                            <tr>
                                                <td class="line"><?= $row['tipo']; ?></td>
                                                <td class="line"><?= $row['quantidade']; ?></td>
                                            </tr>
                                            <?php
                                            $total += $row['quantidade'];
                                        }

                                        $jsonChart1 = json_encode($dataChart1);
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="header table-borderless">
                                            <td><?= TRANS('TOTAL'); ?></td>
                                            <td><?= $total; ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="chart-container">
                                <canvas id="canvasChart1"></canvas>
                            </div>

                        <?php

                            $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                            CONCAT(te.tipo_nome, ' ', f.fab_nome, ' ', mc.marc_nome) as tipo,
                                                            s.sistema AS area, 
                                                            SUM(txr.amount) as quantidade ";

                            $sqlParts['GROUP_BY'] = "GROUP BY tipo, s.sistema ";
                            
                            $sqlTable2 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];


                            $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                                s.sistema as area,
                                                                SUM(txr.amount) as quantidade ";
                            $sqlParts['GROUP_BY'] = " GROUP BY s.sistema ";

                            $sqlChart2 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];

                    
                            try {
                                $resultado = $conn->query($sqlTable2);
                                $linhasTable2 = $resultado->rowCount();


                                if ($linhasTable2) {
                                    ?>
                                    <!-- Segunda tabela  -->
                                    <p><?= $titleAboutRange; ?><span class="px-2 new_search" style="float:right" ><?= TRANS('NEW_SEARCH'); ?></span></p>
                                    <small class="text-muted"><?= $criteria; ?></small>
                                    <h5 class="my-4"><i class="fas fa-map-marker text-secondary"></i>&nbsp;<?= TRANS('TTL_GROUP_BY_AREAS'); ?></h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <!-- <caption><?= $criteria; ?></caption> -->
                                            <thead>
                                                <tr class="header table-borderless">
                                                    <td class="line"><?= mb_strtoupper(TRANS('RESOURCE')); ?></td>
                                                    <td class="line"><?= mb_strtoupper(TRANS('AREA')); ?></td>
                                                    <td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $data = [];
                                                $data2 = [];
                                                
                                                $total = 0;
                                                foreach ($resultado->fetchall() as $row) {
                                                    ?>
                                                    <tr>
                                                        <td class="line"><?= $row['tipo']; ?></td>
                                                        <td class="line"><?= $row['area']; ?></td>
                                                        <td class="line"><?= $row['quantidade']; ?></td>
                                                    </tr>
                                                    <?php
                                                    $total += $row['quantidade'];
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="header table-borderless">
                                                    <td></td>
                                                    <td><?= TRANS('TOTAL'); ?></td>
                                                    <td><?= $total; ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>     

                                    <?php
                                        $resChart2 = $conn->query($sqlChart2);
                                        $linhasChart2 = $resChart2->rowCount();
                                        foreach ($resChart2->fetchall() as $row) {
                                            $dataChart2[] = $row;
                                        }
                                        $jsonChart2 = json_encode($dataChart2);
                                    ?>
                                    
                                    <!-- Segundo gráfico -->
                                    <div class="chart-container">
                                        <canvas id="canvasChart2"></canvas>
                                    </div>
                            
                                    <?php

                                        $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                            CONCAT(te.tipo_nome, ' ', f.fab_nome, ' ', mc.marc_nome) as tipo,
                                                            p.problema AS problema, 
                                                            SUM(txr.amount) as quantidade ";

                                        $sqlParts['GROUP_BY'] = "GROUP BY tipo, p.problema ";
                                        
                                        $sqlTable3 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];


                                        $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                                p.problema as problema,
                                                                SUM(txr.amount) as quantidade ";
                                        $sqlParts['GROUP_BY'] = " GROUP BY p.problema ";

                                        $sqlChart3 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];

                                        try {
                                            $resultado = $conn->query($sqlTable3);
                                            $linhasTable3 = $resultado->rowCount();

                                            if ($linhasTable3) {
                                                ?>
                                                <!-- Terceira tabela  -->
                                                <p><?= $titleAboutRange; ?><span class="px-2 new_search" style="float:right" ><?= TRANS('NEW_SEARCH'); ?></span></p>
                                                <small class="text-muted"><?= $criteria; ?></small>
                                                <h5 class="my-4"><i class="fas fa-map-marker text-secondary"></i>&nbsp;<?= TRANS('TTL_GROUP_BY_ISSUES_TYPES'); ?></h5>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered">
                                                        <!-- <caption><?= $criteria; ?></caption> -->
                                                        <thead>
                                                            <tr class="header table-borderless">
                                                                <td class="line"><?= mb_strtoupper(TRANS('RESOURCE')); ?></td>
                                                                <td class="line"><?= mb_strtoupper(TRANS('ISSUE_TYPE')); ?></td>
                                                                <td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $data = [];
                                                            $data2 = [];
                                                            
                                                            $total = 0;
                                                            foreach ($resultado->fetchall() as $row) {
                                                                ?>
                                                                <tr>
                                                                    <td class="line"><?= $row['tipo']; ?></td>
                                                                    <td class="line"><?= $row['problema']; ?></td>
                                                                    <td class="line"><?= $row['quantidade']; ?></td>
                                                                </tr>
                                                                <?php
                                                                $total += $row['quantidade'];
                                                            }
                                                            ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="header table-borderless">
                                                                <td></td>
                                                                <td><?= TRANS('TOTAL'); ?></td>
                                                                <td><?= $total; ?></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>  
                                                <?php
                                                
                                                    $resChart3 = $conn->query($sqlChart3);
                                                    foreach ($resChart3->fetchall() as $row) {
                                                        $dataChart3[] = $row;
                                                    }
                                                    $jsonChart3 = json_encode($dataChart3);

                                                    ?>
                                                        <!-- terceiro gráfico -->
                                                        <div class="chart-container">
                                                            <canvas id="canvasChart3"></canvas>
                                                        </div>
                                                    <?php



                                                        $sqlParts['SELECT_COLUMNS'] = "SELECT 
                                                            ac.cat_name as categoria, 
                                                            SUM(txr.amount) as quantidade ";

                                                        $sqlParts['GROUP_BY'] = "GROUP BY ac.cat_name ";
                                                        
                                                        $sqlTable4 = $sqlParts['SELECT_COLUMNS'] . $sqlParts['FROM_COLUMNS'] . $sqlParts['WHERE_DEFAULT'] . $sqlParts['WHERE_CUSTOM'] . $sqlParts['GROUP_BY'] . $sqlParts['ORDER_BY'];

                                                        try {
                                                            $resultado = $conn->query($sqlTable4);
                                                            $linhasTable4 = $resultado->rowCount();

                                                            if ($linhasTable4) {
                                                            ?>
                                                                <p><?= $titleAboutRange; ?><span class="px-2 new_search" style="float:right" ><?= TRANS('NEW_SEARCH'); ?></span></p>
                                                                <small class="text-muted"><?= $criteria; ?></small>
                                                                <h5 class="my-4"><i class="fas fa-map-marker text-secondary"></i>&nbsp;<?= TRANS('TTL_GROUP_BY_CATEGORIES'); ?></h5>

                                                                <div class="table-responsive">
                                                                    <table class="table table-striped table-bordered">
                                                                        <!-- table-hover -->
                                                                        <!-- <caption><?= $criteria; ?></caption> -->
                                                                        <thead>
                                                                            <tr class="header table-borderless">
                                                                                <td class="line"><?= mb_strtoupper(TRANS('CATEGORY')); ?></td>
                                                                                <td class="line"><?= mb_strtoupper(TRANS('COL_AMOUNT')); ?></td>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            $dataChart4 = [];
                                                                            
                                                                            $total = 0;
                                                                            foreach ($resultado->fetchall() as $row) {
                                                                                $dataChart4[] = $row;
                                                                                ?>
                                                                                <tr>
                                                                                    <td class="line"><?= $row['categoria']; ?></td>
                                                                                    <td class="line"><?= $row['quantidade']; ?></td>
                                                                                </tr>
                                                                                <?php
                                                                                $total += $row['quantidade'];
                                                                            }

                                                                            $jsonChart4 = json_encode($dataChart4);
                                                                            ?>
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr class="header table-borderless">
                                                                                <td><?= TRANS('TOTAL'); ?></td>
                                                                                <td><?= $total; ?></td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                </div>
                                                                <div class="chart-container">
                                                                    <canvas id="canvasChart4"></canvas>
                                                                </div>

                                                            <?php
                                                            }
                                                        } 
                                                        catch (Exception $e) {
                                                            $linhasTable4 = 0;
                                                        }
                                            }
                                        } catch (Exception $e) {
                                            $linhasTable3 = 0;
                                        }
                                }

                            } catch (Exception $e) {
                                $linhasTable2 = 0;
                            }

                        } else {
                            $_SESSION['flash'] = message('info', '', TRANS('MSG_NO_DATA_IN_PERIOD'), '');
                            redirect($_SERVER['PHP_SELF']);
                        }


                    } catch (PDOException $e) {
                        $_SESSION['flash'] = message('error', '', $e->getMessage(), '');
                        redirect($_SERVER['PHP_SELF']);
                    }


                } else {
                    $_SESSION['flash'] = message('info', '', TRANS('MSG_COMPARE_DATE'), '');
                    redirect($_SERVER['PHP_SELF']);
                }
            }
        }
        ?>
    </div>
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/datetimepicker/build/jquery.datetimepicker.full.min.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>

    <script type="text/javascript" src="../../includes/components/chartjs/dist/Chart.min.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-colorschemes/dist/chartjs-plugin-colorschemes.js"></script>
    <script type="text/javascript" src="../../includes/components/chartjs/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js"></script>
    <script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

    <script type='text/javascript'>
        $(function() {
            $.fn.selectpicker.Constructor.BootstrapVersion = '4';
            $('.bs-select').selectpicker({
                /* placeholder */
                title: "<?= TRANS('ANY_OR_EMPTY', '', 1); ?>",
                liveSearch: true,
                liveSearchNormalize: true,
                liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                style: "",
                styleBase: "form-control ",
            });
            $('.bs-select-sel-any').selectpicker({
                /* placeholder */
                title: "<?= TRANS('OCO_SEL_ANY', '', 1); ?>",
                liveSearch: true,
                liveSearchNormalize: true,
                showSubtext: true,
                liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                style: "",
                styleBase: "form-control ",
            });

            $('.table').DataTable({
				paging: true,
				deferRender: true,
                // columnDefs: [{
				// 	searchable: false,
				// 	orderable: false,
				// 	targets: ['slas']
				// }],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

            loadUnits();
            $("#client").on('change', function() {
				loadUnits();
			});

            $('.new_search').css('cursor', 'pointer').on('click', function(){
                window.history.back();
            });
            /* Idioma global para os calendários */
            $.datetimepicker.setLocale('pt-BR');
            
            /* Calendários de início e fim do período */
            $('#d_ini').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        maxDate: $('#d_fim').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });
            $('#d_fim').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        minDate: $('#d_ini').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });

            $('#idSubmit').on('click', function() {
                $('.loading').show();
            });

            if (<?= $jsonChart1 ?> != 0) {
                showChart1('canvasChart1');
            }
            if (<?= $jsonChart2 ?> != 0) {
                showChart2('canvasChart2');
            }
            if (<?= $jsonChart3 ?> != 0) {
                showChart3('canvasChart3');
            }
            if (<?= $jsonChart4 ?> != 0) {
                showChart4('canvasChart4');
            }

        });


        function loadUnits(targetId = 'units') {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../../ocomon/geral/get_units_by_client.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    client: $("#client").val()
                },
            }).done(function(data) {
                $('#' + targetId).empty();
                $.each(data, function(key, data) {
                    $('#' + targetId).append('<option value="' + data.inst_cod + '">' + data.inst_nome + '</option>');
                });

                $('#' + targetId).selectpicker('refresh');
                if ($('#session_units').val() != '') {
                    // $('#' + targetId).selectpicker('val', $('#session_units').val());
                    // $('#' + targetId).selectpicker('refresh');
                }
            });
        }


        function showChart1(canvasID) {
            var ctx = $('#' + canvasID);
            var dataFromPHP = <?= $jsonChart1; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP) {
                // console.log(dataFromPHP[i]);
                // labels.push(dataFromPHP[i].operador);
                labels.push(dataFromPHP[i].tipo);
                total.push(dataFromPHP[i].quantidade);
            }

            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= TRANS('total','',1); ?>',
                        data: total,
                        // backgroundColor: [
                        //     'rgba(255, 99, 132, 0.2)',
                        //     'rgba(54, 162, 235, 0.2)',
                        //     'rgba(255, 206, 86, 0.2)',
                        //     'rgba(75, 192, 192, 0.2)',
                        //     'rgba(153, 102, 255, 0.2)',
                        //     'rgba(255, 159, 64, 0.2)'
                        // ],
                        // borderColor: [
                        //     'rgba(255, 99, 132, 1)',
                        //     'rgba(54, 162, 235, 1)',
                        //     'rgba(255, 206, 86, 1)',
                        //     'rgba(75, 192, 192, 1)',
                        //     'rgba(153, 102, 255, 1)',
                        //     'rgba(255, 159, 64, 1)'
                        // ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_GROUP_BY_RESOURCES','',1); ?>',
                    },
                    scales: {
                        yAxes: [{
                            display: false,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.Paired12'
                        },
                        datalabels: {
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                            },
                            formatter: (value, ctx) => {
                                let sum = ctx.dataset._meta[0].total;
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            }
                        },
                    },
                }
            });
        }

        function showChart2(canvasID) {
            var ctx2 = $('#' + canvasID);
            var dataFromPHP2 = <?= $jsonChart2; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP2) {
                // console.log(dataFromPHP2[i]);
                // labels.push(dataFromPHP2[i].operador);
                labels.push(dataFromPHP2[i].area);
                total.push(dataFromPHP2[i].quantidade);
            }

            var myChart2 = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= TRANS('total','',1); ?>',
                        data: total,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_GROUP_BY_AREAS','',1); ?>',
                    },
                    scales: {
                        yAxes: [{
                            display: false,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.Paired12'
                        },
                        datalabels: {
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                            },
                            formatter: (value, ctx2) => {
                                let sum = ctx2.dataset._meta[1].total;
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            }
                        },
                    },
                }
            });
        }

        function showChart3(canvasID) {
            var ctx2 = $('#' + canvasID);
            var dataFromPHP3 = <?= $jsonChart3; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP3) {
                // console.log(dataFromPHP3[i]);
                // labels.push(dataFromPHP3[i].operador);
                labels.push(dataFromPHP3[i].problema);
                total.push(dataFromPHP3[i].quantidade);
            }

            var myChart2 = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= TRANS('total','',1); ?>',
                        data: total,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_GROUP_BY_ISSUES_TYPES','',1); ?>',
                    },
                    scales: {
                        yAxes: [{
                            display: false,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.Paired12'
                        },
                        datalabels: {
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                            },
                            formatter: (value, ctx2) => {
                                let sum = ctx2.dataset._meta[2].total;
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            }
                        },
                    },
                }
            });
        }

        function showChart4(canvasID) {
            var ctx2 = $('#' + canvasID);
            var dataFromPHP3 = <?= $jsonChart4; ?>

            var labels = []; // X Axis Label
            var total = []; // Value and Y Axis basis

            for (var i in dataFromPHP3) {
                // console.log(dataFromPHP3[i]);
                // labels.push(dataFromPHP3[i].operador);
                labels.push(dataFromPHP3[i].categoria);
                total.push(dataFromPHP3[i].quantidade);
            }

            var myChart2 = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?= TRANS('total','',1); ?>',
                        data: total,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: '<?= TRANS('TTL_GROUP_BY_CATEGORIES','',1); ?>',
                    },
                    scales: {
                        yAxes: [{
                            display: false,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.Paired12'
                        },
                        datalabels: {
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 1; // or !== 0 or ...
                            },
                            formatter: (value, ctx2) => {
                                let sum = ctx2.dataset._meta[3].total;
                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                return percentage;
                            }
                        },
                    },
                }
            });
        }
    </script>
</body>

</html>