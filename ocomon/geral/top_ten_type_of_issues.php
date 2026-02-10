<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}

$conn = ConnectPDO::getInstance();

$hoje = date("Y-m-d H:i:s");
$isAdmin = $_SESSION['s_nivel'] == 1;
$aliasAreasFilter = ($_SESSION['requester_areas'] ? "ua.AREA" : "o.sistema");
$filtered_areas = $_SESSION['dash_filter_areas'];
$filtered_clients = $_SESSION['dash_filter_clients'];

$qry_filter_areas = "";


/* Controle para limitar os resultados com base nos clientes selecionados */
$qry_filter_clients = "";
if (!empty($filtered_clients)) {
    $qry_filter_clients = " AND o.client IN ({$filtered_clients}) ";
}


/* Filtro de seleção a partir das áreas */
if (empty($filtered_areas)) {
    if ($isAdmin) {
        $qry_filter_areas = "";
    } else {
        $qry_filter_areas = " AND " . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']}, -1)";
    }
} else {
    $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$filtered_areas}))";
}


$sql = "SELECT p.problema, count(*) as total 
        FROM 
            ocorrencias o, problemas p, usuarios ua, `status` s
        WHERE 
            p.prob_id = o.problema AND 
            o.aberto_por = ua.user_id AND
            o.data_abertura >= DATE_SUB('{$hoje}', INTERVAL 1 YEAR) AND
            o.status = s.stat_id AND
            s.stat_ignored <> 1 
            {$qry_filter_clients}
            {$qry_filter_areas}
            ";
$sql.= " GROUP BY p.problema ORDER BY total DESC LIMIT 10";
            

$sql = $conn->query($sql);

$data = array();


if ($sql->rowCount()) {
    foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $data[] = $row;
    }
} else {
    $data[] = array(
        "problema" => TRANS('NO_RECORDS_FOUND'),
        "total" => 0
    );
}


$data[]['chart_title'] = TRANS('TOP_TEN_TYPE_OF_ISSUES', '', 1);
// IMPORTANT, output to json
echo json_encode($data);

?>
