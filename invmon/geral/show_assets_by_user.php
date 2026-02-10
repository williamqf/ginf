<?php 
 /*                        Copyright 2023 Flávio Ribeiro

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
  */session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	$_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
	exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);



?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-exchange-alt text-secondary"></i>&nbsp;<?= TRANS('USER_ALLOCATION_HISTORY'); ?></h4>
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

		<?php
		if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
			echo $_SESSION['flash'];
			$_SESSION['flash'] = '';
        }



$user_id = (isset($_GET['user_id']) && !empty($_GET['user_id']) ? (int)$_GET['user_id'] : '');

if (empty($user_id)) {
    echo message('danger', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '', '', 1);
    return;
}


/* Informações sobre o usuário */
$userInfo = getUserInfo($conn, $user_id);
if (!count($userInfo)) {
    echo message('danger', 'Ooops!', TRANS('MSG_SOMETHING_GOT_WRONG'), '', '', 1);
    return;
}

unset($userInfo['password']);
unset($userInfo['hash']);

$changes = getAssetsFromUserChanges($conn, $user_id);
$hasChanges = count($changes);


$concatUserInfo[] = $userInfo['nome'];
$concatUserInfo[] = $userInfo['login'];
$concatUserInfo[] = $userInfo['email'];
$concatUserInfo[] = $userInfo['fone'];

$userInfoText = implode(' - ', array_filter($concatUserInfo));
?>
    <h6><?= TRANS('FIELD_USER'); ?>: <?= $userInfoText; ?></h6>
    <h6><?= TRANS('CLIENT'); ?>: <?= $userInfo['nickname']; ?></h6>
    <h6><?= TRANS('SERVICE_AREA'); ?>: <?= $userInfo['area_nome']; ?></h6>

    <?php
        if (!$hasChanges) {
            echo message('info', 'Ooops!', TRANS('NO_ASSETS_LINKED_RECORDS_FOUND_FOR_THIS_USER'), '', '', 1);
            return;
        }
    ?>

    <table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

        <thead>
            <tr class="header">
                <td class="line col_model"><?= TRANS('COL_TYPE'); ?></td>
                <td class="line col_model"><?= TRANS('CLIENT'); ?></td>
                <td class="line col_model"><?= TRANS('COL_UNIT'); ?></td>
                <td class="line col_model"><?= TRANS('ASSET_TAG'); ?></td>
                <td class="line col_model"><?= TRANS('ALLOCATED_DATE'); ?></td>
                <td class="line col_model"><?= TRANS('REMOVED_DATE'); ?></td>
				<td class="line author"><?= TRANS('RECORDED_BY'); ?></td>
            </tr>
        </thead>
        <tbody>
        <?php
            foreach ($changes as $change) {
                $currentAsset = null;
                if ($change['is_current'] == 1) {
                    $currentAsset = '<span class="badge badge-info p-2">'. TRANS('IN_USE') .'</span>';
                }
                $concatAssetInfo = [];
                $concatAssetInfo[] = $change['tipo_nome'];
                $concatAssetInfo[] = $change['fab_nome'];
                $concatAssetInfo[] = $change['marc_nome'];
                $assetInfoText = implode(' - ', array_filter($concatAssetInfo));
                
                ?>
                <tr>
                    <td class="line"><?= $assetInfoText; ?></td>
                    <td class="line"><?= $change['cliente'] ?></td>
                    <td class="line"><?= $change['inst_nome'] ?></td>
                    <td class="line"><?= $change['comp_inv'] ?></td>
                    <td class="line" data-sort="<?= $change['created_at']; ?>"><?= dateScreen($change['created_at']); ?></td>
                    <td class="line" data-sort="<?= $change['updated_at']; ?>"><?= $currentAsset ?? dateScreen($change['updated_at']); ?></td>
                    <td class="line"><?= $change['autor']; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

        <div class="row w-100"></div><br/>
        <div class="row">
            <div class="col-md-10 d-none d-md-block"></div>
            <div class="col-12 col-md-2">
                <button type="reset" class="btn btn-secondary btn-block close-or-return"><?= TRANS('BT_RETURN'); ?></button>
            </div>
        </div>
    
    
    </div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/plentz-jquery-maskmoney/dist/jquery.maskMoney.min.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script type="text/javascript">
    
    $(() => {
        
        if ($('#table_lists').length > 0) {
            $('#table_lists').DataTable({
                paging: true,
                deferRender: true,
                
                "language": {
                    "url": "../../includes/components/datatables/datatables.pt-br.json"
                }
            });
        }
        
        closeOrReturn ();

    });

    function closeOrReturn (jumps = 1) {
        buttonValue ();
        $('.close-or-return').on('click', function(){
            if (isPopup()) {
                window.close();
            } else {
                window.history.back(jumps);
            }
        });
    }

    function buttonValue () {
        if (isPopup()) {
            $('.close-or-return').text('<?= TRANS('BT_CLOSE'); ?>');
        }
    }


    </script>
</body>
</html>