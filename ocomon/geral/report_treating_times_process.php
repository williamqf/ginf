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
require_once __DIR__ . "/" . "../../includes/classes/worktime/Worktime.php";
include_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();
$exception = "";
$criteria = [];
$criteriaText = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['total'] = 0;
$uareas = explode(',', (string)$_SESSION['s_uareas']);
$terms = "";
$termsTotal = "";


$labelsByStates = [
    1 => TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE'),
    2 => TRANS('STATE_OPEN_IN_SEARCH_RANGE'),
    3 => TRANS('STATE_OPEN_IN_SEARCH_RANGE_CLOSE_ANY_TIME'),
    4 => TRANS('STATE_OPEN_ANY_TIME_CLOSE_IN_SEARCH_RANGE'),
    5 => TRANS('STATE_JUST_OPEN_IN_SEARCH_RANGE')
];

/* Inicialização dos Critérios da consulta */
$criteria['client'] = TRANS('CLIENT') . ':&nbsp;' . TRANS('ALL');
$criteria['treater'] = TRANS('WORKER') . ':&nbsp;' . TRANS('ALL');
$criteria['area'] = TRANS('AREA') . ':&nbsp;' . TRANS('ALL');
$criteria['state'] = TRANS('CONTEXT') . ':&nbsp;' . TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE');

$post = $_POST;

$hora_inicio = ' 00:00:00';
$hora_fim = ' 23:59:59';

$data['client'] = (isset($post['client']) ? (int)$post['client'] : "");
$data['area'] = (isset($post['area']) ? (int)$post['area'] : "");

$data['treater'] = (isset($post['treater']) ? (int)$post['treater'] : "");

$data['d_ini'] = (isset($post['d_ini']) && isValidDate($post['d_ini']) ? dateDB($post['d_ini'] . $hora_inicio) : '');
$data['d_fim'] = (isset($post['d_fim']) && isValidDate($post['d_fim']) ? dateDB($post['d_fim'] . $hora_fim) : '');

$data['state'] = (isset($post['state']) && !empty($post['state']) ? (int)$post['state'] : 1);

$data['generate_chart'] = 1;



if (empty($data['d_ini']) || empty($data['d_fim']) || ($data['d_ini'] > $data['d_fim'])) {
    $data['success'] = false;
    $data['field_id'] = "d_ini";
    $data['message'] = TRANS('MSG_CHECK_PERIOD');
    $data['message'] = message('warning', 'Ooops!', $data['message'], '', '');
    echo json_encode($data);
    return false;
}



if (!empty($data['client'])) {
    $terms .= " AND o.client = {$data['client']} ";
    $termsTotal .= $terms;
    $criteria['client'] = TRANS('CLIENT') . ':&nbsp;<b>' . getClients($conn, $data['client'])['nickname'] . '</b>';
}





$limited_areas = false;
$string_area_names = "";
if (empty($data['area']) && isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
    /* Visibilidade isolada entre áreas para usuários não admin */
    $limited_areas = true;
    $u_areas = $_SESSION['s_uareas'];

    $terms .= " AND o.sistema IN ({$u_areas}) ";
    $termsTotal .= $terms;

    $array_areas_names = getUserAreasNames($conn, $u_areas);
    $string_area_names = implode(", ", array_filter($array_areas_names));
} elseif (!empty($data['area'])) {

    $string_area_names = getAreaInfo($conn, $data['area'])['area_name'];

    $terms .= " AND o.sistema = {$data['area']} ";
    $termsTotal .= $terms;
    $criteria['area'] = TRANS('AREA') . ':&nbsp;<b>' . $string_area_names . '</b>';
}




if (!empty($data['treater'])) {
    $terms .= " AND u.user_id = {$data['treater']} ";
    $termsTotal .= $terms;
    $criteria['treater'] = TRANS('WORKER') . ':&nbsp;<b>' . getUserInfo($conn, $data['treater'])['nome'] . '</b>';
}








$client_filter = [];


// $termsTotal = "";
$chart_title_sufix = '_chart_title';
$chart_1_prefix = 'chart_01';
$chart_2_prefix = 'chart_02';
$chart_3_prefix = 'chart_03';
$chart_4_prefix = 'chart_04';
$chart_5_prefix = 'chart_05';



/* Período */
$criteria['range'] = TRANS('RANGE_FROM_TO') . ':&nbsp;<b>' . $post['d_ini'] . ' - ' . $post['d_fim'] . '</b>';

/* Contexto */
$criteria['state'] = TRANS('CONTEXT') . ':&nbsp;<b>' . $labelsByStates[$data['state']] . '</b>';



$_SESSION['s_rep_filters']['client'] = $data['client'];
$_SESSION['s_rep_filters']['area'] = $data['area'];
$_SESSION['s_rep_filters']['state'] = $data['state'];
$_SESSION['s_rep_filters']['d_ini'] = $post['d_ini'];
$_SESSION['s_rep_filters']['d_fim'] = $post['d_fim'];



/* Montagem do texto dos critérios */
$criteriaText = implode(' | ', $criteria);
if ($limited_areas) {
    $criteriaText .= " <br />(". TRANS('RESULT_LIMITED_BY_PERMISSIONS').")";
}
$data['criteria'] = $criteriaText;
/* Final da montagem dos critérios */


$clausulesByStates = [
    1 => " AND o.data_abertura >= '{$data['d_ini']}' AND o.data_abertura <= '{$data['d_fim']}' AND 
    o.data_fechamento IS NOT NULL AND o.data_fechamento >= '{$data['d_ini']}' AND o.data_fechamento <= '{$data['d_fim']}' ",
    2 => " AND o.data_abertura >= '{$data['d_ini']}' AND o.data_abertura <= '{$data['d_fim']}' AND 
    (o.data_fechamento >= '{$data['d_fim']}' OR o.data_fechamento IS NULL) ",
    3 => " AND o.data_abertura >= '{$data['d_ini']}' AND o.data_abertura <= '{$data['d_fim']}' AND 
    o.data_fechamento IS NOT NULL ",
    4 => " AND o.data_fechamento >= '{$data['d_ini']}' AND o.data_fechamento <= '{$data['d_fim']}' ",
    5 => " AND o.data_abertura >= '{$data['d_ini']}' AND o.data_abertura <= '{$data['d_fim']}' "
];


/* Agrupada pelos operadores */
$data['table_1'] = [];
/* Agrupada pelos operadores e pelos clientes */
$data['table_2'] = [];
/* Agrupada pelos operadores e pelas áreas de atendimento */
$data['table_3'] = [];
/* Dados baseados na fila direta */
$data['table_4'] = [];

$data['tickets'] = [];

$workers = [];

$sql = "SELECT
            u.user_id, u.nome,
            -- SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop)) AS seconds,
            COALESCE (SUM(tts.full_seconds), 0) as seconds,
            -- CONCAT(FLOOR(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))/3600),':',FLOOR((SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600/60)),':',(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600)%60) as concated_time
            COALESCE (CONCAT
                        (
                            SUM(tts.hours),
                            ':',
                            FLOOR(SUM(tts.minutes)),
                            ':',
                            SUM(tts.seconds)
                        ), '00:00:00') as concated_time
        FROM
            tickets_treaters_stages tts,
            usuarios u,
            sistemas a,
            ocorrencias o
            LEFT JOIN clients cl ON cl.id = o.client
        WHERE
            tts.treater_id = u.user_id AND
            tts.ticket = o.numero AND
            o.sistema = a.sis_id AND
            -- o.client = cl.id AND
            o.data_fechamento IS NOT NULL 
            {$terms}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.user_id, u.nome
        ORDER BY
            seconds DESC
    ";

    // dump($sql); exit;


    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['table_1'] = $res->fetchAll();
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $totalProvided = count($data['table_1']);
    // if (count($data['table_1']) == 0) {
    //     $data['success'] = false;
    //     $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
    //     echo json_encode($data);
    //     return false;
    // }

    $data['table_1']['totalProvided'] = $totalProvided;
    $data['table_1']['title'] = TRANS('TREATING_TIMES');
    $data['table_1']['totalInSeconds'] = array_sum(array_column($data['table_1'], 'seconds'));
    $data['table_1']['total'] = secToTime($data['table_1']['totalInSeconds'])['verbose'];






/* Tabela 2 - agrupamento por cliente atendido */
$sql = "SELECT
            u.user_id, u.nome, cl.id as client_id, 
            COALESCE (cl.nickname, 'N/A') as client_name,
            -- SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop)) AS seconds,
            COALESCE (SUM(tts.full_seconds), 0) as seconds,
            -- CONCAT(FLOOR(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))/3600),':',FLOOR((SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600/60)),':',(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600)%60) as concated_time
            COALESCE (CONCAT
                        (
                            SUM(tts.hours),
                            ':',
                            FLOOR(SUM(tts.minutes)),
                            ':',
                            SUM(tts.seconds)
                        ), '00:00:00') as concated_time
        FROM
            tickets_treaters_stages tts,
            usuarios u,
            sistemas a,
            ocorrencias o
            LEFT JOIN clients cl ON cl.id = o.client
        WHERE
            tts.treater_id = u.user_id AND
            tts.ticket = o.numero AND
            o.sistema = a.sis_id AND
            -- o.client = cl.id AND
            o.data_fechamento IS NOT NULL 
            {$terms}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.user_id, u.nome, cl.id, cl.nickname
        ORDER BY
            seconds DESC
    ";

    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['table_2'] = $res->fetchAll();
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $data['table_2']['title'] = TRANS('TREATING_TIMES_X_CLIENT');
    $data['table_2']['totalInSeconds'] = array_sum(array_column($data['table_2'], 'seconds'));
    $data['table_2']['total'] = secToTime($data['table_2']['totalInSeconds'])['verbose'];



/* Tabela 3 - agrupamento por área de atendimento */
$sql = "SELECT
            u.user_id, u.nome, a.sis_id, a.sistema,
            -- SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop)) AS seconds,
            COALESCE (SUM(tts.full_seconds), 0) as seconds,
            -- CONCAT(FLOOR(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))/3600),':',FLOOR((SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600/60)),':',(SUM(TIMESTAMPDIFF(SECOND, tts.date_start, tts.date_stop))%3600)%60) as concated_time
            COALESCE (CONCAT
                        (
                            SUM(tts.hours),
                            ':',
                            FLOOR(SUM(tts.minutes)),
                            ':',
                            SUM(tts.seconds)
                        ), '00:00:00') as concated_time
        FROM
            tickets_treaters_stages tts,
            usuarios u,
            sistemas a,
            -- clients cl
            ocorrencias o
            LEFT JOIN clients cl ON cl.id = o.client
        WHERE
            tts.treater_id = u.user_id AND
            tts.ticket = o.numero AND
            o.sistema = a.sis_id AND
            -- o.client = cl.id AND
            o.data_fechamento IS NOT NULL 
            {$terms}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.user_id, u.nome, a.sis_id, a.sistema
        ORDER BY
            seconds DESC
    ";

    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['table_3'] = $res->fetchAll();
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $data['table_3']['title'] = TRANS('TREATING_TIMES_X_SERVICE_AREAS');
    $data['table_3']['totalInSeconds'] = array_sum(array_column($data['table_3'], 'seconds'));
    $data['table_3']['total'] = secToTime($data['table_3']['totalInSeconds'])['verbose'];




/* Tabela 4 - Agora os dados são obtidos de forma automática com base no tempo em que cada chamado
esteve na fila direta de atendimento de cada operador */

$sql = "SELECT
            ts.ticket
        FROM 
            tickets_stages ts,
            status st,
            usuarios u,
            sistemas a,
            -- clients cl
            ocorrencias o
            LEFT JOIN clients cl ON cl.id = o.client
        WHERE 
            ts.status_id = st.stat_id AND
            ts.treater_id = u.user_id AND
            st.stat_painel = 1 AND
            u.nivel < 3 AND
            o.sistema = a.sis_id AND
            -- o.client = cl.id AND
            ts.ticket = o.numero AND
            o.data_fechamento IS NOT NULL
            {$terms}
            {$clausulesByStates[$data['state']]}
        GROUP BY 
            ts.ticket
        ORDER BY
            ticket
        ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    if ($res->rowCount()) {
        $data['tickets'] = $res->fetchAll();
    }
} catch (PDOException $e) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
    echo json_encode($data);
    return false;
}



$holidays = getHolidays($conn);
$arrayObj = [];

$arrayTreaters = [];

foreach ($data['tickets'] as $ticket) {
    $profileCod = getProfileCod($conn, $_SESSION['s_wt_areas'], $ticket['ticket']);
    $worktimeProfile = getWorktimeProfile($conn, $profileCod);

    $ticketTreaters = getTicketTreaters($conn, $ticket['ticket']);
    $ticketTreatersStages = [];

    foreach ($ticketTreaters as $treater) {
        
        if (empty($data['treater']) || $data['treater'] == $treater['user_id']) {
            $data['treaters'][] = $treater;
            $ticketTreatersStages[$treater['user_id']] = getStagesFromTicket($conn, $ticket['ticket'], ['treater' => $treater['user_id'], 'panel' => '1']);
        }
    }

    foreach ($ticketTreatersStages as $key => $arrayValues) {
        // $key = treater id
        // $arrayValues = array de stages
        $arrayObj[$key] = new WorkTime( $worktimeProfile, $holidays );
    
        $treaterAbsoluteSecondsSliced = [];
        foreach ($arrayValues as $row) {
            $arrayObj[$key]->startTimer($row['date_start']);
            $arrayObj[$key]->stopTimer($row['date_stop'] ?? date('Y-m-d H:i:s'));
    
            $treaterAbsoluteSecondsSliced[$key][] = absoluteTime($row['date_start'], $row['date_stop'] ?? date('Y-m-d H:i:s'))['inSeconds'];
        }
    
        $totalAbsSecondsSliced[$key] = array_sum($treaterAbsoluteSecondsSliced[$key]);
        $totalAbsTimeSliced[$key] = secToTime($totalAbsSecondsSliced[$key])['verbose'];
        
        // $arrayTreaters[$key]['filteredTime'][] = $arrayObj[$key]->getTime() ?? '0';
        $arrayTreaters[$key]['filteredSeconds'][] = $arrayObj[$key]->getSeconds() ?? '0';
        $arrayTreaters[$key]['absoluteSeconds'][] = $totalAbsSecondsSliced[$key];
    }

}

// var_dump($arrayTreaters);

$totalAbsoluteSeconds = [];
$totalFilteredSeconds = [];

foreach ($arrayTreaters as $key => $arrayValues) {
    $workers[$key]['user_id'] = $key;
    $workers[$key]['name'] = getUsers($conn, $key)['nome'];

    $workers[$key]['filteredtime'] = secToTime(array_sum($arrayTreaters[$key]['filteredSeconds']))['verbose'];
    $workers[$key]['filteredSeconds'] = array_sum($arrayTreaters[$key]['filteredSeconds']);
    $workers[$key]['absolutetime'] = secToTime(array_sum($arrayTreaters[$key]['absoluteSeconds']))['verbose'];
    $workers[$key]['absoluteSeconds'] = array_sum($arrayTreaters[$key]['absoluteSeconds']);

    $totalAbsoluteSeconds[$key] = array_sum($arrayTreaters[$key]['absoluteSeconds']);
    $totalFilteredSeconds[$key] = array_sum($arrayTreaters[$key]['filteredSeconds']);
}


$data['table_4'] = $workers;
$data['table_4']['totalInDirectQueue'] = count($data['table_4']);
$data['table_4']['title'] = TRANS('TIME_IN_DIRECT_QUEUE');
$data['table_4']['totalAbsoluteTime'] = secToTime(array_sum($totalAbsoluteSeconds))['verbose'];
$data['table_4']['totalFilteredTime'] = secToTime(array_sum($totalFilteredSeconds))['verbose'];




if ($data['table_1']['totalProvided'] == 0 && $data['table_4']['totalInDirectQueue'] == 0) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
        echo json_encode($data);
        return false;
}




/* Gráfico 1 */
$data[$chart_1_prefix] = $data['table_1'];
$data[$chart_1_prefix . $chart_title_sufix] = TRANS('DISTRIBUTION_PER_OPERATOR');


/* Gráfico 2 */
$data[$chart_2_prefix] = $data['table_4'];
$data[$chart_2_prefix . $chart_title_sufix] = TRANS('DISTRIBUTION_PER_DIRECT_QUEUE');

echo json_encode($data);

return true;
