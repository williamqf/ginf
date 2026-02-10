<?php
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
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	$_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
	exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3);

$termUpdated = isUserTermUpdated($conn, $_SESSION['s_uid']);
$termSigned = ($termUpdated ? isLastUserTermSigned($conn, $_SESSION['s_uid']) : false);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>


	<div class="container-fluid">
        <div id="divResult"></div>
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

        if (!$termUpdated) {
            echo "<br /><br /><br />";
            echo message('warning', 'Ooops!', TRANS('TERM_NOT_UPDATED'), '', '', true);
            exit;
        }
        $termInfo = getUserLastCommitmentTermInfo($conn, $_SESSION['s_uid']);

        if ($termInfo['html_doc'] == '') {
            ?>
                <input type="hidden" name="update_html_doc" id="update_html_doc" value="1">
            <?php
        } else {
            ?>
                <input type="hidden" name="update_html_doc" id="update_html_doc" value="0">
            <?php
        }

        echo $termInfo['html_doc'];

		?>
        <div class="form-group col-12 col-md-2 ">
            <input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['s_uid']; ?>">
            <input type="hidden" name="action" id="action" value="sign">
            <?php
                if (!$termSigned) {
                    ?>
                        <button id="bt_sign" name="bt_sigh" class="btn btn-primary btn-block"><?= TRANS('BT_SIGN'); ?></button>
                    <?php
                }
            ?>
        </div>
        
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(function() {


            if ($('#update_html_doc').val() == '1') {
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $.ajax({
                    url: './generate_user_term.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: $("#user_id").val(),
                        action: 'update_html_doc'
                    },
                }).done(function(response) {
                    if (!response.success) {
					    $('#divResult').html(response.message);
                    } else {
                        location.reload();
                    }
                });
            }



            $('#bt_sign').on('click', function(e){
                e.preventDefault();
                var loading = $(".loading");
                $(document).ajaxStart(function() {
                    loading.show();
                });
                $(document).ajaxStop(function() {
                    loading.hide();
                });

                $.ajax({
                    url: './generate_user_term.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: $("#user_id").val(),
                        action: $("#action").val()
                    },
                }).done(function(response) {
                    if (!response.success) {
					    $('#divResult').html(response.message);
                    } else {
                        parent.location.reload();
                    }
                });
            });




		});





	</script>
</body>

</html>