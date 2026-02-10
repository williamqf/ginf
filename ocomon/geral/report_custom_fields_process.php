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
$criteria['units'] = TRANS('COL_UNIT') . ':&nbsp;' . TRANS('ALL');
$criteria['area'] = TRANS('AREA') . ':&nbsp;' . TRANS('ALL');
$criteria['custom_field'] = TRANS('CUSTOM_FIELD') . ':&nbsp;' . TRANS('ALL');
$criteria['state'] = TRANS('CONTEXT') . ':&nbsp;' . TRANS('STATE_OPEN_CLOSE_IN_SEARCH_RANGE');

$post = $_POST;

$hora_inicio = ' 00:00:00';
$hora_fim = ' 23:59:59';

$data['client'] = (isset($post['client']) ? (int)$post['client'] : "");
$data['area'] = (isset($post['area']) ? (int)$post['area'] : "");

$data['units'] = (isset($post['unit']) && !empty(array_filter($post['unit'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['unit']) : []);

$data['custom_field'] = (isset($post['custom_field']) ? (int)$post['custom_field'] : "");

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
}

$criteria['area'] = TRANS('AREA') . ':&nbsp;<b>' . $string_area_names . '</b>';
// $onlyView = ($isRequester && !in_array($row['area_cod'], $uareas));





// if (empty($data['custom_field'])) {
//     $data['success'] = false;
//     $data['message'] = "Confira a informação referente ao campo personalizado!";
//     echo json_encode($data);
//     return false;
// }









$client_filter = [];
$units_filter = [];


// $termsTotal = "";
$chart_title_sufix = '_chart_title';
$chart_1_prefix = 'chart_01';
$chart_2_prefix = 'chart_02';
$chart_3_prefix = 'chart_03';
$chart_4_prefix = 'chart_04';
$chart_5_prefix = 'chart_05';





/* Unidades com base no filtro de clientes */
if (!empty($data['client'])) {
    $terms .= " AND o.client = {$data['client']}";
    $termsTotal .= $terms;

    $client_filter = getClients($conn, $data['client']);
    
    $criteria['client'] = TRANS('CLIENT') . ':&nbsp;<b>' . $client_filter['nickname'] . '</b>';
    
    if (empty($data['units'])) {

        $units_filter = getUnits($conn, null, null, $data['client']);
        $units_filter = (!empty($units_filter) ? $units_filter : [['inst_cod' => 0]]);

    } else {
        foreach ($data['units'] as $unit) {
            $units_filter[] = getUnits($conn, null, $unit);
        }

        $criteria['units'] = TRANS('COL_UNIT') . ':&nbsp;<b>' . implode(', ', array_column($units_filter, 'inst_nome')) . '</b>';
        $units_text_ids = implode(', ', array_column($units_filter, 'inst_cod'));

        
        $terms .= " AND o.instituicao IN ({$units_text_ids})";
        $termsTotal .= $terms;
    }
    

} elseif (!empty($data['units'])) {

    $criteria['client'] = "";
    foreach ($data['units'] as $unit) {
        $units_filter[] = getUnits($conn, null, $unit);
    }

    $unitsString = implode(',', $data['units']);
    $terms .= " AND o.instituicao IN ({$unitsString})";
    $termsTotal .= $terms;
}


/* Campo customizado */
if (!empty($data['custom_field'])) {

    $data['generate_chart'] = 1;

    $terms .= " AND c.id = {$data['custom_field']} ";

    $custom_field_info = getCustomFields($conn, $data['custom_field']);

    $criteria['custom_field'] = TRANS('FIELD_TO_GROUP') . ':&nbsp;<b>' . $custom_field_info['field_label'] . '</b>';
}


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


/* Retorna o total de registro com base nos campos de filtro */
$sqlTotalRecords = "SELECT 
count(*) as total
FROM 
    sistemas ar,
    sistemas uar,
    usuarios ua,
    `status` st,
    ocorrencias o 
        LEFT JOIN clients cl ON cl.id = o.client
        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao 
WHERE 
    o.sistema = ar.sis_id AND 
    o.aberto_por = ua.user_id AND
    uar.sis_id = ua.AREA AND
    o.status = st.stat_id AND 
    st.stat_ignored <> 1
    {$termsTotal}
    {$clausulesByStates[$data['state']]}
";

try {
    $res = $conn->prepare($sqlTotalRecords);
    $res->execute();
    $data['totalRecords'] = $res->fetchAll();

    $data['totalRecords'] = (!empty($data['totalRecords']) ? $data['totalRecords'][0]['total'] : 0);

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sqlTotalRecords'] = $sqlTotalRecords;
}



if ($data['totalRecords'] == 0) {
    $data['success'] = false;
    $data['message'] = message('warning', 'Ooops!', TRANS('NO_RECORDS_FOUND') . $exception,'');
    echo json_encode($data);
    return false;
}



/* Traz os valores dos campos customizados que são obtidos diretamente no campo cfield_value */
$sqlTablePart1 = "SELECT 
count(*) as total,
c.field_label, 
COALESCE (t.cfield_value, 'N/A') AS 
    'field_value'
FROM 
    custom_fields c, 
    tickets_x_cfields t,
    sistemas ar,
    sistemas uar,
    usuarios ua,
    `status` st,
    ocorrencias o 
        LEFT JOIN clients cl ON cl.id = o.client
        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao 
WHERE 
    o.sistema = ar.sis_id AND 
    o.aberto_por = ua.user_id AND
    uar.sis_id = ua.AREA AND
    o.status = st.stat_id AND 
    st.stat_ignored <> 1 AND 
    o.numero = t.ticket AND
    t.cfield_id = c.id AND
    (t.cfield_is_key = 0 OR t.cfield_is_key IS NULL) 
    {$terms}
    {$clausulesByStates[$data['state']]}
GROUP BY 
    c.field_label, t.cfield_value
ORDER BY 
    total DESC, field_label";


try {
    $res = $conn->prepare($sqlTablePart1);
    $res->execute();
    $data['tablePart1'] = $res->fetchAll();
} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sqlTablePart1'] = $sqlTablePart1;
}

/* Traz os valores dos campos customizados que são obtidos diretamente no 
campo cfield_value - exceto campos de seleção múltipla
 */
$sqlTablePart2 = "SELECT 
    count(*) as total,
    c.field_label, 
    cfov.option_value as field_value
FROM 
    custom_fields c, 
    tickets_x_cfields t,
    custom_fields_option_values cfov,
    sistemas ar,
    sistemas uar,
    usuarios ua,
    `status` st,
    ocorrencias o 
        LEFT JOIN clients cl ON cl.id = o.client
        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao 
WHERE 
    o.sistema = ar.sis_id AND 
    o.aberto_por = ua.user_id AND
    uar.sis_id = ua.AREA AND
    o.status = st.stat_id AND 
    st.stat_ignored <> 1 AND 
    o.numero = t.ticket AND
    c.field_type <> 'select_multi' AND
    t.cfield_is_key = 1 AND 
    t.cfield_value IS NOT NULL AND
    t.cfield_id = c.id AND
    t.cfield_value = cfov.id 
    {$terms}
    {$clausulesByStates[$data['state']]}
GROUP BY c.field_label, cfov.option_value
ORDER BY total DESC, field_label";

try {
    $res = $conn->prepare($sqlTablePart2);
    $res->execute();
    $data['tablePart2'] = $res->fetchAll();
} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sqlTablePart2'] = $sqlTablePart2;
}




/* Traz os valores dos campos customizados que são obtidos por chave selecionada 
    em custom_fields_option_values mas que estão nulos */
$sqlTablePart3 = "SELECT 
    count(*) as total,
    c.field_label, 
    COALESCE (t.cfield_value, 'N/A') AS 
        'field_value'
FROM 
    custom_fields c, 
    tickets_x_cfields t,
    sistemas ar,
    sistemas uar,
    usuarios ua,
    `status` st,
    ocorrencias o 
        LEFT JOIN clients cl ON cl.id = o.client
        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao 
WHERE 
    o.sistema = ar.sis_id AND 
    o.aberto_por = ua.user_id AND
    uar.sis_id = ua.AREA AND
    o.status = st.stat_id AND 
    st.stat_ignored <> 1 AND 
    o.numero = t.ticket AND
    t.cfield_id = c.id AND
    t.cfield_value IS NULL AND
    t.cfield_is_key = 1 
    {$terms}
    {$clausulesByStates[$data['state']]}
GROUP BY c.field_label, t.cfield_value
ORDER BY total DESC, field_label";

try {
    $res = $conn->prepare($sqlTablePart3);
    $res->execute();
    $data['tablePart3'] = $res->fetchAll();
} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sqlTablePart3'] = $sqlTablePart3;
}



/* Traz os valores dos campos customizados que são obtidos por chave selecionada 
    em custom_fields_option_values e que não são nulos.
    - Apenas para campos de seleção múltipla pois estes precisam ser tratados separadamente
 */
$sqlTablePart4 = "SELECT 
    t.ticket,
    c.field_label, 
    t.cfield_value as field_value
FROM 
    custom_fields c, 
    tickets_x_cfields t,
    sistemas ar,
    sistemas uar,
    usuarios ua,
    `status` st,
    ocorrencias o 
        LEFT JOIN clients cl ON cl.id = o.client
        LEFT JOIN instituicao un ON un.inst_cod = o.instituicao 
WHERE 
    o.sistema = ar.sis_id AND 
    o.aberto_por = ua.user_id AND
    uar.sis_id = ua.AREA AND
    o.status = st.stat_id AND 
    st.stat_ignored <> 1 AND 
    o.numero = t.ticket AND
    c.field_type = 'select_multi' AND
    t.cfield_is_key = 1 AND 
    t.cfield_value IS NOT NULL AND
    t.cfield_id = c.id 
    {$terms}
    {$clausulesByStates[$data['state']]}
ORDER BY ticket, field_label";

try {
    $res = $conn->prepare($sqlTablePart4);
    $res->execute();
    $data['tablePart4'] = $res->fetchAll();

    $tmp = [];
    $table4 = [];
    if (!empty($data['tablePart4'])) {
        foreach ($data['tablePart4'] as $key => $value) {

            /* Buscando os valores a partir dos múltiplos índices(strings separadas por vírgula) 
            de valor em cada um dos registros */
            $tmp = explode(',', getCustomFieldValue($conn, $value['field_value']));

            /* Montando um array temporário sem consolidar as quantidades dos valores repetidos */
            foreach ($tmp as $tmp_value) {
                $table4[] = [
                    'total' => 1,
                    'field_label' => $value['field_label'],
                    'field_value' => $tmp_value
                ];
            }
        }

        
        /* Consolida os totais de valores repetidos nas respostas múltiplas */
        $new_list = [];
        foreach ($table4 as $item) {
            $found = false;
            foreach ($new_list as $index => $new_item) {
                if ($new_item['field_value'] == $item['field_value'] && $new_item['field_label'] == $item['field_label']) {
                    $new_list[$index]['total'] += $item['total'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $new_list[] = $item;
            }
        }
        $data['tablePart4'] = $new_list;

    }

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sqlTablePart4'] = $sqlTablePart4;
}


// var_dump($table4);
// dump($sqlTablePart4);


/* Concatenar os arrays $data['tablePart1'], $data['tablePart2'] e $data['tablePart3'] */
$data['table'] = array_merge($data['tablePart1'], $data['tablePart2'], $data['tablePart3'], $data['tablePart4']);

/* Ordenar o array $data['table'] pela coluna total */
$data['table'] = arraySortByColumn($data['table'], 'total', SORT_DESC, SORT_NUMERIC);

// var_dump($data['table']);
$data['table']['title'] = TRANS('CUSTOM_FIELD_REPORT_TABLE');


/* Gráfico 1 */
$data[$chart_1_prefix] = $data['table'];
$data[$chart_1_prefix . $chart_title_sufix] = TRANS('GENERAL_DISTRIBUTION');


echo json_encode($data);

return true;

