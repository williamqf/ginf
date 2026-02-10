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
$termsNoTreater = "";
$termsTotal = "";
$holidays = getHolidays($conn);


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

$data['generate_chart'] = 0;



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
    $termsNoTreater .= " AND o.client = {$data['client']} ";
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
    $termsNoTreater .= " AND o.sistema IN ({$u_areas}) ";
    $termsTotal .= $terms;

    $array_areas_names = getUserAreasNames($conn, $u_areas);
    $string_area_names = implode(", ", array_filter($array_areas_names));
} elseif (!empty($data['area'])) {

    $string_area_names = getAreaInfo($conn, $data['area'])['area_name'];

    $terms .= " AND o.sistema = {$data['area']} ";
    $termsNoTreater .= " AND o.sistema = {$data['area']} ";
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


$data['table_1'] = [];
$data['table_2'] = [];

$data['tickets'] = [];
$data['tickets_2'] = [];


/* Busca na tickets_treaters_stages pelos tempos fornecidos */
$sql = "SELECT
            o.numero,
            COALESCE(cl.nickname, 'N/A') AS cliente,
            a.sistema,
            p.problema,
            COALESCE (SUM(tts.full_seconds), 0) as seconds,
            COALESCE (CONCAT
                        (
                            SUM(tts.hours),
                            ':',
                            FLOOR(SUM(tts.minutes)),
                            ':',
                            SUM(tts.seconds)
                        ), '00:00:00') as concated_time
            -- o.operador AS operador
        FROM
            sistemas a,
            problemas p,
            ((ocorrencias o
            LEFT JOIN
                tickets_treaters_stages tts ON tts.ticket = o.numero
                LEFT JOIN
                    usuarios u ON tts.treater_id = u.user_id)
            LEFT JOIN
                clients cl ON o.client = cl.id)
        WHERE
            o.sistema = a.sis_id AND
            o.problema = p.prob_id AND
            o.data_fechamento IS NOT NULL 
            {$terms}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            o.numero, cl.nickname, a.sistema, p.problema
            -- , o.operador
        ORDER BY numero
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


    // if (count($data['table_1']) == 0) {
    //     $data['success'] = false;
    //     $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
    //     echo json_encode($data);
    //     return false;
    // }


    foreach ($data['table_1'] as $row) {
        if (is_array($row) && array_key_exists('numero', $row)) {
            /* Utilizar a chave 'numero' como chave */
            $data['tickets'][$row['numero']] = $row;
        }
    }


    /* Busca geral - não pode filtrar pelo treater pois essa verificação é feita no loop da fila direta */
    $sql = "SELECT
                o.numero,
                COALESCE(cl.nickname, 'N/A') AS cliente,
                a.sistema,
                p.problema,
                -- '00:00:00' as concated_time,
                o.operador
            FROM
                sistemas a,
                problemas p,
                usuarios u,
                (ocorrencias o
                    LEFT JOIN
                        clients cl ON o.client = cl.id)
            WHERE
                o.sistema = a.sis_id AND
                o.problema = p.prob_id AND
                o.operador = u.user_id AND
                o.data_fechamento IS NOT NULL 
                {$termsNoTreater}
                {$clausulesByStates[$data['state']]}
            GROUP BY
                o.numero, cl.nickname, a.sistema, p.problema, o.operador
            ORDER BY numero";

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

    foreach ($data['table_2'] as $row) {
        if (is_array($row) && array_key_exists('numero', $row)) {
            /* Utilizar a chave 'numero' como chave */
            $data['tickets_2'][$row['numero']] = $row;
        }
    }

    $data['tickets'] = mergeArrays($data['tickets'], $data['tickets_2']);

    foreach ($data['tickets'] as $key => $ticketInfo) {
        if (!array_key_exists('concated_time', $ticketInfo)) {
            $data['tickets'][$key]['concated_time'] = '00:00:00';
        }
    }


    if (count($data['tickets']) == 0) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
        echo json_encode($data);
        return false;
    }


    if (!empty($data['tickets'])) {
        /* Realizar os cálculos de tempo em fila direta para cada chamado */
        $treaterAbsoluteSecondsSliced = [];

        foreach ($data['tickets'] as $key => $ticketInfo) {

            $profileCod = getProfileCod($conn, $_SESSION['s_wt_areas'], $key);
            $worktimeProfile = getWorktimeProfile($conn, $profileCod);

            $arrayObj[$key] = new WorkTime( $worktimeProfile, $holidays );

            $stages[$key] = getStagesFromTicket($conn, $key, ['panel' => '1']);

            foreach ($stages[$key] as $stageInfo) {
                /* date_start, date_stop, status, user_id */

                if (empty($data['treater']) || $data['treater'] == $stageInfo['user_id']) {
                    $arrayObj[$key]->startTimer($stageInfo['date_start']);
                    $arrayObj[$key]->stopTimer($stageInfo['date_stop'] ?? date('Y-m-d H:i:s'));

                    $treaterAbsoluteSecondsSliced[$key][] = absoluteTime($stageInfo['date_start'], $stageInfo['date_stop'] ?? date('Y-m-d H:i:s'))['inSeconds'];
                } else {
                    $arrayObj[$key]->startTimer(date('Y-m-d H:i:s'));
                    $arrayObj[$key]->stopTimer(date('Y-m-d H:i:s'));
                    $treaterAbsoluteSecondsSliced[$key][] = 0;
                }
            }

            $ticketInfo['abs_secs_in_direct_queue'] = array_sum($treaterAbsoluteSecondsSliced[$key] ?? []);
            $ticketInfo['abs_time_in_direct_queue'] = secToTime($ticketInfo['abs_secs_in_direct_queue'])['verbose'];
            $ticketInfo['filtered_secs_in_direct_queue'] = $arrayObj[$key]->getSeconds() ?? '0';
            $ticketInfo['filtered_time_in_direct_queue'] = $arrayObj[$key]->getTime() ?? '0';

            /* Só mostrar os que tiverem tempo */
            if ($ticketInfo['abs_secs_in_direct_queue'] > 0 || $ticketInfo['concated_time'] != '00:00:00') {
                $data['tickets'][$key] = $ticketInfo;
            } else {
                unset($data['tickets'][$key]);
            }


        }

        $data['tickets']['title'] = TRANS('REPORT_TICKETS_TREATING_TIMES');
        $data['tickets']['totalProvidedInSeconds'] = array_sum(array_column($data['tickets'], 'seconds'));
        $data['tickets']['totalProvidedInTime'] = secToTime($data['tickets']['totalProvidedInSeconds'])['verbose'];

        $data['tickets']['totalInQueueFilteredInSeconds'] = array_sum(array_column($data['tickets'], 'filtered_secs_in_direct_queue'));
        $data['tickets']['totalInQueueFilteredInTime'] = secToTime($data['tickets']['totalInQueueFilteredInSeconds'])['verbose'];

        $data['tickets']['totalInQueueAbsoluteInSeconds'] = array_sum(array_column($data['tickets'], 'abs_secs_in_direct_queue'));
        $data['tickets']['totalInQueueAbsoluteInTime'] = secToTime($data['tickets']['totalInQueueAbsoluteInSeconds'])['verbose'];

    }


// var_dump($data['tickets']); 


echo json_encode($data);

return true;
