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

// $data['measure_type'] = (isset($post['measure_type']) ? noHtml($post['measure_type']) : "");
$data['random'] = (isset($post['random']) ? noHtml($post['random']) : "");

$afterDomClass = "after-dom-ready";
$randomClass = $data['random'];


?>

    <div class="form-group col-md-6 <?= $randomClass; ?> <?= $afterDomClass; ?>">
        <div class="resource_wrapper_fields" id="resource_wrapper_fields">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <a href="javascript:void(0);" class="remove_button_resource" data-random="<?= $randomClass; ?>" title="<?= TRANS('REMOVE'); ?>"><i class="fa fa-minus"></i></a>
                    </div>
                </div>
                
                <select class="form-control bs-select sel-control <?= $afterDomClass; ?>" id="resource<?= $randomClass; ?>" name="resource[]">
                    <option value="="><?= TRANS('TYPE_OF_RESOURCE'); ?></option>
                    <?php
                        $types = getAssetsModels($conn, null, null, null, 1, ['t.tipo_nome'], 1);
                        foreach ($types as $type) {
                            $fullType = $type['tipo'] . ' - ' . $type['fabricante'] . ' - ' . $type['modelo'];
                            ?>
                            
                            <option data-subtext="<?= $type['cat_name']; ?>" data-model="<?= $type['codigo']; ?>" data-random="<?= $randomClass; ?>" value="<?= $type['codigo']; ?>"><?= $fullType; ?></option>

                            <?php
                        }
                    ?>
                </select>
            </div>
            <small class="form-text text-muted"><?= TRANS('TYPE_OF_RESOURCE'); ?></small>
        </div>
    </div>
    <div class="form-group col-md-2 <?= $randomClass; ?> <?= $afterDomClass; ?>">
        <div class="resource_wrapper_fields">
            <input type="number" class="form-control amount-control " id="amount<?= $randomClass; ?>" name="amount[]" data-random="<?= $randomClass; ?>" value="1" min="1" required />
            <small class="form-text text-muted"><?= TRANS('COL_AMOUNT'); ?></small>
        </div>
    </div>
    <div class="form-group col-md-2 <?= $randomClass; ?> <?= $afterDomClass; ?>">
        <div class="resource_wrapper_fields">
            <input type="text" class="form-control " id="unitary_price<?= $randomClass; ?>" name="unitary_price[]" value="0" readonly />
            <small class="form-text text-muted"><?= TRANS('UNITARY_PRICE'); ?></small>
        </div>
    </div>
    <div class="form-group col-md-2 <?= $randomClass; ?> <?= $afterDomClass; ?>">
        <div class="resource_wrapper_fields">
            <input type="text" class="form-control row-price" id="row_price<?= $randomClass; ?>" name="row_price[]" value="0" readonly />
            <small class="form-text text-muted"><?= TRANS('TOTAL'); ?></small>
        </div>
    </div>




   
<?php
