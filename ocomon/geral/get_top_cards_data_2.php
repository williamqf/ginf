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



/* Total do custo dos chamados menos os chamados não autorizados, abertos no dia corrente */
$totalCostTodayNotRejected = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
                    o.aberto_por = ua.user_id AND 
                    o.status = st.stat_id AND 
                    st.stat_ignored <> 1 AND 
                    o.numero = tcf.ticket AND
                    tcf.cfield_id = {$costField} AND
                    o.data_abertura >= '{$hoje}' AND
                    (o.authorization_status <> 3 OR o.authorization_status IS NULL)
                    {$qry_filter_clients}
                    {$qry_filter_areas}
                ";
try {
    $res = $conn->query($sqlTotalCost);
    $totalCostTodayNotRejected = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}


/* Total do custo dos chamados autorizados, abertos no mês corrente */
$totalCostCurrMonthAuthorized = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
                    o.aberto_por = ua.user_id AND 
                    o.status = st.stat_id AND 
                    st.stat_ignored <> 1 AND 
                    o.numero = tcf.ticket AND
                    tcf.cfield_id = {$costField} AND
                    o.data_abertura >= '{$mes}' AND
                    o.authorization_status = 2
                    {$qry_filter_clients}
                    {$qry_filter_areas}
                ";
try {
    $res = $conn->query($sqlTotalCost);
    $totalCostCurrMonthAuthorized = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}


/* Total do custo dos chamados aguardando autorização, abertos no mês corrente */
$totalCostCurrMonthWaitingAuth = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
                    o.aberto_por = ua.user_id AND 
                    o.status = st.stat_id AND 
                    st.stat_ignored <> 1 AND 
                    o.numero = tcf.ticket AND
                    tcf.cfield_id = {$costField} AND
                    o.data_abertura >= '{$mes}' AND
                    o.authorization_status = 1
                    {$qry_filter_clients}
                    {$qry_filter_areas}
                ";
try {
    $res = $conn->query($sqlTotalCost);
    $totalCostCurrMonthWaitingAuth = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}



/* Total do custo dos chamados sem definição de autorização, abertos no mês corrente */
$totalCostCurrMonthNullAuth = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
                    o.aberto_por = ua.user_id AND 
                    o.status = st.stat_id AND 
                    st.stat_ignored <> 1 AND 
                    o.numero = tcf.ticket AND
                    tcf.cfield_id = {$costField} AND
                    o.data_abertura >= '{$mes}' AND
                    o.authorization_status IS NULL
                    {$qry_filter_clients}
                    {$qry_filter_areas}
                ";
try {
    $res = $conn->query($sqlTotalCost);
    $totalCostCurrMonthNullAuth = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}




/* Total do custo dos chamados recusados, abertos no mês corrente */
$totalCostCurrMonthRefused = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
                    o.aberto_por = ua.user_id AND 
                    o.status = st.stat_id AND 
                    st.stat_ignored <> 1 AND 
                    o.numero = tcf.ticket AND
                    tcf.cfield_id = {$costField} AND
                    o.data_abertura >= '{$mes}' AND
                    o.authorization_status = 3
                    {$qry_filter_clients}
                    {$qry_filter_areas}
                ";
try {
    $res = $conn->query($sqlTotalCost);
    $totalCostCurrMonthRefused = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}





/* Total do custo dos chamados menos os chamados não autorizados, abertos no mês corrente */
$totalCostCurrMonthNotRejected = 0;
$sqlTotalCost = "SELECT
                    SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total
                FROM
                    ocorrencias o,
                    `status` st, usuarios ua, 
                    tickets_x_cfields tcf
                WHERE 
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
    $totalCostCurrMonthNotRejected = $res->fetch()['total'];
}
catch (Exception $e) {
    echo 'Erro: ', $e->getMessage(), "<br/>";
    echo $sqlTotalCost;
}




$data = array();
/* Dados que serão retornado para a view */

$formatTotalCostTodayNotRejected = ($totalCostTodayNotRejected == 0) ? 0 : priceScreen($totalCostTodayNotRejected);
$data['totalCostTodayNotRejected'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostTodayNotRejected;
$data['totalCostTodayNotRejectedFilter']["totalCostNotRejected"] = 1;
// $data['totalCostTodayNotRejectedFilter']["data_abertura_from"] = date('Y-m-d');
$data['totalCostTodayNotRejectedFilter']["data_abertura_from"] = date('d/m/Y');
$data['totalCostTodayNotRejectedFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostTodayNotRejectedFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostTodayNotRejectedFilter']["areas_filter"] = $filtered_areas;
$data['totalCostTodayNotRejectedFilter']["clients_filter"] = $filtered_clients;
$data['totalCostTodayNotRejectedFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];


$formatTotalCostMonthAuthorized = ($totalCostCurrMonthAuthorized == 0) ? 0 : priceScreen($totalCostCurrMonthAuthorized);
$data['totalCostMonthAuthorized'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostMonthAuthorized;
$data['totalCostMonthAuthorizedFilter']["authorization_status"] = [2];
// $data['totalCostMonthAuthorizedFilter']["data_abertura_from"] = date('Y-m-01');
$data['totalCostMonthAuthorizedFilter']["data_abertura_from"] = date('01/m/Y');
$data['totalCostMonthAuthorizedFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostMonthAuthorizedFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostMonthAuthorizedFilter']["areas_filter"] = $filtered_areas;
$data['totalCostMonthAuthorizedFilter']["clients_filter"] = $filtered_clients;
$data['totalCostMonthAuthorizedFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];



$formatTotalCostMonthWaitingAuth = ($totalCostCurrMonthWaitingAuth == 0) ? 0 : priceScreen($totalCostCurrMonthWaitingAuth);
$data['totalCostMonthWaitingAuth'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostMonthWaitingAuth;
$data['totalCostMonthWaitingAuthFilter']["authorization_status"] = [1];
$data['totalCostMonthWaitingAuthFilter']["data_abertura_from"] = date('01/m/Y');
$data['totalCostMonthWaitingAuthFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostMonthWaitingAuthFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostMonthWaitingAuthFilter']["areas_filter"] = $filtered_areas;
$data['totalCostMonthWaitingAuthFilter']["clients_filter"] = $filtered_clients;
$data['totalCostMonthWaitingAuthFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];




$formatTotalCostMonthNullAuth = ($totalCostCurrMonthNullAuth == 0) ? 0 : priceScreen($totalCostCurrMonthNullAuth);
$data['totalCostMonthNullAuth'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostMonthNullAuth;
$data['totalCostMonthNullAuthFilter']["no_authorization_status"] = 1;
$data['totalCostMonthNullAuthFilter']["data_abertura_from"] = date('01/m/Y');
$data['totalCostMonthNullAuthFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostMonthNullAuthFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostMonthNullAuthFilter']["areas_filter"] = $filtered_areas;
$data['totalCostMonthNullAuthFilter']["clients_filter"] = $filtered_clients;
$data['totalCostMonthNullAuthFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];



$formatTotalCostMonthRejected = ($totalCostCurrMonthRefused == 0) ? 0 : priceScreen($totalCostCurrMonthRefused);
$data['totalCostMonthRejected'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostMonthRejected;
$data['totalCostMonthRejectedFilter']["totalCostRejected"] = 1;
$data['totalCostMonthRejectedFilter']["data_abertura_from"] = date('01/m/Y');
$data['totalCostMonthRejectedFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostMonthRejectedFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostMonthRejectedFilter']["areas_filter"] = $filtered_areas;
$data['totalCostMonthRejectedFilter']["clients_filter"] = $filtered_clients;
$data['totalCostMonthRejectedFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];




$formatTotalCostMonthNotRejected = ($totalCostCurrMonthNotRejected == 0) ? 0 : priceScreen($totalCostCurrMonthNotRejected);
$data['totalCostMonthNotRejected'] = TRANS('CURRENCY') . "&nbsp;" . $formatTotalCostMonthNotRejected;
// $data['totalCostMonthNotRejectedFilter']["no_authorization_status"] = 1;
$data['totalCostMonthNotRejectedFilter']["totalCostNotRejected"] = 1;
$data['totalCostMonthNotRejectedFilter']["data_abertura_from"] = date('01/m/Y');
$data['totalCostMonthNotRejectedFilter']["app_from"] = $dataPosted['app_from'];
$data['totalCostMonthNotRejectedFilter']["is_requester_area"] = $dataPosted['requester_areas'];
$data['totalCostMonthNotRejectedFilter']["areas_filter"] = $filtered_areas;
$data['totalCostMonthNotRejectedFilter']["clients_filter"] = $filtered_clients;
$data['totalCostMonthNotRejectedFilter']["render_custom_fields"] = $dataPosted['render_custom_fields'];






echo json_encode($data);
