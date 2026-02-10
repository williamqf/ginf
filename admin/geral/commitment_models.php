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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

$_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];

$envVarsContext = 2; //1: email, 2: termos de responsabilidade
$model_types = [
    1 => TRANS('COMMITMENT_TERM'),
    2 => TRANS('TRAFFIC_FORM')
];


/* Não podem ser excluídos e também não podem ser vinculados a clientes e unidades */
$system_defaults_ids = [1,2];

$clients = getClients($conn);
$units = getUnits($conn);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/suneditor/node_modules/suneditor/dist/css/suneditor.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/suneditor/node_modules/suneditor/src/assets/css/suneditor-contents.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
    
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-file-signature text-secondary"></i>&nbsp;<?= TRANS('RESPONSIBILITY_STATEMENTS'); ?></h4>
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
        
        if (!isset($_GET['cod'])) {
            $commitment_models = getCommitmentModels($conn);
        } else {
            $commitment_models = getCommitmentModels($conn, (int)$_GET['cod'])[0];
        }
		$registros = count($commitment_models);

		if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {


            ?>
                <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-light">
							<h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<?= TRANS('CONFIRM_REMOVE'); ?> <span class="j_param_id"></span>?
						</div>
						<div class="modal-footer bg-light">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
							<button type="button" id="deleteButton" class="btn"><?= TRANS('BT_OK'); ?></button>
						</div>
					</div>
				</div>
			</div>

			<button class="btn btn-sm btn-primary" id="idBtIncluir" name="new"><?= TRANS("ACT_NEW"); ?></button><br /><br />
            <?php



			if ($registros == 0) {
				echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
			} else {

			?>
				<table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

					<thead>
						<tr class="header">
							<td class="line issue_type"><?= TRANS('COL_TYPE'); ?></td>
							<td class="line issue_type"><?= TRANS('CLIENT'); ?></td>
							<td class="line issue_type"><?= TRANS('COL_UNIT'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
							<td class="line remover"><?= TRANS('BT_REMOVE'); ?></td>

						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($commitment_models as $row) {

                            $disabled = "";
                            if (in_array($row['id'], $system_defaults_ids)) {
                                $disabled = " disabled";
                            }

						?>
							<tr>
								<td class="line"><?= $model_types[$row['type']]; ?></td>
								<td class="line"><?= $row['nickname']; ?></td>
								<td class="line"><?= $row['inst_nome']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&cod=<?= $row['id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
                                <td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['id']; ?>')" <?= $disabled; ?>><?= TRANS('REMOVE'); ?></button></td>
							</tr>

						<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && empty($_POST['submit'])) {

			$row = $commitment_models;

            $disabled = "";
            if (in_array($row['id'], $system_defaults_ids)) {
                $disabled = " disabled";
            }

		    ?>
			<h6><?= TRANS('BT_EDIT'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">

                    <label for="commitment_type" class="col-md-2 col-form-label  text-md-right"><?= TRANS('COL_TYPE'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control bs-select term-info" id="commitment_type" name="commitment_type" <?= $disabled; ?>>
                            <?php
                                foreach ($model_types as $key => $value) {
                                    ?>
                                    <option value="<?= $key; ?>" <?= (!empty($row['type']) && $row['type'] == $key ? ' selected' : ''); ?>><?= $value; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="client" class="col-sm-2 col-md-2 col-form-label  text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select term-info" id="client" name="client" <?= $disabled; ?>>
							<option value=""><?= TRANS('SEL_SELECT'); ?></option>
                            <?php
                                foreach ($clients as $client) {
                                    ?>
                                        <option value="<?= $client['id']; ?>" <?= (!empty($row['client_id']) && $row['client_id'] == $client['id'] ? ' selected' : ''); ?>
                                        ><?= $client['nickname']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="unit" class="col-sm-2 col-md-2 col-form-label  text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select term-info" id="unit" name="unit" <?= $disabled; ?>>
                        <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                            <?php
                                foreach ($units as $unit) {
                                    ?>
                                        <option value="<?= $unit['inst_cod']; ?>" <?= (!empty($row['unit_id']) && $row['unit_id'] == $unit['inst_cod'] ? ' selected' : ''); ?>
                                        ><?= $unit['inst_nome']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
					
					<label for="variables" class="col-md-2 col-form-label col-form-label-sm text-md-right"></label>
					<div class="form-group col-md-10">
						<div class="accordion" id="accordionVariables">
							<div class="card">
								<div class="card-header" id="headingOne">
									<h2 class="mb-0">
										<button class="btn btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" onClick="blur();">
										<i class="fas fa-percentage text-secondary"></i>&nbsp;<?= TRANS('OPT_ENVIRON_AVAIL'); ?>&nbsp;<i class="fas fa-percentage text-secondary"></i>
										</button>
									</h2>
								</div>

								<div id="collapseOne" class="collapse " aria-labelledby="headingOne" data-parent="#accordionVariables">
									<div class="card-body "> <!-- bg-light -->
										<?= nl2br(getEnvVars($conn, $envVarsContext)); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="w-100"></div>



                    <label for="html_content" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('CONTENT'); ?></label>
                    <div class="form-group col-md-10" id="suneditor">
                        <textarea name="html_content" id="html_content" class="form-control"><?= $row['html_content']; ?></textarea>
                    </div>

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
                        <input type="hidden" name="cod" value="<?= (int)$_GET['cod']; ?>">
                        <input type="hidden" name="db_unit" id="db_unit" value="<?= $row['unit_id']; ?>">
                        <input type="hidden" name="action" id="action" value="edit">
						<button type="submit" id="idSubmit" name="submit" value="edit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
					</div>

				</div>
			</form>
		<?php
		} elseif ((isset($_GET['action']) && $_GET['action'] == "new") && empty($_POST['submit'])) {
            ?>
                <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">

                    <label for="commitment_type" class="col-md-2 col-form-label  text-md-right"><?= TRANS('COL_TYPE'); ?></label>
					<div class="form-group col-md-10">
						<select class="form-control bs-select term-info" id="commitment_type" name="commitment_type" >
                            <?php
                                foreach ($model_types as $key => $value) {
                                    ?>
                                    <option value="<?= $key; ?>"><?= $value; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="client" class="col-sm-2 col-md-2 col-form-label  text-md-right"><?= TRANS('CLIENT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select term-info" id="client" name="client" >
							<option value=""><?= TRANS('SEL_SELECT'); ?></option>
                            <?php
                                foreach ($clients as $client) {
                                    ?>
                                        <option value="<?= $client['id']; ?>"
                                        ><?= $client['nickname']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <label for="unit" class="col-sm-2 col-md-2 col-form-label  text-md-right"><?= TRANS('COL_UNIT'); ?></label>
                    <div class="form-group col-md-10">
                        <select class="form-control bs-select term-info" id="unit" name="unit" >
                        <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                            <?php
                                foreach ($units as $unit) {
                                    ?>
                                        <option value="<?= $unit['inst_cod']; ?>"
                                        ><?= $unit['inst_nome']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

					<label for="variables" class="col-md-2 col-form-label col-form-label-sm text-md-right"></label>
					<div class="form-group col-md-10">
						<div class="accordion" id="accordionVariables">
							<div class="card">
								<div class="card-header" id="headingOne">
									<h2 class="mb-0">
										<button class="btn btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" onClick="blur();">
										<i class="fas fa-percentage text-secondary"></i>&nbsp;<?= TRANS('OPT_ENVIRON_AVAIL'); ?>&nbsp;<i class="fas fa-percentage text-secondary"></i>
										</button>
									</h2>
								</div>

								<div id="collapseOne" class="collapse " aria-labelledby="headingOne" data-parent="#accordionVariables">
									<div class="card-body"> <!--  -->
										<?= nl2br(getEnvVars($conn, $envVarsContext)); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="w-100"></div>
					
                    <label for="html_content" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('CONTENT'); ?></label>
                    <div class="form-group col-md-10" id="suneditor">
                        <textarea name="html_content" id="html_content" class="form-control"></textarea>
                    </div>

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
                        <input type="hidden" name="action" id="action" value="new">
						<button type="submit" id="idSubmit" name="submit" value="edit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
					</div>

				</div>
			</form>
            <?php

        }
		?>
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
    <script src="../../includes/components/suneditor/node_modules/suneditor/dist/suneditor.min.js"></script>
    <script src="../../includes/components/suneditor/node_modules/suneditor/src/lang/pt_br.js"></script>
	<script src="../../includes/javascript/format_bar.js"></script>
	<script type="text/javascript">
		$(function() {

			$('#table_lists').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
					searchable: false,
					orderable: false,
					targets: ['editar']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

            $('.bs-select').selectpicker({
				/* placeholder */
				title: "<?= TRANS('SEL_SELECT', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				
				style: "",
				styleBase: "form-control input-select-multi",
			});


            $('input, select, textarea').on('change', function() {
				$(this).removeClass('is-invalid');
			});

			loadUnits();
            $("#client").on('change', function() {
				loadUnits();
			});

            $('#idBtIncluir').on("click", function() {
				$('#idLoad').css('display', 'block');
				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=new';
				$(location).prop('href', url);
			});


            var editor = render_format_bar('html_content', 400, 'advanced');


			$('#idSubmit').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

                $(".term-info").each(function() {
                    $(this).prop('disabled', false);
                });

				$("#idSubmit").prop("disabled", true);
                editor.save();
				$.ajax({
					url: './commitment_models_process.php',
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
						$("#idSubmit").prop("disabled", false);
					} else {
						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');
						$("#idSubmit").prop("disabled", false);
						var url = '<?= $_SERVER['PHP_SELF'] ?>';
						$(location).prop('href', url);
						return false;
					}
				});
				return false;
			});

			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});
		});



        function loadUnits(targetId = 'unit') {

            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: '../../ocomon/geral/get_units_by_client.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    client: $("#client").val()
                },
            }).done(function(data) {
                $('#' + targetId).empty();
                if (Object.keys(data).length > 1) {
                    $('#' + targetId).append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
                }
                $.each(data, function(key, data) {
                    $('#' + targetId).append('<option value="' + data.inst_cod + '">' + data.inst_nome + '</option>');
                });

                $('#' + targetId).selectpicker('refresh');
                if ($('#db_unit').val() != '') {
                    $('#' + targetId).selectpicker('val', $('#db_unit').val());
                    $('#' + targetId).selectpicker('refresh');
                }
            });
        }

        function confirmDeleteModal(id) {
			$('#deleteModal').modal();
			$('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
		}

		function deleteData(id) {

			var loading = $(".loading");
			$(document).ajaxStart(function() {
				loading.show();
			});
			$(document).ajaxStop(function() {
				loading.hide();
			});

			$.ajax({
				url: './commitment_models_process.php',
				method: 'POST',
				data: {
					cod: id,
					action: 'delete'
				},
				dataType: 'json',
			}).done(function(response) {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
				return false;
			});
			return false;
			// $('#deleteModal').modal('hide'); // now close modal
		}


	</script>
</body>

</html>