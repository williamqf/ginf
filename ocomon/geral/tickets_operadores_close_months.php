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

$u_areas = (!empty($filtered_areas) ? $filtered_areas : $_SESSION['s_uareas']);


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
        // $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']}) OR " . $aliasAreasFilter . " = '-1')";
        $qry_filter_areas = " AND " . $aliasAreasFilter . " IN ({$_SESSION['s_uareas']})";
    }
} else {
    $qry_filter_areas = " AND (" . $aliasAreasFilter . " IN ({$filtered_areas}))";
}


$dates = [];
$datesBegin = [];
$datesEnd = [];
$months = [];
$operadores = [];
$data = [];

// Meses anteriores
$dates = getMonthRangesUpToNOw('P3M');
$datesBegin = $dates['ini'];
$datesEnd = $dates['end'];
$months = $dates['mLabel'];

/* PRIMEIRO BUSCO OS OPERADORES ENVOLVIDAS NA CONSULTA */


if ($_SESSION['requester_areas']) {
    
    $sql = "SELECT user_id, nome FROM usuarios WHERE nivel < 4 AND AREA IN ({$_SESSION['s_uareas']}) ORDER BY nome";
    if ($isAdmin) {
        $sql = "SELECT user_id, nome FROM usuarios WHERE nivel < 4 ORDER BY nome";
    }
    
} else {
    $sql = "SELECT user_id, nome FROM usuarios WHERE nivel < 3 AND AREA IN ({$_SESSION['s_uareas']}) ORDER BY nome";
    if ($isAdmin) {
        $sql = "SELECT user_id, nome FROM usuarios WHERE nivel < 3 ORDER BY nome";
    }
}

$result = $conn->query($sql);
foreach ($result->fetchAll() as $row) {
    $i = 0;
    foreach ($datesBegin as $dateStart) {
        /* Em cada intervalo de tempo busco os totais de cada área */

        if ($_SESSION['requester_areas']) {
            $sqlEach = "SELECT 
                            count(*) AS total, 
                            ua.user_id,
                            ua.nome 
                        FROM 
                            ocorrencias o, usuarios u, usuarios ua, sistemas s 
                        WHERE 
                            u.user_id = o.operador AND 
                            o.aberto_por = ua.user_id AND
                            " . $aliasAreasFilter . "  = s.sis_id AND 
                            ua.user_id = " . $row['user_id'] . " AND 
                            o.data_fechamento >= '" .  $dateStart  . "' AND 
                            o.data_fechamento <= '" .  $datesEnd[$i]  . "' AND 
                            o.data_fechamento IS NOT NULL 
                            {$qry_filter_clients}
                            {$qry_filter_areas}
                        GROUP BY ua.user_id, ua.nome 
                        ";
        } else {
            $sqlEach = "SELECT 
                            count(*) AS total, 
                            u.user_id,
                            u.nome 
                        FROM 
                            ocorrencias o, usuarios u, usuarios ua, sistemas s 
                        WHERE 
                            u.user_id = o.operador AND 
                            o.aberto_por = ua.user_id AND
                            " . $aliasAreasFilter . "  = s.sis_id AND 
                            u.user_id = " . $row['user_id'] . " AND 
                            o.data_fechamento >= '" .  $dateStart  . "' AND 
                            o.data_fechamento <= '" .  $datesEnd[$i]  . "' AND 
                            o.data_fechamento IS NOT NULL 
                            {$qry_filter_clients}
                            {$qry_filter_areas} 
                        GROUP BY u.user_id, u.nome 
                        ";
        }



        $resultEach = $conn->query($sqlEach);

        if ($resultEach->rowCount()) {
            foreach ($resultEach->fetchAll() as $rowEach) {
                
                if ($rowEach['total']){
                    $operadores[$rowEach['user_id']] = $rowEach['nome'];
                    $meses[] = $months[$i];
                    // $operadorDados[$rowEach['nome']][] = intval($rowEach['total']);
                    $operadorDados[$rowEach['user_id']][] = intval($rowEach['total']);
                } else {
                    $operadores[$row['user_id']] = $row['nome'];
                    // $operadorDados[$row['nome']][] = 0;
                    $operadorDados[$row['user_id']][] = 0;
                    $meses[] = $months[$i];
                }
            }
        } else {
            $operadores[$row['user_id']] = $row['nome'];
            // $operadorDados[$row['nome']][] = 0;
            $operadorDados[$row['user_id']][] = 0;
            $meses[] = $months[$i];
        }
        $i++;
    }
}

/* Ajusto os arrays de labels para não ter repetidos */
$meses = array_unique($meses);


/* Trecho para remover os operadores que não tiveram registros no período */
foreach ($operadorDados as $key => $value) {
    if (array_sum($value) == 0) {
        // unset($operadores[array_search($key, $operadores)]);
        unset($operadores[$key]);
        unset($operadorDados[$key]);
    }
}


/* Separo o conteúdo para organizar o JSON */
$data['operadores'] = $operadores;
$data['months'] = $meses;
$data['totais'] = $operadorDados;
$data['chart_title'] = ($_SESSION['requester_areas'] ? TRANS('TICKETS_BY_REQUESTER_LAST_MONTHS', '', 1) : TRANS('TICKETS_BY_TECHNITIAN_LAST_MONTHS', '', 1));


echo json_encode($data);

?>