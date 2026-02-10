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

$post = $_POST;

$html = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";

$data['ticket'] = (isset($post['ticket']) ? (int)$post['ticket'] : "");
$data['random'] = (isset($post['random']) ? noHtml($post['random']) : "");


if (empty($data['ticket'])) {
    $data['success'] = false;
    $data['message'] = "Ticket number is required";
    return;
}


$ticketInfo = getTicketData($conn, $data['ticket'],['sistema', 'data_atendimento']);
if (empty($ticketInfo)) {
    $data['success'] = false;
    $data['message'] = "Ticket number is not valid!";
    return;
}

$dateFirstResponse = (!empty($ticketInfo['data_atendimento']) ? dateScreen($ticketInfo['data_atendimento'], false, 'd/m/Y H:i') : '');

$possibleTreaters = getUsersByArea($conn, $ticketInfo['sistema']);

$afterDomClass = "after-dom-ready";
$randomClass = $data['random'];


?>
    <label class="col-sm-2 col-md-2 col-form-label col-form-label-sm text-md-right <?= $randomClass; ?>" ><?= TRANS('WORKER'); ?></label>
    <div class="form-group col-md-4 <?= $randomClass; ?>" >
        <div class="field_wrapper_specs" >
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <a href="javascript:void(0);" class="remove_button_treaters" data-random="<?= $randomClass; ?>" title="<?= TRANS('REMOVE'); ?>"><i class="fa fa-minus"></i></a>
                    </div>
                </div>
                <select class="form-control bs-select sel-control <?= $afterDomClass; ?>" name="treater_extra[]" id="<?= $randomClass; ?>" >
                <?php
                    foreach ($possibleTreaters as $treater) {
                    ?>
                        <option value="<?= $treater['user_id']; ?>"
                        <?= ($treater['user_id'] == $_SESSION['s_uid'] ? " selected" : "" ); ?>
                        ><?= $treater['nome']; ?></option>
                    <?php
                    }
                ?>
                </select>
            </div>
        </div>
    </div>

   
    <div class="form-group col-md-3 <?= $randomClass; ?>" >
        <div class="input-group">
            <input type="text" class="form-control datetime-treater date-start " name="treating_start_date[]" id="<?= $randomClass .'_date_start' ?>" value="<?= $dateFirstResponse; ?>" placeholder="dd/mm/aaaa hh:mm" required />
        </div>
        <small class="form-text text-muted"><?= TRANS('TREATING_START_DATE'); ?></small>
    </div>

    <div class="form-group col-md-3 <?= $randomClass; ?>" >
        <div class="input-group">
            <input type="text" class="form-control datetime-treater date-stop" name="treating_stop_date[]" id="<?= $randomClass .'_date_stop' ?>" value="<?= date('d/m/Y H:i'); ?>" placeholder="dd/mm/aaaa hh:mm" required />
        </div>
        <small class="form-text text-muted"><?= TRANS('TREATING_STOP_DATE'); ?></small>
    </div>
<?php
