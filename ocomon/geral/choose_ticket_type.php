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

$pre_filters_string = "";
$pre_filters_array = [];

/* Configurações globais */
$config = getConfig($conn);

$authorInfo = getUserInfo($conn, $_SESSION['s_uid']);

/* Informações sobre a área solicitante - área do usuário logado */
// $areaInfo = getAreaInfo($conn, $_SESSION['s_area']);
$areaInfo = getAreaInfo($conn, $authorInfo['area_id']);




/* Checa se há configuração de pré-filtros na área solicitante e caso não exista consulta também na configuração global */
if (!empty($areaInfo['use_own_config_cat_chain'] && $areaInfo['use_own_config_cat_chain'])) {
	$pre_filters_string = (!empty($areaInfo['sis_cat_chain_at_opening']) ? $areaInfo['sis_cat_chain_at_opening'] : "");
} elseif (!empty($config['conf_cat_chain_at_opening'])) {
	$pre_filters_string = $config['conf_cat_chain_at_opening'];
}

$totalPreFilters = 0;
if (!empty($pre_filters_string)) {
	$pre_filters_array = explode(',', (string)$pre_filters_string);
	$totalPreFilters = count($pre_filters_array);
}


/* Debug */
// if ($_SESSION['s_usuario'] == 'flaviorib@gmail.com') {
// 	var_dump([
// 		'pre_filters_string' => $pre_filters_string,
// 		'pre_filters_array' => $pre_filters_array,
// 		'totalPreFilters' => $totalPreFilters
// 	]);
// }


/* Inicializando os sufixos para as tabelas das categorias */
$categories = [];

$pre_filters_json = json_encode($pre_filters_array);

$categories_json = '{}';

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

/* Se a requisição for para subchamado */
if (isset($_GET) && !empty($_GET)) {

	$_GET = filter_input_array(INPUT_GET, FILTER_DEFAULT);
	$params = "&" . http_build_query($_GET, "", "&");
}


if (!isset($_GET) || empty($_GET['requester'])) {

	header("Location: ./choose_requester.php?" . $params);
	return;
}

if ($_SESSION['s_opening_mode'] == 1) {
	/* Mode de abertura clássico - padrão */
	header("Location: ./ticket_add.php?" . $params);
	return;
}


$requester = (int)$_GET['requester'];
$requester_info = getUserInfo($conn, $requester);
unset($requester_info['password']);
unset($requester_info['hash']);


if ($_SESSION['s_nivel'] == 3 && $requester != $_SESSION['s_uid']) {

	$author_department = getUserDepartment($conn, $_SESSION['s_uid']);
	$requester_department = getUserDepartment($conn, $requester);
	
	if (empty($author_department) || $author_department != $requester_department) {
		// header("Location: ./choose_requester.php?" . $params);
		header("Location: ./choose_requester.php");
		return;
	}
}

// $classMarginTop = "mt-4";
$classMarginTop = "mt-0";

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
		.container-switch {
			position: relative;
		}

		.switch-next-checkbox {
			position: absolute;
			top: 0;
			left: 130px;
			z-index: 1;
		}

		.bootstrap-select .dropdown-menu { 
			max-width: 100% !important; 
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

		<div class="modal fade show d-block" id="modalChooseTicketType" data-backdrop="static" data-keyboard="false" tabindex="-1" style="z-index:2001!important" role="dialog" aria-labelledby="myModalChoose" aria-hidden="true">
			<div class="modal-dialog modal-xl" role="document">
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
            	<div class="modal-content" id="modalContent">
					<div id="divResult"></div>
					<div class="modal-header text-center bg-light">

						<h4 class="modal-title w-100 font-weight-bold text-secondary"><i class="fas fa-ticket-alt"></i>&nbsp;<?= TRANS('CHOOSE_TICKET_TYPE'); ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="row mx-2 mt-4">
						<div class="form-group col-sm-12 col-md-12 ">
						<?= message('info', '', TRANS('REQUESTER') . ": <strong>" . $requester_info['nome'] . "</strong>", '', '', true, 'fas fa-user'); ?>
						</div>
					</div>


					<?php
					$i = 0;
					$nextFilterSufix = "";
					$prevFilterSufix = "";
					$lastFilter = "";
					/* Montagem dos pré-filtros */
					foreach ($pre_filters_array as $key => $filter) {

						if (array_key_exists($key + 1, $pre_filters_array)) {
							$nextFilterSufix = $pre_filters_array[$key + 1];
						} else {
							$nextFilterSufix = "issue_type";
						}

						if (array_key_exists($key - 1, $pre_filters_array)) {
							$prevFilterSufix = $pre_filters_array[$key - 1];
						} else {
							$prevFilterSufix = "";
						}
					?>
						<div class="row mx-2 <?= ($i == 0 ? $classMarginTop : "") ?>">
							<div class="form-group col-sm-12 col-md-12">
								<select class="form-control bs-select prefilter" name="filter_<?= $filter; ?>" id="filter_<?= $filter; ?>" data-sufix="<?= $filter; ?>" data-next="<?= $nextFilterSufix; ?>" data-prev="<?= $prevFilterSufix; ?>" <?= ($i != 0 ? " disabled" : "") ?> data-pos="<?= $key; ?>">
									<option value=""><?= TRANS('SEL_SELECT') . "&nbsp;" ?><?= $config['conf_prob_tipo_' . $filter]; ?></option>
									<!-- O carregamento será feito via ajax -->
								</select>
								<small class="form-text text-muted"><?= $config['conf_prob_tipo_' . $filter]; ?></small>
							</div>
						</div>
					<?php
						$lastFilter = $filter;
						$i++;
					}
					?>


					<div class="row mx-2 <?= (empty($pre_filters_string) ? $classMarginTop : "") ?>">

						<div class="form-group col-sm-12 col-md-12">
							<select class="form-control bs-select" name="issue_type" id="issue_type" data-sufix="" data-prev="<?= $lastFilter; ?>" disabled>
								<option value=""><?= TRANS('SEL_SELECT'); ?></option>
								<!-- O carregamento será feito via ajax -->
							</select>
							<small class="form-text text-muted"><?= TRANS('HELPER_CHOOSE_TICKET_TYPE'); ?></small>
						</div>

						<input type="hidden" name="params" id="params" value="<?= $params; ?>" />

						<div class="form-group col-sm-12 col-md-12">
							<div class="row mx-2" id="possible_areas"></div>
						</div>
					</div>
					<!-- <div class="row mx-2 mt-4" id="prob_description"></div> -->
					<div class="form-group col-sm-12 col-md-12" id="prob_description"></div>



					<div class="modal-footer d-flex justify-content-end bg-light mt-0">
						<!-- <button class="btn btn-warning nowrap" id="empty" name="empty">Limpar categorias</button>
						<button class="btn btn-info nowrap" id="debug" name="debug"><?= TRANS('DEBUG'); ?></button> -->
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
		<input type="hidden" name="categories_json" id="categories_json" value="" />
		<input type="hidden" name="pre_filters_json" id="pre_filters_json" value="" />
	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<script type="text/javascript">
		$(function() {

			$('#categories_json').val('<?= $categories_json; ?>');
			$('#pre_filters_json').val('<?= $pre_filters_json; ?>');

			let pre_filters_json = JSON.parse('<?= $pre_filters_json; ?>');
			// console.log('pre_filters_json: ' + pre_filters_json);


			$.fn.selectpicker.Constructor.BootstrapVersion = '4';
			$('#issue_type').selectpicker({
				/* placeholder */
				title: "<?= TRANS('CHOOSE_TICKET_TYPE', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});

			$('.bs-select').selectpicker({
				/* placeholder */
				title: "<?= TRANS('SEL_SELECT', '', 1); ?>",
				liveSearch: true,
				liveSearchNormalize: true,
				liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
				noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
				style: "",
				styleBase: "form-control ",
			});


			// checar e o pre_filters_json está vazio
			if (pre_filters_json.length > 0) {
				for (let i in pre_filters_json) {
					if (i == 0) {
						/* Carrega a primeira seleção de categorias disponíveis */
						loadUsedCategories(pre_filters_json[i], i);
					}

					$('#filter_' + pre_filters_json[i]).on('change', (function() {

						categoriesControl($(this).attr('data-sufix'));
						categoriesControl($(this).attr('data-sufix'));

					}));
				}
			} else {
				loadPossibleTypesOfIssues();
			}



			
			




			$(function() {
				$('[data-toggle="popover"]').popover({
					html: true
				})
			});

			$('.popover-dismiss').popover({
				trigger: 'focus'
			});


			

			$('#modalChooseTicketType').modal();

			$('#modalChooseTicketType').on('shown.bs.modal', function() {
				// var focusable = document.querySelectorAll('button, input, select, [tabindex]:not([tabindex="-1"])');
				var focusable = document.querySelectorAll('input, [tabindex]:not([tabindex="-1"])');
				var firstFocusable = focusable[0];
				// var lastFocusable = focusable[focusable.length - 1];

				$(firstFocusable).focus();
			});

			$('#modalChooseTicketType').on('hidden.bs.modal', function() {
				window.parent.history.back();
			});


			$('#issue_type').on('change', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './choose_ticket_type_process.php',
					method: 'POST',
					// data: {
					// 	issue_type: $('#issue_type').val(),
					// 	params: $('#params').val()
					// },
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						$('input, select, textarea').removeClass('is-invalid');
						if (response.field_id != "") {
							$('#' + response.field_id).focus().addClass('is-invalid');
						}
						// $("#idSubmit").prop("disabled", false);
					} else {
						$('#divResult').html('');
						$('input, select, textarea').removeClass('is-invalid');

						if (response.prob_descricao != "") {
							$("#prob_description").addClass("form-group col-md-12");
						} else {
							$("#prob_description").removeClass("form-group col-md-12");
							$("#prob_description").empty();
						}
						$('#prob_description').html(response.description);
						
						let html = '<?= TRANS('SOLVER_AREA_INDICATED'); ?>:&nbsp;<b>' + response.area_to_open + '</b>';
						
						$('#possible_areas').html(html);

						return true;
					}
				});
			})


			// $('#debug').on('click', function(e) {
			// 	e.preventDefault();
			// 	console.log('Valores do json: ' + $('#categories_json').val());
			// });

			// $('#empty').on('click', function(e) {
			// 	e.preventDefault();
			// 	categoriesControl(2);
				
			// });


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
					url: './choose_ticket_type_process.php',
					method: 'POST',
					// data: {
					// 	issue_type: $('#issue_type').val(),
					// 	params: $('#params').val()
					// },
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

						// let params = 'issue_type=' + response.issue_type + '&profile_id=' + response.profile_id;
						let params = 'issue_type=' + response.issue_type + '&profile_id=' + response.profile_id + response.params;
						let url = "./ticket_add.php?" + params;

						$(location).prop('href', url);
						return true;
					}
				});
				return false;
			});
		});



		function updateCategoriesValues(sufix, value) {

			let pre_filters = $('#pre_filters_json').val();

			let categories_json = $('#categories_json').val();

			// converter sufix e value em string
			sufix = sufix.toString();
			value = value.toString();

			if (categories_json.hasOwnProperty(sufix)) {
				categories_json[sufix] = value;
			} else {
				categories_json.sufix = value;
			}

			let sufixVal = sufix;

			$('#categories_json').val(categories_json);
		}


		/**
		 * Convert a text value to JSON, check if a given property exists, and update its value if it exists or create it if it doesn't.
		 *
		 * @param {string} textValue - The text value to convert to JSON.
		 * @param {string} property - The property to check or create.
		 * @param {*} value - The value to update the property with or create it with.
		 * @returns {string} - The updated JSON as a text value.
		 */
		function updateJSON(textValue, property, value) {

			let jsonObject = JSON.parse(textValue);

			if (jsonObject.hasOwnProperty(property)) {

				if (value != "") {
					jsonObject[property] = value;
				} else {
					delete jsonObject[property];
				}
			} else {
				if (value != "") {
					jsonObject[property] = value;
				} else {
					delete jsonObject[property];
				}
			}

			$('#categories_json').val(JSON.stringify(jsonObject));
			// return JSON.stringify(jsonObject);
		}



		function loadUsedCategories(categorieSufix, position) {

			if ($('#filter_' + categorieSufix).length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './get_possible_types_of_issues.php',
					method: 'POST',
					dataType: 'json',
					data: {
						categories: $("#categories_json").val(),
						output: 'categories',
						categorieSufix: categorieSufix,
						position: position
					},
				}).done(function(data) {

					$('#filter_' + categorieSufix).empty();
					if (Object.keys(data).length > 1) {
						$('#filter_' + categorieSufix).append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
					}
					$.each(data, function(key, data) {

						$('#filter_' + categorieSufix).append('<option value="' + data.id + '">' + data.name + '</option>');
					});
					$('#filter_' + categorieSufix).selectpicker('refresh');
				});
			}
		}



		function loadPossibleTypesOfIssues() {

			// if ($("#idLocal").length > 0) {
			var loading = $(".loading");
			$(document).ajaxStart(function() {
				loading.show();
			});
			$(document).ajaxStop(function() {
				loading.hide();
			});

			$.ajax({
				url: './get_possible_types_of_issues.php',
				method: 'POST',
				dataType: 'json',
				data: {
					categories: $("#categories_json").val()
				},
			}).done(function(data) {

				$('#issue_type').empty();
				if (Object.keys(data).length > 1) {
					$('#issue_type').append('<option value=""><?= TRANS("SEL_SELECT"); ?></option>');
				}
				
				$.each(data, function(key, data) {
					/* data-content="Methodology of social..." */
					$('#issue_type').append('<option value="' + data.prob_id + '">' + data.problema + '</option>');
				});
				
				$('#issue_type').prop('disabled', false);
				$('#issue_type').selectpicker('refresh');
			});
			// }
		}

		function categoriesControl (currentSufix) {
			
			$('#possible_areas').html('');
			$('#prob_description').html('');
			$('#issue_type').val('').selectpicker('refresh');

			let selectedEl = $('#filter_' + currentSufix);
			let selectedPos = parseInt(selectedEl.attr('data-pos'));
			let selectedValue = selectedEl.val();
			let nextSelectionEl = (selectedEl.attr('data-next') != "issue_type" ? $('#filter_' + selectedEl.attr('data-next')) : $('#issue_type'));

			updateJSON($('#categories_json').val(), selectedEl.attr('data-sufix'), selectedValue);

			if (nextSelectionEl != 'issue_type') {
				$('#issue_type').val('').prop('disabled', true).selectpicker('refresh');
				if (selectedValue != "") {
					loadUsedCategories(nextSelectionEl.attr('data-sufix'), nextSelectionEl.attr('data-pos'));
				} else {
					nextSelectionEl.val('').prop('disabled', true).selectpicker('refresh');
				}
			} else {
				$('#issue_type').val('').prop('disabled', false).selectpicker('refresh');
				loadPossibleTypesOfIssues();
			}


			$('.prefilter').each(function() {
				let currentEl = $(this);
				let currentPos = parseInt(currentEl.attr('data-pos'));
				if (currentPos > selectedPos) {
					currentEl.empty().selectpicker('refresh');
					
					updateJSON($('#categories_json').val(), currentEl.attr('data-sufix'), "");

					if (currentPos > (selectedPos + 1) ) {
						currentEl.prop('disabled', true).selectpicker('refresh');
					} else {
						loadUsedCategories(currentEl.attr('data-sufix'), currentPos);
					}
				} else if (currentPos == selectedPos) {
					updateJSON($('#categories_json').val(), currentEl.attr('data-sufix'), selectedValue);
					if (currentEl.val() != "") {
						if (currentEl.attr('data-next') != 'issue_type') {
							$('#filter_' + currentEl.attr('data-next')).val('').prop('disabled', false).selectpicker('refresh');
							
							let NextCategorieSufix = $('#filter_' + currentEl.attr('data-next')).attr('data-sufix');
							loadUsedCategories(NextCategorieSufix, $('#filter_' + currentEl.attr('data-next')).attr('data-pos'));
						} else {
							$('#issue_type').val('').prop('disabled', false).selectpicker('refresh');
							loadPossibleTypesOfIssues();
						}
					}
				}
			});
		}



	</script>
</body>

</html>