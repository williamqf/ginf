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
require_once __DIR__ . "/" . "../../includes/components/html2text-master/vendor/autoload.php";


use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 1);
$_SESSION['s_page_ocomon'] = $_SERVER['PHP_SELF'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/jquery/datetimepicker/jquery.datetimepicker.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap-select/dist/css/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_bootstrap_select.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>

	<style>
		.dataTables_filter select {
            border: 1px solid gray;
            border-radius: 4px;
            background-color: white;
            height: 25px;

            float: auto !important;
        }

        .filtro-return-date {
            border: 1px solid gray;
            border-radius: 4px;
            background-color: white;
            height: 25px;
        }
	</style>
</head>

<body>
	
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>
    <div id="divResult"></div>

	<div class="container-fluid">
		<h5 class="my-4"><i class="fas fa-book text-secondary"></i>&nbsp;<?= TRANS('TLT_ADMIN_LOAN'); ?></h5>
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

        $lendings = (isset($_GET['cod']) ? getLendings($conn, (int)$_GET['cod']) : getLendings($conn));
		$registros = count($lendings);


		$users = getUsers($conn, null, [1, 2]);

		if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

		?>
			<!-- Modal -->
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

			<button class="btn btn-sm btn-primary" id="idBtCreate" name="new"><?= TRANS("ACT_NEW"); ?></button><br /><br />
			<?php
			if ($registros == 0) {
				echo message('info', '', TRANS('MSG_NO_LOAN'), '', '', true);
			} else {

			?>
				<!-- Filtro customizado -->
				<div id="wrapper-filtro-return-date">
					<select id="filtro-return-date" class="filtro-return-date">
						<option value=""><?= TRANS('ALL_RECORDS'); ?></option>
						<option value="with_return_date"><?= TRANS('WITH_RETURN_DATE'); ?></option>
						<option value="without_return_date" selected><?= TRANS('WITHOUT_RETURN_DATE'); ?></option>
					</select>
				</div>

				<table id="table_lendings" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

					<thead>
						<tr class="header">
							<th class="line material"><?= TRANS('COL_MAT'); ?></th>
							<th class="line responsavel"><?= TRANS('OCO_RESP'); ?></th>
							<th class="line data_emprestimo"><?= TRANS('LENDING_DATE'); ?></th>
							<th class="line return_date"><?= TRANS('COL_DATE_DEV'); ?></th>
							<th class="line quem"><?= TRANS('TAKER'); ?></th>
							<th class="line departamento"><?= TRANS('DEPARTMENT'); ?></th>
							<th class="line telefone"><?= TRANS('COL_PHONE'); ?></th>
							<th class="line editar"><?= TRANS('BT_ALTER'); ?></th>
							<th class="line remover"><?= TRANS('BT_REMOVE'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($lendings as $row) {
							$material = (new \Html2Text\Html2Text(safeStrip($row['material'])))->getText();
						?>
							<tr>
								<td class="line"><?= $material ?></td>
								<td class="line"><?= $row['nome']; ?></td>
								<td class="line" data-sort="<?= dateDB($row['data_empr']); ?>"><?= dateScreen($row['data_empr'], 1); ?></td>
								<td class="line" data-sort="<?= dateDB($row['data_devol'], 1); ?>"><?= dateScreen($row['data_devol'], 1); ?></td>
								<td class="line"><?= $row['quem']; ?></td>
								<td class="line"><?= $row['local_nome']; ?></td>
								<td class="line"><?= $row['ramal']; ?></td>
								<td class="line"><button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=update&cod=<?= $row['empr_id']; ?>')"><?= TRANS('BT_EDIT'); ?></button></td>
								<td class="line"><button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['empr_id']; ?>')"><?= TRANS('REMOVE'); ?></button></td>
							</tr>

						<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} else
		if ((isset($_GET['action'])  && ($_GET['action'] == "create")) && !isset($_POST['submit'])) {

			?>
			<h6><?= TRANS('NEW_RECORD'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
					<label for="material" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MAT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="material" name="material" rows="4" required></textarea>
						<small class="form-text text-muted">
							<?= TRANS('DESCRIPTION_LENDING_HELPER'); ?>.
						</small>
					</div>

					<label for="taker" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TAKER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="taker" name="taker" placeholder="<?= TRANS('HELPER_TAKER'); ?>" autocomplete="off" required />
					</div>

					<label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
					<div class="form-group col-md-4">
						<input type="tel" class="form-control " id="phone" name="phone" placeholder="<?= TRANS('COL_PHONE'); ?>" autocomplete="off" required />
					</div>

					<label for="department" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control bs-select" id="department" name="department" required>
							<option value="" selected><?= TRANS('SEL_DEPARTMENT'); ?></option>
							<?php
							$departments = getDepartments($conn, 1, null, null, null);
							foreach ($departments as $dep) {
								?>
									<option data-subtext="<?= $dep['nickname']; ?>&nbsp;<?= $dep['unidade']; ?>" value="<?= $dep['loc_id']; ?>">
									<?= $dep['local']; ?>
									</option>
								<?php
							}
							?>
						</select>
					</div>

					<label for="authorizer" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_RESP'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control bs-select" id="authorizer" name="authorizer" required>
							<?php
							
							foreach ($users as $rowUser) {

								$userDetails = getUserInfo($conn, $rowUser['user_id'], '', ['nickname','l.local as department']);
								$arraySubtext = [];
								$arraySubtext[] = $userDetails['nickname'];
								$arraySubtext[] = $userDetails['department'];
								$subtext = implode(' - ', array_filter($arraySubtext));
								?>
									<option data-subtext="<?= $subtext; ?>" value="<?= $rowUser['user_id']; ?>"
									<?= ($rowUser['user_id'] == $_SESSION['s_uid'] ? ' selected' : ''); ?>
									><?= $rowUser['nome']; ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<label for="borrow_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LENDING_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="borrow_date" name="borrow_date" value="<?= date("d/m/Y"); ?>" autocomplete="off" required />
					</div>

					<label for="return_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_DATE_DEV'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="return_date" name="return_date" placeholder="<?= TRANS('COL_DATE_DEV'); ?>" autocomplete="off" />
					</div>

					<div class="row w-100">
						<div class="form-group col-md-8 d-none d-md-block">
						</div>
						<div class="form-group col-12 col-md-2 ">

							<input type="hidden" name="action" value="create">
							<button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
						</div>
						<div class="form-group col-12 col-md-2">
							<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
						</div>
					</div>

				</div>
			</form>
		<?php
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "update") && empty($_POST['submit'])) {

			$row = $lendings;
		?>
			<h6><?= TRANS('COL_EDIT_LOAN'); ?></h6>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<div class="form-group row my-4">
					<label for="material" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_MAT'); ?></label>
					<div class="form-group col-md-10">
						<textarea class="form-control " id="material" name="material" rows="4" required><?= $row['material']; ?></textarea>
						<small class="form-text text-muted">
							<?= TRANS('DESCRIPTION_LENDING_HELPER'); ?>.
						</small>
					</div>

					<label for="taker" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('TAKER'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="taker" name="taker" value="<?= $row['quem']; ?>" placeholder="<?= TRANS('HELPER_TAKER'); ?>" autocomplete="off" required />
					</div>

					<label for="phone" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_PHONE'); ?></label>
					<div class="form-group col-md-4">
						<input type="tel" class="form-control " id="phone" name="phone" value="<?= $row['ramal']; ?>" placeholder="<?= TRANS('COL_PHONE'); ?>" autocomplete="off" required />
					</div>

					<label for="department" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('DEPARTMENT'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control bs-select" id="department" name="department" required>
							<option value=""><?= TRANS('SEL_DEPARTMENT'); ?></option>
							<?php

								$departments = getDepartments($conn, 1, null, null, null);
								foreach ($departments as $dep) {
									?>
										<option data-subtext="<?= $dep['nickname']; ?>&nbsp;<?= $dep['unidade']; ?>" value="<?= $dep['loc_id']; ?>"
										<?= ($dep['loc_id'] == $row['local']) ? ' selected': '' ?>
										>
										<?= $dep['local']; ?>
										</option>
									<?php
								}
							?>
						</select>
					</div>


					<label for="authorizer" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OCO_RESP'); ?></label>
					<div class="form-group col-md-4">
						<select class="form-control bs-select" id="authorizer" name="authorizer" required>
							<?php
							foreach ($users as $rowUser) {

								$userDetails = getUserInfo($conn, $rowUser['user_id'], '', ['nickname','l.local as department']);
								$arraySubtext = [];
								$arraySubtext[] = $userDetails['nickname'];
								$arraySubtext[] = $userDetails['department'];
								$subtext = implode(' - ', array_filter($arraySubtext));
								?>
									<option data-subtext="<?= $subtext; ?>" value="<?= $rowUser['user_id']; ?>"
									<?= ($rowUser['user_id'] == $row['responsavel'] ? ' selected' : ''); ?>
									><?= $rowUser['nome']; ?></option>
								<?php
							}
							?>
						</select>
					</div>


					<label for="borrow_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('LENDING_DATE'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="borrow_date" name="borrow_date" value="<?= dateScreen($row['data_empr'], 1); ?>" autocomplete="off" required />
					</div>

					<label for="return_date" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('COL_DATE_DEV'); ?></label>
					<div class="form-group col-md-4">
						<input type="text" class="form-control " id="return_date" name="return_date" value="<?= dateScreen($row['data_devol'], 1); ?>" placeholder="<?= TRANS('COL_DATE_DEV'); ?>" autocomplete="off" />
					</div>

					<input type="hidden" name="cod" value="<?= (int)$_GET['cod']; ?>">
					<input type="hidden" name="action" value="update">

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
						<button type="submit" id="idSubmit" name="submit" value="update" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
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
    <script src="../../includes/components/jquery/datetimepicker/build/jquery.datetimepicker.full.min.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

			
			$.fn.selectpicker.Constructor.BootstrapVersion = '4';
			$('.bs-select').selectpicker({
				/* placeholder */
				title: "<?= TRANS('SEL_SELECT', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control",
			});
			
			
			// Função de filtro customizada para a coluna return_date
			$.fn.dataTable.ext.search.push(
				function(settings, data, dataIndex) {
					var filtroReturnDate = $('#filtro-return-date').val();
					
					if (filtroReturnDate === '') {
						return true;
					}
					
					var table = $('#table_lendings').DataTable();
					var returnDateColumnIndex = -1;
					
					table.columns().every(function(index) {
						var column = this;
						var headerCell = $(column.header());
						
						if (headerCell.hasClass('return_date')) {
							returnDateColumnIndex = index;
							return false;
						}
					});
					
					if (returnDateColumnIndex === -1) {
						return true;
					}
					
					var returnDateValue = data[returnDateColumnIndex];
					var isEmpty = !returnDateValue || 
								returnDateValue.trim() === '' || 
								returnDateValue === '&nbsp;' || 
								returnDateValue === '-' ||
								returnDateValue.toLowerCase() === 'null';
					
					if (filtroReturnDate === 'without_return_date') {
						return isEmpty;
					} else if (filtroReturnDate === 'with_return_date') {
						return !isEmpty;
					}
					
					return true;
				}
			);


			var table = $('#table_lendings').DataTable({
				paging: true,
				deferRender: true,
				// dom: '<"row"<"col-sm-6"l><"col-sm-6"<"filtro-customizado-wrapper">>>' + 
				// 	'<"row"<"col-sm-12"<"table-responsive"t>>>' +
				// 	'<"row"<"col-sm-5"i><"col-sm-7"p>>',
				dom: "<'row'<'#div-filtro-return-date.col-sm-12 col-md-4'><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

				columnDefs: [{
					orderable: false,
					targets: ['editar', 'remover']
				}],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				},
				// Callback executado após a inicialização
				"initComplete": function() {
					// Mover o filtro customizado para o wrapper após inicialização
        			$('#filtro-return-date').appendTo('#div-filtro-return-date');
					this.api().draw();
				}
			});

			// Event listener para o filtro
			$('#filtro-return-date').on('change', function() {
				table.draw(); // Redesenha a tabela aplicando o filtro
			});

            /* Idioma global para os calendários */
            $.datetimepicker.setLocale('pt-BR');
            
            /* Calendários de início e fim do período */
            $('#borrow_date').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        maxDate: $('#return_date').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });
            $('#return_date').datetimepicker({
                format: 'd/m/Y',
                onShow: function(ct) {
                    this.setOptions({
                        minDate: $('#borrow_date').datetimepicker('getValue')
                    })
                },
                timepicker: false
            });

			$('#idBtCreate').on("click", function() {
				$('#idLoad').css('display', 'block');
				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=create';
				$(location).prop('href', url);
			});


			$('#idSubmit').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$("#idSubmit").prop("disabled", true);
				$.ajax({
					url: './lendings_process.php',
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


		});


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
				url: './lendings_process.php',
				method: 'POST',
				data: {
					cod: id,
					action: 'delete'
				},
				dataType: 'json',
			}).done(function(response) {
				
				if (!response.success) {
					$('#divResult').html(response.message);
					$('input, select, textarea').removeClass('is-invalid');
					if (response.field_id != "") {
						$('#' + response.field_id).focus().addClass('is-invalid');
					}
				} else {
					var url = '<?= $_SERVER['PHP_SELF'] ?>';
					$(location).prop('href', url);
					return true;
				}
				
				
			});
			return false;
		}

	</script>
</body>

</html>