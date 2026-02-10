<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || $_SESSION['s_nivel'] != 1 ) {
    exit;
}

$conn = ConnectPDO::getInstance();
$exception = "";
//Todas as áreas que o usuário percente
$uareas = $_SESSION['s_uareas'];

$post = (isset($_POST) ? $_POST : '');

$config = getConfig($conn);


$havingScripts = (isset($post['having_scripts']) && $post['having_scripts'] == 'on' ? true : false);

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
        'alias' => 'pt1'
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
        'alias' => 'pt2'
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
        'alias' => 'pt3'
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
        'alias' => 'pt4'
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
        'alias' => 'pt5'
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
        'alias' => 'pt6'
    ]
];

$termsHavingScriptsLevelOne = "";
$termsHavingScripts = "";
$textHavingScripts = "";
if ($havingScripts) {
    $termsHavingScriptsLevelOne = " WHERE (SELECT MAX(prscpt_id) FROM prob_x_script WHERE prscpt_prob_id = p.prob_id) IS NOT NULL";
    $termsHavingScripts = " AND (SELECT MAX(prscpt_id) FROM prob_x_script WHERE prscpt_prob_id = p.prob_id) IS NOT NULL ";
    $textHavingScripts = "&nbsp;<strong>(" .TRANS("FILTERD_HAVING_SCRIPTS") . ")</strong>";
}


/** Traz o total de tipos de solicitações */
$qryTotal = "SELECT 
            COUNT(*) AS total  
        FROM 
            problemas p 
            {$termsHavingScriptsLevelOne}
        ";
$execTotal = $conn->query($qryTotal);
$regTotal = $execTotal->fetch()['total'];


?>
    <div id="issues_tree_agroup"> <!-- class="just-padding" -->
        <p><?= TRANS('THEREARE'); ?>&nbsp;<span class="font-weight-bold text-danger"><?= $regTotal; ?></span>&nbsp;<?= TRANS('ISSUES_TYPES_REGISTERED') . $textHavingScripts; ?>:</p>

        <div class="list-group list-group-root well">
<?php


if (isset($post['group_1']) && !empty($post['group_1'])) {
    /* Primeiro filtro de agrupamento */

    $sql_level_1 = "SELECT 
            COUNT(*) total, 
            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
            COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                \"{$options[$post['group_1']]['label']}\"
        FROM 
            problemas p
            LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

            {$termsHavingScriptsLevelOne}

        GROUP BY
            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}
        ORDER BY
            total DESC, 
            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']}
    ";

    try {
        $res_level_1 = $conn->query($sql_level_1);
    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
        dump($sql_level_1);
        echo message('danger', 'Ooops!', '<hr>' . $sql_level_1 . $exception, '', '', 1);
        return;
    }

    foreach ($res_level_1->fetchAll() as $row_level_1) {
    ?>
        <!-- Links no primeiro nivel -->
        <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>" class="list-group-item" data-toggle="collapse">
            
            <div class="card-header bg-light" >
                <span class="glyphicon icon-expand"></span>&nbsp;
                <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                    <?php
                        echo $row_level_1[$options[$post['group_1']]['label']];
                    ?>
                </span>
                <span class="badge badge-primary p-2 "><?= $row_level_1['total']; ?></span>
            </div>
        </a>
        <?php

        

        if (isset($post['group_2']) && !empty($post['group_2'])) {
            /* Tratamento para os casos de comparação onde o campo não possui informações - nulo */
            $group_1_id_or_null = (empty($row_level_1[$options[$post['group_1']]['field_id']]) ? " IS NULL " : " = " . $row_level_1[$options[$post['group_1']]['field_id']]);

            $sql_level_2 = "SELECT 
                COUNT(*) total, 
                {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                \"{$options[$post['group_1']]['label']}\",

                {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                COALESCE ({$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 'N/A') AS 
                    \"{$options[$post['group_2']]['label']}\"
                FROM 
                    problemas p

                    LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

                    LEFT JOIN {$options[$post['group_2']]['table']} {$options[$post['group_2']]['alias']} ON {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']} = {$options[$post['group_2']]['table_reference_alias']}.{$options[$post['group_2']]['field_reference']}

                WHERE
                    {$options[$post['group_1']]['sql_alias']} {$group_1_id_or_null} 

                    {$termsHavingScripts}
                    
                GROUP BY
                    {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                    {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']},

                    {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                    {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}
                ORDER BY
                    total DESC
            ";
            
            try {
                $res_level_2 = $conn->query($sql_level_2);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
                dump($sql_level_2);
                echo message('danger', 'Ooops!', '<hr>' . $sql_level_2 . $exception, '', '', 1);
                return;
            }
        
            ?>
                <!-- Div que envolve os links do segundo nível: baseado nas informações do group_1 -->
                <div class="list-group collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>">
            <?php

            foreach ($res_level_2 as $row_level_2) {

                ?>
                    <!-- Links no segundo nível -->
                    <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>" class="list-group-item" data-toggle="collapse">

                        <div class="card-header bg-light" >
                            <span class="glyphicon icon-expand"></span>&nbsp;
                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                                <?php
                                    echo $row_level_1[$options[$post['group_1']]['label']];
                                ?>
                            </span>
                            <!-- <span class="badge badge-secondary p-2 "><?= $row_level_1['total']; ?></span> -->
                            &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_2']]['label']; ?>" data-placement="top" data-trigger="hover">
                                <!-- <?= $row_level_2[$options[$post['group_2']]['label']]; ?> -->
                                <?php
                                    echo $row_level_2[$options[$post['group_2']]['label']];
                                ?>
                            </span>
                            <span class="badge badge-primary p-2 "><?= $row_level_2['total']; ?></span>
                        </div>
                    </a>
                <?php

                if (isset($post['group_3']) && !empty($post['group_3'])) {
                    $group_2_id_or_null = (empty($row_level_2[$options[$post['group_2']]['field_id']]) ? " IS NULL" : " = " . $row_level_2[$options[$post['group_2']]['field_id']]);

                    $sql_level_3 = "SELECT 
                        COUNT(*) total, 
                        {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                        COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                        \"{$options[$post['group_1']]['label']}\",

                        {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                        COALESCE ({$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 'N/A') AS 
                            \"{$options[$post['group_2']]['label']}\",

                        {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                        COALESCE ({$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']}, 'N/A') AS 
                            \"{$options[$post['group_3']]['label']}\"
                        FROM 
                            problemas p

                            LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

                            LEFT JOIN {$options[$post['group_2']]['table']} {$options[$post['group_2']]['alias']} ON {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']} = {$options[$post['group_2']]['table_reference_alias']}.{$options[$post['group_2']]['field_reference']}

                            LEFT JOIN {$options[$post['group_3']]['table']} {$options[$post['group_3']]['alias']} ON {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']} = {$options[$post['group_3']]['table_reference_alias']}.{$options[$post['group_3']]['field_reference']}

                        WHERE
                            {$options[$post['group_1']]['sql_alias']} {$group_1_id_or_null} AND 
                            {$options[$post['group_2']]['sql_alias']} {$group_2_id_or_null} 

                            {$termsHavingScripts}

                        GROUP BY
                            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']},

                            {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                            {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 

                            {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                            {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']}
                        ORDER BY
                            total DESC
                    ";
                    
                    try {
                        $res_level_3 = $conn->query($sql_level_3);


                        ?>
                        <!-- Div que envolve os links do terceiro nível: baseado nas informações do group_2 -->
                        <div class="list-group collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>">
                        <?php

                        foreach ($res_level_3 as $row_level_3) {
                        ?>
                            <!-- Links no terceiro nível -->
                            <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>" class="list-group-item" data-toggle="collapse">

                                <div class="card-header bg-light" >
                                    <span class="glyphicon icon-expand"></span>&nbsp;
                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                                        <?php
                                            echo $row_level_1[$options[$post['group_1']]['label']];
                                        ?>
                                    </span>
                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_2']]['label']; ?>" data-placement="top" data-trigger="hover">
                                        <?php
                                            echo $row_level_2[$options[$post['group_2']]['label']];
                                        ?>
                                    </span>
                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_3']]['label']; ?>" data-placement="top" data-trigger="hover">
                                        <?php
                                            echo $row_level_3[$options[$post['group_3']]['label']];
                                        ?>
                                    </span>
                                    <span class="badge badge-primary p-2 "><?= $row_level_3['total']; ?></span>
                                </div>
                            </a>
                        <?php

                            if (isset($post['group_4']) && !empty($post['group_4'])) {

                                $group_3_id_or_null = (empty($row_level_3[$options[$post['group_3']]['field_id']]) ? " IS NULL" : " = " . $row_level_3[$options[$post['group_3']]['field_id']]);

                                $sql_level_4 = "SELECT 
                                    COUNT(*) total, 
                                    {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                    COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                                    \"{$options[$post['group_1']]['label']}\",
            
                                    {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                    COALESCE ({$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 'N/A') AS 
                                        \"{$options[$post['group_2']]['label']}\",
            
                                    {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                    COALESCE ({$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']}, 'N/A') AS 
                                        \"{$options[$post['group_3']]['label']}\",

                                    {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                    COALESCE ({$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']}, 'N/A') AS 
                                        \"{$options[$post['group_4']]['label']}\"
                                    FROM 
                                        problemas p

                                        LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

                                        LEFT JOIN {$options[$post['group_2']]['table']} {$options[$post['group_2']]['alias']} ON {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']} = {$options[$post['group_2']]['table_reference_alias']}.{$options[$post['group_2']]['field_reference']}

                                        LEFT JOIN {$options[$post['group_3']]['table']} {$options[$post['group_3']]['alias']} ON {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']} = {$options[$post['group_3']]['table_reference_alias']}.{$options[$post['group_3']]['field_reference']}
                                    
                                        LEFT JOIN {$options[$post['group_4']]['table']} {$options[$post['group_4']]['alias']} ON {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']} = {$options[$post['group_4']]['table_reference_alias']}.{$options[$post['group_4']]['field_reference']}
                                    
            
                                    WHERE
                                        {$options[$post['group_1']]['sql_alias']} {$group_1_id_or_null} AND 
                                        {$options[$post['group_2']]['sql_alias']} {$group_2_id_or_null} AND 
                                        {$options[$post['group_3']]['sql_alias']} {$group_3_id_or_null}

                                        {$termsHavingScripts}
                                        
                                    GROUP BY
                                        {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                        {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']},
            
                                        {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                        {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 
            
                                        {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                        {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']},

                                        {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                        {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']}
                                    ORDER BY
                                        total DESC
                                ";
                                
                                try {
                                    $res_level_4 = $conn->query($sql_level_4);
                                    ?>
                                    <!-- Div que envolve os links do quarto nível: baseado nas informações do group_3 -->
                                    <div class="list-group collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>">
                                    <?php
            
                                    foreach ($res_level_4 as $row_level_4) {
                                    ?>
                                        <!-- Links no quarto nível -->
                                        <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']]; ?>" class="list-group-item" data-toggle="collapse">

                                            <div class="card-header bg-light" >
                                                <span class="glyphicon icon-expand"></span>&nbsp;
                                                <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                    <?php
                                                        echo $row_level_1[$options[$post['group_1']]['label']];
                                                    ?>
                                                </span>
                                                &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_2']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                    <?php
                                                        echo $row_level_2[$options[$post['group_2']]['label']];
                                                    ?>
                                                </span>
                                                &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_3']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                    <?php
                                                        echo $row_level_3[$options[$post['group_3']]['label']];
                                                    ?>
                                                </span>
                                                &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_4']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                    <?php
                                                        echo $row_level_4[$options[$post['group_4']]['label']];
                                                    ?>
                                                </span>

                                                <span class="badge badge-primary p-2 "><?= $row_level_4['total']; ?></span>
                                            </div>

                                        </a>
                                    <?php
            
                                        if (isset($post['group_5']) && !empty($post['group_5'])) {
                                            
                                            $group_4_id_or_null = (empty($row_level_4[$options[$post['group_4']]['field_id']]) ? " IS NULL" : " = " . $row_level_4[$options[$post['group_4']]['field_id']]);

                                            $sql_level_5 = "SELECT 
                                                COUNT(*) total, 
                                                {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                                COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                                                \"{$options[$post['group_1']]['label']}\",
                        
                                                {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                                COALESCE ({$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 'N/A') AS 
                                                    \"{$options[$post['group_2']]['label']}\",
                        
                                                {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                                COALESCE ({$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']}, 'N/A') AS 
                                                    \"{$options[$post['group_3']]['label']}\",
            
                                                {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                                COALESCE ({$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']}, 'N/A') AS 
                                                    \"{$options[$post['group_4']]['label']}\",

                                                {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']},
                                                COALESCE ({$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_name']}, 'N/A') AS 
                                                    \"{$options[$post['group_5']]['label']}\"
                                                FROM 
                                                    problemas p

                                                    LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

                                                    LEFT JOIN {$options[$post['group_2']]['table']} {$options[$post['group_2']]['alias']} ON {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']} = {$options[$post['group_2']]['table_reference_alias']}.{$options[$post['group_2']]['field_reference']}

                                                    LEFT JOIN {$options[$post['group_3']]['table']} {$options[$post['group_3']]['alias']} ON {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']} = {$options[$post['group_3']]['table_reference_alias']}.{$options[$post['group_3']]['field_reference']}

                                                    LEFT JOIN {$options[$post['group_4']]['table']} {$options[$post['group_4']]['alias']} ON {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']} = {$options[$post['group_4']]['table_reference_alias']}.{$options[$post['group_4']]['field_reference']}

                                                    LEFT JOIN {$options[$post['group_5']]['table']} {$options[$post['group_5']]['alias']} ON {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']} = {$options[$post['group_5']]['table_reference_alias']}.{$options[$post['group_5']]['field_reference']}
                        
                                                WHERE
                                                    {$options[$post['group_1']]['sql_alias']} {$group_1_id_or_null} AND 
                                                    {$options[$post['group_2']]['sql_alias']} {$group_2_id_or_null} AND 
                                                    {$options[$post['group_3']]['sql_alias']} {$group_3_id_or_null} AND 
                                                    {$options[$post['group_4']]['sql_alias']} {$group_4_id_or_null} 

                                                    {$termsHavingScripts}
                        
                                                GROUP BY
                                                    {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                                    {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']},
                        
                                                    {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                                    {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 
                        
                                                    {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                                    {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']},
            
                                                    {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                                    {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']},

                                                    {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']},
                                                    {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_name']}
                                                ORDER BY
                                                    total DESC
                                            ";
                                            
                                            try {
                                                $res_level_5 = $conn->query($sql_level_5);
                                                ?>
                                                <!-- Div que envolve os links do quinto nível: baseado nas informações do group_4 -->
                                                <div class="list-group collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>">
                                                <?php
                        
                                                foreach ($res_level_5 as $row_level_5) {
                                                ?>
                                                    <!-- Links no quino nível -->
                                                    <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']] ?>" class="list-group-item" data-toggle="collapse">

                                                        <div class="card-header bg-light" >
                                                            <span class="glyphicon icon-expand"></span>&nbsp;
                                                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                <?php
                                                                    echo $row_level_1[$options[$post['group_1']]['label']];
                                                                ?>
                                                            </span>
                                                            &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_2']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                <?php
                                                                    echo $row_level_2[$options[$post['group_2']]['label']];
                                                                ?>
                                                            </span>
                                                            &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_3']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                <?php
                                                                    echo $row_level_3[$options[$post['group_3']]['label']];
                                                                ?>
                                                            </span>
                                                            &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_4']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                <?php
                                                                    echo $row_level_4[$options[$post['group_4']]['label']];
                                                                ?>
                                                            </span>
                                                            &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                            <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_5']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                <?php
                                                                    echo $row_level_5[$options[$post['group_5']]['label']];
                                                                ?>
                                                            </span>


                                                            <span class="badge badge-primary p-2 "><?= $row_level_5['total']; ?></span>
                                                        </div>
                                                        
                                                    </a>











                                            <?php
                                                if (isset($post['group_6']) && !empty($post['group_6'])) {
                                            
                                                    $group_5_id_or_null = (empty($row_level_5[$options[$post['group_5']]['field_id']]) ? " IS NULL" : " = " . $row_level_5[$options[$post['group_5']]['field_id']]);

                                                    $sql_level_6 = "SELECT 
                                                        COUNT(*) total, 
                                                        {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                                        COALESCE ({$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']}, 'N/A') AS 
                                                        \"{$options[$post['group_1']]['label']}\",
                                
                                                        {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                                        COALESCE ({$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 'N/A') AS 
                                                            \"{$options[$post['group_2']]['label']}\",
                                
                                                        {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                                        COALESCE ({$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']}, 'N/A') AS 
                                                            \"{$options[$post['group_3']]['label']}\",
                    
                                                        {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                                        COALESCE ({$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']}, 'N/A') AS 
                                                            \"{$options[$post['group_4']]['label']}\",

                                                        {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']},
                                                        COALESCE ({$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_name']}, 'N/A') AS 
                                                            \"{$options[$post['group_5']]['label']}\",

                                                        {$options[$post['group_6']]['alias']}.{$options[$post['group_6']]['field_id']},
                                                        COALESCE ({$options[$post['group_6']]['alias']}.{$options[$post['group_6']]['field_name']}, 'N/A') AS 
                                                            \"{$options[$post['group_6']]['label']}\"
                                                        FROM 
                                                            problemas p

                                                            LEFT JOIN {$options[$post['group_1']]['table']} {$options[$post['group_1']]['alias']} ON {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']} = {$options[$post['group_1']]['table_reference_alias']}.{$options[$post['group_1']]['field_reference']}

                                                            LEFT JOIN {$options[$post['group_2']]['table']} {$options[$post['group_2']]['alias']} ON {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']} = {$options[$post['group_2']]['table_reference_alias']}.{$options[$post['group_2']]['field_reference']}

                                                            LEFT JOIN {$options[$post['group_3']]['table']} {$options[$post['group_3']]['alias']} ON {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']} = {$options[$post['group_3']]['table_reference_alias']}.{$options[$post['group_3']]['field_reference']}

                                                            LEFT JOIN {$options[$post['group_4']]['table']} {$options[$post['group_4']]['alias']} ON {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']} = {$options[$post['group_4']]['table_reference_alias']}.{$options[$post['group_4']]['field_reference']}

                                                            LEFT JOIN {$options[$post['group_5']]['table']} {$options[$post['group_5']]['alias']} ON {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']} = {$options[$post['group_5']]['table_reference_alias']}.{$options[$post['group_5']]['field_reference']}

                                                            LEFT JOIN {$options[$post['group_6']]['table']} {$options[$post['group_6']]['alias']} ON {$options[$post['group_6']]['alias']}.{$options[$post['group_6']]['field_id']} = {$options[$post['group_6']]['table_reference_alias']}.{$options[$post['group_6']]['field_reference']}
                                
                                                        WHERE
                                                            {$options[$post['group_1']]['sql_alias']} {$group_1_id_or_null} AND 
                                                            {$options[$post['group_2']]['sql_alias']} {$group_2_id_or_null} AND 
                                                            {$options[$post['group_3']]['sql_alias']} {$group_3_id_or_null} AND 
                                                            {$options[$post['group_4']]['sql_alias']} {$group_4_id_or_null} AND
                                                            {$options[$post['group_5']]['sql_alias']} {$group_5_id_or_null} 
                                
                                                            {$termsHavingScripts}
                                                        GROUP BY
                                                            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_id']},
                                                            {$options[$post['group_1']]['alias']}.{$options[$post['group_1']]['field_name']},
                                
                                                            {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_id']},
                                                            {$options[$post['group_2']]['alias']}.{$options[$post['group_2']]['field_name']}, 
                                
                                                            {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_id']},
                                                            {$options[$post['group_3']]['alias']}.{$options[$post['group_3']]['field_name']},
                    
                                                            {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_id']},
                                                            {$options[$post['group_4']]['alias']}.{$options[$post['group_4']]['field_name']},

                                                            {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_id']},
                                                            {$options[$post['group_5']]['alias']}.{$options[$post['group_5']]['field_name']},

                                                            {$options[$post['group_6']]['alias']}.{$options[$post['group_6']]['field_id']},
                                                            {$options[$post['group_6']]['alias']}.{$options[$post['group_6']]['field_name']}
                                                        ORDER BY
                                                            total DESC
                                                    ";
                                                    
                                                    try {
                                                        $res_level_6 = $conn->query($sql_level_6);
                                                        ?>
                                                        <!-- Div que envolve os links do sexto nível: baseado nas informações do group_5 -->
                                                        <div class="list-group collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']] ?>">
                                                        <?php
                                
                                                        foreach ($res_level_6 as $row_level_6) {
                                                        ?>
                                                            <!-- Links no sexto nível -->
                                                            <a href="#<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']] ?>--<?= $post['group_6']; ?>-<?= $row_level_6[$options[$post['group_6']]['field_id']] ?>" class="list-group-item" data-toggle="collapse">

                                                                <div class="card-header bg-light" >
                                                                    <span class="glyphicon icon-expand"></span>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_1']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_1[$options[$post['group_1']]['label']];
                                                                        ?>
                                                                    </span>
                                                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_2']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_2[$options[$post['group_2']]['label']];
                                                                        ?>
                                                                    </span>
                                                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_3']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_3[$options[$post['group_3']]['label']];
                                                                        ?>
                                                                    </span>
                                                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_4']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_4[$options[$post['group_4']]['label']];
                                                                        ?>
                                                                    </span>
                                                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_5']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_5[$options[$post['group_5']]['label']];
                                                                        ?>
                                                                    </span>
                                                                    &nbsp;<i class="fas fa-angle-right"></i>&nbsp;
                                                                    <span class="badge badge-light p-2" data-toggle="popover" data-content="<?= $options[$post['group_6']]['label']; ?>" data-placement="top" data-trigger="hover">
                                                                        <?php
                                                                            echo $row_level_6[$options[$post['group_6']]['label']];
                                                                        ?>
                                                                    </span>


                                                                    <span class="badge badge-primary p-2 "><?= $row_level_6['total']; ?></span>
                                                                </div>
                                                                
                                                            </a>


                                                            <!-- Listagem dos chamados no sexto nível -->
                                                            <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']] ?>--<?= $post['group_6']; ?>-<?= $row_level_6[$options[$post['group_6']]['field_id']] ?>">
                                                                
                                                            </div>
                                                            
                                                        <?php
                                                        }
                                
                                                        ?>
                                                        </div><!-- Envolve os links do sexto nível -->
                                                        <?php
                                
                                
                                
                                                    }
                                                    catch (Exception $e) {
                                                        $exception .= "<hr>" . $e->getMessage();
                                                        dump($sql_level_6);
                                                        echo message('danger', 'Ooops!', '<hr>' . $sql_level_6 . $exception, '', '', 1);
                                                        return;
                                                    }
                    
                                                } else {
                                                    /**
                                                     * Não tem o sexto filtro
                                                     * Exibe a listagem com base apenas no quinto filtro
                                                     */
                                                    ?>
                                                    <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']]; ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']]; ?>">
                                                        
                                                    </div>
                                                <?php
                                                }
                                                ?>










                                                    


                                                     <!-- Listagem dos chamados no quinto nível -->
                                                    <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']] ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']] ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']] ?>--<?= $post['group_5']; ?>-<?= $row_level_5[$options[$post['group_5']]['field_id']] ?>">
                                                        
                                                    </div>
                                                    
                                                <?php
                                            }
                        
                                                ?>
                                                </div><!-- Envolve os links do quinto nível -->
                                                <?php
                        
                        
                        
                                            }
                                            catch (Exception $e) {
                                                $exception .= "<hr>" . $e->getMessage();
                                                dump($sql_level_5);
                                                echo message('danger', 'Ooops!', '<hr>' . $sql_level_5 . $exception, '', '', 1);
                                                return;
                                            }
            
                                        } else {
                                            /**
                                             * Não tem o quinto filtro
                                             * Exibe a listagem com base apenas no quarto filtro
                                             */
                                            ?>
                                            <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>--<?= $post['group_4']; ?>-<?= $row_level_4[$options[$post['group_4']]['field_id']]; ?>">
                                                
                                            </div>
                                        <?php
                                        }
                                    }
            
                                    ?>
                                    </div><!-- Envolve os links do quarto nível -->
                                    <?php
                                }
                                catch (Exception $e) {
                                    $exception .= "<hr>" . $e->getMessage();
                                    dump($sql_level_4);
                                    echo message('danger', 'Ooops!', '<hr>' . $sql_level_4 . $exception, '', '', 1);
                                    return;
                                }
                            } else {
                                /**
                                 * Não tem o quarto filtro
                                 * Exibe a listagem com base apenas no terceiro filtro
                                 */
                                ?>
                                <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>--<?= $post['group_3']; ?>-<?= $row_level_3[$options[$post['group_3']]['field_id']]; ?>">
                                    
                                </div>
                            <?php
                            }
                        }

                        ?>
                        </div><!-- Envolve os links do terceiro nível -->
                        <?php
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage();
                        dump($sql_level_3);
                        echo message('danger', 'Ooops!', '<hr>' . $sql_level_3 . $exception, '', '', 1);
                        return;
                    }

                } else {
                    /**
                     * Não tem o terceiro filtro
                     * Exibe a listagem com base apenas no segundo filtro
                     */
                    ?>
                        <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']]; ?>--<?= $post['group_2']; ?>-<?= $row_level_2[$options[$post['group_2']]['field_id']]; ?>">
                            
                        </div>
                    <?php
                }

            }
        
            ?>
                </div> <!-- Envolve os links do segundo nível -->
            <?php
        } else {
            /**
             * Não tem o segundo filtro
             * Exibe a listagem com base apenas no primeiro filtro
             */
            ?>
                <div class="list-group-item collapse" id="<?= $post['group_1']; ?>-<?= $row_level_1[$options[$post['group_1']]['field_id']] ?>">
                    
                </div>
            <?php
        }
    }
    
} else {
    /**
     * Nenhum filtro de agrupamento
     * Exibirá todos os chamados em aberto para as áreas do usuário logado
     */
    ?>
        <div class="list-group-item " id="show_all-1">
            <?= message('info', 'Ooops!', TRANS('SELECT_AT_LEAST_ONE_FIELD_TO_GROUP'), '', '', 1); ?>
        </div>
    <?php
}



?>
        </div> <!-- list-group-root -->
    </div> <!-- just-padding -->
<?php




