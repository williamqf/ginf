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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 3, 1);

$currentUrl = $_SERVER['PHP_SELF'];
/* Alterar o basename para ficar compatível com o data-app para marcação no menu lateral */
$dataAppUrl = str_replace(basename($currentUrl), 'ticket_add.php', $currentUrl);
$_SESSION['s_page_ocomon'] = $dataAppUrl;


$onlyOpen = $_SESSION['s_nivel'] == 3;

$jumpToChooseTicketType = false;

if ($onlyOpen) {

	$basic_users_can_open_as_others = getConfigValue($conn, 'ALLOW_BASIC_USERS_REQUEST_AS_OTHERS') ?? 0;
	/* Buscar o departamento do usuário */
	$department = getUserDepartment($conn, $_SESSION['s_uid']);
	$jumpToChooseTicketType = (empty($department) || !$basic_users_can_open_as_others ? true : false);
}

$params = "";


$paramPrefix = "amp;";
if (isset($_GET) && !empty($_GET)) {
	foreach ($_GET as $key => $value) {
		if (strpos($key, $paramPrefix) !== false) {
			$newKey = str_replace($paramPrefix, "", $key);
			$_GET[$newKey] = $value;
		}
	}
}


// if (isset($_GET) && !empty($_GET) && !$onlyOpen) {
if (isset($_GET) && !empty($_GET) && !$jumpToChooseTicketType) {

	$_GET = filter_input_array(INPUT_GET, FILTER_DEFAULT);
	$params = "&" . http_build_query($_GET, "", "&");
}



// if ($onlyOpen) {
if ($jumpToChooseTicketType) {
	$requester["requester"] = $_SESSION['s_uid'];
	$params = "&" . http_build_query($requester, "", "&");
	header("Location: ./choose_ticket_type.php?" . $params);
	return;
}


// $users = getUsers($conn);
$users = (!$onlyOpen ? getUsers($conn, null, [1,2,3]) : getUsers($conn, null, [3], null, null, null, $department));


$classMarginTop = "mt-4";

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
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>

	<style>
		li.list_specs {
			line-height: 1.5em;
		}

		
	</style>
</head>

<body>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>


	<div class="container-fluid">
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

		<div class="modal fade show d-block" id="modalChooseRequester" data-backdrop="static" data-keyboard="false" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="myModalChoose" aria-hidden="true">
        	<div class="modal-dialog modal-xl" role="document">
                <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
                    <div class="modal-content">
                        <div id="divResult"></div>
                        <div class="modal-header text-center bg-light">

                            <h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-user"></i>&nbsp;<?= TRANS('REQUESTER'); ?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    
                        <div class="row mx-2 <?= $classMarginTop; ?>">

                            <div class="form-group col-sm-12 col-md-12">


                                <select class="form-control bs-select" name="requester" id="requester">
                                    <?php
                                    foreach ($users as $user) {

                                        $subtext = $user['login'];
                                        ?>
                                        <option data-subtext="<?= $subtext; ?>" value="<?= $user['user_id']; ?>"
                                        <?= ($_SESSION['s_uid'] == $user['user_id'] ? " selected" : ""); ?>
                                        ><?= $user['nome']; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted"><?= TRANS('HELPER_CHOOSE_REQUESTER'); ?></small>
                            </div>

                            <input type="hidden" name="params" id="params" value="<?= $params; ?>" />


                            <div class="form-group col-sm-12 col-md-12" id="user_info">
                                <!-- <div class="row mx-2" id="user_info"></div> -->
                            </div>
                        </div>
                        <!-- <div class="row mx-2 mt-4" id="prob_description"></div> -->


                        <div class="modal-footer d-flex justify-content-end bg-light mt-0">
                            <button class="btn btn-primary nowrap" id="continue" name="continue"><?= TRANS('CONTINUE'); ?></button>
                            <button id="cancelOpening" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?= TRANS('BT_CANCEL'); ?></button>
                        </div>
                    </div>
				</form>
        	</div>
    	</div>

		<?php
		if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
			echo $_SESSION['flash'];
			$_SESSION['flash'] = '';
		}

		?>
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<script type="text/javascript">
		
        $(function() {

            $('.bs-select').selectpicker({
				/* placeholder */
				title: "<?= TRANS('SEL_SELECT', '', 1); ?>",
                showSubtext: true,
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});

            loadUserInfo ();

            $('#modalChooseRequester').modal();
			
			$('#modalChooseRequester').on('shown.bs.modal', function() {
				$('#requester').focus();
			});
			
			$('#modalChooseRequester').on('hidden.bs.modal', function() {
				window.parent.history.back();
			});

            $('#requester').on('change', function() {
                loadUserInfo ($(this).val());
            });



            $('#continue').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './choose_requester_process.php',
					method: 'POST',
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						$('input, select, textarea').removeClass('is-invalid');
						if (response.field_id != "") {
							$('#' + response.field_id).focus().addClass('is-invalid');
						}
					} else {
						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');
						
						let params = 'requester=' + response.requester + response.params;
						let url = "./choose_ticket_type.php?" + params;
						
						$(location).prop('href', url);
						return true;
					}
				});
				return false;
			});



        });


        /* Funções */
        function loadUserInfo (userId = ''){
			
            if (userId == ''){
                userId = $('#requester').val();
            }
            
            var loading = $(".loading");
			$(document).ajaxStart(function() {
				loading.show();
			});
			$(document).ajaxStop(function() {
				loading.hide();
			});

			$.ajax({
				url: './get_userinfo.php',
				method: 'POST',
                dataType: 'json',
				data: {
					user: userId,
				},
			}).done(function(response) {

                let html = response.html;
				$('#user_info').empty().html(html);
				return true;
			});
		}



	</script>
</body>

</html>