<?php session_start();
/*                        Copyright 2023 Flávio Ribeiro

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
require_once __DIR__ . "/" . "../../includes/functions/getWorktimeProfile.php";
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);

set_time_limit(300);

/* if (!isset($_POST['numero'])) {
    exit();
} */

$isAdmin = $_SESSION['s_nivel'] == 1;


$dataPosted = [];
$post = [];


if (isset($_POST)){
    $post = $_POST;
}


$dataPosted['client'] = (isset($post['client']) && !empty($post['client']) ? $post['client'] : "");
$dataPosted['area'] = (isset($post['area']) && !empty($post['area']) ? $post['area'] : "");
/* Se for filtro pelas áreas de destino do chamado (padrão) */
$dataPosted['requester_areas'] = (isset($post['requester_areas']) ? ($post['requester_areas'] == "yes" ? 1 : 0) : 0);
$dataPosted['render_custom_fields'] = (isset($post['render_custom_fields']) ? ($post['render_custom_fields'] == "yes" ? 1 : 0) : 0);
$dataPosted['app_from'] = (isset($post['app_from']) ? (noHtml($post['app_from'])) : "");




/* Filtro de seleção de clientes - formulário no painel de controle */
$filtered_clients = "";
/* Controle para limitar os resultados com base nos clientes selecionados */
$qry_filter_clients = "";
if (!empty($dataPosted['client'])) {
    $filtered_clients = implode(',', $dataPosted['client']);
}
if (!empty($filtered_clients)) {
    $qry_filter_clients = " AND o.client IN ({$filtered_clients}) ";
}



/* Filtro de seleção de áreas - formulário no painel de controle */
$filtered_areas = "";
/* Controle para limitar os resultados das consultas às áreas do usuário logado quando a opção estiver habilitada */
$qry_filter_areas = "";
if (!empty($dataPosted['area'])) {
    $filtered_areas = implode(',', $dataPosted['area']);
}

$aliasAreasFilter = ($dataPosted['requester_areas'] ? "ua.AREA" : "o.sistema");


if (empty($filtered_areas)) {
    if ($isAdmin) {
        $qry_filter_areas = "";
    } else {
        $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']}) OR " . $aliasAreasFilter . " = '-1')";
    }
} else {
    $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$filtered_areas}))";
}


$hoje = date('Y-m-d 00:00:00');
$mes = date('Y-m-01 00:00:00');

$config = getConfig($conn);
$costField = $config['tickets_cost_field'];
$idPrefix = 'issue_';

$data = array();


$issuesToCheck = getIssuesToCostcards($conn);
$idx = 0;
foreach ($issuesToCheck as $issue) {
    /* Total do custo dos chamados menos os chamados não autorizados, abertos no mês corrente */
    // $totalCostByIssue = 0;
    $sqlTotalCost = "SELECT
                        SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                    FROM
                        ocorrencias o,
                        `status` st, usuarios ua, 
                        problemas p,
                        tickets_x_cfields tcf
                    WHERE 
                        o.problema = p.prob_id AND
                        p.prob_id = {$issue['prob_id']} AND
                        o.aberto_por = ua.user_id AND 
                        o.status = st.stat_id AND 
                        st.stat_ignored <> 1 AND 
                        o.numero = tcf.ticket AND
                        tcf.cfield_id = {$costField} AND
                        o.data_abertura >= '{$mes}' AND
                        (o.authorization_status <> 3 OR o.authorization_status IS NULL)
                        {$qry_filter_clients}
                        {$qry_filter_areas}
                    ";
    try {
        $res = $conn->query($sqlTotalCost);

        $total = 'R$ ' . priceScreen($res->fetch()['total']) ?? 0;
        $infoCostByIssue[$idx]['id'] = $idPrefix . $issue['prob_id'];
        $infoCostByIssue[$idx]['total'] = $total;
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']['data_abertura_from'] = date('01/m/Y');
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']['app_from'] = $dataPosted['app_from'];
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']['is_requester_area'] = $dataPosted['requester_areas'];
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']["areas_filter"] = $filtered_areas;
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']["clients_filter"] = $filtered_clients;
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']["problema"] = [$issue['prob_id']];
        $infoCostByIssue[$idPrefix . $issue['prob_id']]['totalCostByIssueFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];
    }
    catch (Exception $e) {
        echo 'Erro: ', $e->getMessage(), "<br/>";
        echo $sqlTotalCost;
    }
    $idx++;
}

$data = $infoCostByIssue;


echo json_encode($data);
