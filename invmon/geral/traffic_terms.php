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

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);


$allowGenerateTrafficTerm = true;
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
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos_custom.css" />

	<title><?= APP_NAME; ?>&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-dolly-flatbed text-secondary"></i>&nbsp;<?= TRANS('TRAFFIC_FORM'); ?></h4>
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


        $asset_id = (isset($_GET['asset_id']) ? noHtml($_GET['asset_id']) : '');
        if (empty($asset_id)) {
            echo message('danger', 'Ooops!', TRANS('MSG_ERR_NOT_EXECUTE'), '', '', 1);
            return;
        }

        $assetInfo = getAssetBasicInfo($conn, $asset_id);

        /**
         * Listar os formulários de trânsito já existentes
        */
        $trafficForms = [];
        $trafficForms = getTrafficInfoFilesFromAsset($conn, $asset_id);
        if (!empty($trafficForms)) {
            echo message('info', '', TRANS('MSG_ASSET_HAS_TRAFFIC_FORM'), '', '', 1);
            ?>
                <table class="table stripe hover order-column row-border" id="table_traffic_docs">
                    <thead>
                        <tr>
                            <td><?= TRANS('DOCUMENT'); ?></td>
                            <td><?= TRANS('DESTINATION'); ?></td>
                            <td><?= TRANS('CARRIER'); ?></td>
                            <td><?= TRANS('AUTHORIZED_BY'); ?></td>
                            <td><?= TRANS('RESPONSIBLE_AREA'); ?></td>
                            <td><?= TRANS('GENERATING_DATE'); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($trafficForms as $row) {
                                $authorizerInfo = getUserInfo($conn, $row['user_authorizer']);
                                $authorizerName = $authorizerInfo['nome'];

                                $areaInfo = getAreaInfo($conn, $row['responsible_area']);
                                $areaName = $areaInfo['area_name'];
                                ?>
                                    <tr>
                                        <td><a onClick="redirect('../../includes/functions/download_traffic_files.php?file_id= + <?= $row["file_id"]; ?>');" href="javascript:void(0);"><?= $row['file_name']; ?></a></td>
                                        <td><?= $row['destination']; ?></td>
                                        <td><?= $row['carrier']; ?></td>
                                        <td><?= $authorizerName; ?></td>
                                        <td><?= $areaName; ?></td>
                                        <td><?= dateScreen($row['created_at']); ?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
                <hr />
            <?php
        }

		?>

        <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
            
            <?= csrf_input(); ?>
        
            <div class="row mb-0 mt-4">
                <div class="col-md-12 mb-0">
                    <h5><?= TRANS('MSG_BELLOW_YOU_CAN_GENERATE_NEW_DOCUMENT'); ?></h5>
                </div>
            </div>
            
            <div class="form-group row mb-0 mt-4">
                
                <div class="form-group col-md-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <a href="javascript:void(0);" id="add_button_assets" class="add_button_assets" title="<?= TRANS('ADD'); ?>"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <input type="text" class="form-control" name="assetTag" id="assetTag" value="<?= $assetInfo['comp_inv']; ?>" disabled>
                    </div>
                    <small class="form-text text-muted"><?= TRANS('ASSET_TAG_TAG'); ?></small>
                </div>
            
                <div class="form-group col-md-4">
                    <select class="form-control bs-select sel-control" name="assetUnit" id="assetUnit" disabled>
                        <option data-subtext="<?= $assetInfo['cliente']; ?>" value="<?= $assetInfo['inst_cod']; ?>"><?= $assetInfo['inst_nome']; ?></option>
                    </select>
                    <small class="form-text text-muted"><?= TRANS('COL_UNIT'); ?></small>
                </div>
            
                <div class="form-group col-md-2">
                    <input type="text" class="form-control" name="assetDepartment" id="assetDepartment" value="<?= $assetInfo['local']; ?>" disabled>
                    <small class="form-text text-muted"><?= TRANS('DEPARTMENT'); ?></small>
                </div>


                <?php
                    $asset_description = $assetInfo['tipo_nome'] . '&nbsp;' . $assetInfo['fab_nome'] . '&nbsp;' . $assetInfo['marc_nome'];
                ?>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" name="assetBasicInfo" id="assetBasicInfo" value="<?= $asset_description; ?>" disabled>
                    <small class="form-text text-muted"><?= TRANS('ASSET_TYPE'); ?></small>
                </div>

                <!-- <div class="form-group col-md-2">
                    <input type="text" class="form-control" name="assetSn" id="assetSn" value="<?= $assetInfo['comp_sn']; ?>" disabled>
                    <small class="form-text text-muted"><?= TRANS('SERIAL_NUMBER'); ?></small>
                </div> -->
            </div>

            <div class="form-group row mt-0 mb-4 new_assets_row" id="new_assets_row"></div>

            <div class="form-group row mt-0 mb-4">
                <label for="carrier" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('CARRIER'); ?></label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" id="carrier" name="carrier" required placeholder="<?= TRANS('PLACEHOLDER_CARRIER'); ?>" />
                    <div class="invalid-feedback">
                        <?= TRANS('MANDATORY_FIELD'); ?>
                    </div>
                </div>
            
                <label for="destination" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('DESTINATION'); ?></label>
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" id="destination" name="destination" required placeholder="<?= TRANS('PLACEHOLDER_DESTINATION'); ?>" />
                    <div class="invalid-feedback">
                        <?= TRANS('MANDATORY_FIELD'); ?>
                    </div>
                </div>

                <label for="authorized_by" class="col-md-2 col-form-label text-md-right"><?= TRANS('AUTHORIZED_BY'); ?></label>
                <div class="form-group col-md-4">
                    <?php
                        $users = getUsers($conn);
                    ?>
                    <select class="form-control bs-select" id="authorized_by" name="authorized_by" >
                        <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        <?php
                            foreach ($users as $user) {
                                ?>
                                <option value="<?= $user['user_id']; ?>"><?= $user['nome']; ?></option>
                                <?php
                            }
                        ?>
                    </select>
                </div>

                <label for="responsible_area" class="col-md-2 col-form-label text-md-right"><?= TRANS('RESPONSIBLE_AREA'); ?></label>
                <div class="form-group col-md-4">
                    <?php
                        $areas = getAreas($conn, 0, 1, 1);
                    ?>
                    <select class="form-control bs-select" id="responsible_area" name="responsible_area" >
                        <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        <?php
                            foreach ($areas as $area) {
                                ?>
                                <option value="<?= $area['sis_id']; ?>"><?= $area['sistema']; ?></option>
                                <?php
                            }
                        ?>
                    </select>
                </div>

                <div class="w-100"></div>
                <label for="reason" class="col-sm-2 col-md-2 col-form-label text-md-right"><?= TRANS('COL_JUSTIFICATION'); ?></label>
                <div class="form-group col-md-10">
                    <textarea class="form-control" id="reason" name="reason" required placeholder="<?= TRANS('COL_JUSTIFICATION'); ?>" ></textarea>
                    <div class="invalid-feedback">
                        <?= TRANS('MANDATORY_FIELD'); ?>
                    </div>
                </div>


                <?php
                    $form_models = getCommitmentModels($conn, null, null, null, 2);
                ?>
                <label for="term_id" class="col-md-2 col-form-label text-md-right"><?= TRANS('FORM_MODEL'); ?></label>
                <div class="form-group col-md-10">
                    <select class="form-control bs-select" id="term_id" name="term_id" >
                        <option value=""><?= TRANS('GENERICAL'); ?></option>
                        <?php
                            foreach ($form_models as $model) {
                                
                                if ($model['id'] != 2) {
                                    ?>
                                        <option data-subtext="<?= $model['nickname']; ?>" value="<?= $model['id']; ?>"><?= $model['inst_nome']; ?></option>
                                    <?php
                                }
                            }
                        ?>
                    </select>
                    <small class="form-text text-muted"><?= TRANS('HELPER_FORM_MODEL'); ?></small>
                </div>
            </div>

            <div class="form-group row my-4 ">
                <div class="row w-100"></div>
                <div class="form-group col-md-7 d-none d-md-block">
                </div>
                <div class="form-group col-12 col-md-3 ">

                    <input type="hidden" name="action" id="action" value="new">
                    <input type="hidden" name="main_asset_id" id="main_asset_id" value="<?= $asset_id; ?>">
                    <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('GENERATE_NEW_DOCUMENT'); ?></button>
                </div>
                <div class="form-group col-12 col-md-2">
                    <button type="reset" class="btn btn-secondary btn-block close-or-return"><?= TRANS('BT_CANCEL'); ?></button>
                </div>
            </div>



        </form>


    </div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/jquery/jquery.initialize.min.js"></script>
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script src="../../includes/components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<script type="text/javascript">
		$(function() {

            if ($('#table_traffic_docs').length > 0) {
                $('#table_traffic_docs').DataTable({
                    paging: true,
                    deferRender: true,
                    // order: [3, 'desc'],
                    columnDefs: [{
                        searchable: false,
                        orderable: false,
                        targets: ['editar', 'remover']
                    }],
                    "language": {
                        "url": "../../includes/components/datatables/datatables.pt-br.json"
                    }
                });
            }
            

            $('.bs-select').selectpicker({
                /* placeholder */
                noneSelectedText: "<?= TRANS('SEL_SELECT', '', 1); ?>",
                liveSearch: true,
                liveSearchNormalize: true,
                showSubtext: true,
                liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                style: "",
                styleBase: "form-control ",
            });
            
            closeOrReturn ();

            $('#add_button_assets').on('click', function() {
				loadNewAssetRow();
			});

            $('.new_assets_row').on('click', '.remove_button_assets', function(e) {
                e.preventDefault();
				dataRandom = $(this).attr('data-random');
				$("."+dataRandom).remove();
            });


            if ($('#new_assets_row').length > 0) {
                /* Adicionei o mutation observer em função dos elementos que são adicionados após o carregamento do DOM */
                var obs = $.initialize(".after-dom-ready", function() {
					$('.bs-select').selectpicker({
                        /* placeholder */
                        noneSelectedText: "<?= TRANS('SEL_SELECT', '', 1); ?>",
                        liveSearch: true,
                        liveSearchNormalize: true,
                        showSubtext: true,
                        liveSearchPlaceholder: "<?= TRANS('BT_SEARCH', '', 1); ?>",
                        noneResultsText: "<?= TRANS('NO_RECORDS_FOUND', '', 1); ?> {0}",
                        style: "",
                        styleBase: "form-control ",
                    });

                    $('.asset_tag_class').on('change', function() {

						var myId = $(this).attr('id');
                        console.log('meu ID: ' + myId);
						loadUnitsByAssetTag(myId);
                        loadAssetBasicInfo(myId);
					});

                    $('.asset_unit_class').on('change', function() {
                        let randomId = $(this).attr('data-base-random');
                        loadAssetBasicInfo(randomId, $('#' + randomId).val(), $(this).val());
                    });

                }, {
                    target: document.getElementById('new_assets_row')
                }); /* o target limita o scopo do observer */
            }

            $('#idSubmit').on('click', function(e) {
                e.preventDefault();
                trafficTermProcess();
            });


			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});
		});


        function loadNewAssetRow() {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './render_new_traffic_asset_row.php',
                method: 'POST',
                data: {
                    random: Math.random().toString(16).substr(2, 8)
                },
                // dataType: 'json',
            }).done(function(data) {
                $('#new_assets_row').append(data);
            });
            return false;
		}

        function loadUnitsByAssetTag(elementID = "assetTag") {
			// if ($('#asset_unit').length > 0) {
			if ($('#' + elementID).length > 0) {
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './../../admin/geral/get_units_by_asset_tag.php',
					method: 'POST',
					data: {
						asset_tag: $('#'+elementID).val(),
                        user_id: $('#cod').val(),
                        fromAnyClient: 1
					},
					dataType: 'json',
				}).done(function(data) {
					$('#divResult').html('');
                    
                    let html = '';
                    $('#'+elementID+'_asset_unit').empty().html(html);
                    $('#'+elementID+'_asset_unit').selectpicker('refresh');

                    if (data.length > 1) {
                        html += '<option value=""><?= TRANS("SEL_SELECT"); ?></option>';
                        for (i in data) {
							html += '<option data-subtext="' + data[i].cliente + '" value="' + data[i].inst_cod + '">' + data[i].inst_nome + '</option>';
						}
                        $('#'+elementID+'_asset_unit').empty().html(html);
					    $('#'+elementID+'_asset_unit').selectpicker('refresh');

                    } else

					if (data.length == 1) {
                        /* Nesse caso, já traz o elemento selecionado */
                        for (i in data) {
							html += '<option data-subtext="' + data[i].cliente + '" value="' + data[i].inst_cod + '">' + data[i].inst_nome + '</option>';
						}
                            
                        $('#'+elementID+'_asset_unit').empty().html(html);
                        $('#'+elementID+'_asset_unit').selectpicker('refresh');
                        $('#'+elementID+'_asset_unit').selectpicker('val', data[0].inst_cod);

                        loadAssetBasicInfo(elementID, $('#'+elementID).val(), data[0].inst_cod);
					} else {
                        
                        var html_message = '<div class="d-flex justify-content-center">';
                        html_message += '<div class="d-flex justify-content-center my-3" style="max-width: 100%; position: fixed; top: 1%; z-index:1030 !important;">';
                        html_message += '<div class="alert alert-warning alert-dismissible fade show w-100" role="alert">';
                        html_message += '<i class="fas fa-exclamation-circle"></i> ';
                        html_message += '<strong>Ooops!</strong> ';
                        html_message += '<?= TRANS('MSG_NO_ASSETS_FOUND_WITH_TAG_AND_CLIENT'); ?>';
                        html_message += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                        html_message += '<span aria-hidden="true">&times;</span>';
                        html_message += '</button>';
                        html_message += '</div></div></div>';
                        
                        $('#divResult').html(html_message);
                    }
				});
				return false;
			}
		}


        function loadAssetBasicInfo (elementID, assetTag, assetUnit) {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            if (assetTag == "") {
                $('#'+elementID+'_asset_department').val('');
                $('#'+elementID+'_asset_desc').val('');
                // $('#'+elementID+'_asset_sn').val('');
            }

            $.ajax({
                url: './../../admin/geral/get_asset_basic_info.php',
                method: 'POST',
                data: {
                    asset_tag: assetTag,
                    asset_unit: assetUnit
                },
                dataType: 'json',
            }).done(function(data) {

                if (!jQuery.isEmptyObject(data)) {
                    $('#'+elementID+'_asset_department').val(data.local);
                
                    let assetDesc = data.tipo_nome + ' ' + data.fab_nome + ' ' + data.marc_nome;
                
                    $('#'+elementID+'_asset_desc').val(assetDesc);
                    // $('#'+elementID+'_asset_sn').val(data.comp_sn);
                } else {
                    $('#'+elementID+'_asset_department').val('');
                    $('#'+elementID+'_asset_desc').val('');
                    // $('#'+elementID+'_asset_sn').val('');

                }
            });
		};


        function trafficTermProcess(numero) {
            var loading = $(".loading");
            $(document).ajaxStart(function() {
                loading.show();
            });
            $(document).ajaxStop(function() {
                loading.hide();
            });

            $.ajax({
                url: './traffic_term_process.php',
                method: 'POST',
                dataType: 'json',
                data: $('#form').serialize(),
            }).done(function(response) {
                if (!response.success) {

                    if (response.field_id != "") {
                        $('#' + response.field_id).focus().addClass('is-invalid');
                    }
                    $('#divResult').html(response.message);
                } else {
                    // $('#modalAssets').modal('hide');
                    location.reload();
                }
            });
            return false;
        }

        
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