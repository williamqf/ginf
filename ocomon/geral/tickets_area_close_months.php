<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}
$conn = ConnectPDO::getInstance();

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
        $qry_filter_areas = " AND " . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']})";
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

// Meses anteriores
$dates = getMonthRangesUpToNOw('P3M');
$datesBegin = $dates['ini'];
$datesEnd = $dates['end'];
$months = $dates['mLabel'];

/* PRIMEIRO BUSCO AS AREAS ENVOLVIDAS NA CONSULTA */
// $sql = "SELECT sis_id, sistema FROM sistemas WHERE sis_id IN ({$u_areas}) ORDER by sistema";

if ($_SESSION['requester_areas']) {
    
    $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_id IN ({$_SESSION['s_uareas']})";
    if ($isAdmin) {
        $sql = "SELECT s.sis_id, s.sistema FROM sistemas s ";
    }
    
} else {
    $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_atende = 1 AND s.sis_id IN ({$_SESSION['s_uareas']})";
    if ($isAdmin) {
        $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_atende = 1 ";
    }
}

$result = $conn->query($sql);
foreach ($result->fetchAll() as $row) {
    $i = 0;
    foreach ($datesBegin as $dateStart) {
        /* Em cada intervalo de tempo busco os totais de cada área */

        $sqlEach = "SELECT 
                        count(*) AS total, s.sis_id, s.sistema
                    FROM 
                        ocorrencias o, sistemas s, usuarios ua, `status` st
                    WHERE 
                        o.aberto_por = ua.user_id AND  
                        o.status = st.stat_id AND
                        st.stat_ignored <> 1 AND
                        {$aliasAreasFilter}  = s.sis_id AND 
                        {$row['sis_id']}  = s.sis_id AND 
                        o.data_fechamento >= '{$dateStart}' AND 
                        o.data_fechamento <= '{$datesEnd[$i]}' 
                        {$qry_filter_clients}
                        {$qry_filter_areas}
                    GROUP BY s.sis_id, s.sistema
                    ";
        
        $resultEach = $conn->query($sqlEach);
        if ($resultEach->rowCount()) {
            foreach ($resultEach->fetchAll() as $rowEach) {
                
                // if ($rowEach['sistema']){
                if ($rowEach['total']){
                    $areas[$rowEach['sis_id']] = $rowEach['sistema'];
                    $meses[] = $months[$i];
                    // $areasDados[$rowEach['sistema']][] = intval($rowEach['total']);
                    $areasDados[$rowEach['sis_id']][] = intval($rowEach['total']);
                } else {
                    $areas[$row['sis_id']] = $row['sistema'];
                    // $areasDados[$row['sistema']][] = 0;
                    $areasDados[$row['sis_id']][] = 0;
                    $meses[] = $months[$i];
                }
            }
        } else {
            $areas[$row['sis_id']] = $row['sistema'];
            $meses[] = $months[$i];
            // $areasDados[$row['sistema']][] = 0;
            $areasDados[$row['sis_id']][] = 0;
        }
        $i++;
    }
}



/* Ajusto os arrays de labels para não ter repetidos */
$meses = array_unique($meses);
$areas = array_unique($areas);


/* Trecho para remover as areas que não tiveram registros no período */
foreach ($areasDados as $key => $value) {
    if (array_sum($value) == 0) {
        // unset($areas[array_search($key, $areas)]);
        unset($areas[$key]);
        unset($areasDados[$key]);
    }
}



/* Separo o conteúdo para organizar o JSON */
$data['areas'] = $areas;
$data['months'] = $meses;
$data['totais'] = $areasDados;






// TICKETS_CLOSED_BY_REQUESTER_AREA_LAST_MONTHS
// $data['chart_title'] = TRANS('TICKETS_CLOSED_BY_AREA_LAST_MONTHS', '', 1);
$data['chart_title'] = ($_SESSION['requester_areas'] ? TRANS('TICKETS_CLOSED_BY_REQUESTER_AREA_LAST_MONTHS', '', 1) : TRANS('TICKETS_CLOSED_BY_AREA_LAST_MONTHS', '', 1));

// var_dump($areas, $totais, $meses, $areasDados, $data);

echo json_encode($data);

?>