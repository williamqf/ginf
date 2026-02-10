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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();


$post = $_POST;

$config = getConfig($conn);
$exception = "";
$terms = "";
$data = [];
$data['script_id'] = $post['cod'];
$data['action'] = (isset($post['action']) ? noHtml($post['action']) : "");
$data['disabled'] = ($data['action'] == "" || $data['action'] == 'details' ? " disabled" : "");


$sql = "SELECT prscpt_id, prscpt_prob_id, prscpt_scpt_id, problema, prob_id FROM prob_x_script 
					LEFT JOIN problemas on prob_id = prscpt_prob_id 
					WHERE prscpt_scpt_id = '" . $data['script_id'] . "' 
					AND prscpt_prob_id = prob_id 
					GROUP BY prob_id, problema, prscpt_id, prscpt_prob_id, prscpt_scpt_id  
                    ORDER BY problema ";

try {
    $res = $conn->query($sql);

    ?>
    <table id="table_script_type_of_issues" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
        <thead>
            <tr class="header">
                <td class='line problema'><?= TRANS('ISSUE_TYPE'); ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_1']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_2']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_3']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_4']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_5']; ?></td>
                <td class='line aberto_por'><?= $config['conf_prob_tipo_6']; ?></td>
            </tr>
        </thead>
    <?php
    foreach ($res->fetchall() as $row) {

        $issueDetails = (!empty($row['prob_id']) ? getIssueDetailed($conn, $row['prob_id'])[0] : []);
        ?>
        <tr>
            <?php
            if (!empty($data['disabled'])) {
                ?>
                <td class="line"><?= $issueDetails['problema']; ?></td>
                <?php
            } else {
                ?>
                <td class="line"><span class="align-top"><i class="fas fa-trash-alt text-danger"></i></span><input type="checkbox" name="delProb[<?= $row['prscpt_id']; ?>]" value="<?= $row['prscpt_id']; ?>"/>&nbsp;<?= $issueDetails['problema']; ?></td>
                <?php
            }
            ?>
            <td class="line"><?= $issueDetails['probt1_desc']; ?></td>
            <td class="line"><?= $issueDetails['probt2_desc']; ?></td>
            <td class="line"><?= $issueDetails['probt3_desc']; ?></td>
            <td class="line"><?= $issueDetails['probt4_desc']; ?></td>
            <td class="line"><?= $issueDetails['probt5_desc']; ?></td>
            <td class="line"><?= $issueDetails['probt6_desc']; ?></td>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
}
catch (Exception $e) {
    $exception .= $e->getMessage();
    echo $exception;
}
?>

