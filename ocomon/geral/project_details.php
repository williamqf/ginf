<?php /*                        Copyright 2023 Flávio Ribeiro

        This file is part of OCOMON.

        OCOMON is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 2 of the License, or
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
require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
include_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";
require_once __DIR__ . "/" . "../../includes/components/html2text-master/vendor/autoload.php";


use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

$projectID = (isset($_GET['project_id']) && !empty($_GET['project_id']) ? (int)$_GET['project_id'] : '');
if (!$projectID) {
    
    echo message('danger', '', TRANS('MSG_PROJECT_ID_NOT_PROVIDED'), '', '', true);
    return false;
    
}

$projectDefinitions = getProjectDefinition($conn, $projectID);
if (empty($projectDefinitions)) {
    echo message('danger', '', TRANS('MSG_PROJECT_ID_NOT_FOUND'), '', '', true);
    return false;
}


$allTickets = getTicketsFromProject($conn, $projectID);

if (empty($allTickets)) {
    echo message('danger', '', TRANS('MSG_NO_TICKETS_FOUND_TO_PROJECT'), '', '', true);
    return false;
}

$authorizationTypes = [
    0 => TRANS('WITHOUT_DEFINITION'),
    1 => TRANS('STATUS_WAITING_AUTHORIZATION'),
    2 => TRANS('STATUS_AUTHORIZED'),
    3 => TRANS('STATUS_REFUSED'),
];

$slaIndicatorLabel = [];
$slaIndicatorLabel[1] = TRANS('SMART_NOT_IDENTIFIED');
$slaIndicatorLabel[2] = TRANS('SMART_IN_SLA');
$slaIndicatorLabel[3] = TRANS('SMART_IN_SLA_TOLERANCE');
$slaIndicatorLabel[4] = TRANS('SMART_OUT_SLA');

$textSlaColumn = [
    'gray-circle.svg' => TRANS('SMART_NOT_IDENTIFIED'),
    'green-circle.svg' => TRANS('SMART_IN_SLA'),
    'yellow-circle.svg' => TRANS('SMART_IN_SLA_TOLERANCE'),
    'red-circle.svg' => TRANS('SMART_OUT_SLA'),
];

$config = getConfig($conn);
$imgsPath = "../../includes/imgs/";
$iconFrozen = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_STOPPED') . "'><i class='fas fa-pause fa-lg'></i></span>";
$iconOutOfWorktime = "<span class='text-oc-teal' title='" . TRANS('HNT_TIMER_OUT_OF_WORKTIME') . "'><i class='fas fa-pause fa-lg'></i></i></span>";
$iconTicketClosed = "<span class='text-oc-teal' title='" . TRANS('HNT_TICKET_CLOSED') . "'><i class='fas fa-check fa-lg'></i></i></span>";
$percLimit = $config['conf_sla_tolerance']; 
$solverAreas = [];
$areasSumTimes = [];
$status = [];
$ledSlaResposta = [];
$textSlaResposta = [];
$ledSlaSolucao = [];
$textSlaSolucao = [];

$issuesTypes = [];
$authorization_status = [];
$clients = [];
$responseSeconds = [];
$solutionSeconds = [];
$resourcesFull = [];
$ticketsCosts = [];
$ticketsCostsSum = 0;

$ticketsResult = [];


$stringAllTickets = implode(', ', $allTickets);

$isAdmin = $_SESSION['s_nivel'] == 1;
$uareas = noHtml($_SESSION['s_uareas']);

$termsControl = "";
/* Usuários admininstradores não possuem restrições para acessar quaisquer chamados */
if (!$isAdmin) {
    // $termsControl = " AND o.sistema IN ({$uareas})";
    $termsControl = " AND (o.sistema IN ({$uareas}) OR o.registration_operator = '{$_SESSION['s_uid']}')";
} 

$sql = $QRY["ocorrencias_full_ini"] . " 
            WHERE 
                o.numero IN ({$stringAllTickets}) 
                {$termsControl}
            ORDER BY numero";
$sqlResult = $conn->query($sql);
$total = $sqlResult->rowCount();


if (!$total) {
    echo message('danger', '', TRANS('MSG_NO_TICKETS_FOUND_TO_PROJECT_BY_LIMITED_AREAS'), '', '', true);
    return false;
}

foreach ($sqlResult->fetchAll() as $row) {

    $ticketsResult[$row['numero']] = $row;

    $clients[] = $row['nickname'] ?? "N/A";
    $solverAreas[] = $row['area'] ?? "N/A";
    $status[] = $row['chamado_status'] ?? "N/A";
    $issuesTypes[] = $row['problema'] ?? "N/A";
    $authorization_status[] = $authorizationTypes[$row['authorization_status']] ?? "N/A";


    /* Custo dos chamados */
    $has_cost = false;
    $cost_field_info = [];
    $tickets_cost_field = (!empty($config['tickets_cost_field']) ? $config['tickets_cost_field'] : "");
    if (!empty($tickets_cost_field)) {
        $cost_field_info = getTicketCustomFields($conn, $row['numero'], $tickets_cost_field);
        if (!empty($cost_field_info['field_value']) && priceDB($cost_field_info['field_value']) > 0) {
            $has_cost = true;
        }
    }

    if ($has_cost) {
        $ticketsCosts[$row['numero']] = $cost_field_info['field_value'];
        $ticketsCostsSum += priceDB($cost_field_info['field_value']);
    }
    /* Fim do bloco de custo dos chamados */



    // Recursos do chamado
    $resources = getResourcesFromTicket($conn, $row['numero']);
    $resources_info = [];
    if (!empty($resources)) {
        foreach ($resources as $resource) {
            $modelInfo = getAssetsModels($conn, $resource['model_id'], null, null, 1, ['t.tipo_nome']);
            
            $resources_info[$resource['model_id']]['model_id'][] = $resource['model_id'];
            $resources_info[$resource['model_id']]['modelo_full'][] = $modelInfo['tipo'] . ' ' . $modelInfo['fabricante'] . ' ' . $modelInfo['modelo'];
            $resources_info[$resource['model_id']]['categoria'][] = $modelInfo['cat_name'];
            $resources_info[$resource['model_id']]['amount'][] = $resource['amount'];
            $resources_info[$resource['model_id']]['unitary_price'][] = $resource['unitary_price'];
        }
    
        foreach ($resources_info as $key => $value) {
            $resources_info[$key]['model_id'] = implode(', ', $resources_info[$key]['model_id']);
            $resources_info[$key]['modelo_full'] = implode(', ', $resources_info[$key]['modelo_full']);
            $resources_info[$key]['categoria'] = implode(', ', $resources_info[$key]['categoria']);
            $resources_info[$key]['amount'] = implode(', ', $resources_info[$key]['amount']);
            $resources_info[$key]['unitary_price'] = implode(', ', $resources_info[$key]['unitary_price']);
        }

        $resources_info = arraySortByColumn($resources_info, 'modelo_full');
        $resourcesFull[] = $resources_info;
    }
    /* Final do bloco de recursos dos chamados */



    /** ************************************************************ 
     *** Processos para os cálculos de tempos de cada chamado ******
     * *************************************************************/
    $referenceDate = (!empty($row['oco_real_open_date']) ? $row['oco_real_open_date'] : $row['data_abertura']);
    $dataAtendimento = $row['data_atendimento']; //data da primeira resposta ao chamado
    $dataFechamento = $row['data_fechamento'];

    $holidays = getHolidays($conn);
    $profileCod = getProfileCod($conn, $_SESSION['s_wt_areas'], $row['numero']);
    $worktimeProfile = getWorktimeProfile($conn, $profileCod);

    /* Objeto para o cálculo de Tempo válido de SOLUÇÃO - baseado no perfil de jornada de trabalho e nas etapas em cada status */
    $newWT = new WorkTime( $worktimeProfile, $holidays );
        
    /* Objeto para o cálculo de Tempo válido de RESPOSTA baseado no perfil de jornada de trabalho e nas etapas em cada status */
    $newWTResponse = new WorkTime( $worktimeProfile, $holidays );

    /* Objeto para checagem se o momento atual está coberto pelo perfil de jornada associado */
    $objWT = new Worktime( $worktimeProfile, $holidays );

    /* Realiza todas as checagens necessárias para retornar os tempos de resposta e solução para o chamado */
    $ticketTimeInfo = getTicketTimeInfo($conn, $newWT, $newWTResponse, $row['numero'], $referenceDate, $dataAtendimento, $dataFechamento, $row['status_cod'], $objWT);

    /* Retorna os leds indicativos (bolinhas) para os tempos de resposta e solução */
    $ledSlaResposta[$row['numero']] = showLedSLA($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
    $ledSlaSolucao[$row['numero']] = showLedSLA($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);

    /* Texto sobre os SLAs - para serem imprimíveis */
    $textSlaResposta[$row['numero']] = $textSlaColumn[$ledSlaResposta[$row['numero']]];
    $textSlaSolucao[$row['numero']] = $textSlaColumn[$ledSlaSolucao[$row['numero']]];

    $isRunning = $ticketTimeInfo['running'];

    $colTVNew = $ticketTimeInfo['solution']['time'];
    if ($row['status_cod'] == 4) {
        $colTVNew = $iconTicketClosed . "&nbsp;" . $colTVNew;
    } elseif (isTicketFrozen($conn, $row['numero'])) {
        $colTVNew = $iconFrozen . "&nbsp;" . $colTVNew;
    } elseif (!$isRunning) {
        $colTVNew = $iconOutOfWorktime . "&nbsp;" . $colTVNew;
    }

    $responseResult = getSlaResult($ticketTimeInfo['response']['seconds'], $percLimit, $row['sla_resposta_tempo']);
    $solutionResult = getSlaResult($ticketTimeInfo['solution']['seconds'], $percLimit, $row['sla_solucao_tempo']);
    $absoluteTime[$row['numero']] = absoluteTime($referenceDate, (!empty($dataFechamento) ? $dataFechamento : date('Y-m-d H:i:s')))['inTime'];
    $absServiceTime[$row['numero']] = absoluteTime((!empty($dataAtendimento) ? $dataAtendimento : $referenceDate), (!empty($dataFechamento) ? $dataFechamento : date('Y-m-d H:i:s')))['inTime'];

    $solution_from_response_seconds[$row['numero']] = $ticketTimeInfo['solution']['seconds'] - $ticketTimeInfo['response']['seconds'];

    if ($solution_from_response_seconds[$row['numero']] != 0) {
        $solution_from_response_time[$row['numero']] = secToTime($solution_from_response_seconds[$row['numero']])['verbose'];
    } else {
        $solution_from_response_time[$row['numero']] = $ticketTimeInfo['solution']['time'];
    }

    $responseSeconds[$row['numero']] = $ticketTimeInfo['response']['seconds'];
    $solutionSeconds[$row['numero']] = $ticketTimeInfo['solution']['seconds'];

    $filteredSolutionTime[$row['numero']] = $ticketTimeInfo['solution']['time'];
    $filteredResponseTime[$row['numero']] = $ticketTimeInfo['response']['time'];

    /** Final dos processos para checagens de tempos do chamado */


    /* Soma os tempos de solução para cada área - Sempre se considera que o chamado mantém a mesma área para todo o tempo */
    if (!empty($row['area'])) {
        if (array_key_exists($row['area'], $areasSumTimes)) {
            $areasSumTimes[$row['area']] += $ticketTimeInfo['solution']['seconds'];
        } else {
            $areasSumTimes[$row['area']] = (int)$ticketTimeInfo['solution']['seconds'];
        }
    }



} // Final da iteração sobre os chamados

$jsonTicketsResult = json_encode($ticketsResult);
/* Primeiro e último chamados */
$firstTicket = min(array_keys($ticketsResult));
$lastTicket = max(array_keys($ticketsResult));

$clients = array_count_values($clients);
$solverAreas = array_count_values($solverAreas);
$status = array_count_values($status);
$issuesTypes = array_count_values($issuesTypes);

$authorization_status = array_count_values($authorization_status);

/* Recursos consolidados */
$resourcesSum = [];
foreach ($resourcesFull as $key => $value) {
    foreach ($value as $key2 => $value2) {
        // var_dump($value2);
        $resourcesSum[$value2['model_id']]['modelo_full'] = $value2['modelo_full'];
        $resourcesSum[$value2['model_id']]['categoria'] = $value2['categoria'];
        $resourcesSum[$value2['model_id']]['amount'] = (isset($resourcesSum[$value2['model_id']]['amount']) ? $resourcesSum[$value2['model_id']]['amount'] + $value2['amount'] : $value2['amount']);
        $resourcesSum[$value2['model_id']]['unitary_price'] = $value2['unitary_price'];
        $resourcesSum[$value2['model_id']]['total_price'] = $value2['unitary_price'] * $resourcesSum[$value2['model_id']]['amount'];
    }
}
$resourcesSum = arraySortByColumn($resourcesSum, 'modelo_full');


/** Não esquecer de listar todos os arquivos anexos aos chamados do projeto */
$sqlFiles = "SELECT * FROM imagens WHERE img_oco IN (" . $stringAllTickets . ") ";
$resultFiles = $conn->query($sqlFiles);
$hasFiles = $resultFiles->rowCount();


/* Definições das colunas */
$colLabel = "col-sm-3 text-md-right font-weight-bold p-2";
$colsDefault = "small text-break border-bottom rounded p-2 bg-white"; /* border-secondary */
$colContent = $colsDefault . " col-sm-3 col-md-3";
$colContentLine = $colsDefault . " col-sm-9";
$colContentLineFile = " text-break border-bottom rounded p-2 bg-white col-sm-9";

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/ocomon-tree.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

    <title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
    <style>
        #spanTktNumber {
            cursor: pointer;
        }
        hr.thick {
			border: 1px solid;
			border-radius: 5px;
		}

        li {
            list-style: none;
            line-height: 2em;
        }

        th {
            /* alinhamento vertical */
            vertical-align: top !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="idLoad" class="loading" style="display:none"></div>
    </div>
    <!-- <div class="container-fluid bg-light"> -->
    <div class="container bg-light">

        <?php
            if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
                echo $_SESSION['flash'];
                $_SESSION['flash'] = '';
            }
        ?>

        <input type="hidden" name="project_id" id="project_id" value="<?= $projectDefinitions['id']; ?>">

        <!-- Modal para exibir as informações de chamados individualmente -->
        <div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div id="divDetails" style="position:relative">
                        <iframe id="ticketInfo"  frameborder="0" style="position:absolute;top:0px;width:95%;height:100vh;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDefineProject" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="projectDefinition" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div id="divResultDefineProject"></div>
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="defineProjectTitle"><i class="fas fa-project-diagram"></i>&nbsp;<?= TRANS('PROJECT_IDENTIFICATION'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= TRANS('PROVIDE_PROJECT_DEFINITION'); ?>
                    </div>
                    <!-- Campos para definição do projeto -->
                    <div class="row mx-2">
                        <div class="form-group col-md-12">
                            <input type="text" name="project_name" id="project_name" class="form-control " placeholder="<?= TRANS('PLACEHOLDER_PROJECT_NAME'); ?>" value="<?= $projectDefinitions['name']; ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <textarea class="form-control " name="project_description" id="project_description" placeholder="<?= TRANS('PLACEHOLDER_PROJECT_DESCRIPTION'); ?>"><?= $projectDefinitions['description']; ?></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" id="projectButton" class="btn "><?= TRANS('BT_OK'); ?></button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    
    
        <nav class="navbar navbar-expand-md navbar-light  p-4 rounded" style="background-color: #48606b;">
            <div class="ml-2 font-weight-bold text-white"><i class="fas fa-project-diagram"></i>&nbsp;<?= TRANS('PROJECT'); ?>:&nbsp;<span id="project-name"><?= $projectDefinitions['name']; ?>&nbsp;<i class="fas fa-edit small"></i></span></div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#idProjectMenu" aria-controls="idProjectMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="idProjectMenu">
                <div class="navbar-nav ml-2 mr-2">

                </div>
            </div>
        </nav>

        <?php
            /* Significa que o resultado foi limitado às áreas que o operador faz parte */
            if (count($allTickets) != count($ticketsResult)) {
                echo message('info', '', TRANS('RESULT_LIMITED_BY_PERMISSIONS'), '', '', true);
            }
        ?>

        
        <!-- Identificação do projeto -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('PLACEHOLDER_PROJECT_NAME'); ?></div>
            <div class="<?= $colContent; ?>"><?= $projectDefinitions['name']; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('DESCRIPTION'); ?></div>
            <div class="<?= $colContent; ?>"><?= $projectDefinitions['description']; ?></div>
        </div>

        <!-- Clientes e quantidade de chamados -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('CLIENTS'); ?></div>
            <div class="<?= $colContent; ?>">
            <?php
                foreach ($clients as $key => $value) {
                    ?>
                        <li><?= $key; ?>&nbsp;(<span class="badge p-1"><?= $value; ?></span>)</li>
                    <?php
                }
            ?> 
            </div>
            <div class="<?= $colLabel; ?>"><?= TRANS('AMOUNT_OF_TICKETS'); ?></div>
            <div class="<?= $colContent; ?>"><?= count($ticketsResult); ?></div>
        </div>

        <!-- Datas de início do primeiro e do último chamado -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('STARTED_AT'); ?></div>
            <div class="<?= $colContent; ?>"><?= dateScreen($ticketsResult[$firstTicket]['data_abertura']); ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('LAST_TICKET_STARTED_AT'); ?></div>
            <div class="<?= $colContent; ?>"><?= dateScreen($ticketsResult[$lastTicket]['data_abertura']); ?></div>
        </div>

        <!-- Tempos do projeto -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('ABSOLUTE_TIME'); ?></div>
            <div class="<?= $colContent; ?>"><?= $absoluteTime[$firstTicket]; ?></div>
            <div class="<?= $colLabel; ?>"><?= TRANS('FILTERED_TIME'); ?></div>
            <div class="<?= $colContent; ?>"><?= $filteredSolutionTime[$firstTicket]; ?></div>
        </div>


        <!-- Status e status de autorização dos chamados envolvidos -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('COL_STATUS'); ?></div>
            <div class="<?= $colContent; ?>">
            <?php
                foreach ($status as $key => $value) {
                    ?>
                        <li><?= $key; ?>&nbsp;(<span class="badge p-1"><?= $value; ?></span>)</li>
                    <?php
                }
            ?> 
            </div>
            <div class="<?= $colLabel; ?>"><?= TRANS('AUTHORIZATION_STATUS'); ?></div>
            <div class="<?= $colContent; ?>">
            <?php
                foreach ($authorization_status as $key => $value) {
                    ?>
                        <li><?= $key; ?>&nbsp;(<span class="badge p-1"><?= $value; ?></span>)</li>
                    <?php
                }
            ?> 
            </div>
        </div>

        <!-- Áreas de atendimento e tipos de solicitações -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('SERVICE_AREAS'); ?></div>
            <div class="<?= $colContent; ?>">
            <?php
                foreach ($solverAreas as $key => $value) {

                    $tempo = (array_key_exists($key, $areasSumTimes) ? secToTime($areasSumTimes[$key])['verbose'] : '');
                    
                    ?>
                        <li><?= $key; ?>&nbsp;<span class="p-2 font-weight-bold"><?= $tempo; ?></span>(<span class="badge p-1"><?= $value; ?></span>)</li>
                    <?php
                }
            ?>        
            </div>

            <div class="<?= $colLabel; ?>"><?= TRANS('PROBLEM_TYPES'); ?></div>
            <div class="<?= $colContent; ?>">
            <?php
                foreach ($issuesTypes as $key => $value) {
                    ?>
                        <li><?= $key; ?>&nbsp;(<span class="badge p-1"><?= $value; ?></span>)</li>
                    <?php
                }
            ?>        
            </div>
        </div>

        <?php
        /* Recursos dos chamados do projeto */
        if (!empty($resourcesSum)) {
            ?>
            <div class="row my-2">
                <div class="<?= $colLabel; ?>"><?= TRANS('RESOURCES'); ?></div>
                <div class="<?= $colContentLine; ?>">
                    <table id="table_materials" class="table stripe hover order-column row-border" border="0" cellspacing="0" width="100%" >
                        <thead>
                            <tr>
                                <th><?= TRANS('COL_TYPE'); ?></th>
                                <th><?= TRANS('COL_AMOUNT'); ?></th>
                                <th><?= TRANS('UNITARY_PRICE'); ?></th>
                                <th><?= TRANS('TOTAL_CURRENCY'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                <?php
                    $summary = 0;
                    foreach ($resourcesSum as $resource) {
                        $row_price = (float)$resource['unitary_price'] * (int)$resource['amount'];
                        $summary += (float)$row_price;
                        ?>
                            <tr>
                                <td><?= $resource['modelo_full']; ?></td>
                                <td><?= $resource['amount']; ?></td>
                                <td><?= priceScreen($resource['unitary_price']); ?></td>
                                <td><?= priceScreen($row_price); ?></td>
                            </tr>
                        <?php
                    }
                ?>
                        <tfoot>
                            <tr><td colspan="3"></td><td class="font-weight-bold"><?= priceScreen($summary); ?></td></tr>
                        </tfoot>
                    </tbody>
                </table>
                </div>
            </div>
            <?php
        }
        ?>

        <!-- Custo dos chamados -->
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"><?= TRANS('TTL_SUM_TICKETS_COST'); ?></div>
            <div class="<?= $colContentLine; ?> font-weight-bold"><?= TRANS('CURRENCY'); ?>&nbsp;<?= priceScreen($ticketsCostsSum); ?></div>
        </div>
        

        <!-- Abas para exibir arquivos anexos e chamados relacionados -->
        <?php
            $classDisabledFiles = ($hasFiles > 0 ? '' : ' disabled');
            $ariaDisabledFiles = ($hasFiles > 0 ? '' : ' true');
            $classDisabledRelatives = ($ticketsResult ? '' : ' disabled');
            $ariaDisabledRelatives = ($ticketsResult ? '' : ' true');
        ?>
        <div class="row my-2">
            <div class="<?= $colLabel; ?>"></div>
            <div class="<?= $colContentLine; ?>">
                <ul class="nav nav-pills " id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" id="divViews-tab" data-toggle="pill" href="#divViews" role="tab" aria-controls="divViews" aria-selected="true"><i class="fas fa-eye-slash"></i>&nbsp;<?= TRANS('HIDE_DATA_LISTS'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active <?= $classDisabledRelatives; ?>" id="divSubs-tab" data-toggle="pill" href="#divSubs" role="tab" aria-controls="divSubs" aria-selected="true" aria-disabled="<?= $ariaDisabledRelatives; ?>"><i class="fas fa-stream"></i>&nbsp;<?= TRANS('TICKETS_REFERENCED'); ?>&nbsp;<span class="badge badge-light pt-1"><?= (count($ticketsResult)); ?></span></a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledFiles; ?>" id="divFiles-tab" data-toggle="pill" href="#divFiles" role="tab" aria-controls="divFiles" aria-selected="true" aria-disabled="<?= $ariaDisabledFiles; ?>"><i class="fas fa-paperclip"></i>&nbsp;<?= TRANS('FILES'); ?>&nbsp;<span class="badge badge-light pt-1"><?= $hasFiles; ?></span></a>
                    </li>

                    <!-- Aba para exibir a hierarquia dos chamados envolvidos -->
                    <li class="nav-item">
                        <a class="nav-link <?= $classDisabledRelatives; ?>" id="divTree-tab" data-toggle="pill" href="#divTree" role="tab" aria-controls="divTree" aria-selected="true" aria-disabled="<?= $ariaDisabledRelatives; ?>"><i class="fas fa-sitemap"></i>&nbsp;<?= TRANS('PROJECT_TREE_VIEW'); ?>&nbsp;<span class="badge badge-light pt-1"><?= (count($ticketsResult)); ?></span></a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- FINAL DAS ABAS -->

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade" id="divViews" role="tabpanel" aria-labelledby="divViews-tab">
                <div class="row my-2">
                    <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="views">
                        <!-- collapse -->
                    </div>
                </div>
            </div>
        
        
        
        
        <?php
        if ($ticketsResult) {
        ?>
            <div class="tab-pane fade show active" id="divSubs" role="tabpanel" aria-labelledby="divSubs-tab">
                <div class="row my-2">

                    <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="subs">
                        <!-- collapse -->
                        <table class="table table-hover table-striped rounded">
                            <thead class=" text-white" style="background-color: #48606b;">
                                <tr>
                                    <th scope="col" class="text-nowrap"><?= TRANS('TICKET_NUMBER'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('AREA'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('ISSUE_TYPE'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('CONTACT') . "<br />" . TRANS('COL_PHONE'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('DEPARTMENT') . "<br />" . TRANS('DESCRIPTION'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('COST'); ?></th>
                                    <th scope="col" class="text-nowrap"><?= TRANS('AUTHORIZATION_STATUS') . "<br />" . TRANS('COL_STATUS'); ?></th>
                                    <th scope="col" class="text-nowrap slas"><?= TRANS('COL_SLAS'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($ticketsResult as $rowDetail) {
                                    
                                    $shortDesc = trim($rowDetail['descricao']);
                                    $shortDesc = toHtml(nl2br($shortDesc));
                                    $shortDesc = (new \Html2Text\Html2Text(safeStrip($shortDesc)))->getText();
                                    
                                    if (strlen((string)$shortDesc) > 200) {
                                        $shortDesc = substr($shortDesc, 0, 195) . " ..... ";
                                    };

                                    $renderContactPhone = "<li class='font-weight-bold'>" . $rowDetail['contato'] . "</li>";
                                    $renderContactPhone .= "<li>" . $rowDetail['telefone'] . "</li>";

                                    $renderDepartment = "<li class='font-weight-bold'>" . $rowDetail['setor'] . "</li>";
                                    $renderDepartment .= "<li>" . $shortDesc . "</li>";

                                    $renderTicketCost = ($ticketsCosts[$rowDetail['numero']] ?? '');

                                    $renderStatus = "<li class='font-weight-bold'>" . $authorizationTypes[$rowDetail['authorization_status'] ?? 0] . "</li>";
                                    $renderStatus .= "<li>" . $rowDetail['chamado_status'] . "</li>";


                                    ?>
                                    <tr onClick="openTicketInfo('<?= $rowDetail['numero']; ?>')" style="cursor: pointer;">
                                        <th scope="row"><?= $rowDetail['numero']; ?></th>
                                        <td class="text-nowrap"><?= $rowDetail['area']; ?></td>
                                        <td><?= $rowDetail['problema']; ?></td>
                                        <td class="text-nowrap"><?= $renderContactPhone; ?></td>
                                        <td><?= $renderDepartment; ?></td>
                                        <td class="font-weight-bold text-nowrap"><?= $renderTicketCost; ?></td>
                                        <td><?= $renderStatus; ?></td>
                                        <td><?= "<img height='20' src='" . $imgsPath . "" . $ledSlaResposta[$rowDetail['numero']] . "' title='" . TRANS('HNT_RESPONSE_LED') . "'>&nbsp;<img height='20' src='" . $imgsPath . "" . $ledSlaSolucao[$rowDetail['numero']] . "' title='" . TRANS('HNT_SOLUTION_LED') . "'>"; ?></td>

                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Final da listagem de chamados relacionados ao projeto -->
        <?php
        }
        




        /* Painel para exibição dos arquivos anexos aos chamados do projeto */
        if ($hasFiles) {
        ?>
            <div class="tab-pane fade" id="divFiles" role="tabpanel" aria-labelledby="divFiles-tab">
                <div class="row my-2">

                    <div class="col-sm-12 border-bottom rounded p-0 bg-white " id="files">
                        <!-- collapse -->
                        <table class="table table-hover table-striped rounded">
                            <thead class=" text-white" style="background-color: #48606b;">
                                <tr>
                                    <th scope="col"><?= TRANS('TICKET_NUMBER'); ?></th>
                                    <th scope="col"><?= TRANS('COL_TYPE'); ?></th>
                                    <th scope="col"><?= TRANS('SIZE'); ?></th>
                                    <th scope="col"><?= TRANS('FILE'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($resultFiles->fetchAll() as $rowFiles) {

                                    $size = round($rowFiles['img_size'] / 1024, 1);
                                    $rowFiles['img_tipo'] . "](" . $size . "k)";

                                    if (isImage($rowFiles["img_tipo"])) {
                                        $viewImage = "&nbsp;<a onClick=\"javascript:popupWH('../../includes/functions/showImg.php?" .
                                            "file=" . $row['numero'] . "&cod=" . $rowFiles['img_cod'] . "'," . $rowFiles['img_largura'] . "," . $rowFiles['img_altura'] . ")\" " .
                                            "title='view'><i class='fa fa-search'></i></a>";
                                    } else {
                                        $viewImage = "";
                                    }
                                ?>
                                    <tr>
                                        <th scope="row" onClick="openTicketInfo('<?= $rowFiles['img_oco']; ?>')" style="cursor: pointer;"><?= $rowFiles['img_oco']; ?></th>
                                        <td><?= $rowFiles['img_tipo']; ?></td>
                                        <td><?= $size; ?>k</td>
                                        <td><a onClick="redirect('../../includes/functions/download.php?file=<?= $rowFiles['img_oco']; ?>&cod=<?= $rowFiles['img_cod']; ?>')" title="Download the file"><?= $rowFiles['img_nome']; ?></a><?= $viewImage; ?></i></td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php
        }
        /* FINAL DO TRECHO DE LISTAGEM DE ARQUIVOS ANEXOS*/




        if ($ticketsResult) {
            $storeSons = [];
            $storeParent = [];
            $firstOfAll = getFirstFather($conn, $allTickets[0], $storeParent);
            $storeSons = getTicketDownRelations($conn, $firstOfAll, $storeSons);
            $familyTree = generateFamilyTree($storeSons);
            ?>
                <input type="hidden" name="ticket" id="ticket" value="<?= $allTickets[0]; ?>">
                <div class="tab-pane fade" id="divTree" role="tabpanel" aria-labelledby="divTree-tab">
                    <div class="row my-2">
                        <div class="col-sm-2 border-bottom rounded p-0 bg-white ">    
                        </div>
                        <div class="col-sm-10 border-bottom rounded p-0 bg-white " id="tree">    
                            <?= $familyTree; ?>
                        </div>
                    </div>
                </div>
            <?php
        }


        ?>
        
        </div> <!-- tab-content -->
        <!-- </div> -->
        <!-- </div> -->

        
    <!-- </div> -->
    </div> <!-- container -->
    <script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>


    <script>
        $(function() {


            let jsonTickets = JSON.parse('<?= $jsonTicketsResult; ?>');
            // console.log(jsonTickets);

            $('.table').DataTable({
				paging: true,
				deferRender: true,
                columnDefs: [{
					searchable: false,
					orderable: false,
					targets: ['slas']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

            $('.tree-nodes').css('cursor', 'pointer').on('click', function(e) {
                e.preventDefault();
                openTicketInfo($(this).attr('data-ticket'));
            });
            
            $('#project-name').css('cursor', 'pointer').on('click', function(e) {
                e.preventDefault();
                openDefineProjectModal($('#project_id').val());
            });

            // getTreeviewData();
            for (const key in jsonTickets) {
                if (jsonTickets.hasOwnProperty(key)) {
                    // console.log(`${key} -> ${jsonTickets[key]['area']}`)
                    if ($('#badge_' + key).length > 0) {
                        let html = "" + jsonTickets[key]['area'] + "<br />";
                        html += "<small><mark>" + jsonTickets[key]['problema'] + "</mark></small>";
                        
                        $('#badge_' + key).html(html);
                    }
                }
            }

        });

        function openDefineProjectModal(id) {
            $('#modalDefineProject').modal();
            $('#projectButton').html('<a class="btn btn-primary" onclick="updateProject(' + id + ')"><?= TRANS('BT_OK'); ?></a>');
        }

        function updateProject(id) {
            $(document).ajaxStart(function() {
                $(".loading").show();
            });
            $(document).ajaxStop(function() {
                $(".loading").hide();
            });
            $.ajax({
                url: 'set_project.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    'project_id': id,
                    'action': 'update',
                    'name': $('#project_name').val(),
                    'description': $('#project_description').val()
                },
            }).done(function(response) {
                if (!response.success) {

                    if (response.field_id != "") {
                        $('#' + response.field_id).focus().addClass('is-invalid');
                    }
                    $('#divResultDefineProject').html(response.message);
                } else {
                    $('#divResultDefineProject').html('');
                    $('#modalDefineProject').modal('hide');
                    
                    // reload the parent window
                    window.opener.location.reload();
                    location.reload();
                }
            });
            return false;
        }

        function getTreeviewData() {
            $.ajax({
                url: 'get_treeview_data.php',
                method: 'POST',
                data: {
                    ticket: $('#ticket').val()
                },
                dataType: 'json',

            }).done(function(data) {

                // percorrer o resultado data no formato key:value
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        // console.log(`${key} -> ${data[key]['area']}`)
                        if ($('#badge_' + key).length > 0) {
                            let html = "<small><mark>" + data[key]['area'] + "</mark></small><br />";
                            html += "<small><mark>" + data[key]['problema'] + "</mark></small>";
                            
                            $('#badge_' + key).html(html);
                        }
                    }
                }

            }).fail(function() {
                // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
                console.log(data);
            });
            return false;
        }

        function openTicketInfo(ticket) {
            let location = 'ticket_show.php?numero=' + ticket;
            $("#ticketInfo").attr('src',location)
            $('#modal').modal();
        }
    </script>
</body>

</html>