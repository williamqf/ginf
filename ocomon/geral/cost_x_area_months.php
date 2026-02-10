<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}
$conn = ConnectPDO::getInstance();


$config = getConfig($conn);
$costField = $config['tickets_cost_field'];

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
        $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']}) OR " . $aliasAreasFilter . " = '-1')";
    }
} else {
    $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$filtered_areas}))";
}



$dates = [];
$datesBegin = [];
$datesEnd = [];
$months = [];
$areas = [];
$data = [];

// Peíodos anteriores
$dates = getMonthRangesUpToNow2('P11M', 'P1M');
$datesBegin = $dates['begin'];
$datesEnd = $dates['end'];
$months = $dates['periodLabel'];

/* PRIMEIRO BUSCO AS AREAS ENVOLVIDAS NA CONSULTA */
$areasTerms = "";
if ($_SESSION['requester_areas']) {
    if (!$isAdmin) {
        $areasTerms = " AND s.sis_id IN ({$_SESSION['s_uareas']})";
    }
    
} else {
    if (!$isAdmin) {
        $areasTerms = " AND s.sis_atende = 1 AND s.sis_id IN ({$_SESSION['s_uareas']})";
    }
}

$sql = "SELECT
            s.sistema, s.sis_id
        FROM
            ocorrencias o,
            sistemas s,
            tickets_x_cfields tcf
        WHERE 
            o.sistema = s.sis_id AND
            o.numero = tcf.ticket AND 
            (o.authorization_status <> 3 OR o.authorization_status IS NULL) AND
            tcf.cfield_id = {$costField} AND
            tcf.cfield_value > 0 AND
            o.data_abertura >= '{$datesBegin[0]}' AND
            o.data_abertura <= '" . date('Y-m-d 23:59:59') . "' 
            {$areasTerms}
        GROUP BY 
            s.sistema, s.sis_id
        ORDER BY
            s.sistema;
";





$result = $conn->query($sql);
foreach ($result->fetchAll() as $row) {
    $i = 0;
    foreach ($datesBegin as $dateStart) {
        /* Em cada intervalo de tempo busco os totais de cada área */

        $sqlEach = "SELECT
            SUM(CAST(REPLACE(REPLACE(tcf.cfield_value, '.', ''), ',', '.') AS DECIMAL(10,2))) as total,
            s.sistema
        FROM
            ocorrencias o,
            sistemas s,
            `status` st, usuarios ua, 
            tickets_x_cfields tcf
        WHERE 
            s.sis_id = {$aliasAreasFilter} AND 
            {$aliasAreasFilter} = {$row['sis_id']} AND

            o.aberto_por = ua.user_id AND 
            o.status = st.stat_id AND 
            st.stat_ignored <> 1 AND 
            o.numero = tcf.ticket AND
            tcf.cfield_id = {$costField} AND

            o.data_abertura >= '{$dateStart}' AND
            o.data_abertura <= '{$datesEnd[$i]}' AND

            (o.authorization_status <> 3 OR o.authorization_status IS NULL)
            {$qry_filter_clients}
            {$qry_filter_areas}

        GROUP BY s.sistema
        ";
        
        $resultEach = $conn->query($sqlEach);
        $countResults = $resultEach->rowCount();

        if ($countResults) {
            foreach ($resultEach->fetchAll() as $rowEach) {

                if ($rowEach['sistema']) {
                    $areas[] = $rowEach['sistema'];
                    // $totais[] = (int)$rowEach['total'];
                    $meses[] = $months[$i];
                    $areasDados[$rowEach['sistema']][] = $rowEach['total'];
                    // $areasDados[$rowEach['sistema']][] = priceScreen($rowEach['total']);
                } else {
                    $areas[] = $row['sistema'];
                    $meses[] = $months[$i];
                    $areasDados[$row['sistema']][] = 0;
                }
            }
        } else {
            $areas[] = $row['sistema'];
            $meses[] = $months[$i];
            $areasDados[$row['sistema']][] = 0;
        }

        $i++;
    }
}



/* Ajusto os arrays de labels para não ter repetidos */
$meses = array_unique($meses);
$areas = array_unique($areas);

/* Separo o conteúdo para organizar o JSON */
$data['areas'] = $areas;
$data['months'] = $meses;
$data['totais'] = $areasDados;
$data['chart_title'] = ($_SESSION['requester_areas'] ? TRANS('COST_BY_REQUESTER_AREA_LAST_MONTHS', '', 1) : TRANS('COST_BY_AREA_LAST_MONTHS', '', 1));

// var_dump($areas, $totais, $meses, $areasDados, $data);

echo json_encode($data);

?>
