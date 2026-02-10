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

$post = $_POST;

$client = (isset($post['client']) ? noHtml($post['client']) : "");

$asset_units = (isset($post['asset_unit']) && !empty(array_filter($post['asset_unit'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['asset_unit']) : []);

$asset_departments = (isset($post['asset_department']) && !empty(array_filter($post['asset_department'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['asset_department']) : []);

$asset_users = (isset($post['asset_user']) && !empty(array_filter($post['asset_user'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['asset_user']) : []);

$asset_categories = (isset($post['asset_category']) && !empty(array_filter($post['asset_category'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['asset_category']) : []);

$asset_types = (isset($post['asset_type']) && !empty(array_filter($post['asset_type'], function($v) { return !empty($v); })) ? array_map('noHtml', $post['asset_type']) : []);

$use_asset_client = (isset($post['use_asset_client']) && !empty($post['use_asset_client']) ? true : false);
$use_asset_department = (isset($post['use_asset_department']) && !empty($post['use_asset_department']) ? true : false);


$termsTotal = "";
$terms = "";
$chart_title_sufix = '_chart_title';
$chart_1_prefix = 'chart_01';
$chart_2_prefix = 'chart_02';
$chart_3_prefix = 'chart_03';
$chart_4_prefix = 'chart_04';
$chart_5_prefix = 'chart_05';



/* Critérios da consulta */
$criteria['client'] = TRANS('CLIENT') . ':&nbsp;' . TRANS('ALL');
$criteria['units'] = TRANS('COL_UNIT') . ':&nbsp;' . TRANS('ALL');
$criteria['departments'] = TRANS('DEPARTMENT') . ':&nbsp;' . TRANS('ALL');
$criteria['users'] = TRANS('FIELD_USER') . ':&nbsp;' . TRANS('ALL');
$criteria['categories'] = TRANS('CATEGORY') . ':&nbsp;' . TRANS('ALL');
$criteria['asset_type'] = TRANS('COL_TYPE') . ':&nbsp;' . TRANS('ALL');


$units_text_ids = "";
$departments_text_ids = "";
$users_text_ids = "";
$categories_text_ids = "";
$asset_types_text_ids = "";


$client_filter = [];
$units_filter = [];
$types_filter = [];

$sql_terms = "";


/* Critérios de Cliente e unidades */
if (!empty($client)) {

    /* Cliente do usuário - padrão */
    // $sql_terms .= " AND clu.id = '{$client}' ";
    $sql_terms .=  ($use_asset_client ? " AND cla.id = '{$client}'" : " AND clu.id = '{$client}' ");

    $client_filter = getClients($conn, $client);
    $criteria['client'] = TRANS('CLIENT') . ':&nbsp;<b>' . $client_filter['nickname'] . '</b>';
    
    if (empty($asset_units)) {

        $units_filter = getUnits($conn, null, null, $client);
        $units_filter = (!empty($units_filter) ? $units_filter : [['inst_cod' => 0]]);

    } else {
        foreach ($asset_units as $unit) {
            $units_filter[] = getUnits($conn, null, $unit);
        }
        $criteria['units'] = TRANS('COL_UNIT') . ':&nbsp;<b>' . implode(', ', array_column($units_filter, 'inst_nome')) . '</b>';
        $units_text_ids = implode(', ', array_column($units_filter, 'inst_cod'));
        $sql_terms .= " AND c.comp_inst IN ({$units_text_ids}) ";
    }

} elseif (!empty($asset_units)) {

    foreach ($asset_units as $unit) {
        $units_filter[] = getUnits($conn, null, $unit);
    }

    $criteria['units'] = TRANS('COL_UNIT') . ':&nbsp;<b>' . implode(', ', array_column($units_filter, 'inst_nome')) . '</b>';
    $units_text_ids = implode(', ', array_column($units_filter, 'inst_cod'));
    $sql_terms .= " AND c.comp_inst IN ({$units_text_ids}) ";

}

/* Critérios de Departamentos */
if (!empty($asset_departments)) {

    foreach ($asset_departments as $department) {
        $departments_filter[] = getDepartments($conn, null, $department);
    }

    $criteria['departments'] = TRANS('DEPARTMENT') . ':&nbsp;<b>' . implode(', ', array_column($departments_filter, 'local')) . '</b>';
    $departments_text_ids = implode(', ', array_column($departments_filter, 'loc_id'));
    /* Departamento do usuário (padrao) */
    // $sql_terms .= " AND u.user_department IN ({$departments_text_ids}) ";
    $sql_terms .= ($use_asset_department ? " AND c.comp_local IN ({$departments_text_ids})" :" AND u.user_department IN ({$departments_text_ids}) ");
}


/* Critérios de Usuários */
if (!empty($asset_users)) {

    foreach ($asset_users as $user) {
        $users_filter[] = getUsers($conn, $user);
    }

    $criteria['users'] = TRANS('FIELD_USER') . ':&nbsp;<b>' . implode(', ', array_column($users_filter, 'nome')) . '</b>';
    $users_text_ids = implode(', ', array_column($users_filter, 'user_id'));
    $sql_terms .= " AND u.user_id IN ({$users_text_ids}) ";
}


/* Critério de Tipo de ativos com base no filtro de categoria */
if (!empty($asset_categories)) {
    
    foreach ($asset_categories as $categorie) {
        $categories_filter[] = getAssetsCategories($conn, $categorie);
    }
    $criteria['categories'] = TRANS('CATEGORY') . ':&nbsp;<b>' . implode(', ', array_column($categories_filter, 'cat_name')) . '</b>';
    $categories_text_ids = implode(', ', array_column($categories_filter, 'id'));
    $sql_terms .= " AND cat.id IN ({$categories_text_ids}) ";

    if (empty($asset_types)) {
        $types_filter = getAssetsTypes($conn, null, null, $asset_categories );
    } else {
        foreach ($asset_types as $asset_type) {
            $types_filter[] = getAssetsTypes($conn, $asset_type);
        }
        $criteria['asset_type'] = TRANS('COL_TYPE') . ':&nbsp;<b>' . implode(', ', array_column($types_filter, 'tipo_nome')) . '</b>';
        $asset_types_text_ids = implode(', ', array_column($types_filter, 'tipo_cod'));
        $sql_terms .= " AND c.comp_tipo_equip IN ({$asset_types_text_ids}) ";
    }
} elseif (!empty($asset_types)) {

    foreach ($asset_types as $asset_type) {
        $types_filter[] = getAssetsTypes($conn, $asset_type);
    }
    $criteria['asset_type'] = TRANS('COL_TYPE') . ':&nbsp;<b>' . implode(', ', array_column($types_filter, 'tipo_nome')) . '</b>';
    $asset_types_text_ids = implode(', ', array_column($types_filter, 'tipo_cod'));
    $sql_terms .= " AND c.comp_tipo_equip IN ({$asset_types_text_ids}) ";

}

$criteriaText = implode(' | ', $criteria);
if (!empty($_SESSION['s_allowed_units'])) {
    $criteriaText .= " <br />(". TRANS('RESULT_LIMITED_BY_PERMISSIONS').")";
}
$data['criteria'] = $criteriaText;





// var_dump($criteriaText); 


/* Controle no limite de visualização para a área primária do usuário */
// if (!empty($_SESSION['s_allowed_units'])) {
//     $terms .= " AND e.comp_inst IN ({$_SESSION['s_allowed_units']}) ";

//     $termsTotal .= (!empty($termsTotal) ? " AND " : " WHERE ");
//     $termsTotal .= " e.comp_inst IN ({$_SESSION['s_allowed_units']}) ";
// }


/* Tabela de custos por usuário com base nos critérios informados */
$sql = "SELECT
            SUM(CAST(c.comp_valor AS DECIMAL(10,2))) AS amount,
            u.nome,
            clu.nickname AS cliente_usuario,
            COALESCE (lu.local, 'N/A') AS departamento_usuario

        FROM
            users_x_assets uxa,
            clients clu,
            usuarios u,
            equipamentos c,
            instituicao i
                LEFT JOIN clients AS cla ON cla.id = i.inst_client,
            localizacao la,
            usuarios ud 
                LEFT JOIN localizacao AS lu ON lu.loc_id = ud.user_department,
            tipo_equip t 
                LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria
        WHERE
            uxa.user_id = u.user_id AND
            uxa.user_id = ud.user_id AND
            uxa.is_current = 1 AND
            uxa.asset_id = c.comp_cod AND
            clu.id = u.user_client AND
            c.comp_inst = i.inst_cod AND
            c.comp_tipo_equip = t.tipo_cod AND
            c.comp_local = la.loc_id 
            {$sql_terms}

        GROUP BY
            u.user_id, 
            u.nome, 
            cliente_usuario, 
            departamento_usuario
        HAVING 
            amount > 0
        ORDER BY
            amount DESC";
try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll();
    $data['table'] = $result;
    
    /* somatório do campo 'amount' */
    $data['total'] = priceScreen(array_sum(array_column($result, 'amount')));
    
    /* Aplica a função 'priceScreen' em cada elemento da coluna 'amount' - function (&$value, $key) */
    array_walk($data['table'], function (&$value) {
        /* armazena o valor original da nova coluna 'price_db' */
        $value['price_db'] = $value['amount'];
        $value['amount'] = priceScreen($value['amount']);
    });
    $data['table']['title'] = TRANS('ASSETS_COST_BY_USER');
    

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}




$select_terms = (!$use_asset_department ? " lu.local " : " la.local ");
$table_2_title = (!$use_asset_department ? TRANS('ASSETS_COST_BY_USER_DEPARTMENT') : TRANS('ASSETS_COST_BY_ASSET_DEPARTMENT'));

/* Tabela 2 - Tabela de custos por Departamento - com base nos critérios informados */
$sql = "SELECT
            SUM(CAST(c.comp_valor AS DECIMAL(10,2))) AS amount,
            clu.nickname AS cliente_usuario,
            -- COALESCE (lu.local, 'N/A') AS departamento_usuario
            COALESCE ({$select_terms}, 'N/A') AS departamento_usuario

        FROM
            users_x_assets uxa,
            clients clu,
            usuarios u,
            equipamentos c,
            instituicao i
                LEFT JOIN clients AS cla ON cla.id = i.inst_client,
            localizacao la,
            usuarios ud 
                LEFT JOIN localizacao AS lu ON lu.loc_id = ud.user_department,
            tipo_equip t 
                LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria
        WHERE
            uxa.user_id = u.user_id AND
            uxa.user_id = ud.user_id AND
            uxa.is_current = 1 AND
            uxa.asset_id = c.comp_cod AND
            clu.id = u.user_client AND
            c.comp_inst = i.inst_cod AND
            c.comp_tipo_equip = t.tipo_cod AND
            c.comp_local = la.loc_id 
            {$sql_terms}

        GROUP BY
            departamento_usuario,
            cliente_usuario
        HAVING 
            amount > 0
        ORDER BY
            amount DESC";
try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll();
    $data['table_2'] = $result;
    
    /* somatório do campo 'amount' */
    $data['total_2'] = priceScreen(array_sum(array_column($result, 'amount')));
    
    /* Aplica a função 'priceScreen' em cada elemento da coluna 'amount' - function (&$value, $key) */
    array_walk($data['table_2'], function (&$value) {
        /* armazena o valor original da nova coluna 'price_db' */
        $value['price_db'] = $value['amount'];
        $value['amount'] = priceScreen($value['amount']);
        $value['quantidade'] = $value['price_db'];
    });

    $data['table_2']['title'] = $table_2_title;
    

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}


/* Dados para o gráfico 1 - Com base na segunda tabela */
$data[$chart_1_prefix]['table_2'] = $data['table_2'];
$data[$chart_1_prefix]['table_2' . $chart_title_sufix] = $table_2_title;








$select_terms = (!$use_asset_client ? " clu.nickname " : " cla.nickname ");
$table_3_title = (!$use_asset_client ? TRANS('ASSETS_COST_BY_USER_CLIENT') : TRANS('ASSETS_COST_BY_ASSET_CLIENT'));

/* Tabela 3 - Tabela de custos por Cliente - com base nos critérios informados */
$sql = "SELECT
            SUM(CAST(c.comp_valor AS DECIMAL(10,2))) AS amount,
            {$select_terms} AS cliente_usuario

        FROM
            users_x_assets uxa,
            clients clu,
            usuarios u,
            equipamentos c,
            instituicao i
                LEFT JOIN clients AS cla ON cla.id = i.inst_client,
            localizacao la,
            usuarios ud 
                LEFT JOIN localizacao AS lu ON lu.loc_id = ud.user_department,
            tipo_equip t 
                LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria
        WHERE
            uxa.user_id = u.user_id AND
            uxa.user_id = ud.user_id AND
            uxa.is_current = 1 AND
            uxa.asset_id = c.comp_cod AND
            clu.id = u.user_client AND
            c.comp_inst = i.inst_cod AND
            c.comp_tipo_equip = t.tipo_cod AND
            c.comp_local = la.loc_id 
            {$sql_terms}

        GROUP BY
            cliente_usuario
        HAVING 
            amount > 0
        ORDER BY
            amount DESC";
try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll();
    $data['table_3'] = $result;
    
    /* somatório do campo 'amount' */
    $data['total_3'] = priceScreen(array_sum(array_column($result, 'amount')));
    
    /* Aplica a função 'priceScreen' em cada elemento da coluna 'amount' - function (&$value, $key) */
    array_walk($data['table_3'], function (&$value) {
        /* armazena o valor original da nova coluna 'price_db' */
        $value['price_db'] = $value['amount'];
        $value['amount'] = priceScreen($value['amount']);
        $value['quantidade'] = $value['price_db'];
    });

    $data['table_3']['title'] = $table_3_title;
    

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}


/* Dados para o gráfico 2 */
$data[$chart_2_prefix]['table_3'] = $data['table_3'];
$data[$chart_2_prefix]['table_3' . $chart_title_sufix] = $table_3_title;






echo json_encode($data);

return true;
dump($sql); exit;









/* GRáfico 1 */
$sql = "SELECT 
            count(*) as quantidade, 
            ROUND (count(*)*100/" . $data['total'] . ", 2) as percentual,
            t.tipo_nome AS asset_type
        FROM 
            equipamentos e, tipo_equip t, instituicao u
        WHERE
            e.comp_tipo_equip = t.tipo_cod AND
            e.comp_inst = u.inst_cod
            {$terms}

        GROUP BY
            t.tipo_nome
        ORDER BY
            quantidade DESC,
            t.tipo_nome";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll(PDO::FETCH_ASSOC);
    $data[$chart_1_prefix] = $result;

    $data[$chart_1_prefix . $chart_title_sufix] = TRANS('GENERAL_DISTRIBUTION');

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}



/* Gráfico 2 */
$sql = "SELECT 
            count(*) as quantidade, 
            ROUND (count(*)*100/" . $data['total'] . ", 2) as percentual,
            COALESCE(cat.cat_name, 'N/A') AS categorie
        FROM 
            equipamentos e, tipo_equip t
            LEFT JOIN assets_categories cat ON cat.id = t.tipo_categoria
        WHERE
            e.comp_tipo_equip = t.tipo_cod 
            {$terms}

        GROUP BY
            cat.cat_name
        ORDER BY
            quantidade DESC
            ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll(PDO::FETCH_ASSOC);
    $data[$chart_2_prefix] = $result;

    $data[$chart_2_prefix . $chart_title_sufix] = TRANS('DISTRIBUTION_BY_CATEGORY');

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}



/* Gráfico 3 */
$sql = "SELECT 
            count(*) as quantidade, 
            ROUND (count(*)*100/" . $data['total'] . ", 2) as percentual,
            COALESCE(c.nickname, 'N/A') AS client
        FROM 
            equipamentos e, tipo_equip t, instituicao u
            LEFT JOIN clients c ON c.id = u.inst_client
        WHERE
            e.comp_tipo_equip = t.tipo_cod AND
            e.comp_inst = u.inst_cod
            {$terms}

        GROUP BY
            c.nickname
        ORDER BY
            quantidade DESC
            ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll(PDO::FETCH_ASSOC);
    $data[$chart_3_prefix] = $result;

    $data[$chart_3_prefix . $chart_title_sufix] = TRANS('DISTRIBUTION_BY_CLIENT');

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}


/* Gráfico 4 */
$sql = "SELECT 
            count(*) as quantidade, 
            ROUND (count(*)*100/" . $data['total'] . ", 2) as percentual,
            -- t.tipo_nome AS asset_type,
            u.inst_nome AS asset_unit
        FROM 
            equipamentos e, tipo_equip t, instituicao u
        WHERE
            e.comp_tipo_equip = t.tipo_cod AND
            e.comp_inst = u.inst_cod
            {$terms}

        GROUP BY
            -- t.tipo_nome,
            u.inst_nome
        ORDER BY
            quantidade DESC,
            u.inst_nome
            -- t.tipo_nome 
            ";

try {
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetchAll(PDO::FETCH_ASSOC);
    $data[$chart_4_prefix] = $result;

    $data[$chart_4_prefix . $chart_title_sufix] = TRANS('DISTRIBUTION_BY_UNIT');

} catch (Exception $e) {
    $exception .= "<hr>" . $e->getMessage();
    $data['exception'] = $exception;
    $data['sql'] = $sql;
}







echo json_encode($data);