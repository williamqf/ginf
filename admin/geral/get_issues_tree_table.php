<?php session_start();
 /* Copyright 2023 Flávio Ribeiro

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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();

$uareas = $_SESSION['s_uareas'];
$post = (isset($_POST) ? $_POST : '');

$havingScripts = (isset($post['having_scripts']) && $post['having_scripts'] == 'on' ? true : false);

$config = getConfig($conn);
$exception = "";

$options = [
    'prob_tipo_1' => [
        'label' => $config['conf_prob_tipo_1'],
        'table' => 'prob_tipo_1',
        'field_id' => 'probt1_cod',
        'field_name' => 'probt1_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_1',
        'sql_alias' => 'p.prob_tipo_1',
        'alias' => 'pt1',
        'value' => ''
    ],
    'prob_tipo_2' => [
        'label' => $config['conf_prob_tipo_2'],
        'table' => 'prob_tipo_2',
        'field_id' => 'probt2_cod',
        'field_name' => 'probt2_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_2',
        'sql_alias' => 'p.prob_tipo_2',
        'alias' => 'pt2',
        'value' => ''
    ],
    'prob_tipo_3' => [
        'label' => $config['conf_prob_tipo_3'],
        'table' => 'prob_tipo_3',
        'field_id' => 'probt3_cod',
        'field_name' => 'probt3_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_3',
        'sql_alias' => 'p.prob_tipo_3',
        'alias' => 'pt3',
        'value' => ''
    ],
    'prob_tipo_4' => [
        'label' => $config['conf_prob_tipo_4'],
        'table' => 'prob_tipo_4',
        'field_id' => 'probt4_cod',
        'field_name' => 'probt4_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_4',
        'sql_alias' => 'p.prob_tipo_4',
        'alias' => 'pt4',
        'value' => ''
    ],
    'prob_tipo_5' => [
        'label' => $config['conf_prob_tipo_5'],
        'table' => 'prob_tipo_5',
        'field_id' => 'probt5_cod',
        'field_name' => 'probt5_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_5',
        'sql_alias' => 'p.prob_tipo_5',
        'alias' => 'pt5',
        'value' => ''
    ],
    'prob_tipo_6' => [
        'label' => $config['conf_prob_tipo_6'],
        'table' => 'prob_tipo_6',
        'field_id' => 'probt6_cod',
        'field_name' => 'probt6_desc',
        'table_reference' => 'problemas',
        'table_reference_alias' => 'p',
        'field_reference' => 'prob_tipo_6',
        'sql_alias' => 'p.prob_tipo_6',
        'alias' => 'pt6',
        'value' => ''
    ]
];

if (!empty($post)) {

    /* Níveis possíveis de agrupamento */
    $groups = [
        'group_1' => '',
        'group_2' => '',
        'group_3' => '',
        'group_4' => '',
        'group_5' => '',
        'group_6' => ''
    ];

    foreach ($groups as $key => $group) {
        if (!empty($post[$key])) {
            $groups[$key] = $post[$key];
        } else {
            unset($groups[$key]);
        }
    }

    $table_id = $post['params'];
    $params = [];
    if (isset($post['params']) && !empty($post['params'])) {

        /* Tratamento quando o último parâmetro for de valor nulo */
        if (substr($post['params'], -1) == '-') {
            $post['params'] .= '0';
        }
        $params = explode('--', str_replace('---','-0--', $post['params']));
    }


    $tmp = [];
    /** Adicionando o valor para pesquisa no array principal $options */
    foreach ($params as $param) {
        $tmp = explode('-' , $param);
        $options[$tmp[0]]['value'] = (array_key_exists(1, $tmp) ? $tmp[1] : '0');
    }


    /* Monta os termos de pesquisa para a consulta SQL que exibirá a tabela resultante */
    $sql_terms = "";
    foreach ($options as $key => $value) {
        if ($value['value'] !== '') {

            $sql_terms .= ($value['value'] == 0 ? "AND {$value['sql_alias']} IS NULL " : "AND {$value['sql_alias']}={$value['value']} ");
        }
    }


    $termsHavingScripts = "";
    if ($havingScripts) {
        $termsHavingScripts = " AND (SELECT MAX(prscpt_id) FROM prob_x_script WHERE prscpt_prob_id = p.prob_id) IS NOT NULL ";
    }
    

    $sql = "SELECT DISTINCT 
                p.prob_id, p.problema,
                p.prob_descricao, p.prob_active, p.prob_profile_form, 
                p.prob_area_default,
                p.need_authorization, 
                p.card_in_costdash, 
                sl.slas_desc, 
                pt1.probt1_desc, 
                pt2.probt2_desc, 
                pt3.probt3_desc, 
                pt4.probt4_desc,
                pt5.probt5_desc,
                pt6.probt6_desc
            FROM 
                areas_x_issues as ai, problemas as p 
                LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla 
                LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 
                LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 
                LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 
                LEFT JOIN prob_tipo_4 as pt4 on pt4.probt4_cod = p.prob_tipo_4
                LEFT JOIN prob_tipo_5 as pt5 on pt5.probt5_cod = p.prob_tipo_5
                LEFT JOIN prob_tipo_6 as pt6 on pt6.probt6_cod = p.prob_tipo_6
            WHERE 
                p.prob_id = ai.prob_id
                {$termsHavingScripts}
                {$sql_terms}
            ORDER BY p.problema
            ";

    /* Só enviará dados se for o último nível do agrupamento selecionado */
    if (count($params) == count($groups)) {

        try {
            $res = $conn->query($sql);

        ?>
        <div id="tables">
            <!-- Listagem dos tipos de solicitações -->
            <table id="table<?= $table_id; ?>" class="lista_agrupamento stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
                <thead>
                    <tr class="header">
                        <th class="line p-4"><?= TRANS('ISSUE_TYPE'); ?></th>
                        <th class="line p-4"><?= TRANS('AREA'); ?></th>
                        <th class="line p-4"><?= TRANS('COL_SLA'); ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_1']; ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_2']; ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_3']; ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_4']; ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_5']; ?></th>
                        <th class="line p-4"><?= $config['conf_prob_tipo_6']; ?></th>
                        <th class="line p-4"><?= TRANS('ACTIVE_O'); ?></th>
                        <th class="line p-4"><?= TRANS('ADM_SCRIPTS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    foreach ($res->fetchall() as $rowDetail) { /* registros */
                        $area_default = $rowDetail['prob_area_default'];
                        $active = ($rowDetail['prob_active'] ? '<span class="text-success"><i class="fas fa-check"></i></span>' : '');
                        $inactive_class = (empty($active) ? 'table-danger' : '');
                        $listAreas = "";

                        if (count($hiddenInAreas = hiddenAreasByIssue($conn, $rowDetail['prob_id']))) {

                            $listAreas = '<hr/><p class="text-danger font-weight-bold mt-2 mb-1">' . TRANS('EXCEPT') . ':</p>';
                            foreach ($hiddenInAreas as $area) {
                                $listAreas .= '<li class="except_areas text-danger" data-content="' . $area['area_id'] . '">' . $area['area_name'] ?? '' . '</li>';
                            }
                        }

                        $areasByIssue = getAreasByIssue($conn, $rowDetail['prob_id'], TRANS('ALL_A'));
                        $linkedAreas = "";
                        foreach ($areasByIssue as $areaByIssue) {

                            if ($areaByIssue['sistema'] != TRANS('ALL_A')) {
                                
                                $boldDefaulArea = "";
                                if ($areaByIssue['sis_id'] == $area_default) {
                                    $boldDefaulArea = "font-weight-bold";
                                }
                                
                                $linkedAreas .= '<li class="except_areas ' .$boldDefaulArea. ' text-secondary" data-content="' . $areaByIssue['sis_id'] . '">' . $areaByIssue['sistema'] ?? '' . '</li>';
                            } else {
                                $linkedAreas .= '<li class=" text-secondary" data-content="">' . $areaByIssue['sistema'] ?? '' . '</li>';
                            }
                        }

                        $td_class = (empty($listAreas) ? 'except_areas' : '');


                        // $ShowlinkScript = "";
                        // $qryScript = "SELECT * FROM prob_x_script WHERE prscpt_prob_id = {$rowDetail['prob_id']}";
                        // $execQryScript = $conn->query($qryScript);
                        // if ($execQryScript->rowCount() > 0)
                        //     $ShowlinkScript = "<a onClick=\"popup_alerta('../../admin/geral/scripts_documentation.php?action=endview&prob=" . $rowDetail['prob_id'] . "')\" title='" . TRANS('HNT_SCRIPT_PROB') . "'><i class='far fa-lightbulb text-success'></i></a>";

                        $showScripts = "";
                        $issueScripts = getScripts($conn, null, $rowDetail['prob_id']);
                        foreach ($issueScripts as $script) {
                            $showScripts .= "<li class='list-scripts' data-content='" . $script['scpt_id'] . "'>" . $script['scpt_nome'] . "</li>";
                        }


                    ?>
                        <tr class='<?= $inactive_class; ?>'>
                            <!-- data-sort -->
                            <td class="line issue-edit" data-id="<?= $rowDetail['prob_id']; ?>"><i class="fas fa-edit text-secondary"></i>&nbsp;<?= $rowDetail['problema']; ?></td>
                            <td class="line <?= $td_class; ?>"><?= $linkedAreas . $listAreas; ?></td>
                            <td class="line"><?= ($rowDetail['slas_desc'] == '' ? TRANS('MSG_NOT_DEFINED') : $rowDetail['slas_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt1_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt1_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt2_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt2_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt3_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt3_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt4_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt4_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt5_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt5_desc']); ?></td>
                            <td class="line"><?= ($rowDetail['probt6_desc'] == '' ? '<span class="text-danger"><i class="fas fa-ban"></i></span>' : $rowDetail['probt6_desc']); ?></td>
                            <td class="line"><?= $active; ?></td>
                            <td class="line"><?= $showScripts; ?></td>
                            
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php


        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage();
        }
    }
}



