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



$termsWithoutTreater = $termsTotal;
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
$chart_consolidated_prefix = 'chart_06';



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


/* Operadores que estão definidos diretamente nas ocorrências */
$data['table_1'] = [];
/* Operadores definidos como responsáveis em tickets_x_workers */
$data['table_2'] = [];
/* Operadores definidos como auxiliares em tickets_x_workers */
$data['table_3'] = [];
/* Operadores adicionados manualmente no ato do encerramento em tickets_treaters_stages */
$data['table_4'] = [];
/* Operadores que tiveram chamados em sua fila direta em tickets_stages */
$data['table_5'] = [];

/* Tabela auxiliar que concatena as tabelas 1, 2, 3, 4 e 5 */
$data['all_tables_merged'] = [];

/* Tabela final processada com a contabilização final - Remove os casos onde 
operadores que estão definidos diretamente nas ocorrências também aparecem nas outras tabelas */
$data['table_consolidated'] = [];

$workers = [];


/* Total de chamados do período para qualquer operador */
$sql = "SELECT
            count(o.numero) AS amount
        FROM
            ocorrencias o,
            usuarios u,
            `status` st,
            sistemas s
        WHERE
            o.operador = u.user_id
            AND u.nivel <> 3
            AND o.status = st.stat_id
            AND st.stat_ignored <> 1
            AND o.sistema = s.sis_id
            {$termsWithoutTreater}
            {$clausulesByStates[$data['state']]}
    ";

    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['total'] = $res->fetch()['amount'];
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $msg_completed_tickets = TRANS('MSG_AMOUNT_OF_COMPLETED_TICKETS') . ':&nbsp;<strong>' . $data['total'] . '</strong>';
    // $data['total_msg'] = message('info', '', $msg_completed_tickets, '', '', true);
    $data['total_msg'] = $msg_completed_tickets;





/* Consulta nas ocorrências com base no campo `operador` */
$sql = "SELECT
            u.nome AS operator,
            count(o.numero) AS amount
        FROM
            ocorrencias o,
            usuarios u,
            `status` st,
            sistemas s
        WHERE
            o.operador = u.user_id
            AND u.nivel <> 3
            AND o.status = st.stat_id
            AND st.stat_ignored <> 1
            AND o.sistema = s.sis_id
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
    ";

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

    $records = count($data['table_1']);

    $data['all_tables_merged'] = $data['table_1'];

    $data['table_1']['records'] = $records;
    $data['table_1']['title'] = TRANS('TITLE_REPORT_TREATERS_FIELD_OPERATOR');
    $data['table_1']['sum'] = array_sum(array_column($data['table_1'], 'amount'));
    $data['table_1']['msg_context'] = message('info', '', TRANS('MSG_REPORT_TREATERS_FIELD_OPERATOR'),'', '', true);
    $data['table_1']['msg_no_results'] = message('info', '', TRANS('NOT_FOUND_TREATERS_FIELD_OPERATOR'), '', '', true);



/** 
 * Todas as consultas seguintes terão duas versões:
 * 1. para exibição individual no relatório
 * 2. para agrupamento (merge com a tabela 1), excluindo-se os casos em que cada operador 
 * já estiver marcado como `operador` nas ocorrências
 * 
*/
/* Tabela 2 para MERGE - consulta na tickets_x_workers - operadores principais */
$sql = "SELECT 
            u.nome AS operator,
            count(o.numero) AS amount
        FROM 
            usuarios as u, 
            sistemas as s,
            `status` st, 
            ocorrencias as o, 
            tickets_extended te, 
            ticket_x_workers txw
        WHERE 
            -- excluir todos os casos em que o operador já estiver marcado no chamado --
            o.operador <> u.user_id AND
            --
            
            txw.user_id = u.user_id AND
            txw.ticket = te.ticket AND
            txw.main_worker = 1 AND
            o.status = st.stat_id AND 
            o.numero = te.ticket AND
            st.stat_ignored <> 1 AND 
            o.sistema = s.sis_id
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
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

    $data['all_tables_merged'] = array_merge($data['all_tables_merged'], $data['table_2']);


/* Tabela 2 para EXIBIÇÃO - consulta na tickets_x_workers - operadores principais */
$sql = "SELECT 
            u.nome AS operator,
            count(o.numero) AS amount
        FROM 
            usuarios as u, 
            sistemas as s,
            `status` st, 
            ocorrencias as o, 
            tickets_extended te, 
            ticket_x_workers txw
        WHERE 
            txw.user_id = u.user_id AND
            txw.ticket = te.ticket AND
            txw.main_worker = 1 AND
            o.status = st.stat_id AND 
            o.numero = te.ticket AND
            st.stat_ignored <> 1 AND 
            o.sistema = s.sis_id
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
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

    $records = count($data['table_2']);
    $data['table_2']['records'] = $records;
    $data['table_2']['title'] = $TRANS["TITLE_REPORT_TREATERS_FIELD_MAIN_WORKER"];
    $data['table_2']['sum'] = array_sum(array_column($data['table_2'], 'amount'));
    $data['table_2']['msg_context'] = message('info', '', $TRANS["MSG_REPORT_TREATERS_FIELD_MAIN_WORKER"],'', '', true);
    $data['table_2']['msg_no_results'] = message('info', '', $TRANS["NOT_FOUND_TREATERS_FIELD_MAIN_WORKER"], '', '', true);



/* Tabela 3 para MERGE - consulta na tickets_x_workers - operadores auxiliares*/
$sql = "SELECT 
            u.nome AS operator,
            count(o.numero) AS amount
        FROM 
            usuarios as u, 
            sistemas as s,
            `status` st, 
            ocorrencias as o, 
            tickets_extended te, 
            ticket_x_workers txw
        WHERE 
            -- excluir todos os casos em que o operador já estiver marcado no chamado --
            o.operador <> u.user_id AND
            --
            
            txw.user_id = u.user_id AND
            txw.ticket = te.ticket AND
            txw.main_worker = 0 AND
            o.status = st.stat_id AND 
            o.numero = te.ticket AND
            st.stat_ignored <> 1 AND 
            o.sistema = s.sis_id
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
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

    $data['all_tables_merged'] = array_merge($data['all_tables_merged'], $data['table_3']);



/* Tabela 3 para EXIBIÇÃO - consulta na tickets_x_workers - operadores auxiliares*/
$sql = "SELECT 
            u.nome AS operator,
            count(o.numero) AS amount
        FROM 
            usuarios as u, 
            sistemas as s,
            `status` st, 
            ocorrencias as o, 
            tickets_extended te, 
            ticket_x_workers txw
        WHERE 
            txw.user_id = u.user_id AND
            txw.ticket = te.ticket AND
            txw.main_worker = 0 AND
            o.status = st.stat_id AND 
            o.numero = te.ticket AND
            st.stat_ignored <> 1 AND 
            o.sistema = s.sis_id
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
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

    $records = count($data['table_3']);
    $data['table_3']['records'] = $records;
    $data['table_3']['title'] = TRANS('TITLE_REPORT_TREATERS_FIELD_AUX_WORKER');
    $data['table_3']['sum'] = array_sum(array_column($data['table_3'], 'amount'));
    $data['table_3']['msg_context'] = message('info', '', TRANS('MSG_REPORT_TREATERS_FIELD_AUX_WORKER'),'', '', true);
    $data['table_3']['msg_no_results'] = message('info', '', TRANS('NOT_FOUND_TREATERS_FIELD_AUX_WORKER'), '', '', true);



/* Tabela 4 para MERGE - Chamados em que o operador foi adicionado como participante no ato da conclusão do atendimento */
$sql = "SELECT 
            u.nome as operator,
            COUNT(DISTINCT tts.ticket) AS amount
        FROM
            tickets_treaters_stages tts,
            ocorrencias o,
            usuarios u,
            sistemas s,
            `status` st
        WHERE
            -- excluir todos os casos em que o operador já estiver marcado no chamado --
            o.operador <> u.user_id AND
            --
            
            tts.ticket = o.numero AND
            tts.treater_id = u.user_id AND
            o.sistema = s.sis_id AND
            o.status = st.stat_id AND
            st.stat_ignored <> 1 
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
    ";

    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['table_4'] = $res->fetchAll();
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $data['all_tables_merged'] = array_merge($data['all_tables_merged'], $data['table_4']);


/* Tabela 4 para EXIBIÇÃO - Chamados em que o operador foi adicionado como participante no ato da conclusão do atendimento */
$sql = "SELECT 
            u.nome as operator,
            COUNT(DISTINCT tts.ticket) AS amount
        FROM
            tickets_treaters_stages tts,
            ocorrencias o,
            usuarios u,
            sistemas s,
            `status` st
        WHERE
            tts.ticket = o.numero AND
            tts.treater_id = u.user_id AND
            o.sistema = s.sis_id AND
            o.status = st.stat_id AND
            st.stat_ignored <> 1 
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
    ";

    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $data['table_4'] = $res->fetchAll();
        }
    } catch (PDOException $e) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
        echo json_encode($data);
        return false;
    }

    $records = count($data['table_4']);
    $data['table_4']['records'] = $records;
    $data['table_4']['title'] = TRANS('TITLE_REPORT_TREATERS_FIELD_PARTICIPANT');
    $data['table_4']['sum'] = array_sum(array_column($data['table_4'], 'amount'));
    $data['table_4']['msg_context'] = message('info', '', TRANS('MSG_REPORT_TREATERS_FIELD_PARTICIPANT'),'', '', true);
    $data['table_4']['msg_no_results'] = message('info', '', TRANS('NOT_FOUND_TREATERS_FIELD_PARTICIPANT'), '', '', true);



/* Tabela 5 para MERGE - Quantidade de chamados que estiveram ao menos uma vez em fila direta do operador */
$sql = "SELECT
            u.nome AS operator,
            COUNT(DISTINCT ts.ticket) AS amount
        FROM 
            tickets_stages ts,
            status st,
            usuarios u,
            ocorrencias o,
            sistemas a,
            clients cl
        WHERE 
            -- excluir todos os casos em que o operador já estiver marcado no chamado --
            o.operador <> u.user_id AND
            --
            
            ts.status_id = st.stat_id AND
            ts.treater_id = u.user_id AND
            st.stat_painel = 1 AND
            u.nivel < 3 AND
            o.sistema = a.sis_id AND
            o.client = cl.id AND
            ts.ticket = o.numero AND
            o.data_fechamento IS NOT NULL
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
        ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    if ($res->rowCount()) {
        $data['table_5'] = $res->fetchAll();
    }
} catch (PDOException $e) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
    echo json_encode($data);
    return false;
}

$data['all_tables_merged'] = array_merge($data['all_tables_merged'], $data['table_5']);



/* Tabela 5 para EXIBIÇÃO - Quantidade de chamados que estiveram ao menos uma vez em fila direta do operador */
$sql = "SELECT
            u.nome AS operator,
            COUNT(DISTINCT ts.ticket) AS amount
        FROM 
            tickets_stages ts,
            status st,
            usuarios u,
            ocorrencias o,
            sistemas a,
            clients cl
        WHERE 
            ts.status_id = st.stat_id AND
            ts.treater_id = u.user_id AND
            st.stat_painel = 1 AND
            u.nivel < 3 AND
            o.sistema = a.sis_id AND
            o.client = cl.id AND
            ts.ticket = o.numero AND
            o.data_fechamento IS NOT NULL
            {$termsTotal}
            {$clausulesByStates[$data['state']]}
        GROUP BY
            u.nome
        ORDER BY
            amount DESC
        ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    if ($res->rowCount()) {
        $data['table_5'] = $res->fetchAll();
    }
} catch (PDOException $e) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', $e->getMessage(),'');
    echo json_encode($data);
    return false;
}

$records = count($data['table_5']);
$data['table_5']['records'] = $records;
$data['table_5']['title'] = TRANS('TITLE_REPORT_TREATERS_DIRECT_QUEUE');
$data['table_5']['sum'] = array_sum(array_column($data['table_5'], 'amount'));
$data['table_5']['msg_context'] = message('info', '', TRANS('MSG_REPORT_TREATERS_DIRECT_QUEUE'),'', '', true);
$data['table_5']['msg_no_results'] = message('info', '', TRANS('NOT_FOUND_TREATERS_DIRECT_QUEUE'), '', '', true);



$data['table_consolidated'] = array_group_sum($data['all_tables_merged'], 'operator', 'amount');
$data['table_consolidated'] = arraySortByColumn($data['table_consolidated'], 'amount', SORT_DESC, SORT_NUMERIC);

$records = count($data['table_consolidated']);
$data['table_consolidated']['records'] = $records;
$data['table_consolidated']['title'] = TRANS('TITLE_REPORT_TREATERS_CONSOLIDATED');
$data['table_consolidated']['sum'] = array_sum(array_column($data['table_consolidated'], 'amount'));
$data['table_consolidated']['msg_context'] = message('info', '', TRANS('MSG_REPORT_TREATERS_CONSOLIDATED'),'', '', true);
$data['table_consolidated']['msg_no_results'] = message('info', '', TRANS('NOT_FOUND_TREATERS_CONSOLIDATED'), '', '', true);


if (empty($data['all_tables_merged'])) {
        $data['success'] = false;
        $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
        echo json_encode($data);
        return false;
}

// echo json_encode($data);
// return true;


/* Gráfico 1 */
$data[$chart_1_prefix] = $data['table_1'];
$data[$chart_1_prefix . $chart_title_sufix] = $data[$chart_1_prefix]['title'];

/* Gráfico 2 */
$data[$chart_2_prefix] = $data['table_2'];
$data[$chart_2_prefix . $chart_title_sufix] = $data[$chart_2_prefix]['title'];

/* Gráfico 3 */
$data[$chart_3_prefix] = $data['table_3'];
$data[$chart_3_prefix . $chart_title_sufix] = $data[$chart_3_prefix]['title'];

/* Gráfico 4 */
$data[$chart_4_prefix] = $data['table_4'];
$data[$chart_4_prefix . $chart_title_sufix] = $data[$chart_4_prefix]['title'];

/* Gráfico 5 */
$data[$chart_5_prefix] = $data['table_5'];
$data[$chart_5_prefix . $chart_title_sufix] = $data[$chart_5_prefix]['title'];

/* Gráfico Consolidado */
$data[$chart_consolidated_prefix] = $data['table_consolidated'];
$data[$chart_consolidated_prefix . $chart_title_sufix] = $data[$chart_consolidated_prefix]['title'];


echo json_encode($data);

return true;
